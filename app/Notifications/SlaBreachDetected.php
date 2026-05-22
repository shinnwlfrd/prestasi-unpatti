<?php

namespace App\Notifications;

use App\Models\StudentAchievement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaBreachDetected extends Notification implements ShouldQueue
{
    use Queueable;

    protected StudentAchievement $achievement;

    protected int $daysOverdue;

    protected string $stage;

    public function __construct(StudentAchievement $achievement, int $daysOverdue, string $stage)
    {
        $this->achievement = $achievement;
        $this->daysOverdue = $daysOverdue;
        $this->stage = $stage;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $studentName = $this->achievement->student?->name ?? 'Mahasiswa';
        $facultyName = $this->achievement->student?->faculty ?? 'Fakultas';
        $stageName = $this->stage === 'faculty' ? 'Tahap Fakultas' : 'Tahap Universitas';

        return (new MailMessage())
            ->subject('[SLA Breach] Pengajuan Prestasi Melebihi Batas Waktu')
            ->greeting('Halo '.$notifiable->name.'!')
            ->line('Ada pengajuan prestasi mahasiswa yang telah melebihi batas waktu (SLA) dan memerlukan perhatian segera.')
            ->line('Detail Pengajuan:')
            ->line('- Nama Mahasiswa: '.$studentName)
            ->line('- Nama Kegiatan: '.$this->achievement->event_name)
            ->line('- Fakultas: '.$facultyName)
            ->line('- Tahap Saat Ini: '.$stageName)
            ->line('- Keterlambatan: '.$this->daysOverdue.' hari')
            ->action('Buka Halaman Validasi', url('/'))
            ->line('Mohon segera memproses pengajuan ini untuk menjaga kualitas pelayanan.');
    }

    public function toArray(object $notifiable): array
    {
        $studentName = $this->achievement->student?->name ?? 'Mahasiswa';
        $facultyName = $this->achievement->student?->faculty ?? 'Fakultas';
        $stageName = $this->stage === 'faculty' ? 'Tahap Fakultas' : 'Tahap Universitas';

        return [
            'achievement_id' => $this->achievement->sa_id,
            'event_name' => $this->achievement->event_name,
            'student_name' => $studentName,
            'faculty_name' => $facultyName,
            'stage' => $this->stage,
            'days_overdue' => $this->daysOverdue,
            'message' => "Pengajuan prestasi oleh {$studentName} ({$this->achievement->event_name}) terlambat {$this->daysOverdue} hari di {$stageName}.",
        ];
    }
}
