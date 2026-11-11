<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_name',
        'surname',
        'student_id',
        'parent_phone',
        'photo_path',
        'face_data',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'face_data' => 'array',
            'archived_at' => 'datetime',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function full_name(): string
    {
        return trim(collect([$this->first_name, $this->middle_name, $this->surname])
            ->filter()
            ->implode(' '));
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function hasFaceData(): bool
    {
        $descriptors = $this->face_data ?? [];

        return is_array($descriptors) && count($descriptors) > 0;
    }

    public function descriptors(): array
    {
        $descriptors = $this->face_data ?? [];

        return array_values(array_filter(
            $descriptors,
            fn ($descriptor) => is_array($descriptor) && count($descriptor) === 128
        ));
    }
}
