<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArchiveController extends Controller
{
    /**
     * List archived students with their lifetime attendance totals.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $students = Student::archived()
            ->when($search, function ($query, $search) {
                $term = '%'.str_replace('%', '\%', $search).'%';

                $query->where(function ($q) use ($term) {
                    $q->where('first_name', 'like', $term)
                        ->orWhere('middle_name', 'like', $term)
                        ->orWhere('surname', 'like', $term)
                        ->orWhere('student_id', 'like', $term);
                });
            })
            ->withCount('attendances')
            ->orderBy('archived_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('archive.index', [
            'students' => $students,
            'search' => (string) $search,
        ]);
    }

    /**
     * Move a student to the archive. Their history is kept intact.
     */
    public function store(Request $request, Student $student): RedirectResponse
    {
        if ($student->isArchived()) {
            return back()->withErrors(['student' => 'This student is already archived.']);
        }

        $student->update(['archived_at' => now()]);

        return redirect()
            ->route('archive.index')
            ->with('success', "Student {$student->full_name()} moved to the archive.");
    }

    /**
     * Restore an archived student back to the active roster.
     */
    public function restore(Student $student): RedirectResponse
    {
        if (! $student->isArchived()) {
            return back()->withErrors(['student' => 'This student is not archived.']);
        }

        $student->update(['archived_at' => null]);

        return redirect()
            ->route('archive.index')
            ->with('success', "Student {$student->full_name()} restored to the active roster.");
    }
}
