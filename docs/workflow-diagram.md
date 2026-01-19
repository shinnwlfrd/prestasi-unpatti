# Diagram Alur Kerja Sistem Validasi Prestasi

## 1. Alur Utama Sistem (SSO SIKAD)

```mermaid
flowchart TB
    subgraph SSO["🔐 SSO SIKAD (Single Sign-On)"]
        L1[Halaman Login Prestasi]
        L1 --> L2[Redirect ke SSO SIKAD]
        L2 --> L3[Login dengan Akun SIKAD]
        L3 --> L4[SIKAD Autentikasi]
        L4 --> L5{Validasi Token}
        L5 -->|Valid| L6[Callback ke Aplikasi]
        L5 -->|Invalid| L2
        L6 --> L7{Cek Role dari SIKAD}
    end

    subgraph Student["👨‍🎓 MAHASISWA"]
        S1[Dashboard Mahasiswa]
        S2[Form Ajukan Prestasi]
        S3[Upload Dokumen]
        S4[Lihat Status]
        S5[Ajukan Banding]
        
        S1 --> S2
        S2 --> S3
        S3 --> S4
        S4 -->|Ditolak| S5
    end

    subgraph Validator["✅ VALIDATOR"]
        V1[Dashboard Validator]
        V2[Detail Prestasi]
        V3[Checklist Validasi]
        V4[Verifikasi Dokumen]
        V5[Approve/Reject/Revisi]
        
        V1 --> V2
        V2 --> V3
        V2 --> V4
        V3 --> V5
        V4 --> V5
    end

    subgraph Admin["👑 ADMIN"]
        A1[Dashboard Admin]
        A2[Monitoring Prestasi]
        A3[Kelola Banding]
        A4[Export Data]
        A5[Kelola User]
        
        A1 --> A2
        A1 --> A3
        A1 --> A4
        A1 --> A5
    end

    L7 -->|Mahasiswa| S1
    L7 -->|Validator| V1
    L7 -->|Admin| A1
    
    S3 -->|Submit| V1
    V5 -->|Notifikasi| S4
    S5 --> A3
```

## 1.1 Detail Alur SSO SIKAD

```mermaid
sequenceDiagram
    participant U as User
    participant App as Aplikasi Prestasi
    participant SSO as SSO SIKAD
    participant DB as Database SIKAD

    U->>App: Akses /login
    App->>SSO: Redirect ke SSO dengan client_id
    SSO->>U: Tampilkan Form Login SIKAD
    U->>SSO: Input NIM/NIP & Password
    SSO->>DB: Validasi Kredensial
    DB-->>SSO: User Data + Role
    SSO->>App: Callback dengan auth_code
    App->>SSO: Exchange code untuk token
    SSO-->>App: Access Token + User Info
    App->>App: Simpan Session
    App->>App: Sync/Create User Lokal
    
    alt Role = Mahasiswa
        App->>U: Redirect ke /dashboard
    else Role = Validator
        App->>U: Redirect ke /validator
    else Role = Admin
        App->>U: Redirect ke /admin
    end
```

## 1.2 Mapping Role dari SIKAD

```mermaid
flowchart LR
    subgraph SIKAD["📚 Data dari SIKAD"]
        SK1[Mahasiswa Aktif]
        SK2[Dosen]
        SK3[Staff Akademik]
        SK4[Staff Kemahasiswaan]
        SK5[Wakil Dekan III]
        SK6[Admin Fakultas]
    end
    
    subgraph App["🎯 Role di Aplikasi"]
        A1[Mahasiswa]
        A2[Validator]
        A3[Admin]
    end
    
    SK1 --> A1
    SK2 --> A2
    SK3 --> A2
    SK4 --> A2
    SK5 --> A3
    SK6 --> A3
    
    style A1 fill:#e3f2fd
    style A2 fill:#e8f5e9
    style A3 fill:#fff3e0
```

## 2. Alur Pengajuan Prestasi (Mahasiswa)

```mermaid
flowchart LR
    A[Mulai] --> B[Isi Form Prestasi]
    B --> C[Upload Sertifikat<br/>Opsional]
    C --> D[Submit]
    D --> E[Halaman Dokumen]
    E --> F{Upload<br/>Dokumen?}
    F -->|Ya| G[Upload Multi-Dokumen]
    G --> H[Pilih Jenis Dokumen]
    H --> I[Status: Draft]
    I --> J[Submit Dokumen]
    J --> K[Status: Pending]
    F -->|Tidak| L[Selesai]
    K --> L
    
    style A fill:#e1f5fe
    style L fill:#c8e6c9
    style I fill:#fff9c4
    style K fill:#ffe0b2
```

## 3. Alur Status Dokumen

