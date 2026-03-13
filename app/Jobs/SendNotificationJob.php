<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProjectNotificationMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter; // 👈 Panggil dewa pembatasnya di sini

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $baseMsg;
    public $subject;
    public $channel;

    public function __construct($user, $baseMsg, $subject, $channel)
    {
        $this->user = $user;
        $this->baseMsg = $baseMsg;
        $this->subject = $subject;
        $this->channel = $channel;
    }

    public function handle(): void
    {
        // JURUS RATE LIMITING: Maksimal 30 eksekusi per 1 Jam (3600 detik)
        $executed = RateLimiter::attempt(
            'notif-limiter-new-project', // Nama ID antreannya (bebas)
            30, // Maksimal tembakan
            function () {
                // === KALAU KUOTA MASIH ADA, EKSEKUSI BLOK INI ===
                $greeting = "Kepada Yth. {$this->user->name},\n\n";
                $finalMsg = $greeting . $this->baseMsg;

                // 1. Tembak WA (Real-time sat-set!)
                if (in_array($this->channel, ['all', 'wa']) && !empty($this->user->whatsapp) && env('WA_NOTIFICATION_ENABLED', true)) {
                    try {
                        FonnteService::send($this->user->whatsapp, $this->baseMsg);

                        sleep(rand(60, 120));
                    } catch (\Exception $e) {
                        Log::error("WA Queue Error: " . $e->getMessage());
                    }
                }

                // 2. Tembak Email
                if (in_array($this->channel, ['all', 'email']) && !empty($this->user->email)) {
                    try {
                        Mail::to($this->user->email)->send(new ProjectNotificationMail($finalMsg, $this->subject));
                        sleep(10);
                    } catch (\Exception $e) {
                        Log::error("Email Queue Error: " . $e->getMessage());
                    }
                }
            },
            3600
        );

        // === KALAU KUOTA 30/JAM UDAH HABIS ===
        if (! $executed) {
            // 600 detik = 10 Menit.
            // Jadi sistem lo bakal otomatis nyoba ngirim sisanya secara berkala tanpa bikin error.
            $this->release(600);
            Log::warning("Rate limit reached for SendNotificationJob. Job released back to queue to retry after 10 minutes.");
        }
    }
}
