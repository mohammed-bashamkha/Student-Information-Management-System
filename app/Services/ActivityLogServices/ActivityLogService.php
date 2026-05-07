<?php

namespace App\Services\ActivityLogServices;

use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    /**
     * Log an activity in the system.
     *
     * @param string $logName The log channel/name (e.g., 'students', 'grades')
     * @param object|null $model The model instance being operated on
     * @param string $event The type of event ('create', 'update', 'delete', etc.)
     * @param string $description Arabic description of what happened
     */
    public function logAction(string $logName, $model, string $event, string $description): void
    {
        if (!Auth::check()) {
            return;
        }

        $activity = activity($logName)
            ->causedBy(Auth::user())
            ->event($event);

        if ($model) {
            $activity->performedOn($model);
        }

        $activity->log($description);
    }
}
