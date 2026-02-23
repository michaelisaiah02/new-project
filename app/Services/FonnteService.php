<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    public static function send($target, $message)
    {
        // Pastikan target valid dan bukan null
        if (empty($target)) {
            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN'), // Masukin token di .env
            ])->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
            ]);

            // Optional: Log kalo gagal
            if ($response->failed()) {
                Log::error('Fonnte Error: '.$response->body());
            }
        } catch (\Exception $e) {
            Log::error('Fonnte Exception: '.$e->getMessage());
        }
    }
}
