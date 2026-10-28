<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Notifications\Notification;

class AlertEscalatedNotification extends Notification
{
    public function __construct(public Alert $alert)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'alert_id' => $this->alert->id,
            'severity' => $this->alert->severity,
            'disease' => $this->alert->disease->name ?? 'Unknown',
            'message' => "Escalated: {$this->alert->disease->name} alert requires review",
            'url' => route('admin.alerts.show', $this->alert),
        ];
    }
}
