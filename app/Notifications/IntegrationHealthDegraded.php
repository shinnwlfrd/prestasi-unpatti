<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IntegrationHealthDegraded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $service,
        protected int $consecutiveFailures,
        protected ?string $latestMessage = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('[Integrasi] Layanan Eksternal Mengalami Gangguan Berulang')
            ->greeting('Halo '.$notifiable->name.'!')
            ->line("Layanan {$this->service} terdeteksi gagal {$this->consecutiveFailures} kali berturut-turut.")
            ->line('Gangguan ini berpotensi memengaruhi sinkronisasi dan referensi data di SIMAPRES.')
            ->line('Detail terakhir: '.($this->latestMessage ?? 'Tidak ada detail tambahan.'))
            ->action('Buka Dashboard Admin', url('/admin'))
            ->line('Mohon segera lakukan pengecekan pada integrasi terkait.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'service' => $this->service,
            'consecutive_failures' => $this->consecutiveFailures,
            'latest_message' => $this->latestMessage,
            'message' => "Integrasi {$this->service} gagal {$this->consecutiveFailures} kali berturut-turut.",
        ];
    }
}
