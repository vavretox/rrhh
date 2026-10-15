<?php

namespace App\Http\Controllers;

use App\Models\EmployeeForm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Elibyy\TCPDF\TCPDF;

class PDFController extends Controller
{
    public function payment_slip($employeeFormSlug) // Terminado
    {
        $employeeForm = EmployeeForm::where('slug', $employeeFormSlug)->first();
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false);

        $pdf->SetCreator('Direccion Departamental de Recursos Humanos - Gobierno Autonomo Departamental de Tarija');
        $pdf->SetAuthor('Direccion Departamental de Recursos Humanos - Gobierno Autonomo Departamental de Tarija');
        $pdf->SetTitle('Boleta de pago');
        $pdf->SetSubject('Direccion Departamental de Recursos Humanos - Gobierno Autonomo Departamental de Tarija');
        $pdf->SetKeywords('REPORTE, PDF, INFORMACION, RRHH');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $pdf->SetMargins(10, 10, 10);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->AddPage();

        $logo = "/images/logo-gadt.png";
        $fecha_reporte = Carbon::now()->translatedFormat('d \d\e F \d\e Y h:m:s');

        $header = <<<INFO
            <table border="0">
                <tr>
                    <td width="50%" align="center">
                        <br/><img src="$logo" width="50px"/>
                        <br/>Gobierno Aut&oacute;nomo Departamental de Tarija
                        <br/>Direcci&oacute;n de Recursos Humanos
                    </td>
                    <td style="font-size: 7px;" align="right">
                        <b>Fecha de exportaci&oacute;n:</b> $fecha_reporte
                    </td>
                </tr>
            </table>
        INFO;

        $pdf->writeHTML($header, true, false, true, false, '');

        $pdf->SetFont('dejavusans', '', 12);

        $title = <<<INFO
            <table>
                <tr>
                    <td align="center">
                        <b>BOLETA DE PAGO</b>
                    </td>
                </tr>
            </table>
        INFO;

        $pdf->writeHTML($title, true, false, true, false, '');

        $pdf->SetFont('dejavusans', '', 7);

        $period = strtoupper(Carbon::parse($employeeForm->Form->period)->translatedFormat('F/Y'));
        $info = <<<INFO
            <table border="0">
                <tr>
                    <td width="20%" align="right">
                        <b>Nombres y Apellidos:</b>
                    </td>
                    <td width="40%" align="left">
                        {$employeeForm->Employee->Person->first_name} {$employeeForm->Employee->Person->middle_name} {$employeeForm->Employee->Person->first_surname} {$employeeForm->Employee->Person->second_surname}
                    </td>
                    <td width="20%" align="right">
                        <b>C&oacute;digo:</b>
                    </td>
                    <td width="20%" align="right">
                        {$employeeForm->Employee->item_number}
                    </td>
                </tr>
                <tr>
                    <td width="20%" align="right">
                        <b>Planilla:</b>
                    </td>
                    <td width="40%" align="left">
                        ADM. CENTRAL
                    </td>
                    <td width="20%" align="right">
                        <b>Partida:</b>
                    </td>
                    <td width="20%" align="right">
                        11700
                    </td>
                </tr>
                <tr>
                    <td width="20%" align="right">
                        <b>Cargo:</b>
                    </td>
                    <td colspan="3" align="left">
                        {$employeeForm->Employee->Charges()->wherePivot('state', 'activo')->first()->name}
                    </td>
                </tr>
                <tr>
                    <td width="20%" align="right">
                        <b>Unidad Ejecutora:</b>
                    </td>
                    <td colspan="3" align="left">
                        {$employeeForm->Employee->OrganizationalAreas()->wherePivot('state', 'activo')->first()->name}
                    </td>
                </tr>
                <tr>
                    <td width="20%" align="right">
                        <b>Mes/Gesti&oacute;n:</b>
                    </td>
                    <td width="40%" align="left">
                        {$period}
                    </td>
                    <td width="20%" align="right">
                        <b>Cua:</b>
                    </td>
                    <td width="20%" align="right">
                        {$employeeForm->Employee->Person->unique_affiliate_number}
                    </td>
                </tr>
            </table>
        INFO;

        $pdf->writeHTML($info, true, false, true, false, '');

