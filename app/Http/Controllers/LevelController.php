<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Services\LevelServices\LevelService;
use Illuminate\Http\JsonResponse;

class LevelController extends Controller
{
    protected $levelService;
    public function __construct(LevelService $levelService)
    {
        $this->levelService = $levelService;
    }

    public function index(): JsonResponse
    {
        $levels = $this->levelService->getLevels();
        return response()->json($levels, 200);
    }
}
