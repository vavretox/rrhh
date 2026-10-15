<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentForm extends Model
{
    protected $fillable = [
        'planilla',
        'codigo',
        'cedula',
        'apaterno',
        'amaterno',
        'nombres',
        'nivel',
        'categoria',
        'haber_basico',
        'dias_trabajados',
        'subtotal',
        'bono_frontera',
        'monto_categoria',
        'total_ganado',
        'aporte_afp',
        'riesgo_comun',
        'comision_afp',
        'ap_nal_sol',
        'ap_nal_aseg',
        'iva',
        'faltas_multas',
        'retencion_judicial',
        'otros_descuentos',
        'total_descuentos',
        'liquido_pagable',
        'sexo',
        'nacimiento',
        'afiliado',
        'libreta_militar',
        'codigo_rentista',
        'tipo_relacion',
        'afp_destino',
        'fecha_ingreso',
        'lugar_trabajo',
        'grado_academico',
        'unidad_organizacional',
        'primer_nombre',
        'segundo_nombre',
        'acasada',
    ];
}
