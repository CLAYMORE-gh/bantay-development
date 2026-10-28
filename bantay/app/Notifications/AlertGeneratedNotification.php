<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Notifications\Notification;

class AlertGeneratedNotification extends Notification
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
            'barangay' => $this->alert->barangay->name ?? 'City-wide',
            'message' => ucfirst($this->alert->severity) . " alert: {$this->alert->disease->name} in " . ($this->alert->barangay->name ?? 'City-wide'),
            'url' => route('admin.alerts.show', $this->alert),
        ];
    }
}
