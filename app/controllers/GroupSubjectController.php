<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\GroupSubject;
use Throwable;

class GroupSubjectController extends Controller
{
    private ?GroupSubject $groupSubjectModel = null;

    public function index(): void
    {
        try {
            $assignments = $this->groupSubjectModel()->getAll();

            $this->view('group_subjects/index', [
                'title' => 'Group Subjects',
                'assignments' => $assignments,
                'success' => $this->getQueryParam('success'),
                'error' => $this->getQueryParam('error'),
            ]);
        } catch (Throwable $throwable) {
            $this->view('group_subjects/index', [
                'title' => 'Group Subjects',
                'assignments' => [],
                'error' => 'Unable to load group-subject assignments.',
            ]);
        }
    }

    public function create(): void
    {
        try {
            $groups = $this->groupSubjectModel()->getGroups();
            $subjects = $this->groupSubjectModel()->getSubjects();

            $this->view('group_subjects/create', [
                'title' => 'Create Assignment',
                'groups' => $groups,
                'subjects' => $subjects,
                'error' => $this->getQueryParam('error'),
                'success' => $this->getQueryParam('success'),
                'oldGroupId' => $this->getQueryParam('group_id'),
                'oldSubjectId' => $this->getQueryParam('subject_id'),
            ]);
        } catch (Throwable $throwable) {
            $this->view('group_subjects/create', [
                'title' => 'Create Assignment',
                'groups' => [],
                'subjects' => [],
                'error' => 'Unable to load groups or subjects.',
                'oldGroupId' => $this->getQueryParam('group_id'),
                'oldSubjectId' => $this->getQueryParam('subject_id'),
            ]);
        }
    }

    public function store(): void
    {
        $groupId = (int) ($_POST['group_id'] ?? 0);
        $subjectId = (int) ($_POST['subject_id'] ?? 0);

        if ($groupId <= 0) {
            $this->redirect(
                '/group-subjects/create?error=' . urlencode('Group is required.')
                . '&subject_id=' . urlencode((string) $subjectId)
            );
        }

        if ($subjectId <= 0) {
            $this->redirect(
                '/group-subjects/create?error=' . urlencode('Subject is required.')
                . '&group_id=' . urlencode((string) $groupId)
            );
        }

        try {
            if (! $this->groupSubjectModel()->groupExists($groupId)) {
                $this->redirect(
                    '/group-subjects/create?error=' . urlencode('Selected group does not exist.')
                    . '&group_id=' . urlencode((string) $groupId)
                    . '&subject_id=' . urlencode((string) $subjectId)
                );
            }

            if (! $this->groupSubjectModel()->subjectExists($subjectId)) {
                $this->redirect(
                    '/group-subjects/create?error=' . urlencode('Selected subject does not exist.')
                    . '&group_id=' . urlencode((string) $groupId)
                    . '&subject_id=' . urlencode((string) $subjectId)
                );
            }

            if ($this->groupSubjectModel()->assignmentExists($groupId, $subjectId)) {
                $this->redirect(
                    '/group-subjects/create?error=' . urlencode('This subject is already assigned to the selected group.')
                    . '&group_id=' . urlencode((string) $groupId)
                    . '&subject_id=' . urlencode((string) $subjectId)
                );
            }

            $this->groupSubjectModel()->create($groupId, $subjectId);

            $this->redirect('/group-subjects?success=' . urlencode('Assignment created successfully.'));
        } catch (Throwable $throwable) {
            $this->redirect(
                '/group-subjects/create?error=' . urlencode('Unable to save assignment.')
                . '&group_id=' . urlencode((string) $groupId)
                . '&subject_id=' . urlencode((string) $subjectId)
            );
        }
    }

    public function delete(): void
    {
        $assignmentId = (int) ($_POST['id'] ?? 0);

        if ($assignmentId <= 0) {
            $this->redirect('/group-subjects?error=' . urlencode('Assignment not found.'));
        }

        try {
            $assignment = $this->groupSubjectModel()->findById($assignmentId);

            if ($assignment === null) {
                $this->redirect('/group-subjects?error=' . urlencode('Assignment not found.'));
            }

            $this->groupSubjectModel()->delete($assignmentId);

            $this->redirect('/group-subjects?success=' . urlencode('Assignment deleted successfully.'));
        } catch (Throwable $throwable) {
            $this->redirect('/group-subjects?error=' . urlencode('Unable to delete assignment.'));
        }
    }

    private function getQueryParam(string $key): ?string
    {
        $value = $_GET[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function groupSubjectModel(): GroupSubject
    {
        if ($this->groupSubjectModel === null) {
            $this->groupSubjectModel = new GroupSubject();
        }

        return $this->groupSubjectModel;
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
