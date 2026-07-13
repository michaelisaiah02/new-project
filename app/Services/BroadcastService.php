<?php

namespace App\Services;

use App\Helpers\WhatsappHelper;
use App\Jobs\SendNotificationJob;
use App\Mail\ProjectNotificationMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
        // 1. JALUR WA (Pesan Diacak & Unik per User)
        // ==========================================
        $allUsersForWa = collect($targetsTo)->merge($targetsCc)->unique('id');

        if (in_array($channel, ['all', 'wa']) && env('WA_NOTIFICATION_ENABLED', true)) {
            foreach ($allUsersForWa as $user) {
                if (!empty($user->whatsapp)) {

                    // A. Sapaan dinamis berdasarkan jam & nama user (Biar personal)
                    $greetingTemplate = WhatsappHelper::getGreeting() . " *{$user->name}*,\n\n";

                    // B. Call-to-Action (CTA) buat balas pesan (Di-spintax juga biar variatif)
                    $ctaTemplate = "\n\n_{Ref ID: " . date("ymd-His") . "-" . Str::random(4) . "}_\n" .
                        "{Mohon balas pesan ini jika sudah dibaca (OK / SIAP / Laksanakan).|Harap balas OK / SIAP / Laksanakan jika sudah dibaca.|Tolong konfirmasi dengan membalas pesan ini (OK / SIAP / Laksanakan).} " .
                        "{Terima kasih.|Terima kasih banyak.|Atas perhatiaanya terima kasih.}";

                    // C. Gabungkan: Sapaan + Pesan Asli (dari Observer) + Penutup
                    $rawWaMessage = $greetingTemplate . $baseMsgWa . $ctaTemplate;

                    // D. SPIN TEXT! (Fungsi helper bakal ngacak kata-kata di dalam {...|...})
                    $finalMsgWa = WhatsappHelper::spinText($rawWaMessage);

                    // E. Lempar ke Job Queue
                    SendNotificationJob::dispatch($user, $finalMsgWa, $subject, 'wa');
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
