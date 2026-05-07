<?php

namespace App\Services\FinalResultImportServices;

use App\Imports\FinalResultImportImproved;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class FinalResultImportService
{
    public function ImportForm()
    {
        return ['message' => 'Ready to import'];
    }

    public function importResults($data, $file, $isPreview = false)
    {
        try {
            $import = new FinalResultImportImproved(
                $data['academic_year_id'],
                $data['class_id'],
                $data['school_id']
            );

            if ($isPreview) {
                $import->preview = true;
            }

            Excel::import($import, $file);

            // الحصول على تقرير الاستيراد
            $report = $import->getImportReport();

            if ($isPreview) {
                return [
                    'status' => 'preview',
                    'report' => $report,
                    'sample_data' => $import->previewData,
                ];
            }

            return [
                'status' => 'success',
                'report' => $report,
                'message' => $report['summary']['failed'] > 0 
                    ? 'تم الاستيراد مع بعض الأخطاء' 
                    : 'تم استيراد النتائج النهائية بنجاح'
            ];
        } catch (Exception $e) {
            throw new Exception('حدث خطأ أثناء الاستيراد: ' . $e->getMessage());
        }
    }
}

