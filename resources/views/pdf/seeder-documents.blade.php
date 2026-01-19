<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dokumen Seeder Prestasi Mahasiswa</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #333;
        }
        .page {
            page-break-after: always;
            padding: 40px 50px;
            min-height: 100%;
        }
        .page:last-child {
            page-break-after: avoid;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #333;
            padding-bottom: 20px;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 10px;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
        }
        .institution {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .sub-institution {
            font-size: 11pt;
            color: #666;
        }
        h1 {
            text-align: center;
            font-size: 18pt;
            margin: 30px 0 20px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        h2 {
            text-align: center;
            font-size: 14pt;
            margin: 20px 0;
            font-weight: normal;
        }
        .certificate-box {
            border: 3px solid #1e3a8a;
            padding: 30px;
            margin: 20px 0;
            background: linear-gradient(to bottom, #f8fafc, #e2e8f0);
        }
        .certificate-content {
            text-align: center;
        }
        .recipient-name {
            font-size: 24pt;
            font-weight: bold;
            color: #1e3a8a;
            margin: 20px 0;
            font-family: 'Georgia', serif;
        }
        .nim {
            font-size: 12pt;
            color: #666;
            margin-bottom: 20px;
        }
        .achievement-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 15px 0;
            color: #333;
        }
        .achievement-detail {
            font-size: 11pt;
            margin: 5px 0;
        }
        .ranking {
            font-size: 16pt;
            font-weight: bold;
            color: #dc2626;
            margin: 15px 0;
            padding: 10px 20px;
            border: 2px solid #dc2626;
            display: inline-block;
        }
        table.info {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        table.info td {
            padding: 8px 10px;
            vertical-align: top;
        }
        table.info td:first-child {
            width: 180px;
            font-weight: bold;
        }
        table.info td:nth-child(2) {
            width: 20px;
            text-align: center;
        }
        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 20px;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            width: 200px;
            margin: 60px auto 10px;
        }
        .signature-name {
            font-weight: bold;
        }
        .signature-title {
            font-size: 10pt;
            color: #666;
        }
        .stamp {
            width: 80px;
            height: 80px;
            border: 2px solid #dc2626;
            border-radius: 50%;
            margin: 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #dc2626;
            font-size: 8pt;
            text-align: center;
            font-weight: bold;
        }
        .sk-number {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
        }
        .sk-title {
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
        }
        .status-approved {
            color: #16a34a;
            font-weight: bold;
            padding: 5px 15px;
            border: 2px solid #16a34a;
            display: inline-block;
        }
        .status-rejected {
            color: #dc2626;
            font-weight: bold;
            padding: 5px 15px;
            border: 2px solid #dc2626;
            display: inline-block;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            font-size: 9pt;
            color: #666;
            text-align: center;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60pt;
            color: rgba(0,0,0,0.05);
            font-weight: bold;
            z-index: -1;
        }
        .doc-type {
            background: #1e3a8a;
            color: white;
            padding: 5px 15px;
            font-size: 10pt;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<!-- ==================== SERTIFIKAT 1: AKADEMIK ==================== -->
<div class="page">
    <div class="watermark">SAMPLE</div>
    <div class="header">
        <div class="institution">UNIVERSITAS PATTIMURA</div>
        <div class="sub-institution">Jl. Ir. M. Putuhena, Poka, Ambon, Maluku 97233</div>
    </div>
    
    <span class="doc-type">SERTIFIKAT PRESTASI AKADEMIK</span>
    
    <h1>SERTIFIKAT</h1>
    <h2>Nomor: CERT/AKD/2026/001</h2>
    
    <div class="certificate-box">
        <div class="certificate-content">
            <p>Diberikan kepada:</p>
            <div class="recipient-name">Mahasiswa Test</div>
            <div class="nim">NIM: 2021001001</div>
            
            <p>Atas partisipasi dan prestasi dalam:</p>
            <div class="achievement-title">PUBLIKASI JURNAL INTERNASIONAL</div>
            
            <div class="achievement-detail">Penyelenggara: IEEE International Conference</div>
            <div class="achievement-detail">Tingkat: Internasional</div>
            <div class="achievement-detail">Tanggal: 15 Maret 2025</div>
            
            <div class="ranking">BEST PAPER AWARD</div>
        </div>
    </div>
    
    <div class="signature-section">
        <div class="signature-box">
            <p>Ambon, 20 Maret 2025</p>
            <p>Rektor,</p>
            <div class="signature-line"></div>
            <div class="signature-name">Prof. Dr. M. J. Saptenno, S.H., M.Hum</div>
            <div class="signature-title">NIP. 196312311989031001</div>
        </div>
    </div>
    
    <div class="footer">
        <em>Dokumen ini adalah contoh sertifikat untuk keperluan uji coba sistem.<br>
        Tidak memiliki kekuatan hukum apa pun.</em>
    </div>
</div>

<!-- ==================== SERTIFIKAT 2: NON-AKADEMIK ==================== -->
<div class="page">
    <div class="watermark">SAMPLE</div>
    <div class="header">
        <div class="institution">UNIVERSITAS PATTIMURA</div>
        <div class="sub-institution">Jl. Ir. M. Putuhena, Poka, Ambon, Maluku 97233</div>
    </div>
    
    <span class="doc-type">SERTIFIKAT PRESTASI NON-AKADEMIK</span>
    
    <h1>SERTIFIKAT</h1>
    <h2>Nomor: CERT/NAK/2026/002</h2>
    
    <div class="certificate-box">
        <div class="certificate-content">
            <p>Diberikan kepada:</p>
            <div class="recipient-name">Mahasiswa Test</div>
            <div class="nim">NIM: 2021001001</div>
            
            <p>Atas partisipasi dan prestasi dalam:</p>
            <div class="achievement-title">LOMBA DEBAT BAHASA INGGRIS NASIONAL</div>
            
            <div class="achievement-detail">Penyelenggara: Kementerian Pendidikan dan Kebudayaan</div>
            <div class="achievement-detail">Tingkat: Nasional</div>
            <div class="achievement-detail">Tanggal: 22 November 2024</div>
            
            <div class="ranking">JUARA 1</div>
        </div>
    </div>
    
    <div class="signature-section">
        <div class="signature-box">
            <p>Ambon, 25 November 2024</p>
            <p>Rektor,</p>
            <div class="signature-line"></div>
            <div class="signature-name">Prof. Dr. M. J. Saptenno, S.H., M.Hum</div>
            <div class="signature-title">NIP. 196312311989031001</div>
        </div>
    </div>
    
    <div class="footer">
        <em>Dokumen ini adalah contoh sertifikat untuk keperluan uji coba sistem.<br>
        Tidak memiliki kekuatan hukum apa pun.</em>
    </div>
</div>

<!-- ==================== SK 1: DISETUJUI ==================== -->
<div class="page">
    <div class="watermark">SAMPLE</div>
    <div class="header">
        <div class="institution">UNIVERSITAS PATTIMURA</div>
        <div class="sub-institution">Jl. Ir. M. Putuhena, Poka, Ambon, Maluku 97233</div>
    </div>
    
    <span class="doc-type">SURAT KEPUTUSAN</span>
    
    <div class="sk-number">Nomor: 001/SK/UNPATTI/I/2026</div>
    
    <div class="sk-title">
        TENTANG<br>
        PENETAPAN DAN VALIDASI PRESTASI MAHASISWA<br>
        UNIVERSITAS PATTIMURA
    </div>
    
    <p style="margin: 20px 0;"><strong>REKTOR UNIVERSITAS PATTIMURA</strong></p>
    
    <p style="margin-bottom: 20px;">Menimbang bahwa prestasi mahasiswa yang bersangkutan telah memenuhi kriteria dan persyaratan yang ditetapkan, maka dengan ini menetapkan:</p>
    
    <table class="info">
        <tr>
            <td>Nama Mahasiswa</td>
            <td>:</td>
            <td>Mahasiswa Test</td>
        </tr>
        <tr>
            <td>NIM</td>
            <td>:</td>
            <td>2021001001</td>
        </tr>
        <tr>
            <td>Program Studi</td>
            <td>:</td>
            <td>Teknik Informatika</td>
        </tr>
        <tr>
            <td>Fakultas</td>
            <td>:</td>
            <td>Fakultas Teknik</td>
        </tr>
        <tr>
            <td>Nama Kegiatan</td>
            <td>:</td>
            <td>Publikasi Jurnal Internasional</td>
        </tr>
        <tr>
            <td>Tingkat</td>
            <td>:</td>
            <td>Internasional</td>
        </tr>
        <tr>
            <td>Penyelenggara</td>
            <td>:</td>
            <td>IEEE International Conference</td>
        </tr>
        <tr>
            <td>Tanggal Kegiatan</td>
            <td>:</td>
            <td>15 Maret 2025</td>
        </tr>
        <tr>
            <td>Peringkat</td>
            <td>:</td>
            <td>Best Paper Award</td>
        </tr>
        <tr>
            <td>Status Validasi</td>
            <td>:</td>
            <td><span class="status-approved">DISETUJUI</span></td>
        </tr>
        <tr>
            <td>Validator</td>
            <td>:</td>
            <td>Validator Prestasi</td>
        </tr>
        <tr>
            <td>Tanggal Validasi</td>
            <td>:</td>
            <td>10 Januari 2026</td>
        </tr>
    </table>
    
    <p style="margin: 20px 0;">Surat Keputusan ini berlaku sejak tanggal ditetapkan.</p>
    
    <div class="signature-section">
        <div class="signature-box" style="text-align: right;">
            <p>Ditetapkan di: Ambon</p>
            <p>Pada tanggal: 10 Januari 2026</p>
            <p style="margin-top: 20px;">Rektor,</p>
            <div class="signature-line" style="margin-left: auto;"></div>
            <div class="signature-name">Prof. Dr. M. J. Saptenno, S.H., M.Hum</div>
            <div class="signature-title">NIP. 196312311989031001</div>
        </div>
    </div>
    
    <div class="footer">
        <em>Dokumen ini adalah contoh SK untuk testing aplikasi.<br>
        Tidak memiliki kekuatan hukum apa pun.</em>
    </div>
</div>

<!-- ==================== SK 2: DITOLAK ==================== -->
<div class="page">
    <div class="watermark">SAMPLE</div>
    <div class="header">
        <div class="institution">UNIVERSITAS PATTIMURA</div>
        <div class="sub-institution">Jl. Ir. M. Putuhena, Poka, Ambon, Maluku 97233</div>
    </div>
    
    <span class="doc-type">SURAT KEPUTUSAN</span>
    
    <div class="sk-number">Nomor: 002/SK/UNPATTI/I/2026</div>
    
    <div class="sk-title">
        TENTANG<br>
        PENETAPAN DAN VALIDASI PRESTASI MAHASISWA<br>
        UNIVERSITAS PATTIMURA
    </div>
    
    <p style="margin: 20px 0;"><strong>REKTOR UNIVERSITAS PATTIMURA</strong></p>
    
    <p style="margin-bottom: 20px;">Setelah meninjau dokumen dan bukti yang diajukan, dengan ini menetapkan:</p>
    
    <table class="info">
        <tr>
            <td>Nama Mahasiswa</td>
            <td>:</td>
            <td>Mahasiswa Test</td>
        </tr>
        <tr>
            <td>NIM</td>
            <td>:</td>
            <td>2021001001</td>
        </tr>
        <tr>
            <td>Program Studi</td>
            <td>:</td>
            <td>Teknik Informatika</td>
        </tr>
        <tr>
            <td>Fakultas</td>
            <td>:</td>
            <td>Fakultas Teknik</td>
        </tr>
        <tr>
            <td>Nama Kegiatan</td>
            <td>:</td>
            <td>Kompetisi Olahraga Nasional</td>
        </tr>
        <tr>
            <td>Tingkat</td>
            <td>:</td>
            <td>Nasional</td>
        </tr>
        <tr>
            <td>Penyelenggara</td>
            <td>:</td>
            <td>KONI Pusat</td>
        </tr>
        <tr>
            <td>Tanggal Kegiatan</td>
            <td>:</td>
            <td>5 Desember 2024</td>
        </tr>
        <tr>
            <td>Status Validasi</td>
            <td>:</td>
            <td><span class="status-rejected">DITOLAK</span></td>
        </tr>
        <tr>
            <td>Alasan Penolakan</td>
            <td>:</td>
            <td>Bukti dokumen tidak lengkap. Sertifikat asli tidak dapat diverifikasi.</td>
        </tr>
        <tr>
            <td>Validator</td>
            <td>:</td>
            <td>Validator Prestasi</td>
        </tr>
        <tr>
            <td>Tanggal Validasi</td>
            <td>:</td>
            <td>11 Januari 2026</td>
        </tr>
    </table>
    
    <p style="margin: 20px 0;">Mahasiswa dapat mengajukan banding dengan melengkapi dokumen yang diperlukan dalam waktu 14 hari kerja.</p>
    
    <div class="signature-section">
        <div class="signature-box" style="text-align: right;">
            <p>Ditetapkan di: Ambon</p>
            <p>Pada tanggal: 11 Januari 2026</p>
            <p style="margin-top: 20px;">Rektor,</p>
            <div class="signature-line" style="margin-left: auto;"></div>
            <div class="signature-name">Prof. Dr. M. J. Saptenno, S.H., M.Hum</div>
            <div class="signature-title">NIP. 196312311989031001</div>
        </div>
    </div>
    
    <div class="footer">
        <em>Dokumen ini adalah contoh SK untuk testing aplikasi.<br>
        Tidak memiliki kekuatan hukum apa pun.</em>
    </div>
</div>

<!-- ==================== DOKUMEN PENDUKUNG ==================== -->
<div class="page">
    <div class="watermark">SAMPLE</div>
    <div class="header">
        <div class="institution">UNIVERSITAS PATTIMURA</div>
        <div class="sub-institution">Jl. Ir. M. Putuhena, Poka, Ambon, Maluku 97233</div>
    </div>
    
    <span class="doc-type">DOKUMEN PENDUKUNG</span>
    
    <h1>SURAT KETERANGAN</h1>
    <h2>Nomor: SKT/UNPATTI/2026/001</h2>
    
    <p style="margin: 30px 0;">Yang bertanda tangan di bawah ini menerangkan bahwa:</p>
    
    <table class="info">
        <tr>
            <td>Nama</td>
            <td>:</td>
            <td>Mahasiswa Test</td>
        </tr>
        <tr>
            <td>NIM</td>
            <td>:</td>
            <td>2021001001</td>
        </tr>
        <tr>
            <td>Program Studi</td>
            <td>:</td>
            <td>Teknik Informatika</td>
        </tr>
        <tr>
            <td>Fakultas</td>
            <td>:</td>
            <td>Fakultas Teknik</td>
        </tr>
    </table>
    
    <p style="margin: 20px 0;">Adalah benar mahasiswa aktif Universitas Pattimura yang telah mengikuti berbagai kegiatan prestasi baik akademik maupun non-akademik selama masa studinya.</p>
    
    <p style="margin: 20px 0;">Surat keterangan ini dibuat untuk keperluan administrasi dan validasi dokumen prestasi mahasiswa.</p>
    
    <p style="margin: 20px 0;"><strong>Jenis Dokumen yang Dapat Diupload:</strong></p>
    <ul style="margin-left: 30px;">
        <li>SK Resmi dari Penyelenggara</li>
        <li>Sertifikat/Piagam Penghargaan</li>
        <li>Foto Dokumentasi Kegiatan</li>
        <li>Surat Keterangan dari Instansi Terkait</li>
        <li>Link Publikasi (untuk karya ilmiah)</li>
    </ul>
    
    <p style="margin: 20px 0;">Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
    
    <div class="signature-section">
        <div class="signature-box" style="text-align: right;">
            <p>Ambon, 12 Januari 2026</p>
            <p>Kepala Bagian Kemahasiswaan,</p>
            <div class="signature-line" style="margin-left: auto;"></div>
            <div class="signature-name">Dr. Admin Sistem, M.Kom</div>
            <div class="signature-title">NIP. 198001012005011001</div>
        </div>
    </div>
    
    <div class="footer">
        <em>Dokumen ini hanya untuk memvalidasi upload file dan preview.<br>
        Tidak digunakan dalam proses resmi apa pun.</em>
    </div>
</div>

</body>
</html>
