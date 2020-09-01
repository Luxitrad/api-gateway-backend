<?php

namespace App\Mail;

use Sichikawa\LaravelSendgridDriver\SendGrid;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerification extends Mailable
{
    use Queueable, SerializesModels, SendGrid;

    public $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $address = 'dev@worthboard.com';
        $subject = 'This is a demo!';
        $name = 'Jane Doe';

        return $this->view('emails.userverify')
            ->from($address, $name)
            ->cc($address, $name)
            ->bcc($address, $name)
            ->replyTo($address, $name)
            ->subject($subject)
            ->with(['user' => $this->user]);

        dd('Mail sent');
    }
}
