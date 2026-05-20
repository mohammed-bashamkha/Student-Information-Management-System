<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\FinalResult;
use App\Services\FinalResultServices\FinalResultService;

class FinalResultController extends Controller
{
    protected $finalResultService;
    public function __construct(FinalResultService $finalResultService)
    {
        $this->finalResultService = $finalResultService;
    }
    public function index(Request $request)
    {
        $results = $this->finalResultService->getFinalResults($request->all());
        return response()->json($results);
    }

}