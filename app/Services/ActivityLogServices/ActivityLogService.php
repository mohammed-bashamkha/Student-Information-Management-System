<?php

namespace App\Services\ActivityLogServices;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class ActivityLogService
{
    use AuthorizesRequests;
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

    public function getAllActivityLogs(array $filters = [])
    {
        $this->authorize('viewAny', User::class);

        $query = Activity::with(['causer', 'subject'])->latest();

        if (isset($filters['user_id'])) {
            $query->where('causer_id', $filters['user_id']);
        }

        return $query->paginate(15);
    }

    public function myLogs()
    {
        $logs = Activity::with(['subject'])
            ->where('causer_id', Auth::id())
            ->latest()
            ->paginate(15);

        return $logs;
    }
}
