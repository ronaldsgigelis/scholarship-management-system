<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\StipendEntry;
use Throwable;

class StipendEntryController extends Controller
{
    private ?StipendEntry $stipendEntryModel = null;

    public function index(): void
    {
        $selectedPeriodId = (int) ($_GET['period_id'] ?? 0);
        $selectedGroupId = (int) ($_GET['group_id'] ?? 0);

        try {
            $periods = $this->stipendEntryModel()->getPeriods();
            $groups = $this->stipendEntryModel()->getGroups();

            $selectedPeriod = null;
            $selectedGroup = null;
            $students = [];
            $subjects = [];
            $warning = null;
            $success = $this->getQueryParam('success');
            $error = $this->getQueryParam('error');
            $savedEntries = [];

            if ($selectedPeriodId > 0 || $selectedGroupId > 0) {
                if ($selectedPeriodId <= 0 || $selectedGroupId <= 0) {
                    $warning = 'Please select both stipend period and group.';
                } else {
                    $selectedPeriod = $this->stipendEntryModel()->getPeriodById($selectedPeriodId);
                    $selectedGroup = $this->stipendEntryModel()->getGroupById($selectedGroupId);

                    if ($selectedPeriod === null || $selectedGroup === null) {
                        $warning = 'Selected stipend period or group was not found.';
                    } else {
                        $students = $this->stipendEntryModel()->getStudentsByGroupId($selectedGroupId);
                        $subjects = $this->stipendEntryModel()->getSubjectsByGroupId($selectedGroupId);
                        $savedEntries = $this->stipendEntryModel()->getSavedEntriesByGroupAndPeriod(
                            $selectedGroupId,
                            $selectedPeriodId
                        );
                    }
                }
            }

            $this->view('stipend_entry/index', [
                'title' => 'Stipend Entry',
                'periods' => $periods,
                'groups' => $groups,
                'selectedPeriodId' => $selectedPeriodId > 0 ? (string) $selectedPeriodId : null,
                'selectedGroupId' => $selectedGroupId > 0 ? (string) $selectedGroupId : null,
                'selectedPeriod' => $selectedPeriod,
                'selectedGroup' => $selectedGroup,
                'students' => $students,
                'subjects' => $subjects,
                'warning' => $warning,
                'success' => $success,
                'error' => $error,
                'savedEntries' => $savedEntries,
            ]);
        } catch (Throwable $throwable) {
            $this->view('stipend_entry/index', [
                'title' => 'Stipend Entry',
                'periods' => [],
                'groups' => [],
                'selectedPeriodId' => null,
                'selectedGroupId' => null,
                'selectedPeriod' => null,
                'selectedGroup' => null,
                'students' => [],
                'subjects' => [],
                'warning' => 'Unable to load stipend entry data.',
                'success' => null,
                'error' => null,
                'savedEntries' => [],
            ]);
        }
    }

    public function save(): void
    {
        $periodId = (int) ($_POST['period_id'] ?? 0);
        $groupId = (int) ($_POST['group_id'] ?? 0);
        $entries = $_POST['entries'] ?? [];

        if ($periodId <= 0) {
            $this->redirectWithMessage(0, $groupId, 'error', 'Stipend period is required.');
        }

        if ($groupId <= 0) {
            $this->redirectWithMessage($periodId, 0, 'error', 'Group is required.');
        }

        if (! is_array($entries)) {
            $this->redirectWithMessage($periodId, $groupId, 'error', 'Invalid entry data.');
        }

        try {
            $period = $this->stipendEntryModel()->getPeriodById($periodId);
            $group = $this->stipendEntryModel()->getGroupById($groupId);
            $students = $this->stipendEntryModel()->getStudentsByGroupId($groupId);
            $subjects = $this->stipendEntryModel()->getSubjectsByGroupId($groupId);

            if ($period === null || $group === null) {
                $this->redirectWithMessage($periodId, $groupId, 'error', 'Selected stipend period or group was not found.');
            }

            if ($students === []) {
                $this->redirectWithMessage($periodId, $groupId, 'error', 'This group has no students.');
            }

            if ($subjects === []) {
                $this->redirectWithMessage($periodId, $groupId, 'error', 'This group has no assigned subjects.');
            }

            $validStudentIds = array_map(static fn (array $student): int => (int) $student['id'], $students);
            $validSubjectIds = array_map(static fn (array $subject): int => (int) $subject['id'], $subjects);

            $normalizedEntries = $this->validateEntries($entries, $validStudentIds, $validSubjectIds);

            $this->stipendEntryModel()->saveEntries($periodId, $groupId, $normalizedEntries, $period);

            $this->redirectWithMessage($periodId, $groupId, 'success', 'Stipend entry data saved successfully.');
        } catch (\RuntimeException $exception) {
            $friendlyMessages = [
                'Absence count must be a non-negative whole number.',
                'Activity bonus must be numeric and not negative.',
                'Each grade must be numeric.',
                'Each grade must be between 0 and 10.',
            ];

            $message = in_array($exception->getMessage(), $friendlyMessages, true)
                ? $exception->getMessage()
                : 'Unable to save stipend entry data.';

            $this->redirectWithMessage($periodId, $groupId, 'error', $message);
        } catch (Throwable $throwable) {
            $this->redirectWithMessage($periodId, $groupId, 'error', 'Unable to save stipend entry data.');
        }
    }

