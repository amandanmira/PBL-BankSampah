<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PengepulDitolak extends Mailable
{
    use Queueable, SerializesModels;

    public $namaPengepul;
    public $ket_status;

    public function __construct($namaPengepul, $ket_status)
    {
        $this->namaPengepul = $namaPengepul;
        $this->ket_status = $ket_status;
    }

    public function build()
    {
        return $this->subject('Informasi Pendaftaran Pengepul')
            ->view('emails.pengepul_ditolak');
    }
}
