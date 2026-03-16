<?php

namespace App\Notifications;

use App\Models\StudentAchievement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AchievementStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected StudentAchievement $achievement;

    protected string $action;

    public function __construct(StudentAchievement $achievement, string $action)
    {
        $this->achievement = $achievement;
        $this->action = $action;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage())
            ->subject($this->getSubject())
            ->greeting('Halo '.$notifiable->name.'!');

        switch ($this->action) {
            case 'approved':
                $message->line('Selamat! Prestasi Anda telah disetujui.')
                    ->line('Nama Lomba: '.$this->achievement->event_name)
                    ->line('Tingkat: '.$this->achievement->level)
                    ->action('Lihat Detail', url('/achievements/'.$this->achievement->sa_id));
                break;

            case 'rejected':
                $latestLog = $this->achievement->validationLogs()->latest()->first();
                $message->line('Mohon maaf, prestasi Anda tidak dapat disetujui.')
                    ->line('Nama Lomba: '.$this->achievement->event_name)
                    ->line('Alasan: '.($latestLog?->notes ?? 'Tidak memenuhi kriteria'))
                    ->line('Anda dapat mengajukan banding jika merasa keputusan ini tidak tepat.')
                    ->action('Ajukan Banding', url('/achievements/'.$this->achievement->sa_id.'/appeal'));
                break;

            case 'need_revision':
                $latestLog = $this->achievement->validationLogs()->latest()->first();
                $message->line('Prestasi Anda memerlukan revisi atau dokumen tambahan.')
                    ->line('Nama Lomba: '.$this->achievement->event_name)
                    ->line('Catatan: '.($latestLog?->notes ?? 'Silakan lengkapi dokumen'))
                    ->action('Upload Dokumen', url('/achievements/'.$this->achievement->sa_id.'/edit'));
                break;
        }

        return $message->line('Terima kasih telah menggunakan sistem kami.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'achievement_id' => $this->achievement->sa_id,
            'event_name' => $this->achievement->event_name,
            'action' => $this->action,
            'status' => $this->achievement->validation_status,
            'message' => $this->getMessage(),
        ];
    }

    protected function getSubject(): string
    {
        return match ($this->action) {
            'approved' => '[Prestasi Unpatti] Prestasi Anda Telah Disetujui',
            'rejected' => '[Prestasi Unpatti] Prestasi Anda Tidak Disetujui',
            'need_revision' => '[Prestasi Unpatti] Prestasi Anda Memerlukan Revisi',
            default => '[Prestasi Unpatti] Update Status Prestasi',
        };
    }

    protected function getMessage(): string
    {
        return match ($this->action) {
            'approved' => 'Prestasi "'.$this->achievement->event_name.'" telah disetujui.',
            'rejected' => 'Prestasi "'.$this->achievement->event_name.'" tidak disetujui.',
            'need_revision' => 'Prestasi "'.$this->achievement->event_name.'" memerlukan revisi.',
            default => 'Status prestasi "'.$this->achievement->event_name.'" telah diperbarui.',
        };
    }
}
