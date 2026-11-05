<?php

namespace App\Support;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Pengganti halaman error 419 ("Page Expired"). Token CSRF di form tidak cocok
 * lagi dengan sesi bila halaman dibuka lebih lama dari SESSION_LIFETIME, atau
 * sesi berganti karena masuk/keluar di tab lain. Permintaannya tetap ditolak;
 * pengguna hanya dibawa kembali ke form dengan penjelasan, bukan halaman error.
 */
final class ExpiredFormRedirect
{
    /** Isian yang tidak boleh dikembalikan ke form. */
    private const SECRET_INPUT = ['_token', '_method', 'password', 'password_confirmation', 'current_password'];

    public function __invoke(HttpException $e, Request $request): ?RedirectResponse
    {
        if ($e->getStatusCode() !== 419 || $request->expectsJson()) {
            return null;
        }

        $loggedIn = $request->user() !== null;

        if ($request->routeIs('admin.logout')) {
            return $loggedIn
                ? redirect()->route('admin.dashboard')->with('warning', 'Halaman sudah kedaluwarsa. Klik Keluar sekali lagi.')
                : redirect()->route('login')->with('status', 'Anda sudah keluar.');
        }

        if ($request->routeIs('login.store')) {
            return redirect()->route('login')
                ->withInput($request->only('login', 'remember'))
                ->with('warning', 'Halaman masuk sudah kedaluwarsa, jadi Anda belum masuk. Silakan masukkan kata sandi lagi.');
        }

        if (! $loggedIn) {
            // Sesi habis saat mengisi form: setelah masuk lagi, kembali ke halaman form tadi.
            // Hanya halaman dashboard di situs ini (Referer bisa dari situs lain: jangan jadi open redirect).
            $previous = url()->previous();
            if (str_starts_with($previous, url('/admin').'/') || $previous === url('/admin')) {
                $request->session()->put('url.intended', $previous);
            }

            return redirect()->route('login')
                ->with('warning', 'Sesi Anda sudah berakhir karena terlalu lama tidak aktif. Silakan masuk lagi; isian tadi belum tersimpan.');
        }

        return back()
            ->withInput($request->except(self::SECRET_INPUT))
            ->with('warning', 'Halaman ini sudah kedaluwarsa sehingga data belum tersimpan. Periksa isiannya lalu kirim sekali lagi.');
    }
}
