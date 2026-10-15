<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetExport implements WithMultipleSheets
{
    protected $formId;

    public function __construct(int $formId)
    {
        $this->formId = $formId;
    }

    public function sheets(): array
    {
        return [
            new SheetOneExport($this->formId),
            new SheetTwoExport($this->formId),
            new SheetThreeExport($this->formId),
            new SheetFourExport($this->formId),
            // Agrega más si tienes más hojas
        ];
    }
}
