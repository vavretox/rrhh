<?php

namespace App\Imports;

use App\Models\PaymentForm;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;

class PaymentFormImport implements ToModel
{
    public function model(array $row)
    {
        return new PaymentForm([
            'codigo' => $row[1],
            'cedula' => $row[2],
            'apaterno' => $row[3],
            'amaterno' => $row[4],
            'nombres' => $row[5],
            'nivel' => $row[6],
            'categoria' => $row[7],
            'haber_basico' => $row[8],
            'dias_trabajados' => $row[9],
            'subtotal' => $row[10],
            'bono_frontera' => $row[11],
            'monto_categoria' => $row[12],
            'total_ganado' => $row[13],
            'aporte_afp' => $row[14],
            'riesgo_comun' => $row[15],
            'comision_afp' => $row[16],
            'ap_nal_sol' => $row[17],
            'ap_nal_aseg' => $row[18],
            'iva' => $row[19],
            'faltas_multas' => $row[20],
            'ret_jud' => $row[21],
            'otros_descuentos' => $row[22],
            'total_descuentos' => $row[23],
            'liquido_pagable' => $row[24],
        ]);
    }
}
