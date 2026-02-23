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
    public static function send($users, $baseMsg, $subject = 'PT CAR - Project Notification', $channel = 'all')
    {
        foreach ($users as $user) {
            $greeting = "Kepada Yth. {$user->name},\n\n";
            $finalMsg = $greeting.$baseMsg;

            // KONDISI 1: Tembak WA HANYA JIKA channel-nya 'all' atau 'wa'
            if (in_array($channel, ['all', 'wa']) && ! empty($user->whatsapp) && env('WA_NOTIFICATION_ENABLED', true)) {
                FonnteService::send($user->whatsapp, $baseMsg);
                sleep(rand(1, 4));
            }

            // KONDISI 2: Tembak Email HANYA JIKA channel-nya 'all' atau 'email'
            if (in_array($channel, ['all', 'email']) && ! empty($user->email)) {
                Mail::to($user->email)->send(new ProjectNotificationMail($finalMsg, $subject));
            }
        }
    }
}
