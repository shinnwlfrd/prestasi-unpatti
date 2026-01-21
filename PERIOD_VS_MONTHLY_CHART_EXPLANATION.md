# Penjelasan Chart: Per Periode vs Per Bulan

## Ringkasan
Dashboard monitoring prestasi memiliki 2 mode tampilan chart yang berbeda tergantung filter periode yang dipilih.

---

## Mode 1: SEMUA PERIODE (Distribusi Per Periode) ✅

### Kapan Muncul?
Saat dropdown periode menampilkan **"Semua Periode"**

### Chart Yang Ditampilkan
**Period Comparison Chart** - Bar Chart (Grafik Batang)

### Apa Yang Ditampilkan?
Perbandingan prestasi **per periode akademik** (bukan per bulan)

### Struktur Data
```
X-Axis (Horizontal): Nama Periode
- Semester Ganjil 2025/2026
- Semester Genap 2024/2025  
- Semester Ganjil 2024/2025

Y-Axis (Vertical): Jumlah Prestasi

Datasets (4 kelompok bar):
1. Total (Ungu) - Total semua prestasi
2. Disetujui (Hijau) - Prestasi yang disetujui
3. Menunggu (Kuning) - Prestasi yang menunggu review
4. Ditolak (Merah) - Prestasi yang ditolak
```

### Contoh Data
```
Period                        | Total | Approved | Pending | Rejected
------------------------------|-------|----------|---------|----------
Semester Ganjil 2025/2026     |   39  |    14    |   13    |    4
Semester Genap 2024/2025      |   62  |    33    |   19    |    3
Semester Ganjil 2024/2025     |   49  |    26    |   13    |    7
```

### Visual Representation
```
Chart Title: "Perbandingan Prestasi Per Periode"
Subtitle: "Distribusi dan status prestasi di setiap periode akademik"

     70 |
     60 |     ████
     50 |     ████  ████
     40 |     ████  ████  ████
     30 |     ████  ████  ████
     20 |     ████  ████  ████
     10 |     ████  ████  ████
      0 |___________________________
          2025/26  2024/25  2024/25
          Ganjil   Genap    Ganjil

Legend: ■ Total  ■ Disetujui  ■ Menunggu  ■ Ditolak
```

### Kegunaan
- Membandingkan performa antar periode
- Melihat tren pertumbuhan prestasi dari semester ke semester
- Mengidentifikasi periode dengan prestasi terbanyak
- Membandingkan tingkat approval antar periode

---

## Mode 2: PERIODE SPESIFIK (Distribusi Per Bulan) ✅

### Kapan Muncul?
Saat memilih periode tertentu dari dropdown, misalnya:
- "Semester Ganjil 2024/2025 (Aktif)"
- "Semester Genap 2024/2025"
- "Semester Ganjil 2025/2026"

### Chart Yang Ditampilkan
**Monthly Trend Chart** - Line Chart (Grafik Garis)

### Apa Yang Ditampilkan?
Trend pengajuan prestasi **per bulan** dalam periode yang dipilih

### Struktur Data
```
X-Axis (Horizontal): Bulan dalam periode
- Contoh untuk Ganjil 2024/2025: Sep 2024, Oct 2024, Nov 2024, Dec 2024, Jan 2025
- Contoh untuk Genap 2024/2025: Feb 2025, Mar 2025, Apr 2025, May 2025, Jun 2025, Jul 2025

Y-Axis (Vertical): Jumlah Prestasi

Datasets (2 garis):
1. Submitted (Ungu) - Prestasi yang diajukan
2. Approved (Hijau) - Prestasi yang disetujui
```

### Contoh Data - Semester Ganjil 2024/2025
```
Month      | Submitted | Approved
-----------|-----------|----------
Sep 2024   |     8     |    4
Oct 2024   |     7     |    1
Nov 2024   |    12     |    8
Dec 2024   |    13     |    7
Jan 2025   |     9     |    6
-----------|-----------|----------
TOTAL      |    49     |   26
```

