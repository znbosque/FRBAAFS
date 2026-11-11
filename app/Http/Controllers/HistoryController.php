<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    /**
     * Full attendance history with student / date-range / status filters.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'student' => ['nullable', 'integer', 'exists:students,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'status' => ['nullable', 'in:present,late'],
        ]);

        $attendance = Attendance::with('student')
            ->when($validated['student'] ?? null, fn ($q, $student) => $q->where('student_id', $student))
            ->when($validated['from'] ?? null, fn ($q, $from) => $q->whereDate('attendance_date', '>=', $from))
            ->when($validated['to'] ?? null, fn ($q, $to) => $q->whereDate('attendance_date', '<=', $to))
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('attendance_date')
            ->orderByDesc('time_in')
            ->paginate(25)
            ->withQueryString();

        return view('history.index', [
            'attendance' => $attendance,
            'students' => Student::active()->orderBy('surname')->get(),
            'filters' => $validated,
        ]);
    }
}
