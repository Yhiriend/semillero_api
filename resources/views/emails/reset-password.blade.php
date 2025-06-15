<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recuperación de contraseña</title>
</head>
<body style="background: #f4f4f7; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif;">
    <div style="max-width: 480px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 32px 24px; text-align: center;">
        <div style="font-size: 40px; margin-bottom: 8px;">👀</div>
        <h1 style="margin: 0 0 16px 0; font-size: 2em; font-weight: bold; color: #222;">Restablecer contraseña</h1>
        <p style="font-size: 1.1em; color: #222; font-weight: 600; margin-bottom: 8px;">Alguien ha solicitado restablecer la contraseña de la siguiente cuenta:</p>
        <p style="color: #444; margin-bottom: 24px;">Para restablecer tu contraseña, haz clic en el siguiente botón:</p>
        <a href="{{ $resetUrl }}" style="display: inline-block; padding: 14px 28px; background-color: #2563eb; color: #fff; text-decoration: none; border-radius: 6px; font-size: 1.1em; font-weight: 600; margin-bottom: 18px;">Haz clic aquí para restablecer tu contraseña</a>
        <p style="margin: 24px 0 8px 0; color: #222;">Tu correo: <span style="color: #2563eb; font-weight: 500;">{{ $email ?? 'tu_correo@ejemplo.com' }}</span></p>
        <p style="color: #888; font-size: 0.98em; margin-bottom: 0;">Si no solicitaste este cambio, puedes ignorar este correo y no pasará nada.</p>
        <p style="color: #888; font-size: 0.98em; margin-top: 8px;">Este enlace expirará en una hora.</p>
    </div>
    <div style="text-align: center; color: #bbb; font-size: 0.95em; margin-top: 24px;">
        Copyright © {{ date('Y') }} <b>Semillero</b>. Todos los derechos reservados.
    </div>
</body>
</html>
