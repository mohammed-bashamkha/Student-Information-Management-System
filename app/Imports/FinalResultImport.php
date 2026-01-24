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
    protected $userId;

    private $schoolsCache = [];
    private $levelsCache = [];
    private $classesCache = [];
    private $subjectsCache = [];

    public function __construct(int $academicYearId)
    {
        $this->academicYearId = $academicYearId;
        $this->userId = Auth::id() ?? 1;
    }

    public function collection(Collection $rows)
    {
        dd($rows->first()->toArray());
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                // استخدام slugged keys التي تولدها المكتبة (أو استخدام طريقة preg_match التي لا تهتم بالمفاتيح)
                // للتبسيط، سنستخدم الأسماء العربية مباشرة في الكود ونتأكد من أن ملف الإكسل يطابقها
                if (empty($row['الرقم المدرسي']) || empty($row['اسم الطالب']) || empty($row['الصف']) || empty($row['المستوى الدراسي']) || empty($row['المدرسة'])) {
                    continue;
                }

                $school = $this->findOrCreateSchool($row['المدرسة']);
                $level = $this->findOrCreateLevel($row['المستوى الدراسي']);
                $class = $this->findOrCreateClass($row['الصف'], $level->id);

                $student = Student::updateOrCreate(
                    ['school_number' => $row['الرقم المدرسي']],
                    [
                        'full_name' => $row['اسم الطالب'],
                        'school_id' => $school->id,
                        'class_id' => $class->id,
                        'created_by' => $this->userId,
                    ]
                );

                // --- المنطق الديناميكي الذي يقرأ العناوين العربية مباشرة ---
                $gradesData = [];
                foreach ($row as $header => $value) {
                    // هذا التعبير يبحث عن نمط مثل "الرياضيات ف1" أو "العلوم المجموع"
                    if (preg_match('/^(.*) (ف1|ف2|المجموع)$/u', $header, $matches)) {
                        $subjectName = trim($matches[1]);
                        $gradeType = trim($matches[2]);

                        if (empty($subjectName)) continue;

                        $subject = $this->findOrCreateSubject($subjectName, $level->id);

                        if (!isset($gradesData[$subject->id])) $gradesData[$subject->id] = [];
                        if ($gradeType === 'ف1') $gradesData[$subject->id]['first_semester_total'] = $value;
                        if ($gradeType === 'ف2') $gradesData[$subject->id]['second_semester_total'] = $value;
                        if ($gradeType === 'المجموع') $gradesData[$subject->id]['total'] = $value;
                    }
                }

                // إنشاء/تحديث الدرجات
                foreach ($gradesData as $subjectId => $data) {
                    Grade::updateOrCreate(
                        ['student_id' => $student->id, 'subject_id' => $subjectId, 'academic_year_id' => $this->academicYearId, 'created_by' => $this->userId],
                        ['first_semester_total' => $data['first_semester_total'] ?? null, 'second_semester_total' => $data['second_semester_total'] ?? null, 'total' => $data['total'] ?? null]
                    );
                }

                // إنشاء/تحديث النتيجة النهائية
                FinalResult::updateOrCreate(
                    ['student_id' => $student->id, 'academic_year_id' => $this->academicYearId, 'created_by' => $this->userId],
                    ['total_student_grades' => $row['المجموع الكلي'] ?? 0, 'final_result' => $row['النتيجة النهائية'] ?? 'N/A', 'notes' => $row['ملاحظات'] ?? null]
                );
            }
        });
    }

    // --- دوال مساعدة (لا تغيير هنا) ---
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
    private function findOrCreateSubject(string $name, int $levelId) {
        $cacheKey = $name . '_' . $levelId;
        if (isset($this->subjectsCache[$cacheKey])) return $this->subjectsCache[$cacheKey];
        return $this->subjectsCache[$cacheKey] = Subject::firstOrCreate(['name' => $name, 'level_id' => $levelId], ['created_by' => $this->userId]);
    }
}
