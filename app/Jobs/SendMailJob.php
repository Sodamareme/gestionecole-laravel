<?php

namespace App\Jobs;

use App\Mail\InscriptionApprenantMail; // Correct import
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailData;

    public function __construct($mailData)
    {
        $this->mailData = $mailData;
    }

    public function handle()
    {
        // Envoyer l'email à l'apprenant avec la classe correcte Mailable
        Mail::to($this->mailData['email'])->send(new InscriptionApprenantMail($this->mailData));
    }
}