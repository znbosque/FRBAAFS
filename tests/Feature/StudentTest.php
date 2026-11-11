<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['email_verified_at' => now()]));
    }

    public function test_students_index_lists_active_students(): void
    {
        $active = Student::factory()->create(['archived_at' => null]);
        $archived = Student::factory()->archived()->create();

        $response = $this->get(route('students.index'));

        $response->assertOk()
            ->assertSeeText($active->full_name())
            ->assertDontSeeText($archived->full_name());
    }

    public function test_a_student_can_be_enrolled_with_face_data(): void
    {
        $descriptor = array_map(fn () => round(mt_rand(-100, 100) / 100, 4), range(1, 128));

        $this->post(route('students.store'), [
            'first_name' => 'Juan',
            'middle_name' => 'Reyes',
            'surname' => 'Dela Cruz',
            'student_id' => '2026-00001',
            'parent_phone' => '+639171234567',
            'face_data' => json_encode([$descriptor, $descriptor]),
        ])->assertRedirect(route('students.show', Student::whereStudentId('2026-00001')->first()));

        $student = Student::whereStudentId('2026-00001')->first();

        $this->assertSame(2, count($student->descriptors()));
        $this->assertSame(128, count($student->descriptors()[0]));
    }

    public function test_enrollment_requires_at_least_one_face_sample(): void
    {
        $this->from(route('students.create'))
            ->post(route('students.store'), [
                'first_name' => 'Juan',
                'middle_name' => null,
                'surname' => 'Dela Cruz',
                'student_id' => '2026-00002',
                'parent_phone' => '+639171234567',
                'face_data' => null,
            ])
            ->assertSessionHasErrors('face_data');

        $this->assertDatabaseMissing('students', ['student_id' => '2026-00002']);
    }

    public function test_student_ids_must_be_unique(): void
    {
        Student::factory()->create(['student_id' => '2026-00003']);

        $this->from(route('students.create'))
            ->post(route('students.store'), [
                'first_name' => 'Duplicate',
                'middle_name' => null,
                'surname' => 'Student',
                'student_id' => '2026-00003',
                'parent_phone' => '+639171234567',
                'face_data' => json_encode([array_fill(0, 128, 0.1)]),
            ])
            ->assertSessionHasErrors('student_id');
    }

    public function test_a_student_can_be_updated_without_recapturing_faces(): void
    {
        $student = Student::factory()->create([
            'face_data' => [array_fill(0, 128, 0.2)],
        ]);

        $this->put(route('students.update', $student), [
            'first_name' => 'Renamed',
            'middle_name' => $student->middle_name,
            'surname' => $student->surname,
            'student_id' => $student->student_id,
            'parent_phone' => '+639999999999',
        ])->assertRedirect(route('students.show', $student));

        $this->assertSame('+639999999999', $student->fresh()->parent_phone);
        $this->assertSame(1, count($student->fresh()->descriptors()));
    }

    public function test_a_student_can_be_archived_and_restored(): void
    {
        $student = Student::factory()->create();

        $this->post(route('students.archive', $student))
            ->assertRedirect(route('archive.index'));

        $this->assertNotNull($student->fresh()->archived_at);

        $this->post(route('archive.restore', $student))
            ->assertRedirect(route('archive.index'));

        $this->assertNull($student->fresh()->archived_at);
    }
}
