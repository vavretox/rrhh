<table>
    <thead>
        <tr>
            <th colspan="24" style="font-weight: bold; text-align: right;">Fecha de impresión de
                Planilla:</th>
            <th colspan="2">{{ Carbon\Carbon::now()->format('d/m/Y') }}</th>
        </tr>
        <tr>
            <th colspan="7" style="font-weight: bold; text-align: center;">GOBERNACION
                DEL DEPARTAMENTO AUTONOMO DE TARIJA</th>
            <th colspan="19">&nbsp;</th>
        </tr>
        <tr>
            <th colspan="7" style="font-weight: bold; text-align: center;">DIRECCION DE
                RECURSOS HUMANOS</th>
            <th colspan="19">&nbsp;</th>
        </tr>
        <tr>
            <th colspan="7" style="font-weight: bold; text-align: center;">TARIJA-BOLIVIA</th>
            <th colspan="19">&nbsp;</th>
        </tr>
        <tr>
            <th colspan="26" style="font-weight: bold; text-align: center;">PLANILLA DE SUELDO
                PARTIDA 11700 - ADMINISTRACION CENTRAL @if ($form->Type->id == 2)
                    -- CON CHEQUE --
                @endif
            </th>
        </tr>
        <tr>
            <th colspan="26" style="font-weight: bold; text-align: center;">{{ $sheet->description }}</th>
        </tr>
        <tr>
            <th colspan="26" style="font-weight: bold; text-align: center;">CORRESPONDIENTE AL
                MES DE {{ strtoupper(Carbon\Carbon::parse($form->period)->translatedFormat('F/Y')) }}</th>
        </tr>
        <tr>
            <th colspan="26" style="font-weight: bold; text-align: center;">(EN BOLIVIANOS)
            </th>
        </tr>
        <tr>
            <th colspan="26">DA: 001 ; UE: 002 ; Cat. Prog.: 00 0000 001</th>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000;">Nro</th>
            <th style="font-weight: bold; border: 1px solid #000;">Codigo</th>
            <th style="font-weight: bold; border: 1px solid #000;">Cedula Identidad</th>
            <th style="font-weight: bold; border: 1px solid #000;">Apellido Paterno</th>
            <th style="font-weight: bold; border: 1px solid #000;">Apellido Materno</th>
            <th style="font-weight: bold; border: 1px solid #000;">Nombres</th>
            <th style="font-weight: bold; border: 1px solid #000;">Nivel</th>
            <th style="font-weight: bold; border: 1px solid #000;">Categoria</th>
            <th style="font-weight: bold; border: 1px solid #000;">Haber Basico</th>
            <th style="font-weight: bold; border: 1px solid #000;">Dias Trabajados</th>
            <th style="font-weight: bold; border: 1px solid #000;">Subtotal</th>
            <th style="font-weight: bold; border: 1px solid #000;">Bono de Frontera</th>
            <th style="font-weight: bold; border: 1px solid #000;">Monto Categoria</th>
            <th style="font-weight: bold; border: 1px solid #000;">Total Ganado</th>
            <th style="font-weight: bold; border: 1px solid #000;">Aporte AFP (10%)</th>
            <th style="font-weight: bold; border: 1px solid #000;">Riesgo Comun (1,71%)</th>
            <th style="font-weight: bold; border: 1px solid #000;">Comision AFP (0.5%)</th>
            <th style="font-weight: bold; border: 1px solid #000;">Ap. Nal. Sol.</th>
            <th style="font-weight: bold; border: 1px solid #000;">Ap. Sol. Aseg. (0.5%)</th>
            <th style="font-weight: bold; border: 1px solid #000;">IVA</th>
            <th style="font-weight: bold; border: 1px solid #000;">Faltas y Multas</th>
            <th style="font-weight: bold; border: 1px solid #000;">Ret. Jud.</th>
            <th style="font-weight: bold; border: 1px solid #000;">Otros Descuentos</th>
            <th style="font-weight: bold; border: 1px solid #000;">Total Descuentos</th>
            <th style="font-weight: bold; border: 1px solid #000;">Liquido Pagable</th>
            <th style="font-weight: bold; border: 1px solid #000;">Bloqueado</th>
        </tr>
    </thead>
    <tbody>
        @php $k = $l = $m = $n = $o = $p = $q = $r = $s = $t = $u = $v = $w = $x = $y = 0; @endphp
        @foreach (App\Models\OrganizationalArea::where('state', 'activo')->get() as $organizationalArea)
            {{-- @if ($organizationalArea->Employees()->wherePivot('state', 'activo')->where('employees.state', 'activo')->count() > 0) --}}
            <tr>
                <td colspan="26" style="font-weight: bold; background-color: #DDEBF7; border: 1px solid #000;">
                    - {{ $organizationalArea->name }}
                </td>
            </tr>
            @foreach ($form->Employees()->wherePivot('sheet_id', $sheet->id)->get() as $key => $employee)
                @php
                    $employeeArea = $employee->OrganizationalAreas()->wherePivot('state', 'activo')->first();
                @endphp
                @if ($employeeArea?->id == $organizationalArea->id)
                    @php
                        $employeeCharge = $employee->Charges()->wherePivot('state', 'activo')->first();
                        $employeeCharge ??= (object) ['code' => 0, 'basic_salary' => 0];
                        $subtotal = ($employeeCharge->basic_salary / 30) * $employee->pivot->worked_days;
                        $categoryMount =
                            ($form->Setting->national_minimum_wage * $employee->Category->percentage) / 100;
                        $totalEarned = $subtotal + $employee->pivot->border_bonus + $categoryMount;
                        $afp = 0;
                        $commonRisk = ($subtotal + $employee->pivot->border_bonus + $categoryMount) * 0.0171;
                        $comisionAndApSolAseg = ($subtotal + $employee->pivot->border_bonus + $categoryMount) * 0.005;
                        $totalDiscounts =
                            $afp +
                            $commonRisk +
                            $comisionAndApSolAseg +
                            0 +
                            $comisionAndApSolAseg +
                            $employee->pivot->iva +
                            $employee->pivot->violations_and_fines +
                            $employee->pivot->judicial_withholdings +
                            $employee->pivot->other_discounts;
                    @endphp
                    <tr>
                        <td style="border: 1px solid #000;">{{ $key + 1 }}</td>
                        <td style="border: 1px solid #000;">{{ $employee->item_number }}</td>
                        <td style="border: 1px solid #000;">{{ $employee->Person->identity_card }}</td>
                        <td style="border: 1px solid #000;">{{ $employee->Person->first_surname }}</td>
                        <td style="border: 1px solid #000;">{{ $employee->Person->second_surname }}</td>
                        <td style="border: 1px solid #000;">{{ $employee->Person->first_name }}
                            {{ $employee->Person->middle_name }}</td>
                        <td style="border: 1px solid #000;">{{ $employeeCharge->code }}</td>
                        <td style="border: 1px solid #000;">{{ $employee->Category->percentage }}</td>
                        <td style="border: 1px solid #000;">{{ $employeeCharge->basic_salary }}</td>
                        <td style="border: 1px solid #000;">{{ $employee->pivot->worked_days }}</td>
                        <td style="border: 1px solid #000;">{{ $subtotal }}@php($k += $subtotal)</td>
                        <td style="border: 1px solid #000;">
                            {{ $employee->pivot->border_bonus }}@php($l += $employee->pivot->border_bonus)
                        </td>
                        <td style="border: 1px solid #000;">{{ $categoryMount }}@php($m += $categoryMount)</td>
                        <td style="border: 1px solid #000;">{{ $totalEarned }}@php($n += $totalEarned)</td>
                        <td style="border: 1px solid #000;">{{ $afp }}@php($o += $afp)</td>
                        <td style="border: 1px solid #000;">{{ $commonRisk }}@php($p += $commonRisk)</td>
                        <td style="border: 1px solid #000;">{{ $comisionAndApSolAseg }}@php($q += $comisionAndApSolAseg)
                        </td>
                        <td style="border: 1px solid #000;">{{ 0 }}@php($r += 0)</td>
                        <td style="border: 1px solid #000;">{{ $comisionAndApSolAseg }}@php($s += $comisionAndApSolAseg)
                        </td>
                        <td style="border: 1px solid #000;">{{ $employee->pivot->iva }}@php($t += $employee->pivot->iva)
                        </td>
                        <td style="border: 1px solid #000;">
                            {{ $employee->pivot->violations_and_fines }}@php($u += $employee->pivot->violations_and_fines)</td>
                        <td style="border: 1px solid #000;">
                            {{ $employee->pivot->judicial_withholdings }}@php($v += $employee->pivot->judicial_withholdings)</td>
                        <td style="border: 1px solid #000;">
                            {{ $employee->pivot->other_discounts }}@php($w += $employee->pivot->other_discounts)</td>
                        <td style="border: 1px solid #000;">{{ $totalDiscounts }}@php($x += $totalDiscounts)</td>
                        <td style="border: 1px solid #000;">
                            {{ $totalEarned - $totalDiscounts }}@php($y += $totalEarned - $totalDiscounts)
                        </td>
                        <td style="border: 1px solid #000;">[C]</td>
                    </tr>
                @endif
            @endforeach
            {{-- @endif --}}
        @endforeach
        <tr>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">&nbsp;</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;" colspan="9">TOTAL</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ $k }}</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ $l }}</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ $m }}</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ $n }}</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ $o }}</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ $p }}</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ $q }}</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ $r }}</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ $s }}</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ $t }}</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ $u }}</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ $v }}</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ $w }}</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ $x }}</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ $y }}</td>
            <td style="font-weight: bold; border: 1px solid #000; text-align: center;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="26">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="26">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="26">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="26">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
            <td colspan="4" style="text-align: center;">Lic. Marco Antonio Valencia Vasco</td>
            <td colspan="2">&nbsp;</td>
            <td colspan="4" style="text-align: center;">M.Sc. Lic. Valentina Zenteno Rivera</td>
            <td colspan="2">&nbsp;</td>
            <td colspan="4" style="text-align: center;">Lic. Juan Pablo Bellido Morales</td>
            <td colspan="2">&nbsp;</td>
            <td colspan="4" style="text-align: center;">Lic. Jose Manuel Gamarra Hoyos</td>
            <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
            <td colspan="4" style="font-weight: bold; text-align: center;">ENCARGADO DE PLANILLAS DE PERSONAL</td>
            <td colspan="2">&nbsp;</td>
            <td colspan="4" style="font-weight: bold; text-align: center;">DIRECTORA DPTAL. DE RECURSOS HUMANOS
            </td>
            <td colspan="2">&nbsp;</td>
            <td colspan="4" style="font-weight: bold; text-align: center;">JEFE DE CONTABILIDAD</td>
            <td colspan="2">&nbsp;</td>
            <td colspan="4" style="font-weight: bold; text-align: center;">DIRECTOR DPTAL. DE FINANZAS</td>
            <td colspan="2">&nbsp;</td>
        </tr>
    </tbody>
</table>
