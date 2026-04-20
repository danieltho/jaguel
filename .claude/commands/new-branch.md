---
description: Analiza los cambios actuales y crea un branch con nombre apropiado siguiendo Conventional Branch
---

Tu tarea es crear un nuevo branch analizando los cambios pendientes en el working directory.

## Paso 1: Verificar el estado del repo

Ejecutá estos comandos para entender el contexto:

1. `git status` — ver qué archivos cambiaron
2. `git diff HEAD` — ver los cambios sin commitear (tracked)
3. `git diff` — ver cambios no staged
4. `git diff --staged` — ver cambios staged
5. `git branch --show-current` — ver el branch actual

Si no hay cambios pendientes, avisale al usuario y preguntale qué branch quiere crear manualmente (tipo + descripción).

Si el branch actual NO es `main`, `master` o `develop`, avisale al usuario y preguntale si quiere:
- Volver al branch base y crear el nuevo desde ahí
- Crear el nuevo branch desde el actual de todas formas

## Paso 2: Analizar los cambios

Revisá los diffs y determiná:

### Tipo de branch (elegí UNO):
- `feature/` — se agregan nuevas funcionalidades, archivos nuevos con lógica de negocio, nuevos endpoints, componentes, etc.
- `fix/` — se corrigen bugs, se arreglan condiciones erróneas, se ajusta lógica rota
- `hotfix/` — fix urgente, típicamente cambios pequeños y quirúrgicos en código crítico
- `chore/` — cambios de configuración, dependencias (package.json, lock files), CI/CD, linters, formatters
- `docs/` — solo cambios en README, comentarios, archivos .md, documentación
- `refactor/` — reorganización de código sin cambio funcional (renombrar, extraer funciones, mover archivos)
- `test/` — solo se agregan o modifican archivos de test

### Señales para decidir:
- Archivos nuevos en `src/` con lógica → probablemente `feature`
- Cambios solo en `*.test.*`, `*.spec.*`, `__tests__/` → `test`
- Solo `package.json`, `package-lock.json`, `.github/`, `.eslintrc`, etc. → `chore`
- Solo archivos `.md` → `docs`
- Diffs que eliminan/corrigen condiciones, manejo de errores, valores incorrectos → `fix`
- Mismo comportamiento pero código reorganizado → `refactor`

Si hay señales mixtas, elegí el tipo que represente el cambio principal/mayoritario.

### Descripción:
Basándote en los archivos modificados y el contenido del diff, generá una descripción corta (3-5 palabras) en kebab-case, en inglés, que describa el propósito del cambio.

Ejemplos:
- Agregaron un componente de login → `add-login-component`
- Arreglaron un null check en checkout → `fix-checkout-null-check`
- Actualizaron dependencias → `update-dependencies`
- Agregaron tests al auth service → `add-auth-service-tests`

## Paso 3: Proponer el nombre

Mostrale al usuario:

1. Un resumen de los cambios detectados (2-3 líneas máximo)
2. El nombre de branch propuesto en formato: `<tipo>/<descripcion>`
3. El razonamiento corto (por qué ese tipo)

Ejemplo de output: