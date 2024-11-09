<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Anggota;
use Exception;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SocialiteController extends Controller
{
    /**
     * Function: authProviderRedirect
     * Description: This function will redirect to Given Provider
     * @param string $provider
     * @return void
     */
    public function authProviderRedirect($provider) {
        if (in_array($provider, ['google', 'facebook', 'twitter'])) { // Cek apakah provider valid
            return Socialite::driver($provider)->redirect();
        }
        abort(404); // Jika provider tidak valid, tampilkan 404
    }

    /**
     * Function: socialAuthentication
     * Description: This function will authenticate the user through the OAuth provider (e.g., Google)
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    

     public function socialAuthentication($provider) {
        try {
            if ($provider) {
                // Mendapatkan data pengguna dari provider (misalnya Google)
                $socialUser = Socialite::driver($provider)->user();
    
                // Cek apakah email sudah terdaftar di tabel anggota
                $anggota = Anggota::where('email', $socialUser->email)->first();
    
                if (!$anggota) {
                    return redirect()->route('login')->with('error', "Tidak Terdaftar Pi Email Ta' Kanda.");
                }
    
                // Cek apakah pengguna sudah ada di database berdasarkan auth_provider_id (ID Google)
                $user = User::where('auth_provider_id', $socialUser->id)->first();
    
                if ($user) {
                    // Jika pengguna sudah ada, login
                    Auth::login($user);
                } else {
                    // Jika pengguna belum ada, buat akun baru hanya jika belum ada email yang sama
                    $userWithEmail = User::where('email', $socialUser->email)->first();
                    
                    if ($userWithEmail) {
                        // Jika email sudah terdaftar, langsung login pengguna yang ada
                        Auth::login($userWithEmail);
                    } else {
                        // Buat akun baru jika email belum ada
                        $userData = User::create([
                            'name' => $socialUser->name,
                            'email' => $socialUser->email,
                            'password' => bcrypt('ppginf005'),
                            'auth_provider_id' => $socialUser->id,
                            'auth_provider' => $provider,
                        ]);
    
                        // Jika pengguna berhasil dibuat, login
                        Auth::login($userData);
                    }
                }
    
                return redirect()->route('dashboard'); // Redirect ke halaman dashboard setelah login
            }
            abort(404); // Jika provider tidak ditemukan, tampilkan 404
    
        } catch (Exception $e) {
            Log::error('Social authentication error: ' . $e->getMessage()); // Log error
            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat login dengan ' . $provider . '. Silakan coba lagi.'); // Redirect dengan pesan error
        }
    }
    
}
