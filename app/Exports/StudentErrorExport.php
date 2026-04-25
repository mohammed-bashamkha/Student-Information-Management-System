<?php

namespace App\Exports;

use App\Models\Error;
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

class StudentErrorExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithEvents, WithCustomStartCell, WithDrawings
{
    protected $academicYearId, $academicYearInfo, $errorsCollection;

    public function __construct($academicYearId)
    {
        $this->academicYearId = $academicYearId;
        $year = AcademicYear::find($academicYearId);
        $this->academicYearInfo = $year->year ?? '';
    }

    public function title(): string
    {
        return 'سجل التعديلات والأخطاء';
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
        $drawing->setCoordinates('D1');
        $drawing->setOffsetX(20);
        return $drawing;
    }

    public function collection()
    {
        $this->errorsCollection = Error::where('academic_year_id', $this->academicYearId)
            ->with(['student', 'school', 'schoolClass', 'createdBy'])
            ->orderBy('student_id')
            ->orderBy('created_at')
            ->get();

        return $this->errorsCollection;
    }

    public function headings(): array
    {
        return [
            'م',
            'الرقم المدرسي الط.',
            'اسم الطالب',
            'الحقل المعدل',
            'القيمة القديمة',
            'القيمة الجديدة',
            'المدرسة',
            'الصف',
            'مدخل التعديل',
            'تاريخ التعديل',
            'السبب'
        ];
    }

    private $rowNumber = 0;
    private $lastStudentId = null;
    private $lastReason = null;

