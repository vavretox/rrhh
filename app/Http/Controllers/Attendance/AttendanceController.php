<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\WorkSchedule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(): View
    {
        return view('attendance.index', [
            'recentMarks' => Attendance::with(['employee.Person', 'device'])->latest('recorded_at')->paginate(20),
            'pendingLeaves' => LeaveRequest::where('status', 'pending')->count(),
            'activeSchedules' => WorkSchedule::where('is_active', true)->count(),
            'upcomingHolidays' => Holiday::where('is_active', true)->whereDate('holiday_date', '>=', today())->orderBy('holiday_date')->limit(5)->get(),
        ]);
    }
}
