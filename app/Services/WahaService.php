<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WahaService
{
    public static function send($target, $message)
    {
        // Pastikan target valid dan bukan null
        if (empty($target)) {
            return;
        }

        // 1. Bersihin Karakter Aneh (Spasi, Strip, dll)
        $cleanPhone = preg_replace('/[^0-9]/', '', $target);

        // 2. Format ke standar WAHA (Wajib awalan 62)
        if (str_starts_with($cleanPhone, '0')) {
            // Kalau depannya 0, potong 0-nya ganti 62
            $cleanPhone = '62' . substr($cleanPhone, 1);
        } elseif (str_starts_with($cleanPhone, '8')) {
            $cleanPhone = '62' . $cleanPhone;
        }

        // 3. Tambahin buntut @c.us buat chat personal
        $chatId = $cleanPhone . '@c.us';

        try {
            // Ambil URL server WAHA dari .env (Contoh: http://localhost:3000)
            $wahaUrl = env('WAHA_URL', 'http://localhost:3000');
            // Ambil nama session WAHA dari .env (Biasanya 'default')
            $wahaSession = env('WAHA_SESSION', 'default');

            // Tembak API WAHA! 🚀
            $response = Http::withHeaders([
                'X-Api-Key' => env('WAHA_API_KEY', ''),
                'Accept'    => 'application/json',
            ])->post("{$wahaUrl}/api/sendText", [
                'session' => $wahaSession,
                'chatId'  => $chatId,
                'text'    => $message,
            ])->throw();

            Log::info("WAHA Response [{$chatId}]: " . json_encode($response->json()));

            // Optional: Log kalo WAHA nolak
            if ($response->failed()) {
                Log::error("WAHA Error [{$chatId}]: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('WAHA Exception: ' . $e->getMessage());
        }
    }
}
