# Panduan Dual-Gate Authentication System

## Daftar Isi
1. [Arsitektur Sistem](#1-arsitektur-sistem)
2. [Struktur Database](#2-struktur-database)
3. [Flow Login Lokal](#3-flow-login-lokal)
4. [Flow Login SSO](#4-flow-login-sso)
5. [Menghindari Konflik Akun](#5-menghindari-konflik-akun)
6. [Migrasi ke Single Gate](#6-migrasi-ke-single-gate)
7. [Best Practice Keamanan](#7-best-practice-keamanan)

---

## 1. Arsitektur Sistem

### 1.1 Diagram Arsitektur Dual-Gate

```
┌─────────────────────────────────────────────────────────────┐
│                      HALAMAN LOGIN                          │
│  ┌─────────────────────┐    ┌─────────────────────┐        │
│  │   Login Lokal       │    │    Login SSO        │        │
│  │   (Email+Password)  │    │    (SIAKAD/IdP)     │        │
│  └──────────┬──────────┘    └──────────┬──────────┘        │
└─────────────┼───────────────────────────┼──────────────────┘
              │                           │
              ▼                           ▼
┌─────────────────────────┐    ┌─────────────────────────────┐
│  AuthController         │    │  SSOController              │
│  - validate credentials │    │  - redirect to IdP          │
│  - check password hash  │    │  - handle callback          │
│  - create session       │    │  - validate token           │
└──────────┬──────────────┘    └──────────┬──────────────────┘
           │                              │
           └──────────────┬───────────────┘
                          ▼
              ┌───────────────────────┐
              │   AuthService         │
              │   - findOrCreateUser  │
              │   - linkAccounts      │
              │   - generateSession   │
              └───────────┬───────────┘
                          ▼
              ┌───────────────────────┐
              │   USERS TABLE         │
              │   (unified storage)   │
              └───────────────────────┘
```

### 1.2 Prinsip Desain

| Prinsip | Penjelasan |
|---------|------------|
| **Single User Table** | Satu tabel untuk semua user, baik lokal maupun SSO |
| **Provider Agnostic** | Sistem bisa handle multiple SSO provider |
| **Graceful Linking** | Akun lokal bisa di-link ke SSO tanpa kehilangan data |
| **Feature Flag** | Toggle untuk enable/disable mode login |
| **Backward Compatible** | Migrasi ke single-gate tidak break existing users |

---

## 2. Struktur Database

### 2.1 Tabel Users

```sql
CREATE TABLE users (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    
    -- Identitas Dasar
    name            VARCHAR(255) NOT NULL,
    email           VARCHAR(255) UNIQUE NOT NULL,
    
    -- Login Lokal (nullable untuk SSO-only users)
    password        VARCHAR(255) NULL,
    
    -- SSO Provider Info
    provider        VARCHAR(50) NULL,        -- 'siakad', 'google', 'local'
    provider_id     VARCHAR(255) NULL,       -- ID unik dari provider (NIM/NIP)
    
    -- Data Tambahan dari SSO
    provider_token  TEXT NULL,               -- Access token (encrypted)
    provider_refresh_token TEXT NULL,        -- Refresh token (encrypted)
    provider_token_expires_at TIMESTAMP NULL,
    provider_data   JSON NULL,               -- Raw data dari SSO
    
    -- Role & Status
    role            VARCHAR(50) DEFAULT 'student',
    is_active       BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    
    -- Linking Info
    linked_at       TIMESTAMP NULL,          -- Kapan akun di-link ke SSO
    primary_auth    VARCHAR(20) DEFAULT 'local', -- 'local' atau 'sso'
    
    -- Timestamps
    last_login_at   TIMESTAMP NULL,
    last_login_method VARCHAR(20) NULL,      -- 'local' atau 'sso'
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_provider (provider, provider_id),
    INDEX idx_email (email),
    INDEX idx_role (role)
);
```

### 2.2 Penjelasan Field

| Field | Tipe | Keterangan |
|-------|------|------------|
| `password` | nullable | NULL jika user hanya login via SSO |
| `provider` | string | Identifier SSO: 'siakad', 'google', 'local' |
| `provider_id` | string | ID unik dari SSO (NIM untuk mahasiswa, NIP untuk staff) |
| `provider_data` | JSON | Data mentah dari SSO untuk referensi |
| `primary_auth` | enum | Metode login utama user |
| `linked_at` | timestamp | Kapan akun lokal di-link ke SSO |

---

## 3. Flow Login Lokal

### 3.1 Sequence Diagram

```
User                    App                     Database
 │                       │                         │
 │──── GET /login ──────>│                         │
 │<─── Login Form ───────│                         │
 │                       │                         │
 │── POST /login ───────>│                         │
 │   {email, password}   │                         │
 │                       │── Find user by email ──>│
 │                       │<── User record ─────────│
 │                       │                         │
 │                       │── Verify password ─────>│
 │                       │   (bcrypt compare)      │
 │                       │                         │
 │                       │── Create session ──────>│
 │                       │── Log login activity ──>│
 │                       │                         │
 │<── Redirect Dashboard─│                         │
```

### 3.2 Pseudocode Login Lokal

```php
// Controller: POST /login
function login(Request $request) {
    // 1. Validasi input
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:8'
    ]);
    
    // 2. Cari user
    $user = User::where('email', $credentials['email'])->first();
    
    // 3. Validasi
    if (!$user) {
        return error('Email tidak terdaftar');
    }
    
    if (!$user->password) {
        return error('Akun ini terdaftar via SSO. Silakan login dengan SSO.');
    }
    
    if (!Hash::check($credentials['password'], $user->password)) {
        return error('Password salah');
    }
    
    if (!$user->is_active) {
        return error('Akun tidak aktif');
    }
    
    // 4. Create session
    Auth::login($user);
    
    // 5. Update login info
    $user->update([
        'last_login_at' => now(),
        'last_login_method' => 'local'
    ]);
    
    // 6. Redirect berdasarkan role
    return redirectByRole($user->role);
}
```

---

## 4. Flow Login SSO

### 4.1 OAuth2 Authorization Code Flow

```
User          App              SSO/IdP           Database
 │             │                  │                  │
 │─ Click SSO─>│                  │                  │
 │             │── Redirect ─────>│                  │
 │             │   ?client_id     │                  │
 │             │   &redirect_uri  │                  │
 │             │   &scope         │                  │
 │             │   &state         │                  │
 │             │                  │                  │
 │<────────────│── Login Page ────│                  │
 │── Credentials ────────────────>│                  │
 │             │                  │── Validate ─────>│
 │             │                  │<── User Data ────│
 │             │                  │                  │
 │<────────────│── Callback ──────│                  │
 │             │   ?code=xxx      │                  │
 │             │   &state=yyy     │                  │
 │             │                  │                  │
 │             │── Exchange ─────>│                  │
 │             │   code for token │                  │
 │             │<── Access Token ─│                  │
 │             │                  │                  │
 │             │── Get User Info─>│                  │
 │             │<── User Profile ─│                  │
 │             │                  │                  │
 │             │── Find/Create ───────────────────> │
 │             │   User                             │
 │             │<── User Record ────────────────────│
 │             │                  │                  │
 │<── Session ─│                  │                  │
```

### 4.2 Pseudocode SSO

```php
// Controller: GET /auth/sso/redirect
function redirectToSSO() {
    // Generate state untuk CSRF protection
    $state = Str::random(40);
    session(['sso_state' => $state]);
    
    $params = http_build_query([
        'client_id' => config('sso.client_id'),
        'redirect_uri' => config('sso.redirect_uri'),
        'response_type' => 'code',
        'scope' => 'openid profile email',
        'state' => $state,
    ]);
    
    return redirect(config('sso.authorize_url') . '?' . $params);
}

// Controller: GET /auth/sso/callback
function handleCallback(Request $request) {
    // 1. Validasi state (CSRF protection)
    if ($request->state !== session('sso_state')) {
        return error('Invalid state - possible CSRF attack');
    }
    
    // 2. Exchange code untuk token
    $tokenResponse = Http::post(config('sso.token_url'), [
        'grant_type' => 'authorization_code',
        'client_id' => config('sso.client_id'),
        'client_secret' => config('sso.client_secret'),
        'redirect_uri' => config('sso.redirect_uri'),
        'code' => $request->code,
    ]);
    
    if ($tokenResponse->failed()) {
        return error('Gagal mendapatkan token');
    }
    
    $tokens = $tokenResponse->json();
    
    // 3. Get user info dari SSO
    $userInfo = Http::withToken($tokens['access_token'])
        ->get(config('sso.userinfo_url'))
        ->json();
    
    // 4. Find or create user
    $user = $this->authService->findOrCreateFromSSO($userInfo, $tokens);
    
    // 5. Login
    Auth::login($user);
    
    return redirectByRole($user->role);
}
```

### 4.3 Service: Find or Create User dari SSO

```php
class AuthService {
    
    function findOrCreateFromSSO(array $ssoData, array $tokens): User {
        $providerId = $ssoData['sub'] ?? $ssoData['id']; // NIM/NIP
        $email = $ssoData['email'];
        $provider = 'siakad';
        
        // 1. Cari by provider_id (paling akurat)
        $user = User::where('provider', $provider)
                    ->where('provider_id', $providerId)
                    ->first();
        
        if ($user) {
            return $this->updateSSOUser($user, $ssoData, $tokens);
        }
        
        // 2. Cari by email (untuk linking akun lokal)
        $user = User::where('email', $email)->first();
        
        if ($user) {
            return $this->linkExistingUser($user, $provider, $providerId, $ssoData, $tokens);
        }
        
        // 3. Create new user
        return $this->createSSOUser($provider, $providerId, $ssoData, $tokens);
    }
    
    private function updateSSOUser(User $user, array $ssoData, array $tokens): User {
        $user->update([
            'name' => $ssoData['name'] ?? $user->name,
            'provider_token' => encrypt($tokens['access_token']),
            'provider_refresh_token' => encrypt($tokens['refresh_token'] ?? null),
            'provider_token_expires_at' => now()->addSeconds($tokens['expires_in'] ?? 3600),
            'provider_data' => $ssoData,
            'last_login_at' => now(),
            'last_login_method' => 'sso',
        ]);
        
        return $user;
    }
    
    private function linkExistingUser(User $user, string $provider, string $providerId, 
                                       array $ssoData, array $tokens): User {
        $user->update([
            'provider' => $provider,
            'provider_id' => $providerId,
            'provider_token' => encrypt($tokens['access_token']),
            'provider_data' => $ssoData,
            'linked_at' => now(),
            'last_login_at' => now(),
            'last_login_method' => 'sso',
        ]);
        
        return $user;
    }
    
    private function createSSOUser(string $provider, string $providerId, 
                                    array $ssoData, array $tokens): User {
        return User::create([
            'name' => $ssoData['name'],
            'email' => $ssoData['email'],
            'password' => null, // SSO user tidak punya password lokal
            'provider' => $provider,
            'provider_id' => $providerId,
            'provider_token' => encrypt($tokens['access_token']),
            'provider_data' => $ssoData,
            'role' => $this->mapRole($ssoData),
            'primary_auth' => 'sso',
            'email_verified_at' => now(), // SSO = verified
            'last_login_at' => now(),
            'last_login_method' => 'sso',
        ]);
    }
    
    private function mapRole(array $ssoData): string {
        // Mapping role dari SIAKAD ke aplikasi
        $siakadRole = $ssoData['role'] ?? $ssoData['user_type'] ?? 'student';
        
        return match($siakadRole) {
            'mahasiswa', 'student' => 'student',
            'dosen', 'lecturer' => 'validator',
            'staff', 'admin' => 'admin',
            default => 'student',
        };
    }
}
```

---

## 5. Menghindari Konflik Akun

### 5.1 Skenario Konflik & Solusi

| Skenario | Masalah | Solusi |
|----------|---------|--------|
| User lokal login SSO dengan email sama | Duplikasi akun | Auto-link berdasarkan email |
| User SSO coba register lokal | Akun sudah ada | Tolak, arahkan ke SSO |
| Email SSO berbeda dari email lokal | Tidak ter-link | Manual linking oleh admin |
| User ganti email di SIAKAD | Data tidak sinkron | Update email saat login SSO |

### 5.2 Strategi Linking

```php
// Middleware: Cek konflik sebelum register lokal
function checkAccountConflict(Request $request) {
    $email = $request->email;
    
    $existing = User::where('email', $email)->first();
    
    if ($existing && $existing->provider) {
        // Akun sudah terdaftar via SSO
        return redirect('/login')
            ->with('error', 'Email sudah terdaftar via SSO. Silakan login dengan SSO.');
    }
    
    return $next($request);
}
```

### 5.3 Diagram Resolusi Konflik

```
                    ┌─────────────────┐
                    │  Login Request  │
                    └────────┬────────┘
                             │
                    ┌────────▼────────┐
                    │  Cari by Email  │
                    └────────┬────────┘
                             │
              ┌──────────────┼──────────────┐
              │              │              │
        ┌─────▼─────┐  ┌─────▼─────┐  ┌─────▼─────┐
        │ Not Found │  │ Local Only│  │ Has SSO   │
        └─────┬─────┘  └─────┬─────┘  └─────┬─────┘
              │              │              │
              │         ┌────▼────┐    ┌────▼────┐
              │         │ Login   │    │ Login   │
              │         │ Method? │    │ Method? │
              │         └────┬────┘    └────┬────┘
              │              │              │
        ┌─────▼─────┐   ┌────┴────┐    ┌────┴────┐
        │  Create   │   │Local SSO│    │Local SSO│
        │  New User │   │  ↓   ↓  │    │  ↓   ↓  │
        └───────────┘   │  OK Link│    │ Err  OK │
                        └─────────┘    └─────────┘
```

---

## 6. Migrasi ke Single Gate

### 6.1 Tahapan Migrasi

```
Phase 1: Dual Gate (Sekarang)
├── Login lokal aktif
├── Login SSO aktif (IdP sementara)
└── Kedua metode bisa digunakan

Phase 2: SSO Primary (Transisi)
├── SSO jadi default
├── Login lokal masih ada (untuk fallback)
├── Notifikasi user untuk link akun
└── Deadline untuk migrasi

Phase 3: Single Gate (Target)
├── Hanya SSO SIAKAD
├── Login lokal disabled
├── Semua akun sudah ter-link
└── Password field deprecated
```

### 6.2 Feature Flags

```php
// config/auth.php
return [
    'gates' => [
        'local' => [
            'enabled' => env('AUTH_LOCAL_ENABLED', true),
            'registration' => env('AUTH_LOCAL_REGISTRATION', true),
        ],
        'sso' => [
            'enabled' => env('AUTH_SSO_ENABLED', true),
            'provider' => env('AUTH_SSO_PROVIDER', 'siakad'),
            'force' => env('AUTH_SSO_FORCE', false), // Force SSO only
        ],
    ],
    'migration' => [
        'deadline' => env('AUTH_MIGRATION_DEADLINE', null),
        'notify_users' => env('AUTH_NOTIFY_MIGRATION', false),
    ],
];
```

### 6.3 Middleware untuk Transisi

```php
class EnforceAuthMethod {
    function handle($request, $next) {
        $user = auth()->user();
        
        // Jika SSO force mode aktif
        if (config('auth.gates.sso.force')) {
            // User belum link ke SSO
            if (!$user->provider_id) {
                return redirect('/link-sso')
                    ->with('warning', 'Silakan hubungkan akun Anda dengan SSO SIAKAD');
            }
        }
        
        // Notifikasi deadline migrasi
        $deadline = config('auth.migration.deadline');
        if ($deadline && !$user->provider_id) {
            session()->flash('migration_warning', 
                "Harap link akun ke SSO sebelum {$deadline}");
        }
        
        return $next($request);
    }
}
```

### 6.4 Script Migrasi

```php
// Command: php artisan auth:migrate-to-sso
class MigrateToSSO extends Command {
    function handle() {
        // 1. Disable local registration
        $this->info('Disabling local registration...');
        // Update .env atau database config
        
        // 2. Notify unlinked users
        $unlinked = User::whereNull('provider_id')->get();
        $this->info("Found {$unlinked->count()} unlinked users");
        
        foreach ($unlinked as $user) {
            $user->notify(new LinkAccountReminder());
        }
        
        // 3. Generate report
        $this->table(
            ['Status', 'Count'],
            [
                ['Linked to SSO', User::whereNotNull('provider_id')->count()],
                ['Local Only', User::whereNull('provider_id')->count()],
                ['SSO Only (no password)', User::whereNull('password')->count()],
            ]
        );
    }
}
```

---

## 7. Best Practice Keamanan

### 7.1 Checklist Keamanan

#### Login Lokal
- [ ] Password hashing dengan bcrypt/argon2 (cost factor ≥ 12)
- [ ] Rate limiting: max 5 attempts per 15 menit
- [ ] Account lockout setelah 10 failed attempts
- [ ] CSRF token pada form login
- [ ] Secure session dengan HttpOnly & Secure flags
- [ ] Password complexity requirements
- [ ] Brute force protection

#### Login SSO
- [ ] State parameter untuk CSRF protection
- [ ] Validasi redirect_uri (whitelist)
- [ ] Token encryption at rest
- [ ] Token expiry handling
- [ ] Secure token exchange (HTTPS only)
- [ ] PKCE untuk public clients (optional)

### 7.2 Implementasi Rate Limiting

```php
// Middleware: RateLimitLogin
function handle($request, $next) {
    $key = 'login_attempts:' . $request->ip();
    
    if (RateLimiter::tooManyAttempts($key, 5)) {
        $seconds = RateLimiter::availableIn($key);
        return response()->json([
            'error' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik."
        ], 429);
    }
    
    $response = $next($request);
    
    // Increment on failed login
    if ($response->status() === 401) {
        RateLimiter::hit($key, 900); // 15 menit
    } else {
        RateLimiter::clear($key);
    }
    
    return $response;
}
```

### 7.3 Secure Session Configuration

```php
// config/session.php
return [
    'driver' => 'database', // atau redis untuk production
    'lifetime' => 120,
    'expire_on_close' => false,
    'encrypt' => true,
    'cookie' => 'app_session',
    'secure' => env('SESSION_SECURE_COOKIE', true), // HTTPS only
    'http_only' => true,
    'same_site' => 'lax',
];
```

### 7.4 Token Storage Security

```php
// Encrypt tokens sebelum simpan
$user->provider_token = encrypt($accessToken);
$user->provider_refresh_token = encrypt($refreshToken);

// Decrypt saat digunakan
$accessToken = decrypt($user->provider_token);
```

### 7.5 Audit Logging

```php
// Log setiap aktivitas auth
function logAuthActivity(User $user, string $action, array $metadata = []) {
    AuthLog::create([
        'user_id' => $user->id,
        'action' => $action, // 'login', 'logout', 'failed_login', 'sso_link'
        'method' => $metadata['method'] ?? 'local',
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'metadata' => $metadata,
        'created_at' => now(),
    ]);
}
```

---

## 8. Contoh Konfigurasi Environment

```env
# ===== AUTH CONFIGURATION =====

# Local Auth
AUTH_LOCAL_ENABLED=true
AUTH_LOCAL_REGISTRATION=true

# SSO Configuration
AUTH_SSO_ENABLED=true
AUTH_SSO_PROVIDER=siakad
AUTH_SSO_FORCE=false

# SSO SIAKAD Credentials
SIAKAD_SSO_URL=https://sso.siakad.unpatti.ac.id
SIAKAD_SSO_CLIENT_ID=prestasi-app
SIAKAD_SSO_CLIENT_SECRET=your-secret-key
SIAKAD_SSO_REDIRECT_URI=https://prestasi.unpatti.ac.id/auth/sso/callback
SIAKAD_SSO_SCOPES=openid,profile,email,role

# Migration Settings
AUTH_MIGRATION_DEADLINE=2026-06-01
AUTH_NOTIFY_MIGRATION=true

# Security
SESSION_SECURE_COOKIE=true
SESSION_LIFETIME=120
```

---

## 9. Ringkasan Alur

```
┌────────────────────────────────────────────────────────────────┐
│                    DUAL GATE LOGIN FLOW                        │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  ┌──────────┐         ┌──────────┐         ┌──────────┐       │
│  │  LOCAL   │         │   SSO    │         │  LINKED  │       │
│  │  LOGIN   │         │  LOGIN   │         │  ACCOUNT │       │
│  └────┬─────┘         └────┬─────┘         └────┬─────┘       │
│       │                    │                    │              │
│       ▼                    ▼                    ▼              │
│  ┌─────────┐          ┌─────────┐          ┌─────────┐        │
│  │Validate │          │Redirect │          │ Either  │        │
│  │Password │          │to IdP   │          │ Method  │        │
│  └────┬────┘          └────┬────┘          └────┬────┘        │
│       │                    │                    │              │
│       │                    ▼                    │              │
│       │               ┌─────────┐               │              │
│       │               │Callback │               │              │
│       │               │+ Token  │               │              │
│       │               └────┬────┘               │              │
│       │                    │                    │              │
│       └────────────────────┼────────────────────┘              │
│                            ▼                                   │
│                    ┌───────────────┐                           │
│                    │ Find/Create   │                           │
│                    │ User Record   │                           │
│                    └───────┬───────┘                           │
│                            ▼                                   │
│                    ┌───────────────┐                           │
│                    │Create Session │                           │
│                    │& Redirect     │                           │
│                    └───────────────┘                           │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

---

*Dokumen ini adalah panduan implementasi. Sesuaikan dengan kebutuhan dan infrastruktur yang tersedia.*