```mermaid
stateDiagram-v2
    Mahasiswa --> Draft: Upload File
    Draft --> Pending: Submit
    Pending --> Approved: Validator Setuju
    Pending --> Rejected: Validator Tolak
    Pending --> Revision: Minta Revisi
    Revision --> Pending: Upload Ulang & Submit
    Rejected --> Banding
    Banding --> Pending : Alasan Banding
    Approved --> Selesai
    
    note right of Draft: Mahasiswa bisa edit/hapus
    note right of Pending: Menunggu verifikasi
    note right of Revision: Perlu perbaikan
    note right of Approved: Dihitung ke kredibilitas
```

## 4. Alur Validasi (Validator)

```mermaid
flowchart TB
    A[Dashboard Validator] --> B[Pilih Prestasi Pending]
    B --> C[Lihat Detail Prestasi]
    
    C --> D[Verifikasi Dokumen]
    C --> E[Isi Checklist]
    
    D --> D1{Per Dokumen}
    D1 -->|Setuju| D2[Status: Approved]
    D1 -->|Tolak| D3[Status: Rejected]
    D1 -->|Revisi| D4[Status: Revision]
    
    E --> E1[✓ Nama Peserta]
    E --> E2[✓ Nama Lomba]
    E --> E3[✓ Tanggal]
    E --> E4[✓ Peringkat]
    E --> E5[✓ Penyelenggara]
    E --> E6[✓ Keaslian Dokumen]
    
    D2 & D3 & D4 --> F[Hitung Kredibilitas]
    E1 & E2 & E3 & E4 & E5 & E6 --> G{Keputusan}
    
    G -->|Approve| H[Status: Approved]
    G -->|Reject| I[Status: Rejected]
    G -->|Revisi| J[Status: Need Revision]
    
    H & I & J --> K[Kirim Notifikasi]
    K --> L[Selesai]
    
    style H fill:#c8e6c9
    style I fill:#ffcdd2
    style J fill:#bbdefb
```

## 5. Alur Perhitungan Kredibilitas

```mermaid
flowchart LR
    subgraph Input["📄 Dokumen yang Disetujui"]
        D1["SK Resmi<br/>+30 poin"]
        D2["Sertifikat<br/>+25 poin"]
        D3["Surat Keterangan<br/>+20 poin"]
        D4["Foto Dokumentasi<br/>+15 poin"]
        D5["Link Publikasi<br/>+10 poin"]
    end
    
    subgraph Calc["🔢 Kalkulasi"]
        C1[Total Poin Dokumen]
        C2[Maksimal 100 poin]
        C3["Skor = (Total/100) × 100%"]
    end
    
    subgraph Output["📊 Hasil"]
        O1["≥80%: Hijau ✅"]
        O2["70-79%: Kuning ⚠️"]
        O3["<70%: Merah 🔴<br/>Perlu Review Ekstra"]
    end
    
    D1 & D2 & D3 & D4 & D5 --> C1
    C1 --> C2
    C2 --> C3
    C3 --> O1 & O2 & O3
```

## 6. Alur Banding (Appeal)

```mermaid
flowchart TB
    A[Prestasi Ditolak] --> B{Mahasiswa<br/>Ajukan Banding?}
    B -->|Ya| C[Form Banding]
    C --> D[Isi Alasan]
    D --> E[Upload Dokumen Tambahan]
    E --> F[Submit Banding]
    F --> G[Status: Pending Review]
    
    G --> H[Admin Review]
    H --> I{Keputusan}
    I -->|Terima| J[Prestasi Di-review Ulang]
    I -->|Tolak| K[Banding Ditolak]
    
    J --> L[Validator Review Ulang]
    L --> M{Hasil}
    M -->|Approve| N[Prestasi Disetujui]
    M -->|Reject| O[Tetap Ditolak]
    
    B -->|Tidak| P[Selesai]
    K --> P
    N --> P
    O --> P
    
    style N fill:#c8e6c9
    style K fill:#ffcdd2
    style O fill:#ffcdd2
```

## 7. Struktur Halaman Web

```mermaid
graph TB
    subgraph Public["🌐 Public"]
        P1["/login - Redirect ke SSO"]
        P2["/auth/callback - SSO Callback"]
        P3["/logout - Logout & Clear Session"]
    end
    
    subgraph StudentPages["👨‍🎓 Mahasiswa /dashboard"]
        S1["/dashboard - Dashboard"]
        S2["/submit - Form Prestasi"]
        S3["/achievements/{id}/documents - Kelola Dokumen"]
        S4["/achievements/{id}/appeal - Form Banding"]
    end
    
    subgraph ValidatorPages["✅ Validator /validator"]
        V1["/validator - Dashboard"]
        V2["/validator/history - Riwayat"]
        V3["/validator/achievements/{id} - Detail Validasi"]
        V4["/validator/achievements/{id}/documents - Verifikasi Dokumen"]
        V5["/validator/submit - Ajukan untuk Mahasiswa"]
    end
    
    subgraph AdminPages["👑 Admin /admin"]
        A1["/admin - Dashboard"]
        A2["/admin/achievements/dashboard - Monitoring"]
        A3["/admin/achievements/validation - Daftar Validasi"]
        A4["/admin/appeals - Kelola Banding"]
        A5["/admin/users - Kelola User"]
        A6["/admin/students - Data Mahasiswa"]
    end
    
    subgraph Shared["🔗 Shared"]
        X1["/documents/{id}/preview - Preview Dokumen"]
        X2["/documents/{id}/history - Riwayat Dokumen"]
    end
```

