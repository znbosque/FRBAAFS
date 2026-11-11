<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['email_verified_at' => now()]));
    }

    public function test_daily_report_renders_with_attendance(): void
    {
        $student = Student::factory()->create();

        Attendance::create([
            'student_id' => $student->id,
            'attendance_date' => now()->toDateString(),
            'time_in' => '07:45:00',
            'status' => 'present',
        ]);

        $this->get(route('reports.daily', ['date' => now()->toDateString()]))
            ->assertOk()
            ->assertSeeText($student->full_name())
            ->assertSeeText('Students Absent');
    }

    public function test_monthly_report_includes_month_end_records(): void
    {
        $student = Student::factory()->create();
        $month = now()->startOfMonth();
        $lastDay = $month->copy()->endOfMonth();

        foreach ([$month->copy()->day(1), $month->copy()->day(15), $lastDay] as $day) {
            Attendance::create([
                'student_id' => $student->id,
                'attendance_date' => $day->toDateString(),
                'time_in' => '07:30:00',
                'status' => 'present',
            ]);
        }

        $response = $this->get(route('reports.monthly', ['month' => now()->format('Y-m')]));

        $response->assertOk();

        // The per-day breakdown must include the very last day of the month.
        $response->assertSeeText($lastDay->format('M j, Y'));

        // And the student must be counted for all three days.
        $response->assertSeeText($student->full_name());
        $this->assertSame(3, Attendance::count());
    }

    public function test_yearly_report_counts_records_across_months(): void
    {
        $student = Student::factory()->create();

        foreach ([1, 6, 12] as $monthNumber) {
            Attendance::create([
                'student_id' => $student->id,
                'attendance_date' => Carbon::create(now()->year, $monthNumber, 15)->toDateString(),
                'time_in' => '08:30:00',
                'status' => 'late',
            ]);
        }

        $this->get(route('reports.yearly', ['year' => now()->year]))
            ->assertOk()
            ->assertSeeText($student->full_name());
    }
}
