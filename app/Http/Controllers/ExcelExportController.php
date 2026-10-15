<?php

namespace App\Http\Controllers;

use App\Exports\MultiSheetExport;
use App\Exports\ReporteExcelExport;
use App\Models\Form;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExportController extends Controller
{
    public function donwloadSheets($formId)
    {
        $form = Form::find($formId);
        $type = ($form->Type->id == 1) ? 'banco' : 'cheque';
        return Excel::download(new MultiSheetExport($formId), 'planilla-'
            . Carbon::parse($form->period)->translatedFormat('F-Y') . '-'
            . $type . '.xlsx');
    }
}
