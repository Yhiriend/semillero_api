<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordTokenMail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetUrl;
    public $email;

    public function __construct($token, $email)
    {
        $this->resetUrl = env('APP_FRONTEND') . '/auth/reset-password?token=' . $token;
        $this->email = $email;
    }

    public function build()
    {
        return $this->subject('Recuperación de contraseña')
                    ->view('emails.reset-password')
                    ->with([
                        'resetUrl' => $this->resetUrl,
                        'email' => $this->email,
                    ]);
    }
}
