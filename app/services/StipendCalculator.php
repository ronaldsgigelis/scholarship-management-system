<?php

declare(strict_types=1);

namespace App\Services;

class StipendCalculator
{
    public function calculateBaseStipend(?float $averageGrade, int $failedSubjectsCount, int $absences): float
    {
        if ($averageGrade === null) {
            return 0.00;
        }

        if ($absences >= 9) {
            return 0.00;
        }

        if ($failedSubjectsCount >= 2) {
            return 0.00;
        }

        if ($absences >= 2 && $absences <= 8) {
            return 15.00;
        }

        if ($averageGrade < 4.0) {
            return 0.00;
        }

        if ($averageGrade < 6.0) {
            return 16.00;
        }

        if ($averageGrade < 8.0) {
            return 41.00;
        }

        return 81.00;
    }

    public function calculateTotalStipend(float $baseStipend, float $activityBonus): float
    {
        return round($baseStipend + $activityBonus, 2);
    }
}
