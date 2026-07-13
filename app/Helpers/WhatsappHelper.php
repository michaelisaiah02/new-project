<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class WhatsappHelper
{
    /**
     * Engine Spintax (Bisa Nested)
     */
    public static function spinText(string $text): string
    {
        return preg_replace_callback(
            '/{(((?>[^\{\}]+)|(?R))*)}/x',
            function ($match) {
                $inner = self::spinText($match[1]);
                $parts = explode('|', $inner);
                return $parts[array_rand($parts)];
            },
            $text
        );
    }

    /**
     * Otomatis milih sapaan berdasarkan jam + langsung di-spintax!
     */
    public static function getGreeting(): string
    {
        $hour = (int) date('H');

        if ($hour < 11) {
            $greeting = "{Selamat pagi|Pagi|Halo|Hai|Good Morning}";
        } elseif ($hour < 15) {
            $greeting = "{Selamat siang|Siang|Halo|Hai|Good Afternoon}";
        } elseif ($hour < 18) {
            $greeting = "{Selamat sore|Sore|Halo|Hai}";
        } else {
            $greeting = "{Selamat malam|Malam|Halo|Hai}";
        }

        return self::spinText($greeting);
    }

    /**
     * MASTER FORMATTER - Panggil ini di semua Observer & Command lo!
     */
    public static function formatAntiBan(string $rawMessage): string
    {
        // 1. Ganti kata '{GREETING}' di template dengan sapaan otomatis
        $message = str_replace('{GREETING}', self::getGreeting(), $rawMessage);

        // 2. Putar teksnya kalau ada sisipan {kata1|kata2}
        $spunMessage = self::spinText($message);

        // 3. Bikin Kode Unik (Watermark Anti-Spam Meta)
        $timestamp = now()->format('ymd-His');
        $randomCode = Str::random(4);
        $watermark = "\n\n_Ref: {$timestamp}-{$randomCode}_";

        // 4. Return hasil akhir
        return $spunMessage . $watermark;
    }
}