### Visual Representation
```
Chart Title: "Trend Submission Ganjil 2024/2025"
Subtitle: "Sep 2024 - Jan 2025"

     15 |              ●
        |            ╱   ╲
     10 |          ●       ●
        |        ╱           ╲
      5 |    ●─●               ●
        |  ●
      0 |_________________________
         Sep  Oct  Nov  Dec  Jan
         2024 2024 2024 2024 2025

Legend: ─●─ Submitted  ─●─ Approved
```

### Kegunaan
- Melihat trend pengajuan dalam satu semester
- Mengidentifikasi bulan dengan pengajuan tertinggi
- Membandingkan jumlah submitted vs approved per bulan
- Memantau pola pengajuan mahasiswa

---

## Perbandingan Kedua Mode

| Aspek | Semua Periode | Periode Spesifik |
|-------|---------------|------------------|
| **Chart Type** | Bar Chart | Line Chart |
| **X-Axis** | Nama Periode | Bulan |
| **Granularity** | Per Periode (semester) | Per Bulan |
| **Datasets** | 4 (Total, Approved, Pending, Rejected) | 2 (Submitted, Approved) |
| **Tujuan** | Perbandingan antar periode | Trend dalam satu periode |
| **Jumlah Data Points** | 3 periode | 5-6 bulan (tergantung periode) |

---

## Implementasi Teknis

### Controller Logic
```php
// AchievementDashboardController.php

public function index(Request $request)
{
    $periodId = $request->input('period');
    
    // Get period comparison if "Semua Periode" selected
    $periodComparison = null;
    if (!$periodId) {
        $periodComparison = $this->approvalService->getPeriodComparison();
    }
    
    // Get monthly trend (filtered by period if selected)
    $monthlyTrend = $this->approvalService->getMonthlyTrend(6, $periodId);
    
    return view('admin.achievements.dashboard', compact(
        'periodComparison',
        'monthlyTrend',
        // ... other data
    ));
}
```

### View Logic
```blade
@if($periodComparison)
    <!-- Show Period Comparison Chart -->
    <canvas id="periodComparisonChart"></canvas>
@endif

@if(!$periodComparison)
    <!-- Show Monthly Trend Chart -->
    <canvas id="monthlyTrendChart"></canvas>
@endif
```

### JavaScript Logic
```javascript
@if($periodComparison)
    // Render Bar Chart for Period Comparison
    new Chart(document.getElementById('periodComparisonChart'), {
        type: 'bar',
        data: {
            labels: periodData.map(d => d.period),
            datasets: [
                { label: 'Total', data: periodData.map(d => d.total) },
                { label: 'Disetujui', data: periodData.map(d => d.approved) },
                { label: 'Menunggu', data: periodData.map(d => d.pending) },
                { label: 'Ditolak', data: periodData.map(d => d.rejected) }
            ]
        }
    });
@else
    // Render Line Chart for Monthly Trend
    new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'line',
        data: {
            labels: monthlyData.map(d => d.month),
            datasets: [
                { label: 'Submitted', data: monthlyData.map(d => d.submitted) },
                { label: 'Approved', data: monthlyData.map(d => d.approved) }
            ]
        }
    });
@endif
```

---

## Cara Menggunakan

### Untuk Melihat Distribusi Per Periode:
1. Buka Dashboard Monitoring Prestasi
2. Pilih **"Semua Periode"** dari dropdown
3. Chart bar akan muncul menampilkan perbandingan antar periode
4. Hover pada bar untuk melihat detail angka

### Untuk Melihat Distribusi Per Bulan:
1. Buka Dashboard Monitoring Prestasi
2. Pilih periode spesifik (contoh: "Semester Ganjil 2024/2025")
3. Chart garis akan muncul menampilkan trend bulanan
4. Hover pada titik untuk melihat detail angka

---

## Kesimpulan

✅ **Saat "Semua Periode" dipilih**: Menampilkan distribusi **PER PERIODE** (Bar Chart)
✅ **Saat periode spesifik dipilih**: Menampilkan distribusi **PER BULAN** (Line Chart)

Implementasi ini memberikan fleksibilitas untuk:
- Analisis makro (perbandingan antar semester)
- Analisis mikro (trend dalam satu semester)

Kedua mode chart ini saling melengkapi dan memberikan insight yang berbeda untuk monitoring prestasi mahasiswa.
