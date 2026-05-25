<?php

namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SsoAuthService;
use App\Support\OperationalLogContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected SsoAuthService $ssoService;

    public function __construct(SsoAuthService $ssoService)
    {
        $this->ssoService = $ssoService;
    }

    public function redirect(Request $request)
    {
        $request->session()->put('state', $state = Str::random(40));

        return redirect($this->ssoService->getAuthorizationUrl($state));
    }

    public function callback(Request $request)
    {
        $requestState = $request->input('state');
        $sessionState = $request->session()->get('state');

        if (! $requestState || ! $sessionState || ! hash_equals($sessionState, $requestState)) {
            Log::warning('Invalid OAuth state received', [
                'has_request_state' => (bool) $requestState,
                'has_session_state' => (bool) $sessionState,
            ] + OperationalLogContext::authFailure('oauth_state_mismatch'));

            abort(403, 'Invalid OAuth state');
        }

        $request->session()->forget('state');

        try {
            Log::info('SSO Callback Started', [
                'code' => $request->code ? 'present' : 'missing',
                'state' => $request->state ? 'present' : 'missing',
            ]);

            $accessToken = $this->ssoService->exchangeCodeForToken($request->code);

            if (! $accessToken) {
                return redirect()->route('login')->with('error', 'Gagal mendapatkan access token');
            }

            $userInfo = $this->ssoService->getUserInfo($accessToken);

            if (! $userInfo) {
                return redirect()->route('login')->with('error', 'Gagal mendapatkan data user');
            }

            // Detect role from email pattern or existing record
            $email = strtolower($userInfo['email'] ?? '');
            $isStudentEmail = str_contains($email, '@student.ac.id') || str_contains($email, '@student.unpatti.ac.id');
            $existingUser = User::withTrashed()->where('email', $email)->first();

            if ($existingUser) {
                if ($existingUser->trashed()) {
                    return redirect()->route('login')
                        ->with('error', 'Akun Anda ('.$email.') telah dinonaktifkan. Silakan hubungi Administrator.');
                }

                if (strtolower($existingUser->role) === 'student' || $isStudentEmail) {
                    return $this->handleStudentLogin($userInfo, $accessToken);
                }

                return $this->handleStaffLogin($userInfo, $accessToken);
            }

            if ($isStudentEmail) {
                return $this->handleStudentLogin($userInfo, $accessToken);
            }

            return $this->handleStaffLogin($userInfo, $accessToken);

        } catch (\Exception $e) {
            Log::error('SSO Callback Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat autentikasi: '.$e->getMessage());
        }
    }

    private function handleStudentLogin($userInfo, $accessToken)
    {
        $result = $this->ssoService->handleStudentLogin($userInfo, $accessToken);

        if (! $result['success']) {
            return redirect()->route('login')->with('error', $result['error']);
        }

        $message = $result['has_achievements']
            ? 'Selamat datang kembali!'
            : ($result['siakad_success']
                ? 'Selamat datang! Silakan ajukan prestasi pertama Anda.'
                : 'Selamat datang! Beberapa data profil tidak tersedia. Silakan ajukan prestasi pertama Anda.');

        return redirect()->route($result['redirect'])
            ->with($result['has_achievements'] ? 'success' : ($result['siakad_success'] ? 'info' : 'warning'), $message);
    }

    private function handleStaffLogin($userInfo, $accessToken)
    {
        $result = $this->ssoService->handleStaffLogin($userInfo, $accessToken);

        if (! $result['success']) {
            return redirect()->route('login')->with('error', $result['error']);
        }

        return redirect()->route($result['redirect'])
            ->with('success', 'Selamat datang, '.($userInfo['name'] ?? 'User').'!');
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();
        $requestedRedirect = $request->input('logout_redirect') ?? $request->session()->get('origin_url');
        $redirectUrl = $this->isSafeRedirect($requestedRedirect) ? $requestedRedirect : '/dashboard';

        Log::info('SSO logout redirect resolved', [
            'user_id' => $userId,
            'redirect' => $redirectUrl,
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($redirectUrl);
    }

    private function isSafeRedirect(?string $url): bool
    {
        return is_string($url) && str_starts_with($url, '/');
    }
}
