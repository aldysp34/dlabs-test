<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Pengecualian: jika URL adalah '/login' atau '/register', lewati middleware ini
        if ($this->isExcludedRoute($request)) {
            return $next($request);
        }

        try {
            // Coba ambil dan verifikasi token JWT
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            // Jika token tidak ada atau tidak valid
            return response()->json(['error' => 'Token is invalid or expired'], 401);
        }

        // Menambahkan informasi pengguna ke request untuk digunakan di controller
        $request->attributes->add(['user' => $user]);

        return $next($request);
    }

    /**
     * Fungsi untuk mengecek apakah rute saat ini perlu pengecualian atau tidak
     *
     * @param Request $request
     * @return bool
     */
    protected function isExcludedRoute(Request $request)
    {
        // Daftar URL atau metode yang ingin dikecualikan dari pengecekan JWT
        $excludedRoutes = [
            'api/login',
            'api/register',
            'api/users/',
            'api/users/?'
        ];

        // Cek apakah rute yang diminta ada dalam pengecualian
        return in_array($request->path(), $excludedRoutes);
    }
}
