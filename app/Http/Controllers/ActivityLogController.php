<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogServices\ActivityLogService;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of activity logs.
     * This endpoint is meant for admins and can be filtered by user_id.
     */
    public function index(Request $request)
    {
        $logs = $this->activityLogService->getAllActivityLogs($request->all());

        return response()->json($logs);
    }

    /**
     * Display activity logs for the currently authenticated user.
     */
    public function myLogs(Request $request)
    {
        $logs = $this->activityLogService->myLogs();

        return response()->json($logs);
    }
}
