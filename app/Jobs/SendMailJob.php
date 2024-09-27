<?php

namespace App\Jobs;
// Assurez-vous d'importer Mail
use App\Mail\AccountCreated; // Assurez-vous d'importer le Mailable
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $qrCodeUrl;

    /**
     * Create a new job instance.
     *
     * @param string $email
     * @param string $qrCodeUrl
     */
    public function __construct($email, $qrCodeUrl)
    {
        $this->email = $email;
        $this->qrCodeUrl = $qrCodeUrl;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send(new AccountCreated($this->qrCodeUrl));
    }
}
