<?php

namespace App\Http\Controllers;

use App\Jobs\SendAttendanceSms;
use App\Models\Attendance;
use App\Models\Student;
use App\Services\FaceRecognition\FaceRecognitionService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Today's attendance sheet with a link to the face scanner.
     */
    public function index(): View
    {
        $today = CarbonImmutable::today();

        $attendance = Attendance::with('student')
            ->whereDate('attendance_date', $today)
            ->orderBy('time_in')
            ->paginate(20);

        $activeStudents = Student::active()->count();
        $presentCount = $attendance->total();

        return view('attendance.index', [
            'attendance' => $attendance,
            'activeStudents' => $activeStudents,
            'presentCount' => $presentCount,
            'lateCount' => Attendance::whereDate('attendance_date', $today)->where('status', 'late')->count(),
            'absentCount' => max(0, $activeStudents - $presentCount),
        ]);
    }

    /**
     * The face recognition scanner screen (webcam).
     */
    public function scan(): View
    {
        return view('attendance.scan');
    }

    /**
     * Handle a face descriptor from the scanner, record attendance for the
     * matched student, and queue the SMS notification to their parents.
     */
    public function recognize(Request $request, FaceRecognitionService $faceRecognition): JsonResponse
    {
        $validated = $request->validate([
            'descriptor' => ['required', 'array', 'size:128'],
            'descriptor.*' => ['required', 'numeric'],
        ]);

        $match = $faceRecognition->match(array_map('floatval', $validated['descriptor']));

        if ($match === null) {
            return response()->json([
                'status' => 'not_recognized',
                'message' => 'Face not recognized. Please enroll the student first or try again.',
            ], 422);
        }

        $student = $match['student'];
        $now = CarbonImmutable::now();
        $today = $now->toDateString();

        $attendance = Attendance::query()
            ->where('student_id', $student->id)
            ->whereDate('attendance_date', $today)
            ->first();

        // Already clocked in and not yet clocked out -> record time out.
        if ($attendance && $attendance->time_in !== null && $attendance->time_out === null) {
            $clockedInAt = $now->setTimeFromTimeString($attendance->time_in->format('H:i:s'));

            if ($now->greaterThan($clockedInAt->addMinutes(1))) {
                $attendance->update(['time_out' => $now->format('H:i:s')]);

                return response()->json([
                    'status' => 'checked_out',
                    'student' => $this->studentPayload($student),
                    'attendance' => $this->attendancePayload($attendance->refresh()),
                    'message' => "Goodbye {$student->first_name}! Time out recorded.",
                ]);
            }

            return response()->json([
                'status' => 'already',
                'student' => $this->studentPayload($student),
                'attendance' => $this->attendancePayload($attendance),
                'message' => "{$student->full_name()} was already recorded today.",
            ]);
        }

        if ($attendance) {
            return response()->json([
                'status' => 'already',
                'student' => $this->studentPayload($student),
                'attendance' => $this->attendancePayload($attendance),
                'message' => "{$student->full_name()} was already recorded today.",
            ]);
        }

        $schoolStart = CarbonImmutable::today()->setTimeFromTimeString(
            (string) config('services.attendance.school_starts', '08:00')
        );

        $attendance = new Attendance([
            'student_id' => $student->id,
            'attendance_date' => $today,
            'time_in' => $now->format('H:i:s'),
            'status' => $now->greaterThan($schoolStart) ? 'late' : 'present',
            'confidence' => $match['confidence'],
            'sms_sent' => false,
        ]);
        $attendance->save();

        SendAttendanceSms::dispatch($attendance);

        return response()->json([
            'status' => 'checked_in',
            'student' => $this->studentPayload($student),
            'attendance' => $this->attendancePayload($attendance),
            'message' => "Welcome {$student->first_name}! You have been marked {$attendance->status}.",
        ]);
    }

    /**
     * Manually record attendance for a student (fallback without the camera).
     */
    public function storeManual(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'attendance_date' => ['required', 'date'],
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $now = CarbonImmutable::now();
        $date = CarbonImmutable::parse($validated['attendance_date']);

        $exists = Attendance::where('student_id', $student->id)
            ->whereDate('attendance_date', $date)
            ->exists();

        if ($exists) {
            return back()->withErrors(['student_id' => 'This student already has attendance on that date.']);
        }

        $schoolStart = $date->setTimeFromTimeString(
            (string) config('services.attendance.school_starts', '08:00')
        );

        $attendance = Attendance::create([
            'student_id' => $student->id,
            'attendance_date' => $date->toDateString(),
            'time_in' => $now->format('H:i:s'),
            'status' => $now->greaterThan($schoolStart) && $date->isToday() ? 'late' : 'present',
            'confidence' => null,
            'sms_sent' => false,
        ]);

        SendAttendanceSms::dispatch($attendance);

        return back()->with('success', "Attendance recorded for {$student->full_name()}.");
    }

    protected function studentPayload(Student $student): array
    {
        return [
            'id' => $student->id,
            'student_id' => $student->student_id,
            'full_name' => $student->full_name(),
            'photo' => $student->photo_path ? Storage::disk('public')->url($student->photo_path) : null,
        ];
    }

    protected function attendancePayload(Attendance $attendance): array
    {
        return [
            'date' => $attendance->attendance_date->toDateString(),
            'time_in' => $attendance->time_in?->format('g:i A'),
            'time_out' => $attendance->time_out?->format('g:i A'),
            'status' => $attendance->status,
            'sms_sent' => $attendance->sms_sent,
        ];
    }
}
