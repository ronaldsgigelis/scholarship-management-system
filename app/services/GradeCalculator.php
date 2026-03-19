<?php

declare(strict_types=1);

namespace App\Services;

class GradeCalculator
{
    public function getEnteredGrades(array $grades): array
    {
        $numericGrades = [];

        foreach ($grades as $grade) {
            if ($grade === '' || $grade === null) {
                continue;
            }

            $numericGrades[] = (float) $grade;
        }

        return $numericGrades;
    }

    public function hasEnteredGrades(array $grades): bool
    {
        return $this->getEnteredGrades($grades) !== [];
    }

    public function calculateAverageGrade(array $grades): ?float
    {
        $numericGrades = $this->getEnteredGrades($grades);

        if ($numericGrades === []) {
            return null;
        }

        return round(array_sum($numericGrades) / count($numericGrades), 2);
    }

    public function countFailedSubjects(array $grades, array $subjects): int
    {
        $failedSubjects = 0;

        foreach ($subjects as $subject) {
            $subjectId = (int) $subject['id'];

            if (! array_key_exists($subjectId, $grades) || $grades[$subjectId] === '' || $grades[$subjectId] === null) {
                continue;
            }

            $grade = (float) $grades[$subjectId];
            $categoryType = (string) $subject['category_type'];

            if ($categoryType === 'P' && $grade < 5) {
                $failedSubjects++;
            }

            if ($categoryType === 'V' && $grade < 4) {
                $failedSubjects++;
            }
        }

        return $failedSubjects;
    }
}
