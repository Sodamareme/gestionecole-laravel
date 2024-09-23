<?php

namespace App\Listeners;

use App\Events\UserCreated;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoyaltyCardMail;

class SendLoyaltyCard
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\UserCreated  $event
     * @return void
     */
    public function handle(UserCreated $event)
    {
        $user = $event->user;
        $qr_code_url = asset('storage/qrcodes/' . $user->id . '.png');

        Mail::to($user->login)->send(new LoyaltyCardMail($user, $qr_code_url));
    }
}