    private function stipendEntryModel(): StipendEntry
    {
        if ($this->stipendEntryModel === null) {
            $this->stipendEntryModel = new StipendEntry();
        }

        return $this->stipendEntryModel;
    }

    private function validateEntries(array $entries, array $validStudentIds, array $validSubjectIds): array
    {
        $normalizedEntries = [];

        foreach ($validStudentIds as $studentId) {
            $entry = $entries[$studentId] ?? [];
            $absenceInput = trim((string) ($entry['absence_count'] ?? '0'));
            $activityBonusInput = trim((string) ($entry['activity_bonus'] ?? '0'));
            $grades = is_array($entry['grades'] ?? null) ? $entry['grades'] : [];

            if ($absenceInput === '' || ! is_numeric($absenceInput) || (float) $absenceInput < 0 || floor((float) $absenceInput) != (float) $absenceInput) {
                throw new \RuntimeException('Absence count must be a non-negative whole number.');
            }

            if ($activityBonusInput === '' || ! is_numeric($activityBonusInput) || (float) $activityBonusInput < 0) {
                throw new \RuntimeException('Activity bonus must be numeric and not negative.');
            }

            $normalizedGrades = [];

            foreach ($validSubjectIds as $subjectId) {
                $gradeInput = trim((string) ($grades[$subjectId] ?? ''));

                if ($gradeInput === '') {
                    continue;
                }

                if (! is_numeric($gradeInput)) {
                    throw new \RuntimeException('Each grade must be numeric.');
                }

                $gradeValue = (float) $gradeInput;

                if ($gradeValue < 0 || $gradeValue > 10) {
                    throw new \RuntimeException('Each grade must be between 0 and 10.');
                }

                $normalizedGrades[$subjectId] = $gradeValue;
            }

            $normalizedEntries[$studentId] = [
                'absence_count' => (int) $absenceInput,
                'activity_bonus' => (float) $activityBonusInput,
                'grades' => $normalizedGrades,
            ];
        }

        return $normalizedEntries;
    }

    private function getQueryParam(string $key): ?string
    {
        $value = $_GET[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function redirectWithMessage(int $periodId, int $groupId, string $type, string $message): void
    {
        $query = http_build_query(array_filter([
            'period_id' => $periodId > 0 ? $periodId : null,
            'group_id' => $groupId > 0 ? $groupId : null,
            $type => $message,
        ], static fn ($value): bool => $value !== null));

        $this->redirect('/stipend-entry' . ($query !== '' ? '?' . $query : ''));
    }

    private function redirect(string $path): void
    {
        $baseUrl = $this->baseUrl();

        header('Location: ' . $baseUrl . $path);
        exit;
    }

    private function baseUrl(): string
    {
        $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

        return $scriptDirectory === '/' || $scriptDirectory === '.'
            ? ''
            : rtrim($scriptDirectory, '/');
    }
}
