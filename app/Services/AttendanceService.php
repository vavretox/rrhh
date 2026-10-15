<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceRule;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\ScheduleException;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class AttendanceService
{
    public function summarizeDay(Employee $employee, CarbonInterface $date): array
    {
        $date = Carbon::parse($date)->startOfDay();
        $schedule = EmployeeSchedule::with('schedule.days')
            ->where('employee_id', $employee->id)
            ->whereDate('starts_on', '<=', $date)
            ->where(fn ($query) => $query->whereNull('ends_on')->orWhereDate('ends_on', '>=', $date))
            ->latest('starts_on')->first()?->schedule;

        $day = $schedule?->days->firstWhere('day_of_week', $date->dayOfWeekIso);
        $rule = AttendanceRule::where('work_schedule_id', $schedule?->id)->where('is_active', true)->first()
            ?? AttendanceRule::whereNull('work_schedule_id')->where('is_active', true)->first();
        $exception = ScheduleException::where('employee_id', $employee->id)->whereDate('exception_date', $date)->first();
        $holiday = Holiday::where('is_active', true)->where(function ($query) use ($date) {
            $query->whereDate('holiday_date', $date)->orWhere(fn ($recurring) => $recurring->where('is_recurring', true)->whereMonth('holiday_date', $date->month)->whereDay('holiday_date', $date->day));
        })->exists();
        $leave = LeaveRequest::where('employee_id', $employee->id)->where('status', 'approved')->whereDate('starts_on', '<=', $date)->whereDate('ends_on', '>=', $date)->exists();
        $marks = Attendance::where('employee_id', $employee->id)->whereDate('work_date', $date)->where('status', 'valid')->orderBy('recorded_at')->get();

        $workingDay = $exception?->is_working_day ?? ($day?->is_working_day ?? false);
        $entry = $marks->firstWhere('type', 'IN')?->recorded_at;
        $exit = $marks->where('type', 'OUT')->last()?->recorded_at;
        $worked = $entry && $exit ? max(0, $entry->diffInMinutes($exit)) : 0;
        $expectedEntry = $exception?->entry_time ?? $day?->entry_time;
        $expectedMinutes = $day?->expected_minutes ?? 0;
        $tolerance = $rule?->late_tolerance_minutes ?? 0;
        $late = $entry && $expectedEntry ? max(0, Carbon::parse($date->toDateString().' '.$expectedEntry)->addMinutes($tolerance)->diffInMinutes($entry, false)) : 0;
        $overtimeThreshold = $rule?->overtime_after_minutes ?? $expectedMinutes;

        return [
            'date' => $date->toDateString(), 'working_day' => $workingDay, 'holiday' => $holiday,
            'on_leave' => $leave, 'entry' => $entry, 'exit' => $exit, 'worked_minutes' => $worked,
            'late_minutes' => $late, 'overtime_minutes' => max(0, $worked - $overtimeThreshold),
            'absent' => $workingDay && ! $holiday && ! $leave && ! $entry,
        ];
    }
}
