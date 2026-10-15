<?php

namespace App\Http\Controllers;

use App\Enums\TipoPlanilla;
use App\Models\FileC3C;
use App\Models\FileCAR;
use App\Models\FileCUA;
use App\Models\FileDES;
use App\Models\FileEMP;
use App\Models\FileING;
use App\Models\FileRLA;
use App\Models\Form;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class FormattedFilesController extends Controller
{
    // ====================================================
    // SOLO SE DEBEN MODIFICAR LAS PRIMERAS TRES VARIABLES
    // ====================================================

    public $planillaBanco = TipoPlanilla::Banco;  // BANCO 1 CHEQUE 2
    public $codigoPlanilla = 9; // CORRELATIVO MENSUAL
    public $mesPlanilla = 5;
    // public $filename = '2025/junio/junio_banco.xlsx'; // NOMBRE DEL ARCHIVO A IMPORTAR
    public $month = "Junio 2025";

    public $totalIng = array();
    public $totalDes = array();

    public $c3c;
    public $emp = array();
    public $ing = array();
    public $des = array();
    public $rla = array();
    public $car = array();
    public $cua = array();
    public $rde = array();
    public $acr = array();
    public $form;
    public $filas;

    public function index($formId)
    {
        $this->form = Form::where('id', $formId)->first();
        $this->generate(); // Generar los datos para procesar
        $this->generarEMP(); // 2. Generar archivo .EMP
        $this->generarING(); // 3. Generar archivo .ING
        $this->generarDES(); // 4. Generar archivo .DES
        $this->generarRLA(); // 5. Generar archivo .RLA
        $this->generarC3C(); // 1. Generar archivo .C3C
        $this->generarCAR(); // 6. Generar archivo .CAR
        $this->generarCUA(); // 7. Generar archivo .CUA
        $this->generarACR(); // 8. Generar archivo .CUA
        return $this->descargarTodos(); // Descargamos todos los archivos generados
    }

    public function generate()
    {
        $employees = $this->form->Employees;
        $this->filas = $employees->map(function ($employee) {
            $activeCharge = $employee->Charges()->wherePivot('state', 'activo')->first();
            $activeArea = $employee->OrganizationalAreas()->wherePivot('state', 'activo')->first();
            $nivel = $activeCharge?->code ?? 0;
            $haber_basico = $activeCharge?->basic_salary ?? 0;
            $bono_frontera = $employee->pivot->border_bonus ?? 0;
            $subtotal = ($haber_basico / 30) * $employee->pivot->worked_days;
            $monto_categoria = ($this->form->Setting->national_minimum_wage * $employee->Category->percentage) / 100;
            $total_ganado = $subtotal + $bono_frontera + $monto_categoria;
            switch ($employee->pivot->sheet_id) {
                case 1:
                    $aporte_afp = $total_ganado * 0.1;
                    $riesgo_comun = $total_ganado * 0.0171;
                    break;
                case 2:
                    $aporte_afp = $total_ganado * 0.1;
                    $riesgo_comun = 0;
                    break;
                case 3:
                    $aporte_afp = 0;
                    $riesgo_comun = $total_ganado * 0.0171;
                    break;
                case 4:
                    $aporte_afp = 0;
                    $riesgo_comun = 0;
                    break;
            }
            $comision_afp = $total_ganado * 0.005;
            $ap_nal_sol = ($haber_basico > 13000) ? ($haber_basico - 13000) * 0.0115 : 0;
            $ap_nal_aseg = $total_ganado * 0.005;
            $iva = $employee->pivot->iva ?? 0;
            $total_descuentos = $aporte_afp +
                $riesgo_comun +
                $comision_afp +
                $ap_nal_sol +
                $ap_nal_aseg +
                $iva +
                $employee->pivot->violations_and_fines +
                $employee->pivot->judicial_withholdings +
                $employee->pivot->other_discounts;
            return (object) [
                'planilla' => $employee->pivot->sheet_id,
                'codigo' => $employee->item_number,
                'cedula' => $employee->Person->identity_card,
                'apaterno' => $employee->Person->first_surname,
                'amaterno' => $employee->Person->second_surname,
                'nombres' => $employee->Person->first_name . ' ' . $employee->Person->middle_name,
                'nivel' => $nivel,
                'categoria' => $employee->Category->percentage,
                'haber_basico' => $haber_basico,
                'dias_trabajados' => $employee->pivot->worked_days,
                'subtotal' => $subtotal,
                'bono_frontera' => $employee->pivot->border_bonus,
                'monto_categoria' => $monto_categoria,
                'total_ganado' => $total_ganado,
                'aporte_afp' => $aporte_afp,
                'riesgo_comun' => $riesgo_comun,
                'comision_afp' => $comision_afp,
                'ap_nal_sol' => $ap_nal_sol,
                'ap_nal_aseg' => $ap_nal_aseg,
                'iva' => $iva,
                'faltas_multas' => $employee->pivot->violations_and_fines,
                'retencion_judicial' => $employee->pivot->judicial_withholdings,
                'otros_descuentos' => $employee->pivot->other_discounts,
                'total_descuentos' => $total_descuentos,
                'liquido_pagable' => $total_ganado - $total_descuentos,
                'sexo' => $employee->Person->gender,
                'nacimiento' => Carbon::parse($employee->Person->date_of_birth)->format('dmY'),
                'afiliado' => $employee->Person->unique_affiliate_number,
                'libreta_militar' => $employee->Person->military_service_booklet_number,
                'codigo_rentista' => $employee->Person->social_security_affiliation_code,
                'tipo_relacion' => $employee->DependencyRelationship?->code ?? '',
                'afp_destino' => $employee->Person->PensionFundManager?->code ?? '',
                'fecha_ingreso' => Carbon::parse($employee->date_of_entry)->format('dmY'),
                'lugar_trabajo' => 60101,
                'grado_academico' => $employee->AcademicDegree?->code ?? 0,
                'unidad_organizacional' => $activeArea?->code ?? 0,
                'primer_nombre' => $employee->Person->first_name,
                'segundo_nombre' => $employee->Person->middle_name,
                'acasada' => $employee->Person->married_name,
                'nco' => $employee->Person->identification_document_plugin,
            ];
        });
    }

    function generarC3C()
    {
        $ano = 2025;
        $ent = 906;
        $c31 = $this->codigoPlanilla; // CORRELATIVO MENSUAL
        $ori = 0;
        $mes = $this->mesPlanilla;
        $fte = 20;
        $org = 220;
        $glo = $this->planillaBanco == TipoPlanilla::Banco ? "P/Registrar pago de sueldos de la Administracion Central c/cuenta bancaria 11700 v0 mes de " . $this->month : "P/Registrar pago de sueldos de la Administracion Central c/cheque 11700 v0 mes de " . $this->month;
        $tip = 'N';

        $this->c3c = new FileC3C([
            'ano' => str_pad($ano, 4, "0", STR_PAD_LEFT),
            'ent' => str_pad($ent, 4, "0", STR_PAD_LEFT),
            'c31' => str_pad($c31, 6, "0", STR_PAD_LEFT),
            'ori' => str_pad($ori, 6, "0", STR_PAD_LEFT),
            'mes' => str_pad($mes, 2, "0", STR_PAD_LEFT),
            'fte' => str_pad($fte, 2, "0", STR_PAD_LEFT),
            'org' => str_pad($org, 3, "0", STR_PAD_LEFT),
            'glo' => str_pad($glo, 200, ' ', STR_PAD_RIGHT), // 200 char
            'tip' => $tip,
            'tot' => $this->formatearTOT(array_sum($this->totalIng), 11),
            'ded' => $this->formatearTOT(array_sum($this->totalDes), 11),
        ]);
    }

    function generarEMP()
    {
        foreach ($this->filas as $fila) {
            array_push($this->emp, new FileEMP([
                'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                'ap1' => str_pad(str_replace(['Ñ', 'ñ'], ['N', 'n'], $fila->apaterno), 25, " ", STR_PAD_RIGHT),
                'ap2' => str_pad(str_replace(['Ñ', 'ñ'], ['N', 'n'], $fila->amaterno), 25, " ", STR_PAD_RIGHT),
                'ap3' => str_pad(str_replace(['Ñ', 'ñ'], ['N', 'n'], $fila->acasada), 25, " ", STR_PAD_RIGHT),
                'no1' => str_pad(str_replace(['Ñ', 'ñ'], ['N', 'n'], $fila->primer_nombre), 25, " ", STR_PAD_RIGHT),
                'no2' => str_pad(str_replace(['Ñ', 'ñ'], ['N', 'n'], $fila->segundo_nombre), 25, " ", STR_PAD_RIGHT),
                'sex' => $fila->sexo, //!Agregar sex al imput PaymentForm // 1 char
                'nac' => str_pad($fila->nacimiento, 8, "0", STR_PAD_LEFT),
                'nua' => str_pad($fila->afiliado, 10, "0", STR_PAD_LEFT), //!Agregar nua al imput PaymentForm // 10 char
                'lib' => str_pad($fila->libreta_militar, 12, "0", STR_PAD_LEFT), //!Agregar lib al imput PaymentForm // 12 char
                'ren' => str_pad($fila->codigo_rentista, 10, "0", STR_PAD_LEFT), //!Agregar ren al imput PaymentForm // 10 char
                'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
            ]));
        }
    }

    function generarING()
    {
        foreach ($this->filas as $fila) { // INTERACTUA CON EL ARCHIVO EXCEL DE PLANILLAS
            // =====================================
            // INICIALIZAMOS ACUMULADOR DE INGRESOS
            // =====================================
            $ing = 0;

            // =======================
            // 11220: MONTO CATEGORÍA
            // =======================
            if ($fila->monto_categoria != 0) {
                array_push($this->ing, new FileING([
                    'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                    'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                    'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                    'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                    'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                    'cat' => str_pad(1, 8, "0", STR_PAD_LEFT),
                    'par' => str_pad(11220, 5, "0", STR_PAD_LEFT),
                    'tot' => $this->formatearTOT($fila->monto_categoria),
                    'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                ]));

                $ing += number_format($fila->monto_categoria, 2, '.', '');
            }

            // ====================
            // 11700: HABER BÁSICO
            // ====================
            array_push($this->ing, new FileING([
                'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                'cat' => str_pad(1, 8, "0", STR_PAD_LEFT),
                'par' => str_pad(11700, 5, "0", STR_PAD_LEFT),
                'tot' => $this->formatearTOT($fila->haber_basico),
                'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
            ]));

            $ing += number_format($fila->haber_basico, 2, '.', '');

            // ================================================
            // 13110: APORTE AFP 10% PATRONAL DEL TOTAL GANADO
            // ================================================
            if ($fila->total_ganado * 0.1 !== '0') {
                array_push($this->ing, new FileING([
                    'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                    'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                    'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                    'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                    'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                    'cat' => str_pad(1, 8, "0", STR_PAD_LEFT),
                    'par' => str_pad(13110, 5, "0", STR_PAD_LEFT),
                    'tot' => $this->formatearTOT($fila->total_ganado * 0.1),
                    'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                ]));

                $ing += number_format($fila->total_ganado * 0.1, 2, '.', '');
            }

            // ====================================================
            // 13120: RIESGO COMUN 1.71% PATRONAL DEL TOTAL GANADO
            // ====================================================
            if ($fila->planilla == 1) {
                if ($fila->total_ganado * 0.0171 != 0) {
                    array_push($this->ing, new FileING([
                        'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                        'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                        'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                        'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                        'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                        'cat' => str_pad(1, 8, "0", STR_PAD_LEFT),
                        'par' => str_pad(13120, 5, "0", STR_PAD_LEFT),
                        'tot' => $this->formatearTOT($fila->total_ganado * 0.0171),
                        'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                    ]));
                }

                $ing += number_format($fila->total_ganado * 0.0171, 2, '.', '');
            }

            // ===========================
            // 13200: 2% DEL TOTAL GANADO
            // ===========================
            if ($fila->total_ganado * 0.02 != 0) {
                array_push($this->ing, new FileING([
                    'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                    'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                    'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                    'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                    'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                    'cat' => str_pad(1, 8, "0", STR_PAD_LEFT),
                    'par' => str_pad(13200, 5, "0", STR_PAD_LEFT),
                    'tot' => $this->formatearTOT($fila->total_ganado * 0.02),
                    'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                ]));

                $ing += number_format($fila->total_ganado * 0.02, 2, '.', '');
            }

            // ===================================================
            // 13131: 3.5% DEL TOTAL GANADO, SE APLICO SOLO EL 3%
            // ===================================================
            if ($fila->total_ganado * 0.03 != 0) {
                array_push($this->ing, new FileING([
                    'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                    'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                    'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                    'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                    'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                    'cat' => str_pad(1, 8, "0", STR_PAD_LEFT),
                    'par' => str_pad(13131, 5, "0", STR_PAD_LEFT),
                    'tot' => $this->formatearTOT($fila->total_ganado * 0.03),
                    'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                ]));

                $ing += number_format($fila->total_ganado * 0.03, 2, '.', '');
            }

            array_push($this->totalIng, $ing);
        }
    }

    function generarDES()
    {
        foreach ($this->filas as $fila) {
            // =======================================
            // INICIALIZAMOS ACUMULADOR DE DESCUENTOS
            // =======================================
            $des = 0;

            // ================================
            // 74: 2% PATRONAL AL TOTAL GANADO
            // ================================
            switch ($this->planillaBanco) {
                case TipoPlanilla::Banco: // BANCO
                    if ($fila->total_ganado * 0.02 != 0 && in_array($fila->planilla, [1, 2])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(74, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.02, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->total_ganado * 0.02, 2, '.', '');
                    }
                    break;

                case TipoPlanilla::Cheque: // CHEQUE
                    if ($fila->total_ganado * 0.02 != 0 && in_array($fila->planilla, [1, 2])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(74, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.02, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->total_ganado * 0.02, 2, '.', '');
                    }
                    break;
            }
            /* if ($fila->total_ganado * 0.02 != 0 && in_array($fila->planilla, [1, 2])) {
                array_push($this->des, new FileDES([
                    'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                    'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                    'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                    'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                    'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                    'acr' => str_pad(74, 3, "0", STR_PAD_LEFT),
                    'tot' => $this->formatearTOT($fila->total_ganado * 0.02, 6),
                    'nco' => ($fila->nco == '') ? '  ':$fila->nco,
                ]));
            } */

            // ==================================
            // 205: 10% PATRONAL AL TOTAL GANADO
            // ==================================
            switch ($this->planillaBanco) {
                case TipoPlanilla::Banco: // BANCO
                    if ($fila->total_ganado * 0.1 != 0 && in_array($fila->planilla, [1, 2, 3, 4])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(205, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.1, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->total_ganado * 0.1, 2, '.', '');
                    }
                    break;

                case TipoPlanilla::Cheque: // CHEQUE
                    if ($fila->total_ganado * 0.1 != 0 && in_array($fila->planilla, [1, 2, 3])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(205, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.1, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->total_ganado * 0.1, 2, '.', '');
                    }
                    break;
            }

            // =================================
            // 302: 10% LABORAL AL TOTAL GANADO
            // =================================
            switch ($this->planillaBanco) {
                case TipoPlanilla::Banco: // BANCO
                    if ($fila->total_ganado * 0.1 != 0 && in_array($fila->planilla, [1, 2])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(302, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.1, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->total_ganado * 0.1, 2, '.', '');
                    }
                    break;

                case TipoPlanilla::Cheque: // CHEQUE
                    if ($fila->total_ganado * 0.1 != 0 && in_array($fila->planilla, [1, 2])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(302, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.1, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->total_ganado * 0.1, 2, '.', '');
                    }
                    break;
            }

            // ===================================
            // 304: 1.71% LABORAL AL TOTAL GANADO
            // ===================================
            switch ($this->planillaBanco) {
                case TipoPlanilla::Banco: // BANCO
                    if ($fila->total_ganado * 0.0171 != 0 && in_array($fila->planilla, [1])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(304, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.0171, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->total_ganado * 0.0171, 2, '.', '');
                    }
                    break;

                case TipoPlanilla::Cheque: // CHEQUE
                    if ($fila->total_ganado * 0.0171 != 0 && in_array($fila->planilla, [1])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(304, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.0171, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->total_ganado * 0.0171, 2, '.', '');
                    }
                    break;
            }

            // ===============================================
            // 306: 0.5% LABORAL COMISION AFP AL TOTAL GANADO
            // ===============================================
            switch ($this->planillaBanco) {
                case TipoPlanilla::Banco: // BANCO
                    if ($fila->total_ganado * 0.005 != 0 && in_array($fila->planilla, [1, 2, 3, 4])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(306, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.005, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->total_ganado * 0.005, 2, '.', '');
                    }
                    break;

                case TipoPlanilla::Cheque: // CHEQUE
                    if ($fila->total_ganado * 0.005 != 0 && in_array($fila->planilla, [1, 2, 3])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(306, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.005, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->total_ganado * 0.005, 2, '.', '');
                    }
                    break;
            }

            // =================================
            // 308: 1.71% PATRONAL RIESGO COMUN
            // =================================
            switch ($this->planillaBanco) {
                case TipoPlanilla::Banco: // BANCO
                    if ($fila->total_ganado * 0.0171 != 0 && in_array($fila->planilla, [1, 3])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(308, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.0171, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->total_ganado * 0.0171, 2, '.', '');
                    }
                    break;

                case TipoPlanilla::Cheque: // CHEQUE
                    if ($fila->total_ganado * 0.0171 != 0 && in_array($fila->planilla, [1, 3])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(308, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.0171, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->total_ganado * 0.0171, 2, '.', '');
                    }
                    break;
            }

            /* if ($fila->planilla == 2 && $this->planillaBanco == 2) { // CHEQUE
                switch ($fila->total_ganado * 0.0171) {
                    case '0':
                        break;
                    default:
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(308, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.0171, 6),
                            'nco' => ($fila->nco == '') ? '  ':$fila->nco,
                        ]));
                }
            } */

            // =================================
            // 320: 3% PATRONAL AL TOTAL GANADO
            // =================================
            switch ($this->planillaBanco) {
                case TipoPlanilla::Banco: // BANCO
                    if ($fila->total_ganado * 0.03 != 0 && in_array($fila->planilla, [1, 2, 3, 4])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(320, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.03, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->total_ganado * 0.03, 2, '.', '');
                    }
                    break;

                case TipoPlanilla::Cheque: // CHEQUE
                    if ($fila->total_ganado * 0.03 != 0 && in_array($fila->planilla, [1, 2, 3])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(320, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.03, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->total_ganado * 0.03, 2, '.', '');
                    }
                    break;
            }

            // ==================================
            // 324: 0.5% LABORAL AL TOTAL GANADO
            // ==================================
            switch ($this->planillaBanco) {
                case TipoPlanilla::Banco: // BANCO
                    if ($fila->total_ganado * 0.005 != 0 && in_array($fila->planilla, [1, 2, 3, 4])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(324, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.005, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->total_ganado * 0.005, 2, '.', '');
                    }
                    break;

                case TipoPlanilla::Cheque: // CHEQUE
                    if ($fila->total_ganado * 0.005 != 0 && in_array($fila->planilla, [1, 2, 3])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(324, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->total_ganado * 0.005, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->total_ganado * 0.005, 2, '.', '');
                    }
                    break;
            }


            // =====================
            // 002: FALTAS Y MULTAS
            // =====================
            switch ($this->planillaBanco) {
                case TipoPlanilla::Banco: // BANCO
                    if ($fila->faltas_multas != 0 && in_array($fila->planilla, [1, 2, 3, 4])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(2, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->faltas_multas, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->faltas_multas, 2, '.', '');
                    }
                    break;

                case TipoPlanilla::Cheque: // CHEQUE
                    if ($fila->faltas_multas != 0 && in_array($fila->planilla, [1, 2, 3])) {
                        array_push($this->des, new FileDES([
                            'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                            'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                            'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                            'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                            'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                            'acr' => str_pad(2, 3, "0", STR_PAD_LEFT),
                            'tot' => $this->formatearTOT($fila->faltas_multas, 6),
                            'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                        ]));

                        $des += number_format($fila->faltas_multas, 2, '.', '');
                    }
                    break;
            }

            // ========================================================
            // 328: 1% DEL TOTAL GANADO (GOBERNADOR Y VICEGOBERNADORA)
            // ========================================================
            if ($fila->total_ganado * 0.01 != 0 && in_array($fila->planilla, [1, 2, 3, 4]) && in_array($fila->codigo, ['3755', '4161'])) {
                array_push($this->des, new FileDES([
                    'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                    'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                    'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                    'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                    'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                    'acr' => str_pad(328, 3, "0", STR_PAD_LEFT),
                    'tot' => $this->formatearTOT($fila->total_ganado * 0.01, 6),
                    'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
                ]));

                $des += number_format($fila->total_ganado * 0.01, 2, '.', '');
            }

            array_push($this->totalDes, $des);
        }
    }

    function generarRLA()
    {
        foreach ($this->filas as $index => $fila) {
            array_push($this->rla, new FileRLA([
                'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                'ite' => str_pad($fila->codigo, 5, "0", STR_PAD_LEFT),
                'doc' => str_pad($fila->cedula, 15, "0", STR_PAD_LEFT),
                'tip' => $fila->tipo_relacion,
                'afp' => $fila->afp_destino,
                'ing' => str_pad($fila->fecha_ingreso, 8, "0", STR_PAD_LEFT),
                'niv' => str_pad($fila->nivel, 5, "0", STR_PAD_LEFT),
                'dia' => $this->formatearTOT(($fila->dias_trabajados == 30) ? 0 : $fila->dias_trabajados, 5),
                'dis' => str_pad($fila->lugar_trabajo, 5, "0", STR_PAD_LEFT),
                /* 'gan' => $this->formatearTOT($gan, 6),
                'des' => $this->formatearTOT($des, 6), */
                'gan' => $this->formatearTOT($this->totalIng[$index], 6),
                'des' => $this->formatearTOT($this->totalDes[$index], 6),
                'ode' => $this->formatearTOT($fila->retencion_judicial, 6),
                'tit' => 0, // Tiempo completo
                'car' => str_pad($fila->nivel, 4, "0", STR_PAD_LEFT),
                'cga' => str_pad($fila->grado_academico, 2, "0", STR_PAD_LEFT),
                'cua' => str_pad($fila->unidad_organizacional, 4, "0", STR_PAD_LEFT),
                'nco' => ($fila->nco == '') ? '  ' : $fila->nco,
            ]));
        }
    }

    function generarCAR()
    {
        // Crear una variable con array de objetos
        $cars = [
            (object) ['car' => 1, 'des' => 'GOBERNADOR'],
            // (object) ['car' => 2, 'des' => 'VICE GOBERNADOR'],
            // (object) ['car' => 3, 'des' => 'ASAMBLEISTA DEPARTAMENTAL'],
            (object) ['car' => 4, 'des' => 'SECRETARIO DEPARTAMENTAL'],
            // (object) ['car' => 5, 'des' => 'SUB GOBERNADOR'],
            (object) ['car' => 6, 'des' => 'DIRECTOR I'],
            // (object) ['car' => 7, 'des' => 'DIRECTOR II'],
            // (object) ['car' => 8, 'des' => 'JEFE DE UNIDAD'],
            // (object) ['car' => 9, 'des' => 'RESPONSABLE DE AREA'],
            // (object) ['car' => 10, 'des' => 'ASESOR'],
            (object) ['car' => 11, 'des' => 'PROFESIONAL ESPECIALIZADO I'],
            (object) ['car' => 12, 'des' => 'PROFESIONAL ESPECIALIZADO II'],
            // (object) ['car' => 13, 'des' => 'PROFESIONAL I'],
            (object) ['car' => 14, 'des' => 'PROFESIONAL II'],
            // (object) ['car' => 15, 'des' => 'PROFESIONAL III'],
            // (object) ['car' => 16, 'des' => 'TECNICO ESPECIALIZADO'],
            // (object) ['car' => 17, 'des' => 'TECNICO ESPECIALIZADO I'],
            // (object) ['car' => 18, 'des' => 'TECNICO ESPECIALIZADO II'],
            (object) ['car' => 19, 'des' => 'AUXILIAR ADMINISTRATIVO'],
            // (object) ['car' => 20, 'des' => 'TECNICO I'],
            // (object) ['car' => 21, 'des' => 'TECNICO II'],
        ];

        foreach ($cars as $car) {
            array_push($this->car, new FileCAR([
                'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                'car' => str_pad($car->car, 4, "0", STR_PAD_LEFT),
                'des' => str_pad($car->des, 40, ' ', STR_PAD_RIGHT), // 40 char
            ]));
        }
    }

    function generarCUA()
    {
        // CHEQUE

        //Banco 1 Cheque 2
        switch ($this->planillaBanco) {
            case TipoPlanilla::Banco:
                // BANCO
                $cuas = [
                    (object) ['cua' => 1622, 'des' => '- DESPACHO DEL GOBERNADOR               '],
                    (object) ['cua' => 1569, 'des' => '- DIRECCION GENERAL DE DESPACHO         '],
                    (object) ['cua' => 1629, 'des' => '- UNIDAD VENTANILLA UNICA               '],
                    (object) ['cua' => 2145, 'des' => '- UNIDAD DE JUEZ SUMARIANTE             '],
                    (object) ['cua' => 3026, 'des' => '- DIRECCION JURIDICA                    '],
                    (object) ['cua' => 2008, 'des' => '- UNIDAD DE LIMITES                     '],
                    (object) ['cua' => 1628, 'des' => '- UNIDAD DE NOTARIA DE GOBIERNO         '],
                    (object) ['cua' => 1564, 'des' => '- DIRECCION DE GESTION PROCESAL         '],
                    (object) ['cua' => 1982, 'des' => '- UNIDAD DE GESTION PROCESAL PENAL      '],
                    (object) ['cua' => 1626, 'des' => '- UNIDAD DE GESTION PROCESAL CIVIL      '],
                    (object) ['cua' => 1627, 'des' => '- UNIDAD DE SANEAMIENTO LEGAL           '],
                    (object) ['cua' => 3223, 'des' => '- DIRECCION DE ASUNTOS JURIDICOS        '],
                    (object) ['cua' => 3025, 'des' => '- DIRECCION DE COMUNICACION E INFORMACIO'],
                    (object) ['cua' => 2603, 'des' => '- UNIDAD DE RELACIONES PUBLICAS Y PROTOC'],
                    (object) ['cua' => 1623, 'des' => '- UNIDAD DE AUDITORIA INTERNA           '],
                    (object) ['cua' => 1568, 'des' => '- DIRECCION DEPARTAMENTAL DE TRANSPARENC'],
                    (object) ['cua' => 2615, 'des' => '- SECRETARIA DEPARTAMENTAL DE GESTION   '],
                    (object) ['cua' => 3003, 'des' => '- DIRECCION DE DESARROLLO INSTITUCIONAL '],
                    (object) ['cua' => 3224, 'des' => '- DIRECCION DE POLITICAS PUBLICAS       '],
                    (object) ['cua' => 1545, 'des' => '- SECRETARIA DEPARTAMENTAL DE PLANIFICAC'],
                    (object) ['cua' => 1549, 'des' => '- DIRECCION DE PLANIFICACION ESTRATEGICA'],
                    (object) ['cua' => 2923, 'des' => '- DIRECCION DE PROGRAMAS Y PROYECTOS    '],
                    (object) ['cua' => 3004, 'des' => '- DIRECCION DE MONITOREO Y SEGUIMIENTO  '],
                    (object) ['cua' => 2623, 'des' => '- SECRETARIA DEPARTAMENTAL DE ECONOMIA Y'],
                    (object) ['cua' => 1552, 'des' => '- DIRECCION DEPARTAMENTAL DE FINANZAS   '],
                    (object) ['cua' => 1634, 'des' => '- UNIDAD DE PRESUPUESTOS                '],
                    (object) ['cua' => 1633, 'des' => '- UNIDAD DE TESORERIA                   '],
                    (object) ['cua' => 1632, 'des' => '- UNIDAD DE CONTABILIDAD                '],
                    (object) ['cua' => 1635, 'des' => '- AREA DEL ARCHIVO CONTABLE             '],
                    (object) ['cua' => 1553, 'des' => '- DIRECCION DEPARTAMENTAL DE ADMINISTRAC'],
                    (object) ['cua' => 1642, 'des' => '- AREA DEL ARCHIVO CENTRAL              '],
                    (object) ['cua' => 1636, 'des' => '- UNIDAD  DE ACTIVOS FIJOS              '],
                    (object) ['cua' => 1637, 'des' => '- UNIDAD DE ALMACENES                   '],
                    (object) ['cua' => 1638, 'des' => '- UNIDAD DE ADQUISICIONES               '],
                    (object) ['cua' => 1639, 'des' => '- UNIDAD DE SERVICIOS GENERALES         '],
                    (object) ['cua' => 1551, 'des' => '- DIRECCION DEPARTAMENTAL DE CONTRATACIO'],
                    (object) ['cua' => 1554, 'des' => '- DIRECCION DEPARTAMENTAL DE RECURSOS HU'],
                    (object) ['cua' => 2162, 'des' => '- UNIDAD DE PLANILLAS, REGISTRO Y CONTRO'],
                    (object) ['cua' => 2616, 'des' => '- DIRECCION DE TECNOLOGIAS DE INFORMACIO'],
                    (object) ['cua' => 2628, 'des' => '- SECRETARIA DEPARTAMENTAL DE DESARROLLO'],
                    (object) ['cua' => 1577, 'des' => '- DIRECCION DE INDUSTRIA, COMERCIO Y TUR'],
                    (object) ['cua' => 3031, 'des' => '- DIRECCION DE GESTION AMBIENTAL, RIESGO'],
                    (object) ['cua' => 3383, 'des' => '- AREA DE BIODIVERSIDAD Y CODEFAUNA     '],
                    (object) ['cua' => 1586, 'des' => '- DIRECCION DE ENERGIA,HIDROCARBUROS Y M'],
                    (object) ['cua' => 3243, 'des' => '- DIRECCION DE PROMOCION ECONOMICA      '],
                    (object) ['cua' => 1643, 'des' => '- PROGRAMA SOLIDARIO COMUNAL-PROSOL     '],
                    (object) ['cua' => 2882, 'des' => '- SERVICIO DEPARTAMENTAL DE EMPLEO-SEDEM'],
                    (object) ['cua' => 3123, 'des' => '- SERVICIO DEPARTAMENTAL DE GESTION DEL '],
                    (object) ['cua' => 3163, 'des' => '- UNIDAD OPERATIVA DEL PROGRAMA GAS NATU'],
                    (object) ['cua' => 3143, 'des' => '- FONDO DE PROMOCION ECONOMICA DEPARTAME'],
                    (object) ['cua' => 1593, 'des' => '- SECRETARIA DEPARTAMENTAL DE OBRAS PUBL'],
                    (object) ['cua' => 1595, 'des' => '- DIRECCION DE INFRAESTRUCTURA          '],
                    (object) ['cua' => 1594, 'des' => '- DIRECCION DE TRANSPORTES Y COMUNICACIO'],
                    (object) ['cua' => 2645, 'des' => '- DIRECCION DE SERVICIOS BASICOS Y VIVIE'],
                    (object) ['cua' => 3283, 'des' => '- DIRECCION DEPARTAMENTAL DE ADMINISTRAC'],
                    (object) ['cua' => 3363, 'des' => '- UNIDAD DE SANEAMIENTO DAV             '],
                    (object) ['cua' => 2646, 'des' => '- SECRETARIA DEPARTAMENTAL DE DESARROLLO'],
                    (object) ['cua' => 3263, 'des' => '- DIRECCION DE EDUCACION, CIENCIA Y TECN'],
                    (object) ['cua' => 2647, 'des' => '- DIRECCION DE CULTURA Y PATRIMONIO     '],
                    (object) ['cua' => 2042, 'des' => '- ARCHIVO HISTORICO                     '],
                    (object) ['cua' => 3027, 'des' => '- DIRECCION DE SEGURIDAD CIUDADANA      '],
                    (object) ['cua' => 3032, 'des' => '- DIRECCION DE POLITICAS DE GENERO Y GEN'],
                    (object) ['cua' => 1580, 'des' => '- UNIDAD DE JUVENTUDES                  '],
                    (object) ['cua' => 3266, 'des' => '- DELEGACION PUEBLO TAPIETE             '],
                ];
                break;
            case TipoPlanilla::Cheque:
                // CHEQUE
                $cuas = [
                    (object) ['cua' => 1622, 'des' => '- DESPACHO DEL GOBERNADOR               '],
                    (object) ['cua' => 1623, 'des' => '- UNIDAD DE AUDITORIA INTERNA           '],
                    (object) ['cua' => 1553, 'des' => '- DIRECCION DEPARTAMENTAL DE ADMINISTRAC'],
                    (object) ['cua' => 1639, 'des' => '- UNIDAD DE SERVICIOS GENERALES         '],
                    (object) ['cua' => 1643, 'des' => '- PROGRAMA SOLIDARIO COMUNAL-PROSOL     '],
                    (object) ['cua' => 2645, 'des' => '- DIRECCION DE SERVICIOS BASICOS Y VIVIE'],
                    (object) ['cua' => 3283, 'des' => '- DIRECCION DEPARTAMENTAL DE ADMINISTRAC'],
                    (object) ['cua' => 3363, 'des' => '- UNIDAD DE SANEAMIENTO DAV             '],
                    (object) ['cua' => 2646, 'des' => '- SECRETARIA DEPARTAMENTAL DE DESARROLLO'],
                ];
                break;
        }

        foreach ($cuas as $cua) {
            array_push($this->cua, new FileCUA([
                'ano' => str_pad(date('Y'), 4, "0", STR_PAD_LEFT),
                'ent' => str_pad(906, 4, "0", STR_PAD_LEFT),
                'c31' => str_pad($this->codigoPlanilla, 6, "0", STR_PAD_LEFT),
                'cua' => str_pad($cua->cua, 4, "0", STR_PAD_LEFT),
                'des' => str_pad($cua->des, 40, ' ', STR_PAD_RIGHT), // 40 char
            ]));
        }
    }

    function generarRDE()
    {
        //
    }

    function generarACR()
    {
        //
    }

    function formatearTOT($numero, $ent = 6)
    {
        // Formatear el número con dos decimales
        $numeroFormateado = number_format($numero, 2, '.', '');

        // Separar parte entera y decimal
        [$entero, $decimal] = explode('.', $numeroFormateado);

        // Rellenar la parte entera con ceros a la izquierda hasta 5 dígitos
        $enteroFormateado = str_pad($entero, $ent, '0', STR_PAD_LEFT);

        // Unir parte entera y decimal
        return $enteroFormateado . '.' . $decimal;
    }

    private function generarArchivo($nombreArchivo, $type)
    {
        $contenido = '';
        switch ($type) {
            case 'c3c':
                $contenido = $this->c3c->ano . $this->c3c->ent . $this->c3c->c31 . $this->c3c->ori . $this->c3c->mes . $this->c3c->fte . $this->c3c->org . $this->c3c->glo . $this->c3c->tip . $this->c3c->tot . $this->c3c->ded;
                break;
            case 'emp':
                $contenido = implode("\n", array_map(function ($emp) {
                    return "{$emp->doc}{$emp->ap1}{$emp->ap2}{$emp->ap3}{$emp->no1}{$emp->no2}{$emp->sex}{$emp->nac}{$emp->nua}{$emp->lib}{$emp->ren}{$emp->nco}";
                }, $this->emp));
                break;
            case 'ing':
                $contenido = implode("\n", array_map(function ($ing) {
                    return "{$ing->ano}{$ing->ent}{$ing->c31}{$ing->ite}{$ing->doc}{$ing->cat}{$ing->par}{$ing->tot}{$ing->nco}";
                }, $this->ing));
                break;
            case 'des':
                $contenido = implode("\n", array_map(function ($des) {
                    return "{$des->ano}{$des->ent}{$des->c31}{$des->ite}{$des->doc}{$des->acr}{$des->tot}{$des->nco}";
                }, $this->des));
                break;
            case 'rla':
                $contenido = implode("\n", array_map(function ($rla) {
                    return "{$rla->ano}{$rla->ent}{$rla->c31}{$rla->ite}{$rla->doc}{$rla->tip}{$rla->afp}{$rla->ing}{$rla->niv}{$rla->dia}{$rla->dis}{$rla->gan}{$rla->des}{$rla->ode}{$rla->tit}{$rla->car}{$rla->cga}{$rla->cua}{$rla->nco}";
                }, $this->rla));
                break;
            case 'car':
                $contenido = implode("\n", array_map(function ($car) {
                    return "{$car->ano}{$car->ent}{$car->c31}{$car->car}{$car->des}";
                }, $this->car));
                break;
            case 'cua':
                $contenido = implode("\n", array_map(function ($cua) {
                    return "{$cua->ano}{$cua->ent}{$cua->c31}{$cua->cua}{$cua->des}";
                }, $this->cua));
                break;
            case 'rde':
                $contenido = implode("\n", array_map(function ($rde) {
                    return "{$rde->ano}{$rde->ent}{$rde->c31}{$rde->acr}{$rde->dis}{$rde->chq}{$rde->tot}{$rde->ser}";
                }, $this->rde));
                break;
            case 'acr':
                $contenido = implode("\n", array_map(function ($acr) {
                    return "{$acr->doc}{$acr->dis}{$acr->nom}";
                }, $this->acr));
                break;
        }

        $ruta = storage_path($nombreArchivo);

        // Guardar el contenido en un archivo
        file_put_contents($ruta, $contenido);

        return $ruta; // Retornar la ruta del archivo generado
    }

    public function descargarTodos()
    {
        $name = $this->planillaBanco == TipoPlanilla::Banco ? '090600201' : '090600250';
        $foldername = $this->planillaBanco == TipoPlanilla::Banco ? '01;0 _ PARTIDA 11700 - ADMINISTRACION CENTRAL' : '50;0 _ PARTIDA 11700 - ADMINISTRACION CENTRAL -- CON CHEQUE--';
        $archivos = [];

        // 1. Generar los archivos
        $archivos[] = $this->generarArchivo($name . '.C3C', 'c3c');
        $archivos[] = $this->generarArchivo($name . '.EMP', 'emp');
        $archivos[] = $this->generarArchivo($name . '.ING', 'ing');
        $archivos[] = $this->generarArchivo($name . '.DES', 'des');
        $archivos[] = $this->generarArchivo($name . '.RLA', 'rla');
        $archivos[] = $this->generarArchivo($name . '.CAR', 'car');
        $archivos[] = $this->generarArchivo($name . '.CUA', 'cua');
        $archivos[] = $this->generarArchivo($name . '.RDE', 'rde');
        $archivos[] = $this->generarArchivo($name . '.ACR', 'acr');

        // 2. Crear el ZIP
        $zipFile = storage_path($foldername . '.zip');
        $zip = new ZipArchive;

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($archivos as $archivo) {
                $zip->addFile($archivo, basename($archivo));
            }
            $zip->close();
        }

        // 3. Descargar el ZIP y eliminarlo después de la descarga
        return response()->download($zipFile)->deleteFileAfterSend(true);
    }
}
