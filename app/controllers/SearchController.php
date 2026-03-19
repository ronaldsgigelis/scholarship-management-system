<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Search;
use Throwable;

class SearchController extends Controller
{
    private ?Search $searchModel = null;

    public function index(): void
    {
        $year = $this->normalizeYear($_GET['year'] ?? null);
        $period = $this->normalizeTextFilter($_GET['period'] ?? null);
        $periodGroup = $this->normalizeTextFilter($_GET['period_group'] ?? null);
        $groupId = $this->normalizeFilterId($_GET['group_id'] ?? null);
        $studentId = $this->normalizeFilterId($_GET['student_id'] ?? null);
        $studentQuery = $this->normalizeStudentQuery($_GET['student_query'] ?? null);

        try {
            $years = $this->searchModel()->getYears();
            $periods = $this->searchModel()->getPeriods();
            $periodGroups = $this->searchModel()->getPeriodGroups();
            $groups = $this->searchModel()->getGroups();
            $students = $this->searchModel()->getStudents();
            $results = $this->searchModel()->searchResults($year, $period, $periodGroup, $groupId, $studentId, $studentQuery);

            $this->view('search/index', [
                'title' => 'Search History',
                'years' => $years,
                'periods' => $periods,
                'periodGroups' => $periodGroups,
                'groups' => $groups,
                'students' => $students,
                'results' => $results,
                'selectedYear' => $year !== null ? (string) $year : '',
                'selectedPeriod' => $period ?? '',
                'selectedPeriodGroup' => $periodGroup ?? '',
                'selectedGroupId' => $groupId !== null ? (string) $groupId : '',
                'selectedStudentId' => $studentId !== null ? (string) $studentId : '',
                'studentQuery' => $studentQuery ?? '',
                'error' => null,
                'hasActiveFilters' => $year !== null || $period !== null || $periodGroup !== null || $groupId !== null || $studentId !== null || $studentQuery !== null,
            ]);
        } catch (Throwable $throwable) {
            $this->view('search/index', [
                'title' => 'Search History',
                'years' => [],
                'periods' => [],
                'periodGroups' => [],
                'groups' => [],
                'students' => [],
                'results' => [],
                'selectedYear' => '',
                'selectedPeriod' => '',
                'selectedPeriodGroup' => '',
                'selectedGroupId' => '',
                'selectedStudentId' => '',
                'studentQuery' => '',
                'error' => 'Unable to load search data.',
                'hasActiveFilters' => false,
            ]);
        }
    }

    private function searchModel(): Search
    {
        if ($this->searchModel === null) {
            $this->searchModel = new Search();
        }

        return $this->searchModel;
    }

    private function normalizeFilterId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_scalar($value) || ! ctype_digit((string) $value)) {
            return null;
        }

        $id = (int) $value;

        return $id > 0 ? $id : null;
    }

    private function normalizeStudentQuery(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    private function normalizeYear(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_scalar($value) || ! ctype_digit((string) $value)) {
            return null;
        }

        return (int) $value;
    }

    private function normalizeTextFilter(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
