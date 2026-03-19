<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Subject;
use Throwable;

class SubjectController extends Controller
{
    private ?Subject $subjectModel = null;

    public function index(): void
    {
        try {
            $subjects = $this->subjectModel()->getAll();

            $this->view('subjects/index', [
                'title' => t('subjects.title'),
                'subjects' => $subjects,
                'success' => $this->getQueryParam('success'),
                'error' => $this->getQueryParam('error'),
            ]);
        } catch (Throwable $throwable) {
            $this->view('subjects/index', [
                'title' => t('subjects.title'),
                'subjects' => [],
                'error' => t('subject_messages.load_error'),
            ]);
        }
    }

    public function create(): void
    {
        $this->view('subjects/create', [
            'title' => t('subjects.create_title'),
            'error' => $this->getQueryParam('error'),
            'success' => $this->getQueryParam('success'),
            'oldSubjectName' => $this->getQueryParam('subject_name'),
            'oldCategoryType' => $this->getQueryParam('category_type'),
        ]);
    }

    public function store(): void
    {
        $subjectName = trim((string) ($_POST['subject_name'] ?? ''));
        $categoryType = trim((string) ($_POST['category_type'] ?? ''));

        if ($subjectName === '') {
            $this->redirect(
                '/subjects/create?error=' . urlencode(t('subject_messages.name_required'))
                . '&category_type=' . urlencode($categoryType)
            );
        }

        if ($categoryType === '') {
            $this->redirect(
                '/subjects/create?error=' . urlencode(t('subject_messages.category_required'))
                . '&subject_name=' . urlencode($subjectName)
            );
        }

        if (! in_array($categoryType, ['P', 'V'], true)) {
            $this->redirect(
                '/subjects/create?error=' . urlencode(t('subject_messages.category_invalid'))
                . '&subject_name=' . urlencode($subjectName)
            );
        }

        try {
            if ($this->subjectModel()->existsByName($subjectName)) {
                $this->redirect(
                    '/subjects/create?error=' . urlencode(t('subject_messages.name_exists'))
                    . '&subject_name=' . urlencode($subjectName)
                    . '&category_type=' . urlencode($categoryType)
                );
            }

            $this->subjectModel()->create($subjectName, $categoryType);

            $this->redirect('/subjects?success=' . urlencode(t('subject_messages.created')));
        } catch (Throwable $throwable) {
            $this->redirect(
                '/subjects/create?error=' . urlencode(t('subject_messages.save_error'))
                . '&subject_name=' . urlencode($subjectName)
                . '&category_type=' . urlencode($categoryType)
            );
        }
    }

    public function edit(): void
    {
        $subjectId = (int) ($_GET['id'] ?? 0);

        if ($subjectId <= 0) {
            $this->redirect('/subjects?error=' . urlencode(t('subject_messages.not_found')));
        }

        try {
            $subject = $this->subjectModel()->findById($subjectId);

            if ($subject === null) {
                $this->redirect('/subjects?error=' . urlencode(t('subject_messages.not_found')));
            }

            $this->view('subjects/edit', [
                'title' => t('subjects.edit_title'),
                'subject' => $subject,
                'error' => $this->getQueryParam('error'),
                'success' => $this->getQueryParam('success'),
                'oldSubjectName' => $this->getQueryParam('subject_name') ?? $subject['subject_name'],
                'oldCategoryType' => $this->getQueryParam('category_type') ?? $subject['category_type'],
            ]);
        } catch (Throwable $throwable) {
            $this->redirect('/subjects?error=' . urlencode(t('subject_messages.load_one_error')));
        }
    }

    public function update(): void
    {
        $subjectId = (int) ($_POST['id'] ?? 0);
        $subjectName = trim((string) ($_POST['subject_name'] ?? ''));
        $categoryType = trim((string) ($_POST['category_type'] ?? ''));

        if ($subjectId <= 0) {
            $this->redirect('/subjects?error=' . urlencode(t('subject_messages.not_found')));
        }

        if ($subjectName === '') {
            $this->redirect(
                '/subjects/edit?id=' . $subjectId
                . '&error=' . urlencode(t('subject_messages.name_required'))
                . '&category_type=' . urlencode($categoryType)
            );
        }

        if ($categoryType === '') {
            $this->redirect(
                '/subjects/edit?id=' . $subjectId
                . '&error=' . urlencode(t('subject_messages.category_required'))
                . '&subject_name=' . urlencode($subjectName)
            );
        }

        if (! in_array($categoryType, ['P', 'V'], true)) {
            $this->redirect(
                '/subjects/edit?id=' . $subjectId
                . '&error=' . urlencode(t('subject_messages.category_invalid'))
                . '&subject_name=' . urlencode($subjectName)
            );
        }

        try {
            $subject = $this->subjectModel()->findById($subjectId);

            if ($subject === null) {
                $this->redirect('/subjects?error=' . urlencode(t('subject_messages.not_found')));
            }

            if ($this->subjectModel()->existsByNameExcludingId($subjectName, $subjectId)) {
                $this->redirect(
                    '/subjects/edit?id=' . $subjectId
                    . '&error=' . urlencode(t('subject_messages.name_exists'))
                    . '&subject_name=' . urlencode($subjectName)
                    . '&category_type=' . urlencode($categoryType)
                );
            }

            $this->subjectModel()->update($subjectId, $subjectName, $categoryType);

            $this->redirect('/subjects?success=' . urlencode(t('subject_messages.updated')));
        } catch (Throwable $throwable) {
            $this->redirect(
                '/subjects/edit?id=' . $subjectId
                . '&error=' . urlencode(t('subject_messages.update_error'))
                . '&subject_name=' . urlencode($subjectName)
                . '&category_type=' . urlencode($categoryType)
            );
        }
    }

    public function delete(): void
    {
        $subjectId = (int) ($_POST['id'] ?? 0);

        if ($subjectId <= 0) {
            $this->redirect('/subjects?error=' . urlencode(t('subject_messages.not_found')));
        }

        try {
            $subject = $this->subjectModel()->findById($subjectId);

            if ($subject === null) {
                $this->redirect('/subjects?error=' . urlencode(t('subject_messages.not_found')));
            }

            $this->subjectModel()->delete($subjectId);

            $this->redirect('/subjects?success=' . urlencode(t('subject_messages.deleted')));
        } catch (Throwable $throwable) {
            $this->redirect('/subjects?error=' . urlencode(t('subject_messages.delete_error')));
        }
    }

    private function getQueryParam(string $key): ?string
    {
        $value = $_GET[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function subjectModel(): Subject
    {
        if ($this->subjectModel === null) {
            $this->subjectModel = new Subject();
        }

        return $this->subjectModel;
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
