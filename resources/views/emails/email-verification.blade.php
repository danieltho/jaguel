<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tu código de verificación</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f6f6f6; margin: 0; padding: 24px;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden;">
        <tr>
            <td style="padding: 32px; text-align: center;">
                <h1 style="margin: 0 0 16px; font-size: 22px; color: #1a1a1a;">Tu código de verificación</h1>
                <p style="margin: 0 0 24px; color: #555; font-size: 15px; line-height: 1.5;">
                    Usá este código para continuar con tu compra:
                </p>
                <div style="font-size: 36px; letter-spacing: 8px; font-weight: 700; color: #1a1a1a; background: #f1f5f9; padding: 20px; border-radius: 8px; display: inline-block;">
                    {{ $code }}
                </div>
                <p style="margin: 24px 0 0; color: #888; font-size: 13px;">
                    El código vence en 1 hora. Si no realizaste esta solicitud, ignorá este mensaje.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
