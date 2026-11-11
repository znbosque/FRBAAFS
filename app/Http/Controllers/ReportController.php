<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Daily report: every student's attendance for a chosen date.
     */
    public function daily(Request $request): View
    {
        $date = $this->parseDate($request->query('date'), CarbonImmutable::today());

        $attendance = Attendance::with('student')
            ->whereDate('attendance_date', $date)
            ->orderBy('time_in')
            ->get()
            ->map(fn (Attendance $record) => [
                'student' => $record->student,
                'time_in' => $record->time_in?->format('g:i A'),
                'time_out' => $record->time_out?->format('g:i A'),
                'status' => $record->status,
                'sms_sent' => $record->sms_sent,
            ]);

        $presentIds = Attendance::whereDate('attendance_date', $date)->pluck('student_id');

        $absentStudents = Student::active()
            ->whereNotIn('id', $presentIds)
            ->orderBy('surname')
            ->get();

        return view('reports.daily', [
            'date' => $date,
            'attendance' => $attendance,
            'absentStudents' => $absentStudents,
            'activeStudents' => Student::active()->count(),
            'presentCount' => $attendance->count(),
            'lateCount' => $attendance->where('status', 'late')->count(),
        ]);
    }

    /**
     * Monthly report: per-student totals plus a per-day breakdown.
     */
    public function monthly(Request $request): View
    {
        $month = $this->parseMonth($request->query('month'));

        $start = $month->startOfMonth();
        $end = $month->endOfMonth();

        $schoolDays = Attendance::whereBetween('attendance_date', [$start, $end])
            ->distinct('attendance_date')
            ->pluck('attendance_date');

        $students = Student::active()
            ->withSum(['attendances as present_days' => fn ($q) => $q->whereBetween('attendance_date', [$start, $end])], 'id')
            ->withSum(['attendances as late_days' => fn ($q) => $q->whereBetween('attendance_date', [$start, $end])->where('status', 'late')], 'id')
            ->orderBy('surname')
            ->get();

        $perStudent = $students->map(fn (Student $student) => [
            'student' => $student,
            'present_days' => (int) $student->present_days,
            'late_days' => (int) $student->late_days,
            'absent_days' => max(0, $schoolDays->count() - (int) $student->present_days),
        ]);

        $dailyTotals = Attendance::whereBetween('attendance_date', [$start, $end])
            ->selectRaw('attendance_date, COUNT(*) as present, SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late')
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get();

        return view('reports.monthly', [
            'month' => $month,
            'schoolDayCount' => $schoolDays->count(),
            'perStudent' => $perStudent,
            'dailyTotals' => $dailyTotals,
            'totalPresent' => $dailyTotals->sum('present'),
            'totalLate' => $dailyTotals->sum('late'),
        ]);
    }

    /**
     * Yearly report: per-month totals plus per-student totals for the year.
     */
    public function yearly(Request $request): View
    {
        $year = $this->parseYear($request->query('year'));
        $start = $year->startOfYear();
        $end = $year->endOfYear();

        $isSqlite = Attendance::query()->getConnection()->getDriverName() === 'sqlite';

        $monthExpression = $isSqlite
            ? 'strftime("%m", attendance_date)'
            : 'DATE_FORMAT(attendance_date, "%m")';

        $monthlyTotals = Attendance::whereBetween('attendance_date', [$start, $end])
            ->selectRaw("{$monthExpression} as month")
            ->selectRaw('COUNT(*) as present')
            ->selectRaw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $students = Student::active()
            ->withSum(['attendances as present_days' => fn ($q) => $q->whereBetween('attendance_date', [$start, $end])], 'id')
            ->withSum(['attendances as late_days' => fn ($q) => $q->whereBetween('attendance_date', [$start, $end])->where('status', 'late')], 'id')
            ->orderBy('surname')
            ->get();

        $perStudent = $students->map(fn (Student $student) => [
            'student' => $student,
            'present_days' => (int) $student->present_days,
            'late_days' => (int) $student->late_days,
        ]);

        return view('reports.yearly', [
            'year' => $year->year,
            'monthlyTotals' => $monthlyTotals,
            'perStudent' => $perStudent,
            'totalPresent' => $monthlyTotals->sum('present'),
            'totalLate' => $monthlyTotals->sum('late'),
        ]);
    }

    protected function parseDate(mixed $value, CarbonImmutable $default): CarbonImmutable
    {
        try {
            return $value ? CarbonImmutable::parse($value) : $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    protected function parseMonth(mixed $value): CarbonImmutable
    {
        try {
            return $value ? CarbonImmutable::parse($value.'-01')->startOfMonth() : CarbonImmutable::today()->startOfMonth();
        } catch (\Throwable) {
            return CarbonImmutable::today()->startOfMonth();
        }
    }

    protected function parseYear(mixed $value): CarbonImmutable
    {
        $year = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 2000, 'max_range' => 2100]]);

        return CarbonImmutable::create($year ?: CarbonImmutable::today()->year, 1, 1);
    }
}
