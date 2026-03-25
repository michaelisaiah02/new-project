<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Mail\ProjectNotificationMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BroadcastService
{
    /**
     * Kirim notif ke WA dan Email sekaligus
     *
     * @param  \Illuminate\Support\Collection  $users  (Kumpulan object User dari query)
     * @param  string  $msg
     * @param  string  $subject
     */
    public static function sendBatch($targetsTo, $targetsCc, $baseMsgWa, $baseMsgEmail, $subject = 'PT CAR - Project Notification', $channel = 'all')
    {
        // ==========================================
        // 1. JALUR WA (Pakai Pesan Lama & Personal)
        // ==========================================
        $allUsersForWa = collect($targetsTo)->merge($targetsCc)->unique('id');

        if (in_array($channel, ['all', 'wa']) && env('WA_NOTIFICATION_ENABLED', true)) {
            foreach ($allUsersForWa as $user) {
                if (!empty($user->whatsapp)) {
                    SendNotificationJob::dispatch($user, $baseMsgWa, $subject, 'wa');
                }
            }
        }

        // ==========================================
        // 2. JALUR EMAIL (Pakai Pesan Baru & Bulk)
        // ==========================================
        if (in_array($channel, ['all', 'email'])) {
            $toEmails = collect($targetsTo)->pluck('email')->filter()->toArray();
            $ccEmails = collect($targetsCc)->pluck('email')->filter()->toArray();

            if (!empty($toEmails)) {
                try {
                    // Tembak massal 1x hit! 🚀
                    Mail::to($toEmails)
                        ->cc($ccEmails)
                        ->send(new ProjectNotificationMail($baseMsgEmail, $subject));

                    Log::info("Sukses ngirim Bulk Email ke " . count($toEmails) . " TO dan " . count($ccEmails) . " CC");
                } catch (\Exception $e) {
                    Log::error("Gagal ngirim Bulk Email: " . $e->getMessage());
                }
            }
        }
    }
}
