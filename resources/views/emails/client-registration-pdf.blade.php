<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background: #007bff;
            color: white;
            padding: 10px;
            text-align: left;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Heaven's Home Expert Handyman Services</div>
        <p>Solicitud de Registro de Cliente</p>
        <p>Fecha: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <tr>
            <th colspan="2">INFORMACIÓN PERSONAL</th>
        </tr>
        <tr>
            <td width="30%"><strong>Nombre Completo:</strong></td>
            <td>{{ $registration->name }}</td>
        </tr>
        <tr>
            <td><strong>Email:</strong></td>
            <td>{{ $registration->email }}</td>
        </tr>
        <tr>
            <td><strong>Teléfono:</strong></td>
            <td>{{ $registration->phone }}</td>
        </tr>
        <tr>
            <td><strong>Tipo de Documento:</strong></td>
            <td>{{ strtoupper($registration->document_type) }}</td>
        </tr>
        <tr>
            <td><strong>Número de Documento:</strong></td>
            <td>{{ $registration->document_number }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <th colspan="2">INFORMACIÓN DE CONTACTO</th>
        </tr>
        <tr>
            <td width="30%"><strong>Dirección:</strong></td>
            <td>{{ $registration->address }}</td>
        </tr>
        <tr>
            <td><strong>Ciudad:</strong></td>
            <td>{{ $registration->city }}</td>
        </tr>
        <tr>
            <td><strong>Estado:</strong></td>
            <td>{{ $registration->state }}</td>
        </tr>
        <tr>
            <td><strong>Código Postal:</strong></td>
            <td>{{ $registration->zip_code }}</td>
        </tr>
    </table>

    @if($registration->serviceCategory)
    <table>
        <tr>
            <th colspan="2">SERVICIO DE INTERÉS</th>
        </tr>
        <tr>
            <td width="30%"><strong>Categoría:</strong></td>
            <td>{{ $registration->serviceCategory->name }}</td>
        </tr>
    </table>
    @endif

    @if($registration->message)
    <table>
        <tr>
            <th>MENSAJE DEL CLIENTE</th>
        </tr>
        <tr>
            <td>{{ $registration->message }}</td>
        </tr>
    </table>
    @endif

    <div class="footer">
        <p>Este documento fue generado automáticamente por el sistema Heaven's Home</p>
        <p>© {{ date('Y') }} Heaven's Home Expert Handyman Services - Todos los derechos reservados</p>
    </div>
</body>
</html>