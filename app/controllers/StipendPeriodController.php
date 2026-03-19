<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\StipendPeriod;
use Throwable;

class StipendPeriodController extends Controller
{
    private ?StipendPeriod $stipendPeriodModel = null;

    public function index(): void
    {
        try {
            $periods = $this->stipendPeriodModel()->getAll();

            $this->view('stipend_periods/index', [
                'title' => 'Stipend Periods',
                'periods' => $periods,
                'success' => $this->getQueryParam('success'),
                'error' => $this->getQueryParam('error'),
            ]);
        } catch (Throwable $throwable) {
            $this->view('stipend_periods/index', [
                'title' => 'Stipend Periods',
                'periods' => [],
                'error' => 'Unable to load stipend periods.',
            ]);
        }
    }

    public function create(): void
    {
        $this->view('stipend_periods/create', [
            'title' => 'Create Stipend Period',
            'error' => $this->getQueryParam('error'),
            'success' => $this->getQueryParam('success'),
            'oldPeriod' => $this->getQueryParam('period'),
            'oldPeriodGroup' => $this->getQueryParam('period_group'),
            'oldYear' => $this->getQueryParam('year'),
        ]);
    }

    public function store(): void
    {
        $period = trim((string) ($_POST['period'] ?? ''));
        $periodGroup = trim((string) ($_POST['period_group'] ?? ''));
        $yearInput = trim((string) ($_POST['year'] ?? ''));

        if ($period === '') {
            $this->redirect(
                '/stipend-periods/create?error=' . urlencode('Period is required.')
                . '&period_group=' . urlencode($periodGroup)
                . '&year=' . urlencode($yearInput)
            );
        }

        if ($periodGroup === '') {
            $this->redirect(
                '/stipend-periods/create?error=' . urlencode('Period group is required.')
                . '&period=' . urlencode($period)
                . '&year=' . urlencode($yearInput)
            );
        }

        if ($yearInput === '') {
            $this->redirect(
                '/stipend-periods/create?error=' . urlencode('Year is required.')
                . '&period=' . urlencode($period)
                . '&period_group=' . urlencode($periodGroup)
            );
        }

        if (! ctype_digit($yearInput)) {
            $this->redirect(
                '/stipend-periods/create?error=' . urlencode('Year must be numeric.')
                . '&period=' . urlencode($period)
                . '&period_group=' . urlencode($periodGroup)
                . '&year=' . urlencode($yearInput)
            );
        }

        $year = (int) $yearInput;

        try {
            if ($this->stipendPeriodModel()->existsByPeriodData($year, $period, $periodGroup)) {
                $this->redirect(
                    '/stipend-periods/create?error=' . urlencode('Year, period, and period group combination already exists.')
                    . '&period=' . urlencode($period)
                    . '&period_group=' . urlencode($periodGroup)
                    . '&year=' . urlencode((string) $year)
                );
            }

            $this->stipendPeriodModel()->create($year, $period, $periodGroup);

            $this->redirect('/stipend-periods?success=' . urlencode('Stipend period created successfully.'));
        } catch (Throwable $throwable) {
            $this->redirect(
                '/stipend-periods/create?error=' . urlencode('Unable to save stipend period.')
                . '&period=' . urlencode($period)
                . '&period_group=' . urlencode($periodGroup)
                . '&year=' . urlencode((string) $year)
            );
        }
    }

    public function edit(): void
    {
        $periodId = (int) ($_GET['id'] ?? 0);

        if ($periodId <= 0) {
            $this->redirect('/stipend-periods?error=' . urlencode('Stipend period not found.'));
        }

        try {
            $period = $this->stipendPeriodModel()->findById($periodId);

            if ($period === null) {
                $this->redirect('/stipend-periods?error=' . urlencode('Stipend period not found.'));
            }

            $this->view('stipend_periods/edit', [
                'title' => 'Edit Stipend Period',
                'period' => $period,
                'error' => $this->getQueryParam('error'),
                'success' => $this->getQueryParam('success'),
                'oldPeriod' => $this->getQueryParam('period') ?? $period['period'],
                'oldPeriodGroup' => $this->getQueryParam('period_group') ?? $period['period_group'],
                'oldYear' => $this->getQueryParam('year') ?? (string) $period['year'],
            ]);
        } catch (Throwable $throwable) {
            $this->redirect('/stipend-periods?error=' . urlencode('Unable to load stipend period.'));
        }
    }

