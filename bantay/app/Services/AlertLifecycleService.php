<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\AlertHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AlertLifecycleService
{
    /**
     * Valid next statuses from each current status.
     * open -> acknowledged -> assigned -> under_review -> confirmed|dismissed
     * confirmed -> escalated|resolved ; escalated -> resolved ; resolved -> closed
     */
    private const TRANSITIONS = [
        'open' => ['acknowledged', 'dismissed'],
        'acknowledged' => ['assigned', 'under_review', 'dismissed'],
        'assigned' => ['under_review', 'dismissed'],
        'under_review' => ['confirmed', 'dismissed'],
        'confirmed' => ['escalated', 'resolved'],
        'escalated' => ['resolved'],
        'resolved' => ['closed'],
        'dismissed' => ['closed'],
        'closed' => [],
    ];

    public function canTransition(Alert $alert, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$alert->status] ?? [], true);
    }

    public function availableTransitions(Alert $alert): array
    {
        return self::TRANSITIONS[$alert->status] ?? [];
    }

    public function transition(Alert $alert, string $to, User $user, ?string $notes = null): Alert
    {
        if (!$this->canTransition($alert, $to)) {
            throw new \InvalidArgumentException("Cannot move alert from '{$alert->status}' to '{$to}'.");
        }

        DB::transaction(function () use ($alert, $to, $user, $notes) {
            $from = $alert->status;

            $updates = ['status' => $to];

            if ($to === 'acknowledged') {
                $updates['acknowledged_by'] = $user->id;
                $updates['acknowledged_at'] = now();
            }

            if (in_array($to, ['resolved'], true)) {
                $updates['resolved_by'] = $user->id;
                $updates['resolved_at'] = now();
            }

            if ($notes) {
                $updates['notes'] = $notes;
            }

            $alert->update($updates);

            AlertHistory::create([
                'alert_id' => $alert->id,
                'from_status' => $from,
                'to_status' => $to,
                'changed_by' => $user->id,
                'notes' => $notes,
            ]);
        });

        return $alert->fresh();
    }
}
