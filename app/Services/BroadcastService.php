<?php

namespace App\Services;

use App\Mail\ProjectNotificationMail;
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
    public static function send($users, $baseMsg, $subject = 'PT CAR - Project Update')
    {
        // Looping per user biar bisa disapa namanya!
        foreach ($users as $user) {

            // 1. Bikin sapaan "Kepada Yth. [Nama User]"
            $greeting = "Kepada Yth. {$user->name},\n\n";

            // 2. Gabungin sapaan sama pesan utama dari Observer/Cron
            $finalMsg = $greeting.$baseMsg;

            // 3. Tembak WA (Pake Rem ABS)
            if (! empty($user->whatsapp) && env('WA_NOTIFICATION_ENABLED', true)) {
                FonnteService::send($user->whatsapp, $baseMsg);
                sleep(rand(1, 4));
            }

            // 4. Tembak Email
            if (! empty($user->email)) {
                Mail::to($user->email)->send(new ProjectNotificationMail($finalMsg, $subject));
            }
        }
    }
}
