<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Services\Sms\SmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\App;

class SendAttendanceSms implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(public Attendance $attendance)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $sms): void
    {
        $attendance = $this->attendance->fresh(['student']);

        if (! $attendance || ! $attendance->student || $attendance->sms_sent) {
            return;
        }

        $student = $attendance->student;

        $message = sprintf(
            '%s: Good day! Your child %s (%s) was recognized and marked %s at school on %s at %s.',
            config('app.name'),
            $student->full_name(),
            $student->student_id,
            $attendance->status,
            $attendance->attendance_date->format('M j, Y'),
            $attendance->time_in?->format('g:i A'),
        );

        $log = $sms->send($student->parent_phone, $message, $student, $attendance);

        if ($log->status === 'sent') {
            $attendance->forceFill(['sms_sent' => true])->save();
        } else {
            // Surface the failure when not running through a queue worker.
            if (App::runningUnitTests() || config('queue.default') === 'sync') {
                logger()->warning("Attendance SMS failed for student {$student->student_id}: {$log->response}");
            }
        }
    }
}
