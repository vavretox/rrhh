<?php

namespace App\Exports;

use App\Models\Form;
use App\Models\Sheet;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetOneExport implements FromView, WithTitle
{
    protected $form;
    protected $sheet;

    public function __construct(int $formId)
    {
        $this->form = Form::where('id', $formId)->first();
        $this->sheet = Sheet::where('number', 1)->first();
    }

    public function view(): View
    {
        return view('excel.sheet-one', [
            'form' => $this->form,
            'sheet' => $this->sheet,
        ]);
    }

    public function title(): string
    {
        return '01_sld_65-v0';
    }
}