        $aporte_afp = 0;
        $riesgo_comun = 0;
        $nivel = $employeeForm->Employee->Charges()->wherePivot('state', 'activo')->first()->code;
        $haber_basico = $employeeForm->Employee->Charges()->wherePivot('state', 'activo')->first()->basic_salary;
        $bono_frontera = $employeeForm->border_bonus ?? 0;
        $subtotal = ($haber_basico / 30) * $employeeForm->worked_days;
        $monto_categoria = ($employeeForm->Form->Setting->national_minimum_wage * $employeeForm->Employee->Category->percentage) / 100;
        $total_ganado = $subtotal + $bono_frontera + $monto_categoria;
        switch ($employeeForm->sheet_id) {
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
        $ap_nal_sol = ($employeeForm->Employee->Charges()->wherePivot('state', 'activo')->first()->basic_salary > 13000) ? ($employeeForm->Employee->Charges()->wherePivot('state', 'activo')->first()->basic_salary - 13000) * 0.0115 : 0;
        $ap_nal_aseg = $total_ganado * 0.005;
        $iva = $employeeForm->iva ?? 0;
        $total_descuentos = $aporte_afp +
            $riesgo_comun +
            $comision_afp +
            $ap_nal_sol +
            $ap_nal_aseg +
            $iva +
            $employeeForm->violations_and_fines +
            $employeeForm->judicial_withholdings +
            $employeeForm->other_discounts;

        $haber_basico = number_format($haber_basico, 2, ',', '.');
        $aporte_afp = number_format($aporte_afp, 2, ',', '.');
        $riesgo_comun = number_format($riesgo_comun, 2, ',', '.');
        $monto_categoria = number_format($monto_categoria, 2, ',', '.');
        $subtotal = number_format($subtotal, 2, ',', '.');
        $comision_afp = number_format($comision_afp, 2, ',', '.');
        $percentage_category = number_format($employeeForm->Employee->Category->percentage, 2, ',', '.');
        $ap_nal_sol = number_format($ap_nal_sol, 2, ',', '.');
        $bono_frontera = number_format($bono_frontera, 2, ',', '.');
        $ap_nal_aseg = number_format($ap_nal_aseg, 2, ',', '.');
        $liquido_pagable = number_format($total_ganado - $total_descuentos, 2, ',', '.');
        $total_ganado = number_format($total_ganado, 2, ',', '.');
        $iva = number_format($iva, 2, ',', '.');
        $faltas_multas = number_format($employeeForm->violations_and_fines, 2, ',', '.');
        $retencion_judicial = number_format($employeeForm->judicial_withholdings, 2, ',', '.');
        $otros_descuentos = number_format($employeeForm->other_discounts, 2, ',', '.');
        $total_descuentos = number_format($total_descuentos, 2, ',', '.');

        $ci = $employeeForm->Employee->Person->identity_card;
        $nco = ($employeeForm->Employee->Person->identification_document_plugin == '') ? '' : '-' . $employeeForm->Employee->Person->identification_document_plugin;
        $ci .= $nco;
        $detail = <<<INFO
            <table border="0">
                <thead>
                    <tr>
                        <th width="30%" align="right">
                            <b>INGRESOS</b>
                        </th>
                        <th width="20%" align="right">
                            <b>Importe Bs.</b>
                        </th>
                        <th width="30%" align="right">
                            <b>DESCUENTOS</b>
                        </th>
                        <th width="20%" align="right">
                            <b>Importe Bs.</b>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td width="30%" align="right">
                            Haber B&aacute;sico mes
                        </td>
                        <td width="20%" align="right">
                            {$haber_basico}
                        </td>
                        <td width="30%" align="right">
                            Aporte AFP (10%)
                        </td>
                        <td width="20%" align="right">
                            {$aporte_afp}
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" align="right">
                            D&iacute;as trabajados
                        </td>
                        <td width="20%" align="right">
                            {$employeeForm->worked_days}
                        </td>
                        <td width="30%" align="right">
                            Riesgo Com&uacute;n (1.71%)
                        </td>
                        <td width="20%" align="right">
                            {$riesgo_comun}
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" align="right">
                            Subtotal
                        </td>
                        <td width="20%" align="right">
                            {$subtotal}
                        </td>
                        <td width="30%" align="right">
                            Comisi&oacute;n AFP (0.5%)
                        </td>
                        <td width="20%" align="right">
                            {$comision_afp}
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" align="right">
                            % Categor&iacute;a
                        </td>
                        <td width="20%" align="right">
                            {$percentage_category}
                        </td>
                        <td width="30%" align="right">
                            Aporte Nacional Solidario (1%, 3%, 5%)
                        </td>
                        <td width="20%" align="right">
                            {$ap_nal_sol}
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" align="right">
                            Bono Frontera
                        </td>
                        <td width="20%" align="right">
                            {$bono_frontera}
                        </td>
                        <td width="30%" align="right">
                            Aporte Solidario Asegurado (0.5%)
                        </td>
                        <td width="20%" align="right">
                            {$ap_nal_aseg}
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" align="right">
                            Categor&iacute;a Antiguedad
                        </td>
                        <td width="20%" align="right">
                            {$monto_categoria}
                        </td>
                        <td width="30%" align="right">
                            IVA (13%)
                        </td>
                        <td width="20%" align="right">
                            {$iva}
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" align="right">
                            <b>TOTAL GANADO</b>
                        </td>
                        <td width="20%" align="right">
                            {$total_ganado}
                        </td>
                        <td width="30%" align="right">
                            Faltas y Multas
                        </td>
                        <td width="20%" align="right">
                            {$faltas_multas}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right">
                            Retenciones Judiciales
                        </td>
                        <td width="20%" align="right">
                            {$retencion_judicial}
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" align="right">
                            Saldo IVA
                        </td>
                        <td width="20%" align="right">
                            00.00
                        </td>
                        <td width="30%" align="right">
                            Otros Descuentos
                        </td>
                        <td width="20%" align="right">
                            {$otros_descuentos}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right">
                            <b>TOTAL DESCUENTOS</b>
                        </td>
                        <td width="20%" align="right">
                            <b>{$total_descuentos}</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right">
                            <b>L&Iacute;QUIDO PAGABLE</b>
                        </td>
                        <td width="20%" align="right">
                            <b>{$liquido_pagable}</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" align="center">
                            <br/><br/><br/><br/><br/>
                            <br/>{$employeeForm->Employee->Person->first_name} {$employeeForm->Employee->Person->middle_name} {$employeeForm->Employee->Person->first_surname} {$employeeForm->Employee->Person->second_surname}
                            <br/>{$ci}
                            <br/><b>Recib&iacute; conforme</b>
                        </td>
                    </tr>
                </tbody>
            </table>
        INFO;

        $pdf->writeHTML($detail, true, false, true, false, '');

        $pdf->SetFont('dejavusans', '', 7);

        $pdf->lastPage();
        $pdf->Output(date('Ymd') . ' - BOLETA (' . $employeeForm->Employee->Person->first_name . ' ' . $employeeForm->Employee->Person->middle_name . ' ' . $employeeForm->Employee->Person->first_surname . ' ' . $employeeForm->Employee->Person->second_surname . ').pdf', 'I');
    }
}