    public function update(): void
    {
        $periodId = (int) ($_POST['id'] ?? 0);
        $periodValue = trim((string) ($_POST['period'] ?? ''));
        $periodGroup = trim((string) ($_POST['period_group'] ?? ''));
        $yearInput = trim((string) ($_POST['year'] ?? ''));

        if ($periodId <= 0) {
            $this->redirect('/stipend-periods?error=' . urlencode('Stipend period not found.'));
        }

        if ($periodValue === '') {
            $this->redirect(
                '/stipend-periods/edit?id=' . $periodId
                . '&error=' . urlencode('Period is required.')
                . '&period_group=' . urlencode($periodGroup)
                . '&year=' . urlencode($yearInput)
            );
        }

        if ($periodGroup === '') {
            $this->redirect(
                '/stipend-periods/edit?id=' . $periodId
                . '&error=' . urlencode('Period group is required.')
                . '&period=' . urlencode($periodValue)
                . '&year=' . urlencode($yearInput)
            );
        }

        if ($yearInput === '') {
            $this->redirect(
                '/stipend-periods/edit?id=' . $periodId
                . '&error=' . urlencode('Year is required.')
                . '&period=' . urlencode($periodValue)
                . '&period_group=' . urlencode($periodGroup)
            );
        }

        if (! ctype_digit($yearInput)) {
            $this->redirect(
                '/stipend-periods/edit?id=' . $periodId
                . '&error=' . urlencode('Year must be numeric.')
                . '&period=' . urlencode($periodValue)
                . '&period_group=' . urlencode($periodGroup)
                . '&year=' . urlencode($yearInput)
            );
        }

        $year = (int) $yearInput;

        try {
            $existingPeriod = $this->stipendPeriodModel()->findById($periodId);

            if ($existingPeriod === null) {
                $this->redirect('/stipend-periods?error=' . urlencode('Stipend period not found.'));
            }

            if ($this->stipendPeriodModel()->existsByPeriodDataExcludingId($year, $periodValue, $periodGroup, $periodId)) {
                $this->redirect(
                    '/stipend-periods/edit?id=' . $periodId
                    . '&error=' . urlencode('Year, period, and period group combination already exists.')
                    . '&period=' . urlencode($periodValue)
                    . '&period_group=' . urlencode($periodGroup)
                    . '&year=' . urlencode((string) $year)
                );
            }

            $this->stipendPeriodModel()->update($periodId, $year, $periodValue, $periodGroup);

            $this->redirect('/stipend-periods?success=' . urlencode('Stipend period updated successfully.'));
        } catch (Throwable $throwable) {
            $this->redirect(
                '/stipend-periods/edit?id=' . $periodId
                . '&error=' . urlencode('Unable to update stipend period.')
                . '&period=' . urlencode($periodValue)
                . '&period_group=' . urlencode($periodGroup)
                . '&year=' . urlencode((string) $year)
            );
        }
    }

    public function delete(): void
    {
        $periodId = (int) ($_POST['id'] ?? 0);

        if ($periodId <= 0) {
            $this->redirect('/stipend-periods?error=' . urlencode('Stipend period not found.'));
        }

        try {
            $period = $this->stipendPeriodModel()->findById($periodId);

            if ($period === null) {
                $this->redirect('/stipend-periods?error=' . urlencode('Stipend period not found.'));
            }

            $this->stipendPeriodModel()->delete($periodId);

            $this->redirect('/stipend-periods?success=' . urlencode('Stipend period deleted successfully.'));
        } catch (Throwable $throwable) {
            $this->redirect('/stipend-periods?error=' . urlencode('Unable to delete stipend period.'));
        }
    }

    private function getQueryParam(string $key): ?string
    {
        $value = $_GET[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function stipendPeriodModel(): StipendPeriod
    {
        if ($this->stipendPeriodModel === null) {
            $this->stipendPeriodModel = new StipendPeriod();
        }

        return $this->stipendPeriodModel;
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
