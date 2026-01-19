<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Prestasi Mahasiswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .stat-box {
            text-align: center;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 4px;
            width: 18%;
            display: inline-block;
        }
        .stat-box .value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .stat-box .label {
            font-size: 9px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background: #f0f0f0;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background: #fafafa;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PRESTASI MAHASISWA</h1>
        <p>Sistem Prestasi Unpatti - Dicetak pada {{ now()->format('d F Y H:i') }}</p>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="value">{{ $statistics['total'] }}</div>
            <div class="label">Total Prestasi</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ $statistics['pending'] }}</div>
            <div class="label">Pending</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ $statistics['approved'] }}</div>
            <div class="label">Approved</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ $statistics['rejected'] }}</div>
            <div class="label">Rejected</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ $statistics['approval_rate'] }}%</div>
            <div class="label">Approval Rate</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIM</th>
                <th>Nama Mahasiswa</th>
                <th>Nama Lomba</th>
                <th>Tingkat</th>
                <th>Penyelenggara</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Kredibilitas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($achievements as $index => $achievement)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $achievement->student_id }}</td>
                <td>{{ $achievement->student?->name ?? '-' }}</td>
                <td>{{ Str::limit($achievement->event_name, 30) }}</td>
                <td>{{ $achievement->level }}</td>
                <td>{{ Str::limit($achievement->organizer, 20) }}</td>
                <td>{{ $achievement->event_date?->format('d/m/Y') }}</td>
                <td>
                    <span class="badge badge-{{ $achievement->status_badge }}">
                        {{ $achievement->status_label }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-{{ $achievement->credibility_badge }}">
                        {{ number_format($achievement->credibility_score, 0) }}%
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh Sistem Prestasi Unpatti</p>
    </div>
</body>
</html>
