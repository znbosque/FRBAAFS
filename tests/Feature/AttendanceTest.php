<?php

namespace Tests\Feature;

use App\Jobs\SendAttendanceSms;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use App\Services\FaceRecognition\FaceRecognitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['email_verified_at' => now()]));
    }

    protected function fakeDescriptor(float $value = 0.25): array
    {
        return array_fill(0, 128, $value);
    }

    public function test_a_recognized_student_is_marked_present_and_sms_is_queued(): void
    {
        Queue::fake();

        config()->set('services.attendance.school_starts', '23:59');

        $student = Student::factory()->create([
            'face_data' => [$this->fakeDescriptor(0.25)],
        ]);

        $response = $this->postJson(route('attendance.recognize'), [
            'descriptor' => $this->fakeDescriptor(0.25),
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'checked_in')
            ->assertJsonPath('student.student_id', $student->student_id)
            ->assertJsonPath('attendance.status', 'present');

        $this->assertSame(
            now()->toDateString(),
            Attendance::where('student_id', $student->id)->firstOrFail()->attendance_date->toDateString()
        );

        Queue::assertPushed(SendAttendanceSms::class);
    }

    public function test_an_unrecognized_face_is_rejected(): void
    {
        $response = $this->postJson(route('attendance.recognize'), [
            'descriptor' => $this->fakeDescriptor(0.99),
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('status', 'not_recognized');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_a_student_cannot_be_recorded_twice_on_the_same_day(): void
    {
        Student::factory()->create(['face_data' => [$this->fakeDescriptor(0.4)]]);

        $this->postJson(route('attendance.recognize'), ['descriptor' => $this->fakeDescriptor(0.4)]);
        $response = $this->postJson(route('attendance.recognize'), ['descriptor' => $this->fakeDescriptor(0.4)]);

        $response->assertOk()->assertJsonPath('status', 'already');
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_the_attendance_sms_job_texts_the_parent(): void
    {
        config()->set('queue.default', 'sync');
        config()->set('services.sms.driver', 'log');

        $student = Student::factory()->create([
            'parent_phone' => '+639171234567',
            'face_data' => [$this->fakeDescriptor(0.5)],
        ]);

        $this->postJson(route('attendance.recognize'), ['descriptor' => $this->fakeDescriptor(0.5)]);

        $attendance = Attendance::where('student_id', $student->id)->first();

        $this->assertTrue($attendance->sms_sent);
        $this->assertDatabaseHas('sms_logs', [
            'student_id' => $student->id,
            'phone' => '+639171234567',
            'status' => 'sent',
        ]);
    }

    public function test_the_face_matching_service_rejects_far_apart_descriptors(): void
    {
        $service = app(FaceRecognitionService::class);

        $this->assertNull($service->match($this->fakeDescriptor(0.9)));
    }

    public function test_today_sheet_is_rendered(): void
    {
        Student::factory()->count(3)->create();

        $this->get(route('attendance.index'))->assertOk();
    }
}
