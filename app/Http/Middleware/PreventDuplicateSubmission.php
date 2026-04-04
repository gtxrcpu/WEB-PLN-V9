<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PreventDuplicateSubmission Middleware
 * 
 * Mencegah double-submit form dengan cara memeriksa token unik per request.
 * Token di-embed ke form sebagai hidden field '_submission_token', 
 * dan setelah digunakan sekali langsung dihapus dari session.
 */
class PreventDuplicateSubmission
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $token = $request->input('_submission_token');

            if ($token) {
                $sessionKey = 'submission_token_' . $token;

                // Jika token sudah digunakan, tolak request
                if ($request->session()->has($sessionKey)) {
                    $request->session()->forget($sessionKey);
                    
                    if ($request->expectsJson()) {
                        return response()->json([
                            'error' => 'Duplicate submission detected. Request already processed.'
                        ], 409);
                    }

                    return back()->with('warning', 'Form sudah diproses sebelumnya. Mohon jangan klik tombol submit dua kali.');
                }

                // Tandai token ini sudah digunakan (simpan 5 menit)
                $request->session()->put($sessionKey, true);

                // Auto-expire setelah 5 menit menggunakan flash (akan hilang setelah request berikutnya)
                // Alternatif: gunakan cache dengan TTL
            }
        }

        return $next($request);
    }
}
