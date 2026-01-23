<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\FinalResult;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Level;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinalResultImport implements ToCollection, WithHeadingRow
{
    protected $academicYearId;
    protected $userId; // لتخزين created_by

    // Caching لتسريع عملية الاستيراد
    private $schoolsCache = [];
    private $levelsCache = [];
    private $classesCache = [];
    private $subjectsCache = [];

    public function __construct(int $academicYearId)
    {
        $this->academicYearId = $academicYearId;
        $this->userId = Auth::id() ?? 1; // احصل على هوية المستخدم الحالي أو استخدم قيمة افتراضية
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                // تجاهل الصف إذا كانت الأعمدة الأساسية فارغة
                if (empty($row['student_number']) || empty($row['student_name']) || empty($row['class']) || empty($row['level']) || empty($row['school_name'])) {
                    continue;
                }

                // 1. البحث عن المدرسة أو إنشاؤها
                $school = $this->findOrCreateSchool($row['school_name']);

                // 2. البحث عن المستوى أو إنشاؤه
                $level = $this->findOrCreateLevel($row['level']);

                // 3. البحث عن الصف الدراسي أو إنشاؤه
                $class = $this->findOrCreateClass($row['class'], $level->id);

                // 4. الآن، قم بإنشاء الطالب أو تحديثه
                $student = Student::updateOrCreate(
                    [
                        // ابحث عن الطالب بهذا الرقم
                        'school_number' => $row['student_number'],
                    ],
                    [
                        // إذا وجدته، قم بتحديث بياناته، وإذا لم تجده، قم بإنشائه بهذه البيانات
                        'full_name' => $row['student_name'],
                        'school_id' => $school->id,
                        'class_id' => $class->id,
                        'created_by' => $this->userId,
                    ]
                );

                // 5. جلب المواد الخاصة بالصف
                $subjects = $this->getSubjectsForClass($row['class']);
                if ($subjects->isEmpty()) {
                    continue;
                }

                // 6. إنشاء/تحديث الدرجات (نفس المنطق السابق)
                foreach ($subjects as $subject) {
                    $t1Key = Str::snake($subject->name . ' T1');
                    $t2Key = Str::snake($subject->name . ' T2');
                    $totalKey = Str::snake($subject->name . ' Total');

                    Grade::updateOrCreate(
                        ['student_id' => $student->id, 'subject_id' => $subject->id, 'academic_year_id' => $this->academicYearId, 'created_by' => $this->userId],
                        ['first_semester_total' => $row[$t1Key] ?? null, 'second_semester_total' => $row[$t2Key] ?? null, 'total' => $row[$totalKey] ?? null]
                    );
                }

                // 7. إنشاء/تحديث النتيجة النهائية (نفس المنطق السابق)
                FinalResult::updateOrCreate(
                    ['student_id' => $student->id, 'academic_year_id' => $this->academicYearId, 'created_by' => $this->userId],
                    ['total_student_grades' => $row['total_result'] ?? 0, 'final_result' => $row['final_result'] ?? 'N/A', 'notes' => $row['notes'] ?? null]
                );
            }
        });
    }

    // --- دوال مساعدة مع Caching لتحسين الأداء ---

    private function findOrCreateSchool(string $name) {
        if (isset($this->schoolsCache[$name])) return $this->schoolsCache[$name];
        return $this->schoolsCache[$name] = School::firstOrCreate(['name' => $name], ['created_by' => $this->userId]);
    }

    private function findOrCreateLevel(string $name) {
        if (isset($this->levelsCache[$name])) return $this->levelsCache[$name];
        return $this->levelsCache[$name] = Level::firstOrCreate(['name' => $name], ['created_by' => $this->userId]);
    }

    private function findOrCreateClass(string $name, int $levelId) {
        if (isset($this->classesCache[$name])) return $this->classesCache[$name];
        return $this->classesCache[$name] = SchoolClass::firstOrCreate(['name' => $name, 'level_id' => $levelId], ['created_by' => $this->userId]);
    }

    private function getSubjectsForClass(string $className): Collection
    {
        if (isset($this->subjectsCache[$className])) return $this->subjectsCache[$className];
        $subjects = Subject::whereHas('level.classes', fn($q) => $q->where('name', $className))->orderBy('id')->get();
        return $this->subjectsCache[$className] = $subjects;
    }
}