## 8. Arsitektur SSO Integration

```mermaid
flowchart TB
    subgraph Client["🖥️ Browser"]
        C1[User Browser]
    end
    
    subgraph App["📱 Aplikasi Prestasi"]
        A1[Laravel App]
        A2[Session Store]
        A3[Local User DB]
    end
    
    subgraph SSO["🔐 SSO SIKAD Server"]
        S1[OAuth2 Server]
        S2[User Directory]
        S3[Token Service]
    end
    
    subgraph SIKAD["📚 SIKAD Database"]
        D1[Data Mahasiswa]
        D2[Data Dosen]
        D3[Data Staff]
    end
    
    C1 -->|1. Login Request| A1
    A1 -->|2. Redirect| S1
    C1 -->|3. Credentials| S1
    S1 -->|4. Validate| S2
    S2 -->|5. Query| D1 & D2 & D3
    S1 -->|6. Auth Code| C1
    C1 -->|7. Callback| A1
    A1 -->|8. Exchange Token| S3
    S3 -->|9. Access Token + User Info| A1
    A1 -->|10. Create/Update| A3
    A1 -->|11. Store Session| A2
    A1 -->|12. Redirect Dashboard| C1
```

## 8. Database Entity Relationship

```mermaid
erDiagram
    STUDENTS ||--o{ STUDENT_ACHIEVEMENTS : has
    ACHIEVEMENTS ||--o{ STUDENT_ACHIEVEMENTS : categorizes
    STUDENT_ACHIEVEMENTS ||--o{ ACHIEVEMENT_DOCUMENTS : has
    STUDENT_ACHIEVEMENTS ||--o{ VALIDATION_LOGS : has
    STUDENT_ACHIEVEMENTS ||--o| VALIDATION_CHECKLISTS : has
    STUDENT_ACHIEVEMENTS ||--o{ ACHIEVEMENT_APPEALS : has
    ACHIEVEMENT_DOCUMENTS ||--o{ DOCUMENT_REVISIONS : has
    USERS ||--o{ VALIDATION_LOGS : validates
    USERS ||--o{ ACHIEVEMENT_DOCUMENTS : verifies
    USERS ||--o{ ACHIEVEMENT_APPEALS : reviews
    
    STUDENTS {
        string student_id PK "NIM dari SIKAD"
        string name
        string email
        string faculty
        string program_study
        string sikad_token "SSO Token"
    }
    
    USERS {
        int id PK
        string sikad_id "NIP/ID dari SIKAD"
        string name
        string email
        string role "Admin/Validator"
        string sikad_token "SSO Token"
    }
    
    STUDENT_ACHIEVEMENTS {
        int sa_id PK
        string student_id FK
        int achievement_id FK
        string event_name
        string level
        string validation_status
        float credibility_score
        boolean requires_extra_review
    }
    
    ACHIEVEMENT_DOCUMENTS {
        int id PK
        int sa_id FK
        string document_type
        string file_path
        string status
        int verified_by FK
    }
    
    VALIDATION_CHECKLISTS {
        int id PK
        int sa_id FK
        boolean nama_peserta_valid
        boolean nama_lomba_valid
        boolean tanggal_valid
        boolean peringkat_valid
        boolean penyelenggara_valid
        boolean keaslian_dokumen_valid
    }
```

## 9. Konfigurasi SSO

```
# .env Configuration untuk SSO SIKAD

SIKAD_SSO_ENABLED=true
SIKAD_SSO_URL=https://sso.sikad.unpatti.ac.id
SIKAD_SSO_CLIENT_ID=prestasi-app
SIKAD_SSO_CLIENT_SECRET=your-secret-key
SIKAD_SSO_REDIRECT_URI=https://prestasi.unpatti.ac.id/auth/callback
SIKAD_SSO_SCOPES=openid,profile,email,role
```

## Legenda Status

| Status Prestasi | Warna | Keterangan |
|----------------|-------|------------|
| `pending` | 🟡 Kuning | Menunggu validasi |
| `approved` | 🟢 Hijau | Disetujui |
| `rejected` | 🔴 Merah | Ditolak |
| `need_revision` | 🔵 Biru | Perlu revisi dokumen |

| Status Dokumen | Warna | Keterangan |
|----------------|-------|------------|
| `draft` | ⚪ Abu-abu | Baru diupload, belum disubmit |
| `pending` | 🟡 Kuning | Menunggu verifikasi |
| `approved` | 🟢 Hijau | Disetujui, dihitung ke kredibilitas |
| `rejected` | 🔴 Merah | Ditolak |
| `revision` | 🔵 Biru | Perlu upload ulang |

---