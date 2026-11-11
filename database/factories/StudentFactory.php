<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->optional(0.5)->firstName(),
            'surname' => fake()->lastName(),
            'student_id' => '2026-'.str_pad((string) fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'parent_phone' => '+63'.fake()->numerify('9##########'),
            'photo_path' => null,
            'face_data' => [$this->randomDescriptor()],
            'archived_at' => null,
        ];
    }

    /**
     * A student without any enrolled face samples.
     */
    public function withoutFaceData(): static
    {
        return $this->state(fn (array $attributes) => [
            'face_data' => null,
        ]);
    }

    /**
     * A student that has been moved to the archive.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'archived_at' => now(),
        ]);
    }

    protected function randomDescriptor(): array
    {
        $descriptor = [];

        for ($i = 0; $i < 128; $i++) {
            $descriptor[] = round(fake()->randomFloat(4, -0.5, 0.5), 4);
        }

        return $descriptor;
    }
}
