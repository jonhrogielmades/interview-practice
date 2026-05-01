<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemDatabaseNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected array $payload,
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (! empty($this->payload['mail']) || in_array('mail', $this->payload['channels'] ?? [])) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $mail = (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject((string) ($this->payload['title'] ?? 'Notification'))
            ->line((string) ($this->payload['body'] ?? ''));

        if ($url = ($this->payload['action_url'] ?? null)) {
            $mail->action((string) ($this->payload['action_label'] ?? 'View Details'), $url);
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => (string) ($this->payload['title'] ?? 'Notification'),
            'body' => (string) ($this->payload['body'] ?? ''),
            'icon' => (string) ($this->payload['icon'] ?? 'bell'),
            'tone' => (string) ($this->payload['tone'] ?? 'brand'),
            'action_url' => $this->payload['action_url'] ?? null,
            'action_label' => (string) ($this->payload['action_label'] ?? 'Open'),
            'meta' => is_array($this->payload['meta'] ?? null) ? $this->payload['meta'] : [],
        ];
    }
}
