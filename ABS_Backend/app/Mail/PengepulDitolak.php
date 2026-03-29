<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PengepulDitolak extends Mailable
{
    use Queueable, SerializesModels;

    public $namaPengepul;

    public function __construct($namaPengepul)
    {
        $this->namaPengepul = $namaPengepul;
    }

    public function build()
    {
        return $this->subject('Informasi Pendaftaran Pengepul')
                    ->view('emails.pengepul_ditolak');
    }
}
