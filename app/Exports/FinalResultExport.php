<?php

namespace App\Exports;

use App\Models\FinalResult;
use Maatwebsite\Excel\Concerns\FromCollection;

class FinalResultExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return FinalResult::all();
    }
}
