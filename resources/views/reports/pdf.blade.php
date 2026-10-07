<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $t['heavens_home_report'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 11px;
            color: #2c3e50;
            padding: 0;
            background: white;
        }
        .page {
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        /* HEADER PROFESIONAL */
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 25px;
            border-bottom: 4px solid #667eea;
            position: relative;
        }
        .logo {
            font-size: 42px;
            margin-bottom: 5px;
            color: #667eea;
        }
        .header h1 {
            color: #667eea;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .header .subtitle {
            font-size: 16px;
            color: #666;
            font-weight: 500;
            margin-bottom: 12px;
        }
        .period-info {
            background: #f8f9fa;
            display: inline-block;
            padding: 10px 25px;
            border-radius: 25px;
            margin-top: 10px;
            border: 2px solid #667eea;
        }
        .period-info strong {
            color: #667eea;
            font-weight: 700;
        }
        .generated-info {
            margin-top: 12px;
            font-size: 9px;
            color: #999;
        }

        /* SECCIONES */
        .section {
            margin-bottom: 35px;
            page-break-inside: avoid;
        }
        .section-header {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 20px;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 20px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 3px 8px rgba(102, 126, 234, 0.25);
        }

        /* STATS BOX - DISEÑO MEJORADO */
        .stats-container {
            background: #ffffff;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
        }
        .stat-row {
            display: table;
            width: 100%;
            border-bottom: 1px solid #e9ecef;
        }
        .stat-row:last-child {
            border-bottom: none;
        }
        .stat-row:nth-child(even) {
            background: #f8f9fa;
        }
        .stat-cell {
            display: table-cell;
            padding: 14px 20px;
            vertical-align: middle;
        }
        .stat-cell.label {
            width: 60%;
            font-weight: 600;
            color: #495057;
        }
        .stat-cell.value {
            width: 40%;
            text-align: right;
            font-weight: 700;
            font-size: 13px;
            color: #667eea;
        }
        .stat-icon {
            display: inline-block;
            width: 10px;
            height: 10px;
            background: #667eea;
            border-radius: 50%;
            margin-right: 10px;
            vertical-align: middle;
        }
        .value-highlight {
            background: linear-gradient(120deg, #fff3cd 0%, #ffc107 50%, #fff3cd 100%);
            padding: 4px 10px;
            border-radius: 4px;
            color: #856404;
            font-weight: 800;
        }

        /* TABLA PROFESIONAL */
        .table-container {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }
        th {
            color: white;
            padding: 14px 16px;
            text-align: left;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        td {
            padding: 12px 16px;
            border-bottom: 1px solid #e9ecef;
            font-size: 11px;
        }
        tbody tr:last-child td {
            border-bottom: none;
        }
        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        tbody tr:hover {
            background: #e8eaf6;
        }
        td.category-name {
            font-weight: 600;
            color: #2c3e50;
        }
        td.number {
            text-align: center;
            font-weight: 600;
        }
        td.currency {
            text-align: right;
            font-weight: 700;
        }
        .currency-highlight {
            background: linear-gradient(120deg, #fff3cd 0%, #ffc107 50%, #fff3cd 100%);
            padding: 3px 8px;
            border-radius: 4px;
            color: #856404;
            display: inline-block;
        }
        .rating-value {
            color: #667eea;
            font-weight: 700;
        }
        .star {
            color: #ffc107;
            font-size: 12px;
        }
        .no-data {
            text-align: center;
            color: #adb5bd;
            font-style: italic;
            padding: 30px;
            background: #f8f9fa;
        }

        /* FOOTER PROFESIONAL */
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 3px solid #667eea;
            text-align: center;
        }
        .footer-brand {
            font-size: 13px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .footer-text {
            font-size: 9px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- HEADER -->
        <div class="header">
            <div class="logo">🔧</div>
            <h1>HEAVEN'S HOME</h1>
            <div class="subtitle">{{ $t['operations_report'] }}</div>
            <div class="period-info">
                <strong>{{ $t['period'] }}:</strong> {{ $startDate }} - {{ $endDate }}
            </div>
            <div class="generated-info">
                {{ $t['generated_at'] }}: {{ $generatedAt }}
            </div>
        </div>

        <!-- RESUMEN DEL PERÍODO -->
        <div class="section">
            <div class="section-header">{{ $t['period_summary'] }}</div>
            <div class="stats-container">
                <div class="stat-row">
                    <div class="stat-cell label">
                        <span class="stat-icon"></span>{{ $t['start_date'] }}
                    </div>
                    <div class="stat-cell value">
                        {{ $startDate }}
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-cell label">
                        <span class="stat-icon"></span>{{ $t['end_date'] }}
                    </div>
                    <div class="stat-cell value">
                        {{ $endDate }}
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-cell label">
                        <span class="stat-icon"></span>{{ $t['total_revenue'] }}
                    </div>
                    <div class="stat-cell value">
                        <span class="value-highlight">${{ number_format($data['summary']['total_revenue'], 2) }}</span>
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-cell label">
                        <span class="stat-icon"></span>{{ $t['platform_revenue'] }}
                    </div>
                    <div class="stat-cell value">
                        ${{ number_format($data['summary']['platform_revenue'], 2) }}
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-cell label">
                        <span class="stat-icon"></span>{{ $t['total_jobs'] }}
                    </div>
                    <div class="stat-cell value">
                        {{ $data['summary']['total_jobs'] }}
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-cell label">
                        <span class="stat-icon"></span>{{ $t['average_rating'] }}
                    </div>
                    <div class="stat-cell value">
                        {{ number_format($data['summary']['average_rating'], 2) }} <span class="star">★</span> / 5.0
                    </div>
                </div>
            </div>
        </div>

        <!-- SERVICIOS MÁS SOLICITADOS -->
        <div class="section">
            <div class="section-header">{{ $t['top_services'] }}</div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40%;">{{ $t['category'] }}</th>
                            <th style="width: 20%; text-align: center;">{{ $t['total_requests'] }}</th>
                            <th style="width: 20%; text-align: right;">{{ $t['total_revenue'] }}</th>
                            <th style="width: 20%; text-align: center;">{{ $t['average_rating'] }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['most_requested_services'] as $service)
                        <tr>
                            <td class="category-name">{{ $service['category_name'] }}</td>
                            <td class="number">{{ $service['total_requests'] }}</td>
                            <td class="currency">
                                <span class="currency-highlight">${{ number_format($service['total_revenue'], 2) }}</span>
                            </td>
                            <td class="number">
                                <span class="rating-value">{{ number_format($service['average_rating'], 2) }}</span> <span class="star">★</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="no-data">{{ $t['no_data'] }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <div class="footer-brand">{{ $t['footer_text'] }}</div>
            <div class="footer-text">{{ $t['auto_generated'] }} {{ $generatedAt }}</div>
        </div>
    </div>
</body>
</html>