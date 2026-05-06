<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\JsonResponse;

class LevelController extends Controller
{
    public function index(): JsonResponse
    {
        $levels = Level::all();
        return response()->json($levels, 200);
    }
}
