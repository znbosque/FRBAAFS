<?php

namespace App\Services\FaceRecognition;

use App\Models\Student;

class FaceRecognitionService
{
    /**
     * Find the enrolled active student whose stored face descriptors best
     * match the given 128-dimension descriptor (face-api.js format).
     *
     * Returns ['student' => Student, 'distance' => float, 'confidence' => float]
     * or null when no student is within the match threshold.
     *
     * @param  array<int, float>  $descriptor
     * @return array{student: Student, distance: float, confidence: float}|null
     */
    public function match(array $descriptor): ?array
    {
        if (count($descriptor) !== 128) {
            return null;
        }

        $threshold = (float) config('services.face_recognition.threshold', 0.6);

        $best = null;

        Student::query()
            ->active()
            ->whereNotNull('face_data')
            ->chunk(200, function ($students) use ($descriptor, $threshold, &$best) {
                foreach ($students as $student) {
                    foreach ($student->descriptors() as $stored) {
                        $distance = $this->distance($descriptor, $stored);

                        if ($distance < $threshold && ($best === null || $distance < $best['distance'])) {
                            $best = [
                                'student' => $student,
                                'distance' => $distance,
                                'confidence' => $this->confidence($distance),
                            ];
                        }
                    }
                }
            });

        return $best;
    }

    /**
     * Normalize a raw descriptor payload coming from the browser into a
     * clean list of numeric samples, discarding invalid entries.
     *
     * @return array<int, array<int, float>>
     */
    public function sanitizeSamples(array $samples): array
    {
        $clean = [];

        foreach ($samples as $sample) {
            if (! is_array($sample) || count($sample) !== 128) {
                continue;
            }

            $values = [];

            foreach ($sample as $value) {
                if (! is_numeric($value)) {
                    continue 2;
                }

                $values[] = (float) $value;
            }

            if (count($values) === 128) {
                $clean[] = $values;
            }
        }

        return $clean;
    }

    /**
     * Euclidean distance between two 128-dimension descriptors.
     * This is the same metric face-api.js uses (match when distance < 0.6).
     *
     * @param  array<int, float>  $a
     * @param  array<int, float>  $b
     */
    public function distance(array $a, array $b): float
    {
        $sum = 0.0;

        for ($i = 0; $i < 128; $i++) {
            $diff = ($a[$i] ?? 0.0) - ($b[$i] ?? 0.0);
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }

    /**
     * Convert a Euclidean distance into a 0..1 confidence score.
     */
    public function confidence(float $distance): float
    {
        return round(max(0.0, min(1.0, 1.0 - $distance)), 4);
    }
}
