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

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $baseMsg;
    public $subject;
    public $channel;

    // Data dari BroadcastService bakal dilempar ke sini
    public function __construct($user, $baseMsg, $subject, $channel)
    {
        $this->user = $user;
        $this->baseMsg = $baseMsg;
        $this->subject = $subject;
        $this->channel = $channel;
    }

    public function handle(): void
    {
        $greeting = "Kepada Yth. {$this->user->name},\n\n";
        $finalMsg = $greeting . $this->baseMsg;

        // 1. Tembak WA (Pake Try-Catch biar aman)
        if (in_array($this->channel, ['all', 'wa']) && !empty($this->user->whatsapp) && env('WA_NOTIFICATION_ENABLED', true)) {
            try {
                FonnteService::send($this->user->whatsapp, $this->baseMsg);

                // Rem ABS 3 Detik biar WA gak ke-banned
                sleep(rand(3, 5));
            } catch (\Exception $e) {
                Log::error("WA Queue Error ke {$this->user->whatsapp}: " . $e->getMessage());
            }
        }

        // 2. Tembak Email (Pake Try-Catch biar gak bikin sistem crash)
        if (in_array($this->channel, ['all', 'email']) && !empty($this->user->email)) {
            try {
                Mail::to($this->user->email)->send(new ProjectNotificationMail($finalMsg, $this->subject));

                // Kasih jeda 5 detik antar email biar server Plesk gak teriak Spam 554 5.7.0!
                sleep(rand(5, 7));
            } catch (\Exception $e) {
                Log::error("Email Queue Error ke {$this->user->email}: " . $e->getMessage());
            }
        }
    }
}