    public function map($error): array
    {
        // ترجمة الحقول المعدلة إذا لزم الأمر
        $fieldsTranslation = [
            'full_name' => 'الاسم',
            'school_number' => 'الرقم المدرسي',
            'seat_number' => 'رقم الجلوس',
            'gender' => 'الجنس',
            'school_id' => 'المدرسة',
            'class_id' => 'الصف',
            'date_of_birth' => 'تاريخ الميلاد'
        ];

        $fieldName = $fieldsTranslation[$error->field_name] ?? $error->field_name;

        // التحقق مما إذا كان نفس الطالب في التعديلات المتتابعة
        $isSameStudent = ($this->lastStudentId === $error->student_id);

        if (!$isSameStudent) {
            $this->rowNumber++;
        }

        $this->lastStudentId = $error->student_id;
        $this->lastReason = $error->reason;

        return [
            $this->rowNumber,
            $error->student->school_number ?? '',
            $error->student->full_name ?? '',
            $fieldName,
            $error->old_value,
            $error->new_value,
            $error->school->name ?? '',
            $error->schoolClass->name ?? '',
            $error->createdBy->name ?? '',
            $error->created_at ? $error->created_at->format('Y-m-d') : '',
            $error->reason,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);

                $highestCol = 'K';
                $highestRow = $sheet->getHighestRow();
                $count = $this->errorsCollection->count();

                // --- 🌟 الترويسة ---
                $sheet->setCellValue('A1', 'الجمهورية اليمنية');
                $sheet->setCellValue('A2', 'وزارة التربية و التعليم');
                $sheet->setCellValue('A3', 'مكتب وزارة التربية والتعليم بمحافظة حضرموت');
                $sheet->getStyle('A1:A3')->getFont()->setBold(true);

                // --- 🌟 عنوان الكشف ---
                $sheet->setCellValue('A5', "كشف سجل التعديلات والأخطاء للعام الدراسي {$this->academicYearInfo}");
                $sheet->mergeCells("A5:{$highestCol}5");
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // --- 🌟 إحصائيات ---
                $sheet->setCellValue('A7', "إجمالي عدد السجلات : ($count)");
                $sheet->getStyle('A7')->getFont()->setBold(true);

                // --- 📏 ضبط عروض الأعمدة ---
                $sheet->getColumnDimension('A')->setWidth(5);   // م
                $sheet->getColumnDimension('B')->setWidth(15);  // الرقم المدرسي
                $sheet->getColumnDimension('C')->setWidth(30);  // اسم الطالب
                $sheet->getColumnDimension('D')->setWidth(18);  // الحقل المعدل
                $sheet->getColumnDimension('E')->setWidth(30);  // القيمة القديمة
                $sheet->getColumnDimension('F')->setWidth(30);  // القيمة الجديدة
                $sheet->getColumnDimension('G')->setWidth(25);  // المدرسة
                $sheet->getColumnDimension('H')->setWidth(15);  // الصف
                $sheet->getColumnDimension('I')->setWidth(20);  // مدخل التعديل
                $sheet->getColumnDimension('J')->setWidth(15);  // تاريخ التعديل
                $sheet->getColumnDimension('K')->setWidth(35);  // السبب

                // --- 🎨 تنسيق رأس الجدول ---
                $sheet->getStyle("A11:{$highestCol}11")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                // --- 🎨 حدود ومحاذاة الجدول ---
                $sheet->getStyle("A11:{$highestCol}{$highestRow}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // --- 🔗 دمج الخلايا لبيانات الطالب والبيانات المكررة ---
                $currentRow = 12; // بداية البيانات
                $lastStudentId = null;
                $studentStartRow = 12;

                // إعداد أعمدة الدمج الإضافية التي تتم مشاركتها لنفس الطالب
                $columnsToMerge = [
                    'school'  => ['col' => 'G', 'val' => function ($e) {
                        return $e->school_id;
                    }],
                    'class'   => ['col' => 'H', 'val' => function ($e) {
                        return $e->class_id;
                    }],
                    'creator' => ['col' => 'I', 'val' => function ($e) {
                        return $e->createdBy->id ?? '';
                    }],
                    'date'    => ['col' => 'J', 'val' => function ($e) {
                        return $e->created_at ? $e->created_at->format('Y-m-d') : '';
                    }],
                    'reason'  => ['col' => 'K', 'val' => function ($e) {
                        return $e->reason;
                    }],
                ];

                $dynamicStarts = [];
                $dynamicLasts = [];
                foreach (array_keys($columnsToMerge) as $k) {
                    $dynamicStarts[$k] = 12;
                    $dynamicLasts[$k] = null;
                }

                foreach ($this->errorsCollection as $error) {
                    if ($error->student_id !== $lastStudentId) {
                        // دمج بيانات الطالب السابق (A, B, C)
                        if ($lastStudentId !== null && $currentRow - 1 > $studentStartRow) {
                            $sheet->mergeCells("A{$studentStartRow}:A" . ($currentRow - 1));
                            $sheet->mergeCells("B{$studentStartRow}:B" . ($currentRow - 1));
                            $sheet->mergeCells("C{$studentStartRow}:C" . ($currentRow - 1));
                        }
                        $studentStartRow = $currentRow;
                        $lastStudentId = $error->student_id;

                        // إعادة تعيين الأعمدة الأخرى ودمجها للطالب السابق
                        foreach ($columnsToMerge as $k => $config) {
                            if ($dynamicLasts[$k] !== null && $currentRow - 1 > $dynamicStarts[$k]) {
                                $col = $config['col'];
                                $sheet->mergeCells("{$col}{$dynamicStarts[$k]}:{$col}" . ($currentRow - 1));
                            }
                            $dynamicStarts[$k] = $currentRow;
                            $dynamicLasts[$k] = $config['val']($error);
                        }
                    } else {
                        // نفس الطالب: تحقق من كل عمود بشكل مستقل لدمجه
                        foreach ($columnsToMerge as $k => $config) {
                            $currentVal = $config['val']($error);
                            if ($currentVal !== $dynamicLasts[$k]) {
                                if ($dynamicLasts[$k] !== null && $currentRow - 1 > $dynamicStarts[$k]) {
                                    $col = $config['col'];
                                    $sheet->mergeCells("{$col}{$dynamicStarts[$k]}:{$col}" . ($currentRow - 1));
                                }
                                $dynamicStarts[$k] = $currentRow;
                                $dynamicLasts[$k] = $currentVal;
                            }
                        }
                    }
                    $currentRow++;
                }

                // دمج السجلات الأخيرة بعد انتهاء التكرار
                if ($lastStudentId !== null && $currentRow - 1 > $studentStartRow) {
                    $sheet->mergeCells("A{$studentStartRow}:A" . ($currentRow - 1));
                    $sheet->mergeCells("B{$studentStartRow}:B" . ($currentRow - 1));
                    $sheet->mergeCells("C{$studentStartRow}:C" . ($currentRow - 1));
                }
                foreach ($columnsToMerge as $k => $config) {
                    if ($dynamicLasts[$k] !== null && $currentRow - 1 > $dynamicStarts[$k]) {
                        $col = $config['col'];
                        $sheet->mergeCells("{$col}{$dynamicStarts[$k]}:{$col}" . ($currentRow - 1));
                    }
                }

                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            },
        ];
    }
}
