<?php

namespace App\Exports;

use App\Models\Student;
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

class StudentDataExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithEvents, WithCustomStartCell, WithDrawings
{
    protected $schoolId, $classId, $academicYearId, $schoolInfo, $studentsCollection;

    public function __construct($schoolId, $classId, $academicYearId)
    {
        $this->schoolId = $schoolId;
        $this->classId = $classId;
        $this->academicYearId = $academicYearId;

        $school = School::find($schoolId);
        $class = SchoolClass::find($classId);
        $year = AcademicYear::find($academicYearId);

        $this->schoolInfo = [
            'school' => $school->name ?? '',
            'directorate' => $school->directorate ?? 'المكلا', 
            'class' => $class->name ?? '',
            'year' => $year->year ?? '',
        ];
    }

    public function title(): string
    {
        return 'كشف بيانات الطلاب';
    }

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
        $drawing->setCoordinates('E1');
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
        ->with(['enrollments' => function($q) {
            $q->where('school_id', $this->schoolId)
              ->where('class_id', $this->classId)
              ->where('academic_year_id', $this->academicYearId)
              ->with(['school']);
        }])
        ->get();

        return $this->studentsCollection;
    }

    public function headings(): array
    {
        return [
            'م',
            'الرقم المدرسي',
            'الاسم الكامل',
            'رقم الجلوس',
            'الجنسية',
            'الجنس',
            'تاريخ الميلاد',
            'تاريخ التسجيل',
            'المدرسة المسجل بها',
            'تاريخ الإنشاء',
        ];
    }

    private $rowNumber = 0;

    public function map($student): array
    {
        $this->rowNumber++;
        $currentEnrollment = $student->enrollments->first();

        return [
            $this->rowNumber,
            $student->school_number,
            $student->full_name,
            $student->seat_number,
            $student->nationality,
            $student->gender === 'male' ? 'ذكر' : ($student->gender === 'female' ? 'أنثى' : ''),
            $student->date_of_birth,
            $student->registration_date,
            $currentEnrollment->school->name ?? 'N/A',
            $student->created_at->format('Y-m-d'),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);
                
                $highestCol = 'J';
                $highestRow = $sheet->getHighestRow();
                $count = $this->studentsCollection->count();
                $firstStudent = $this->studentsCollection->first()->full_name ?? '---';
                $lastStudent = $this->studentsCollection->last()->full_name ?? '---';

                // --- 🌟 الترويسة ---
                $sheet->setCellValue('A1', 'الجمهورية اليمنية');
                $sheet->setCellValue('A2', 'وزارة التربية و التعليم');
                $sheet->setCellValue('A3', 'مكتب وزارة التربية والتعليم بمحافظة حضرموت');
                $sheet->getStyle('A1:A3')->getFont()->setBold(true);

                // --- 🌟 عنوان الكشف ---
                $sheet->setCellValue('A5', "كشف بيانات الطلاب المسجلين بالصف {$this->schoolInfo['class']} للعام الدراسي {$this->schoolInfo['year']}");
                $sheet->mergeCells("A5:{$highestCol}5");
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // --- 🌟 بيانات المدرسة ---
                $sheet->setCellValue('A7', "مديرية : {$this->schoolInfo['directorate']} | مدرسة : {$this->schoolInfo['school']} | الصف : {$this->schoolInfo['class']}");
                $sheet->mergeCells("A7:G7");
                $sheet->getStyle('A7')->getFont()->setBold(true);

                // --- 🌟 إحصائيات ---
                $sheet->setCellValue('A8', "إجمالي عدد الطلاب : ($count) طالباً/طالبة");
                $sheet->getStyle('A8')->getFont()->setBold(true);

                // --- 🌟 الأسماء ---
                $sheet->setCellValue('A9', "اسم أول طالب : $firstStudent");
                $sheet->setCellValue('G9', "اسم آخر طالب : $lastStudent");
                $sheet->getStyle('A9:G9')->getFont()->setBold(true)->setSize(10);

                // --- 📏 ضبط عروض الأعمدة ---
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(35);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(20);
                $sheet->getColumnDimension('J')->setWidth(20);

                // --- 🎨 تنسيق رأس الجدول ---
                $sheet->getStyle("A11:{$highestCol}11")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                // --- 🎨 حدود الجدول ---
                $sheet->getStyle("A11:{$highestCol}{$highestRow}")->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            },
        ];
    }
}