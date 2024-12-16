<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1A73E8;
            --secondary-color: #34495E;
            --background-light: #F4F6F9;
            --text-dark: #2C3E50;
            --text-muted: #7f8c8d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            line-height: 1.6;
            background-color: var(--background-light);
            color: var(--text-dark);
        }

        .container {
            max-width: 1200px;
            width: 95%;
            margin: 40px auto;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.8);
        }

        .table-wrapper {
            padding: 30px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        table thead {
            background-color: var(--secondary-color);
            color: white;
        }

        table th, table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }

        table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        table tr:last-child td {
            border-bottom: none;
        }

        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .total-wrapper {
            background-color: #f1f5f9;
            padding: 25px 30px;
            margin: 0 30px 30px;
            border-radius: 12px;
        }

        .total-wrapper h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .total-wrapper h3 i {
            margin-right: 10px;
        }

        .total-wrapper p {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            font-size: 16px;
            color: var(--text-dark);
        }

        .total-wrapper p strong {
            color: var(--secondary-color);
        }

        .footer {
            background-color: #f1f5f9;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: var(--text-muted);
        }

        .footer .company {
            font-weight: 700;
            color: var(--primary-color);
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .container {
                width: 98%;
                margin: 20px auto;
            }

            .header h1 {
                font-size: 24px;
            }

            table {
                font-size: 12px;
            }

            .table-wrapper, .total-wrapper {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $title }}</h1>
            <p>{{ \Carbon\Carbon::now()->format('F Y') }}</p>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jumlah Item Terjual</th>
                        <th>Pendapatan Kotor</th>
                        <th>Total Harga Modal</th>
                        <th>Pendapatan Bersih</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->date)->format('d-m-Y') }}</td>
                            <td>{{ $row->total_item }}</td>
                            <td>Rp {{ number_format($row->pendapatan_kotor, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($row->total_harga_modal, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($row->pendapatan_bersih, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="total-wrapper">
            <h3><i class="fas fa-chart-line"></i>Total Keseluruhan</h3>
            <p>
                <strong>Pendapatan Kotor</strong>
                <span>Rp {{ number_format($totals['pendapatan_kotor'], 0, ',', '.') }}</span>
            </p>
            <p>
                <strong>Total Harga Modal</strong>
                <span>Rp {{ number_format($totals['total_harga_modal'], 0, ',', '.') }}</span>
            </p>
            <p>
                <strong>Pendapatan Bersih</strong>
                <span>Rp {{ number_format($totals['pendapatan_bersih'], 0, ',', '.') }}</span>
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ \Carbon\Carbon::now()->format('Y') }} All Rights Reserved</p>
            <div class="company">Toko Bu Budi | TUAN MUDA | Andhika Tri Hazra</div>
        </div>
    </div>
</body>
</html>