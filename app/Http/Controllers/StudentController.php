<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Services\FaceRecognition\FaceRecognitionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /**
     * List active students with search.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $students = Student::active()
            ->when($search, function ($query, $search) {
                $term = '%'.str_replace('%', '\%', $search).'%';

                $query->where(function ($q) use ($term) {
                    $q->where('first_name', 'like', $term)
                        ->orWhere('middle_name', 'like', $term)
                        ->orWhere('surname', 'like', $term)
                        ->orWhere('student_id', 'like', $term)
                        ->orWhere('parent_phone', 'like', $term);
                });
            })
            ->withCount('attendances')
            ->orderBy('surname')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        return view('students.index', [
            'students' => $students,
            'search' => (string) $search,
        ]);
    }

    /**
     * Show the form for enrolling a student (with webcam face capture).
     */
    public function create(): View
    {
        return view('students.create');
    }

    /**
     * Store a newly enrolled student.
     */
    public function store(Request $request, FaceRecognitionService $faceRecognition): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'surname' => ['required', 'string', 'max:100'],
            'student_id' => ['required', 'string', 'max:50', 'unique:students,student_id'],
            'parent_phone' => ['required', 'string', 'max:30', 'regex:/^[+0-9()\s-]+$/'],
            'photo_data' => ['nullable', 'string', 'starts_with:data:image/'],
            'face_data' => ['nullable', 'string'],
        ]);

        $samples = $this->decodeSamples($request, $faceRecognition);

        if (count($samples) === 0) {
            return back()
                ->withInput()
                ->withErrors(['face_data' => 'Please capture at least one face sample from the webcam.']);
        }

        $student = Student::create([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'surname' => $validated['surname'],
            'student_id' => $validated['student_id'],
            'parent_phone' => $validated['parent_phone'],
            'face_data' => $samples,
            'photo_path' => $this->storePhoto($validated['photo_data'] ?? null),
        ]);

        return redirect()
            ->route('students.show', $student)
            ->with('success', "Student {$student->full_name()} enrolled successfully.");
    }

    /**
     * Display a student with their recent attendance history.
     */
    public function show(Student $student): View
    {
        $attendance = $student->attendances()
            ->orderByDesc('attendance_date')
            ->paginate(15);

        $presentCount = $student->attendances()->count();
        $lateCount = $student->attendances()->where('status', 'late')->count();

        return view('students.show', [
            'student' => $student,
            'attendance' => $attendance,
            'presentCount' => $presentCount,
            'lateCount' => $lateCount,
        ]);
    }

    /**
     * Show the form for editing a student.
     */
    public function edit(Student $student): View
    {
        return view('students.edit', ['student' => $student]);
    }

    /**
     * Update a student. Face samples are only replaced when new ones are captured.
     */
    public function update(Request $request, Student $student, FaceRecognitionService $faceRecognition): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'surname' => ['required', 'string', 'max:100'],
            'student_id' => ['required', 'string', 'max:50', 'unique:students,student_id,'.$student->id],
            'parent_phone' => ['required', 'string', 'max:30', 'regex:/^[+0-9()\s-]+$/'],
            'photo_data' => ['nullable', 'string', 'starts_with:data:image/'],
            'face_data' => ['nullable', 'string'],
        ]);

        $samples = $this->decodeSamples($request, $faceRecognition);

        $student->update([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'surname' => $validated['surname'],
            'student_id' => $validated['student_id'],
            'parent_phone' => $validated['parent_phone'],
            'face_data' => count($samples) > 0 ? $samples : $student->face_data,
            'photo_path' => $this->storePhoto($validated['photo_data'] ?? null, $student->photo_path),
        ]);

        return redirect()
            ->route('students.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Permanently delete a student and their photo.
     */
    public function destroy(Student $student): RedirectResponse
    {
        $name = $student->full_name();

        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('success', "Student {$name} permanently deleted.");
    }

    /**
     * Decode the JSON face samples payload into sanitized descriptor arrays.
     *
     * @return array<int, array<int, float>>
     */
    protected function decodeSamples(Request $request, FaceRecognitionService $faceRecognition): array
    {
        $payload = $request->input('face_data');

        if (! is_string($payload) || $payload === '') {
            return [];
        }

        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            return [];
        }

        return $faceRecognition->sanitizeSamples($decoded);
    }

    /**
     * Persist a base64 data-URL snapshot from the webcam and return its path.
     */
    protected function storePhoto(?string $dataUrl, ?string $existing = null): ?string
    {
        if (! $dataUrl || ! preg_match('#^data:image/(jpeg|png|jpg|webp);base64,(.+)$#', $dataUrl, $matches)) {
            return $existing;
        }

        $data = base64_decode($matches[2], true);

        if ($data === false) {
            return $existing;
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $path = 'students/'.now()->format('YmdHis').'-'.str()->random(8).'.'.$extension;

        Storage::disk('public')->put($path, $data);

        if ($existing) {
            Storage::disk('public')->delete($existing);
        }

        return $path;
    }
}
