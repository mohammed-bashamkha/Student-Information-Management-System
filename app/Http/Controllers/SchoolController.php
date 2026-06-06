<?php
 
namespace App\Http\Controllers;
 
use App\Http\Requests\SchoolRequest\StoreSchoolRequest;
use App\Http\Requests\SchoolRequest\UpdateSchoolRequest;
use App\Services\SchoolServices\SchoolService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\ResponseCache\Facades\ResponseCache;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SchoolController extends Controller implements HasMiddleware
{
    protected $schoolService;

    public function __construct(SchoolService $schoolService)
    {
        $this->schoolService = $schoolService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('cacheResponse:86400', only: ['index', 'show']),
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $schoolType = $request->query('school_type');
        $schools = $this->schoolService->getSchools($search, $schoolType);
        return response()->json($schools, 200);
    }

    public function show(string $id): JsonResponse
    {
        $school = $this->schoolService->getSchool($id);
        return response()->json($school, 200);
    }

    public function store(StoreSchoolRequest $request): JsonResponse
    {
        $validateData = $request->validated();
        $school = $this->schoolService->storeSchool($validateData);
        ResponseCache::clear();
        return response()->json([
            'message' => 'تم اضافة المدرسة بنجاح',
            'data' => $school
        ], 201);
    }

    public function update(UpdateSchoolRequest $request, string $id): JsonResponse
    {
        $validateData = $request->validated();
        $school = $this->schoolService->updateSchool($validateData, $id);
        ResponseCache::clear();
        return response()->json([
            'message' => 'تم تعديل المدرسة بنجاح',
            'data' => $school
        ], 202);
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $school = $this->schoolService->deleteSchool($id);
            ResponseCache::clear();
            return response()->json([
                'message' => 'تم حذف المدرسة بنجاح',
                'data' => $school
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }
}
