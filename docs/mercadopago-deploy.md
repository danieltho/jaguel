# Despliegue Mercado Pago — Checkout Pro AR

Checklist para pasar la integración a producción. Las credenciales se administran desde el panel admin (`/backend/mercado-pago-settings`); las vars de `.env` quedan como fallback.

## 1. Credenciales en Mercado Pago

1. Entrar a https://www.mercadopago.com.ar/developers/panel
2. Seleccionar la **aplicación de producción** (o crear una nueva).
3. Copiar de la sección **Credenciales de producción**:
   - `Access Token` (empieza con `APP_USR-...`)
   - `Public Key` (empieza con `APP_USR-...`)
4. Para tests previos, usar **Credenciales de prueba** (`TEST-...`).

## 2. Configurar Webhook en panel MP

1. En el panel MP → **Webhooks** → **Configurar notificaciones**.
2. URL de notificación:
   ```
   https://TU_DOMINIO/webhook/mercadopago
   ```
3. Eventos a suscribir: **Pagos** (`payment`).
4. Generar y copiar el **Secret** (se usa para validar la firma `x-signature`).
5. Probar con el botón **Simular** del panel MP — debería devolver `200 {"ok":true}`.

> ⚠️ MP rechaza URLs HTTP o sin DNS público. El dominio debe tener HTTPS válido.

## 3. Variables de entorno en producción

Editar `.env` en el servidor:

```env
MERCADOPAGO_ACCESS_TOKEN=APP_USR-...        # fallback si Settings page no tiene valor
MERCADOPAGO_PUBLIC_KEY=APP_USR-...
MERCADOPAGO_WEBHOOK_SECRET=                 # opcional, lo ideal es cargarlo desde Settings
MERCADOPAGO_ENVIRONMENT=production
```

Tras editar:

```bash
php artisan config:cache
php artisan route:cache
```

## 4. Cargar credenciales en el panel admin

1. Loguearse como admin en `/backend`.
2. Ir a **Configuración → Mercado Pago**.
3. Cargar:
   - **Ambiente**: `Producción`
   - **Public Key**: el `APP_USR-...`
   - **Access Token**: el `APP_USR-...` (se guarda encriptado)
   - **Webhook Secret**: el secret generado en el paso 2 (encriptado)
4. Guardar y verificar en `settings` table que `value` aparece como string encriptado (no en plano).

## 5. Métodos de pago habilitados

Ir a **Configuración → Medios de Pago** y asegurar que existe al menos un registro con:
- `type` = `credit_card`
- `is_active` = true
- `max_installments` configurado

Los métodos no-MP (`bank_transfer`, `cash_showroom`) siguen funcionando sin tocar nada.

## 6. Queue worker y scheduler

La integración encola `ProcessMpWebhook` para procesar pagos en background. Asegurar que estén corriendo:

```bash
# Worker (un servicio supervisord o similar)
php artisan queue:work --queue=default --tries=5 --sleep=3

# Cron (en /etc/crontab del servidor)
* * * * * cd /var/www/jaguelweb && php artisan schedule:run >> /dev/null 2>&1
```

El scheduler corre `mp:reconcile` cada 15 minutos para cubrir webhooks perdidos.

## 7. Verificación end-to-end

1. **Sandbox primero** (`MERCADOPAGO_ENVIRONMENT=sandbox` + credenciales TEST):
   - Hacer una compra real con [tarjetas de prueba AR](https://www.mercadopago.com.ar/developers/es/docs/checkout-pro/additional-content/test-cards).
   - Verificar que `payment_status` de la order cambia a `paid`.
   - Verificar que aparece un registro en `payment_transactions` con `mp_status = approved`.
2. **Producción** (con un pago real pequeño):
   - Repetir el flujo con tarjeta real.
   - Reembolsar desde Filament → confirmar que el monto vuelve al comprador.

## 8. Monitoreo post-deploy

Primer día de operación:
- Revisar `storage/logs/laravel.log` por warnings `MercadoPago webhook: invalid signature` (si aparecen, alguien está pegándole a la URL sin firma — o el secret está mal cargado).
- Revisar dashboard de Filament:
  - Widget **Pagos pendientes con más de 1 hora** debería estar vacío en condiciones normales.
  - Widget **Pagos aprobados últimos 30 días** debería mostrar actividad.
- Si una orden queda en `pending` más de 30 min, `mp:reconcile` la cerrará automáticamente.

## 9. Rollback

Si hay que volver atrás sin perder datos:

1. En `.env` cambiar `MERCADOPAGO_ENVIRONMENT=sandbox` y poner credenciales TEST.
2. En el panel MP, **deshabilitar** la URL de webhook de producción (o cambiarla a un dominio dummy).
3. Los pagos en curso se marcarán como `failed` por el scheduler `mp:reconcile`. Refunds manuales desde el panel MP.

## Referencias

- Service: [app/Services/MercadoPagoService.php](../app/Services/MercadoPagoService.php)
- Job: [app/Jobs/ProcessMpWebhook.php](../app/Jobs/ProcessMpWebhook.php)
- Comando: `php artisan mp:reconcile --minutes=30 --limit=50`
- Settings page: `/backend/mercado-pago-settings`
- Transacciones: `/backend/payment-transactions`
- Webhook URL: `POST /webhook/mercadopago` (rate limit 120/min, sin CSRF)
