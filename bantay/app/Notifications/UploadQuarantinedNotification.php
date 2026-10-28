<?php

namespace App\Notifications;

use App\Models\DatasetUpload;
use Illuminate\Notifications\Notification;

class UploadQuarantinedNotification extends Notification
{
    public function __construct(public DatasetUpload $upload)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'upload_id' => $this->upload->id,
            'quarantined_rows' => $this->upload->quarantined_rows,
            'message' => "{$this->upload->quarantined_rows} row(s) quarantined in '{$this->upload->original_filename}'",
            'url' => route('admin.uploads.show', $this->upload),
        ];
    }
}
