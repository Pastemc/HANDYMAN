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
        .email-container {
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
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .info-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .info-row {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #667eea;
            display: inline-block;
            width: 150px;
        }
        .value {
            color: #333;
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            background: #667eea;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background: #5568d3;
        }
        .footer {
            background: #f4f4f4;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .alert {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🔔 Nuevo Registro de Cliente</h1>
        </div>
        
        <div class="content">
            <p>Se ha recibido una nueva solicitud de registro de cliente:</p>
            
            <div class="info-section">
                <h3 style="margin-top: 0; color: #667eea;">Información Personal</h3>
                <div class="info-row">
                    <span class="label">Nombre:</span>
                    <span class="value">{{ $registration->name }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span class="value">{{ $registration->email }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Teléfono:</span>
                    <span class="value">{{ $registration->phone }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Documento:</span>
                    <span class="value">{{ strtoupper($registration->document_type) }} - {{ $registration->document_number }}</span>
                </div>
            </div>

            <div class="info-section">
                <h3 style="margin-top: 0; color: #667eea;">Ubicación</h3>
                <div class="info-row">
                    <span class="label">Dirección:</span>
                    <span class="value">{{ $registration->address }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Ciudad:</span>
                    <span class="value">{{ $registration->city }}, {{ $registration->state }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Código Postal:</span>
                    <span class="value">{{ $registration->zip_code }}</span>
                </div>
            </div>

            @if($registration->serviceCategory)
            <div class="info-section">
                <h3 style="margin-top: 0; color: #667eea;">Servicio de Interés</h3>
                <div class="info-row">
                    <span class="value">{{ $registration->serviceCategory->name }}</span>
                </div>
            </div>
            @endif

            @if($registration->message)
            <div class="info-section">
                <h3 style="margin-top: 0; color: #667eea;">Mensaje</h3>
                <p style="margin: 0;">{{ $registration->message }}</p>
            </div>
            @endif

            <div class="alert">
                <strong>⚠️ Acción Requerida:</strong> Por favor revisa esta solicitud e inicia sesión en el sistema para aprobarla o rechazarla.
            </div>

            <div style="text-align: center;">
                <a href="{{ url('/login') }}" class="button">
                    🔐 Iniciar Sesión en el Sistema
                </a>
            </div>

            <p style="margin-top: 30px; color: #666; font-size: 14px;">
                <strong>Nota:</strong> Un PDF con toda la información está adjunto a este correo para tu referencia.
            </p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} Heaven's Home Expert Handyman Services</p>
            <p>Este es un correo automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>