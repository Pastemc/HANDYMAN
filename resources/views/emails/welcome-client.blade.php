<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 40px 30px;
        }
        .credentials {
            background: #f8f9fa;
            padding: 25px;
            border-left: 4px solid #667eea;
            margin: 25px 0;
            border-radius: 5px;
        }
        .credentials h3 {
            margin-top: 0;
            color: #667eea;
        }
        .credential-row {
            margin: 15px 0;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .credential-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #666;
            display: inline-block;
            width: 100px;
        }
        .value {
            color: #333;
            font-weight: 600;
        }
        .button {
            display: inline-block;
            padding: 15px 40px;
            background: #667eea;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
            text-align: center;
        }
        .button:hover {
            background: #5568d3;
        }
        .alert {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .alert strong {
            color: #856404;
        }
        .footer {
            background: #f4f4f4;
            padding: 30px;
            text-align: center;
        }
        .footer p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">🎉</div>
            <h1>¡Bienvenido a Heaven's Home!</h1>
            <p>Tu cuenta ha sido aprobada</p>
        </div>
        
        <div class="content">
            <p>Hola <strong>{{ $user->name }}</strong>,</p>
            
            <p>¡Nos complace informarte que tu solicitud de registro ha sido <strong style="color: #28a745;">APROBADA</strong>!</p>
            
            <p>Ya puedes acceder a nuestra plataforma y solicitar nuestros servicios profesionales de mantenimiento y reparación.</p>
            
            <div class="credentials">
                <h3>🔐 Tus Credenciales de Acceso:</h3>
                <div class="credential-row">
                    <span class="label">Email:</span>
                    <span class="value">{{ $user->email }}</span>
                </div>
                <div class="credential-row">
                    <span class="label">Contraseña:</span>
                    <span class="value">{{ $password }}</span>
                </div>
            </div>
            
            <div class="alert">
                <strong>⚠️ Importante:</strong> Por seguridad, te recomendamos cambiar tu contraseña después del primer inicio de sesión.
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ url('/login') }}" class="button">
                    🚀 Iniciar Sesión Ahora
                </a>
            </div>

            <h3 style="color: #667eea; margin-top: 40px;">✨ ¿Qué puedes hacer ahora?</h3>
            <ul style="line-height: 2;">
                <li>📝 Crear solicitudes de servicio</li>
                <li>👷 Ver handymen disponibles</li>
                <li>💬 Comunicarte con nuestro equipo</li>
                <li>💵 Ver costos estimados y finales</li>
                <li>⭐ Dejar reseñas de servicios</li>
            </ul>
            
            <p style="margin-top: 30px;">
                Si tienes alguna pregunta, no dudes en contactarnos respondiendo a este correo.
            </p>
            
            <p style="margin-top: 30px;">
                Saludos cordiales,<br>
                <strong>El equipo de Heaven's Home</strong>
            </p>
        </div>
        
        <div class="footer">
            <p><strong>Heaven's Home Expert Handyman Services</strong></p>
            <p>📧 claude271109@gmail.com | 📱 +51 999 999 999</p>
            <p>© {{ date('Y') }} Heaven's Home. Todos los derechos reservados.</p>
            <p style="font-size: 12px; color: #999; margin-top: 15px;">
                Este es un correo automático, por favor no responder directamente.
            </p>
        </div>
    </div>
</body>
</html>