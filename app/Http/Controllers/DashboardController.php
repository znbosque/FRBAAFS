<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Home: overview of today's attendance and quick links to every module.
     */
    public function index(Request $request): View
    {
        $today = CarbonImmutable::today();

        $activeStudents = Student::active()->count();
        $presentToday = Attendance::whereDate('attendance_date', $today)->count();
        $lateToday = Attendance::whereDate('attendance_date', $today)->where('status', 'late')->count();
        $archivedStudents = Student::archived()->count();
        $absentToday = max(0, $activeStudents - $presentToday);

        $recentAttendance = Attendance::with('student')
            ->latest('time_in')
            ->latest('id')
            ->take(10)
            ->get();

        return view('dashboard', [
            'activeStudents' => $activeStudents,
            'presentToday' => $presentToday,
            'lateToday' => $lateToday,
            'absentToday' => $absentToday,
            'archivedStudents' => $archivedStudents,
            'recentAttendance' => $recentAttendance,
            'verified' => $request->boolean('verified'),
        ]);
    }
}
