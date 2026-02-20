<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/2.xlsx';

if (!file_exists($file)) {
    die("File not found: $file\n");
}

try {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    
    echo "=== معلومات الملف ===\n";
    echo "الصفحة النشطة: " . $sheet->getTitle() . "\n";
    echo "أعلى صف: " . $sheet->getHighestRow() . "\n";
    echo "أعلى عمود: " . $sheet->getHighestColumn() . "\n\n";
    
    echo "=== أول 10 صفوف (مع محاذاة) ===\n";
    
    // قراءة أول 10 صفوف
    for ($row = 1; $row <= min(10, $sheet->getHighestRow()); $row++) {
        echo "الصف $row: ";
        $rowData = $sheet->rangeToArray('A' . $row . ':' . $sheet->getHighestColumn() . $row, null, true, false)[0];
        
        foreach ($rowData as $index => $cell) {
            $value = $cell ?? '';
            echo "[$index: $value] ";
        }
        echo "\n";
    }
    
    echo "\n=== بيانات الصف 5 (أول صف بيانات حسب startRow) ===\n";
    if ($sheet->getHighestRow() >= 5) {
        $row5Data = $sheet->rangeToArray('A5:' . $sheet->getHighestColumn() . '5', null, true, false)[0];
        foreach ($row5Data as $index => $cell) {
            echo "العمود $index: " . ($cell ?? 'فارغ') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
