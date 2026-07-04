<?php

namespace App\Notifications;

use App\Models\File;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FileSharedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $file;
    public $sharedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(File $file, User $sharedBy)
    {
        $this->file = $file;
        $this->sharedBy = $sharedBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Um novo ficheiro foi partilhado consigo')
                    ->greeting('Olá ' . $notifiable->name . '!')
                    ->line('O utilizador ' . $this->sharedBy->name . ' partilhou o ficheiro "' . $this->file->name . '" consigo de forma segura.')
                    ->action('Ver Ficheiros Partilhados', url('/shared'))
                    ->line('Aceda ao sistema SCD para descarregar o ficheiro.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'file_id' => $this->file->id,
            'shared_by' => $this->sharedBy->id,
        ];
    }
}
