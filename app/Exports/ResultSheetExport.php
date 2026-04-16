<?php

namespace App\Exports;

use App\Models\Student;
use App\Models\Subject;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ResultSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithEvents, WithCustomStartCell, WithDrawings
{
    protected $schoolId, $classId, $academicYearId, $status, $title, $subjects, $schoolInfo, $studentsCollection, $classLevel;

    public function __construct($schoolId, $classId, $academicYearId, $status, $title)
    {
        $this->schoolId = $schoolId;
        $this->classId = $classId;
        $this->academicYearId = $academicYearId;
        $this->status = $status;
        $this->title = $title;

        $this->subjects = Subject::where('school_class_id', $this->classId)->get();
        $classData = SchoolClass::with('level')->find($this->classId);
        $this->classLevel = $classData->level->name;

        $school = School::find($schoolId);
        $this->schoolInfo = [
            'school' => $school->name ?? '',
            'directorate' => $school->directorate ?? 'المكلا', // افترضت وجود حقل المديرية
            'class' => SchoolClass::find($classId)->name ?? '',
            'year' => AcademicYear::find($academicYearId)->year ?? '',
        ];
    }

    public function title(): string
    {
        return $this->title;
    }

    // دفع الجدول لأسفل لترك مساحة للترويسة الضخمة
    public function startCell(): string
    {
        return 'A11';
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('images/yemen.logo.png'));
        $drawing->setHeight(70);
        $drawing->setCoordinates('Q1');
        $drawing->setOffsetX(20);
        return $drawing;
    }

    public function collection()
    {
        $this->studentsCollection = Student::whereHas('enrollments', function ($q) {
            $q->where('school_id', $this->schoolId)
                ->where('class_id', $this->classId)
                ->where('academic_year_id', $this->academicYearId);
        })
            ->whereHas('finalResult', function ($q) {
                $q->where('academic_year_id', $this->academicYearId)
                    ->where('final_result', $this->status);
            })
            ->with([
                'grades' => fn($q) => $q->where('academic_year_id', $this->academicYearId),
                'finalResult' => fn($q) => $q->where('academic_year_id', $this->academicYearId)
            ])
            ->notSuspended()
            ->get();

        return $this->studentsCollection;
    }

    public function headings(): array
    {
        $row1 = ['م', 'الرقم المدرسي', 'اسم الطالب'];
        $row2 = ['', '', ''];
        foreach ($this->subjects as $subject) {
            $row1 = array_merge($row1, [$subject->name, '', '']);
            $row2 = array_merge($row2, ['ف.1', 'ف.2', 'المجموع']);
        }
        $row1 = array_merge($row1, ['المجموع الكلي', 'المعدل', 'النتيجة', 'ملاحظات']);
        $row2 = array_merge($row2, ['', '', '', '']);
        return [$row1, $row2];
    }

    private $rowNumber = 0;
    public function map($student): array
    {
        $this->rowNumber++;
        $row = [$this->rowNumber, $student->school_number, $student->full_name];
        $grades = $student->grades->keyBy('subject_id');
        foreach ($this->subjects as $subject) {
            if ($grades->has($subject->id)) {
                $g = $grades[$subject->id];
                $row = array_merge($row, [$g->first_semester_total, $g->second_semester_total, $g->total]);
            } else {
                $row = array_merge($row, ['-', '-', '-']);
            }
        }
        $fr = $student->finalResult;
        return array_merge($row, [$fr->total_student_grades ?? '-', $fr->average_grade ?? null, $fr->final_result ?? '-', $fr->notes ?? '-']);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);
                $highestCol = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                // بيانات إضافية للحساب
                $count = $this->studentsCollection->count();
                $firstStudent = $this->studentsCollection->first()->full_name ?? '---';
                $lastStudent = $this->studentsCollection->last()->full_name ?? '---';

                // --- 🌟 الترويسة الرسمية (اليمين) ---
                $sheet->setCellValue('A1', 'الجمهورية اليمنية');
                $sheet->setCellValue('A2', 'وزارة التربية و التعليم');
                $sheet->setCellValue('A3', 'مكتب وزارة التربية والتعليم بمحافظة حضرموت');
                $sheet->getStyle('A1:A3')->getFont()->setBold(true);

                // --- 🌟 عنوان الكشف (الوسط) ---
                $sheet->setCellValue('A5', "كشف رصد درجات أعمال السنة واختبار النقل في مرحلة التعليم {$this->classLevel} ( الصف {$this->schoolInfo['class']} ) للعام الدراسي {$this->schoolInfo['year']}");
                $sheet->mergeCells("A5:{$highestCol}5");
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // --- 🌟 بيانات المدرسة والصف (سطر 7) ---
                $sheet->setCellValue('A7', "مديرية : {$this->schoolInfo['directorate']} | مدرسة : {$this->schoolInfo['school']} | الصف : {$this->schoolInfo['class']}");
                $sheet->mergeCells("A7:G7");

                // --- 🌟 إحصائيات العدد (سطر 8) ---
                // ملاحظة: يمكنك إضافة دالة لتحويل الرقم إلى كلمات، هنا وضعتها كنص
                $sheet->setCellValue('A8', "{$this->title} العدد : ($count) لاغير");
                $sheet->getStyle('A8')->getFont()->setBold(true);

                // --- 🌟 اسم أول وآخر تلميذ (سطر 9) ---
                $sheet->setCellValue('A9', "اسم أول تلميذ / تلميذة : $firstStudent");
                $sheet->setCellValue('G9', "اسم آخر تلميذ / تلميذة : $lastStudent");
                $sheet->getStyle('A9:G9')->getFont()->setBold(true)->setSize(10);

                // --- 📏 ضبط عروض الأعمدة ---
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(40);
                $sheet->getColumnDimension('AH')->setWidth(14);
                $sheet->getColumnDimension('AK')->setWidth(16);

                // --- دمج خلايا العناوين للجدول (الآن تبدأ من الصف 12) ---
                $sheet->mergeCells('A11:A12');
                $sheet->mergeCells('B11:B12');
                $sheet->mergeCells('C11:C12');
                $colIdx = 4;
                foreach ($this->subjects as $sub) {
                    $start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                    $end = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 2);
                    $sheet->mergeCells("{$start}11:{$end}11");
                    $colIdx += 3;
                }
                for ($i = 0; $i < 4; $i++) {
                    $c = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + $i);
                    $sheet->mergeCells("{$c}11:{$c}12");
                }

                // --- 🎨 تنسيق الجدول ---
                $sheet->getStyle("A11:{$highestCol}12")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']],
                ]);

                $sheet->getStyle("A11:{$highestCol}{$highestRow}")->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // إعدادات الطباعة
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setFitToPage(true);
            },
        ];
    }
}
