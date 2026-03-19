<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\StipendResult;
use Throwable;

class StipendResultController extends Controller
{
    private ?StipendResult $stipendResultModel = null;

    public function show(): void
    {
        $recordId = (int) ($_GET['id'] ?? 0);

        if ($recordId <= 0) {
            $this->redirect('/search?error=' . urlencode('Stipend result not found.'));
        }

        try {
            $result = $this->stipendResultModel()->findById($recordId);

            if ($result === null) {
                $this->redirect('/search?error=' . urlencode('Stipend result not found.'));
            }

            $grades = $this->stipendResultModel()->getGradesByRecordId($recordId);

            $this->view('stipend_results/show', [
                'title' => 'Stipend Result Detail',
                'result' => $result,
                'grades' => $grades,
            ]);
        } catch (Throwable $throwable) {
            $this->redirect('/search?error=' . urlencode('Unable to load stipend result.'));
        }
    }

    private function stipendResultModel(): StipendResult
    {
        if ($this->stipendResultModel === null) {
            $this->stipendResultModel = new StipendResult();
        }

        return $this->stipendResultModel;
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
