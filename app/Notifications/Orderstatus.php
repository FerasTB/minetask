<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Orderstatus extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct($order, $status, $massage)
    {
        $this->order = $order;
        $this->massage = $massage;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'status' => $this->status,
            'massage' => $this->massage,
            'order_id' => $this->order->id,
            'lab_id' => $this->order->lab->id,
            'doctor_id' => $this->order->doctor->id,
        ];
    }
}
