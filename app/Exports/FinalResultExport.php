<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FinalResultExport implements WithMultipleSheets
{
    protected $schoolId, $classId, $academicYearId;

    public function __construct($schoolId, $classId, $academicYearId)
    {
        $this->schoolId = $schoolId;
        $this->classId = $classId;
        $this->academicYearId = $academicYearId;
    }

    public function sheets(): array
    {
        $sheets = [];

        // تعريف الأوراق المطلوبة (الاسم، الحالة في قاعدة البيانات)
        $categories = [
            ['title' => 'الطلاب الناجحون', 'status' => 'ناجح'],
            ['title' => 'الطالبات الناجحات', 'status' => 'ناجحة'],
            ['title' => 'الطلاب الراسبون', 'status' => 'راسب'],
            ['title' => 'الطالبات الراسبات', 'status' => 'راسبة'],
            ['title' => 'الطلاب الغائبون', 'status' => 'غائب'],
            ['title' => 'الطالبات الغائبات', 'status' => 'غائبة'],
        ];

        foreach ($categories as $cat) {
            $sheets[] = new ResultSheetExport(
                $this->schoolId, 
                $this->classId, 
                $this->academicYearId, 
                $cat['status'], 
                $cat['title']
            );
        }

        return $sheets;
    }
}
