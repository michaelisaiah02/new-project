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
    public static function sendBatch($targetsTo, $targetsCc, $baseMsgWa, $baseMsgEmail, $divisiName, $subject = 'PT CAR - Project Notification')
    {
        // ==========================================
        // 1. JALUR WA (Pakai Pesan Lama & Personal)
        // ==========================================
        $allUsersForWa = collect($targetsTo)->merge($targetsCc)->unique('id');

        if (env('WA_NOTIFICATION_ENABLED', true)) {
            foreach ($allUsersForWa as $user) {
                if (!empty($user->whatsapp)) {
                    // Lempar $baseMsgWa ke Tuyul Antrean.
                    // Nanti di dalem Job, sapaan "Kepada Yth. Nama User" otomatis ditambahin.
                    SendNotificationJob::dispatch($user, $baseMsgWa, $subject, 'wa');
                }
            }
        }

        // ==========================================
        // 2. JALUR EMAIL (Pakai Pesan Baru & Bulk)
        // ==========================================
        $toEmails = collect($targetsTo)->pluck('email')->filter()->toArray();
        $ccEmails = collect($targetsCc)->pluck('email')->filter()->toArray();

        if (!empty($toEmails)) {
            // Racik sapaan divisi khusus buat Email
            $greetingEmail = "Kepada Yth. {$divisiName},\n\n";
            $finalMsgEmail = $greetingEmail . $baseMsgEmail;

            try {
                // Tembak massal 1x hit! 🚀
                Mail::to($toEmails)
                    ->cc($ccEmails)
                    ->send(new ProjectNotificationMail($finalMsgEmail, $subject));

                Log::info("Sukses ngirim Bulk Email ke " . count($toEmails) . " TO dan " . count($ccEmails) . " CC");
            } catch (\Exception $e) {
                Log::error("Gagal ngirim Bulk Email: " . $e->getMessage());
            }
        }
    }
}
