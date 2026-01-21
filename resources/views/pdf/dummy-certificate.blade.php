<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ ucfirst($type) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            text-align: center;
        }
        .header {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #1e40af;
        }
        .content {
            font-size: 14px;
            line-height: 1.8;
            margin: 30px 0;
        }
        .student-name {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            color: #059669;
        }
        .event-name {
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0;
            color: #7c3aed;
        }
        .footer {
            margin-top: 50px;
            font-size: 12px;
            color: #6b7280;
        }
        .stamp {
            margin-top: 40px;
            font-size: 12px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        SERTIFIKAT {{ strtoupper($type) }}
    </div>
    
    <div class="content">
        <p>Diberikan kepada:</p>
        
        <div class="student-name">
            {{ $student->name }}
        </div>
        
        <p>NIM: {{ $student->student_id }}</p>
        <p>{{ $student->program_study }}</p>
        <p>{{ $student->faculty }}</p>
        
        <div class="event-name">
            {{ $eventName }}
        </div>
        
        <p>Sebagai bentuk apresiasi atas partisipasi dan prestasi yang telah dicapai.</p>
    </div>
    
    <div class="stamp">
        <p>Dokumen ini dibuat secara otomatis oleh sistem</p>
        <p>Tanggal: {{ now()->format('d F Y') }}</p>
    </div>
    
    <div class="footer">
        <p>Universitas Pattimura</p>
        <p>Sistem Informasi Prestasi Mahasiswa</p>
    </div>
</body>
</html>
