<?php

namespace App\Models;

use Database\Factories\AttendanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    /** @use HasFactory<AttendanceFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'attendance_date',
        'time_in',
        'time_out',
        'status',
        'confidence',
        'sms_sent',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'time_in' => 'datetime:H:i:s',
            'time_out' => 'datetime:H:i:s',
            'sms_sent' => 'boolean',
            'confidence' => 'float',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function isLate(): bool
    {
        return $this->status === 'late';
    }
}
