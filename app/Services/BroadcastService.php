<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;

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
            // Daripada ngirim langsung, kita lempar ke Job buat diantriin di background!
            SendNotificationJob::dispatch($user, $baseMsg, $subject, $channel);
        }
    }
}
