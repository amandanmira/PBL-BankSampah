<?php

namespace App\Mail;

use App\Models\Pengepul;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PengepulDiterima extends Mailable
{
    use Queueable, SerializesModels;

    public $pengepul;

    public function __construct(Pengepul $pengepul)
    {
        $this->pengepul = $pengepul;
    }

    public function build()
    {
        return $this->subject('Selamat! Pendaftaran Pengepul Diterima')
                    ->view('emails.pengepul_diterima');
    }
}
