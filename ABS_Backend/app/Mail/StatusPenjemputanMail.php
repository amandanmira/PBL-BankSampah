<?php

namespace App\Mail;

use App\Models\Penjemputan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StatusPenjemputanMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $penjemputan;
    public $status_type;

    /**
     * Create a new message instance.
     */
    public function __construct(Penjemputan $penjemputan, string $status_type)
    {
        $this->penjemputan = $penjemputan;
        $this->status_type = $status_type;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'menunggu_persetujuan' => 'Konfirmasi Jadwal Penjemputan Sampah Anda',
            'proses' => 'Jadwal Penjemputan Sampah Terkonfirmasi',
            'dijemput' => 'Tukang Sedang Menuju Lokasi Anda!',
            'perlu_input' => 'Petugas Memproses Sampah Anda!',
            'selesai' => 'Penjemputan Selesai - Saldo Telah Ditambahkan',
        ];

        return new Envelope(
            subject: $subjects[$this->status_type] ?? 'Update Status Penjemputan Sampah',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.status_penjemputan',
            with: [
                'penjemputan' => $this->penjemputan,
                'status_type' => $this->status_type,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
