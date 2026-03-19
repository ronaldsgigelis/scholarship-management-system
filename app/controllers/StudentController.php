<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Student;
use Throwable;

class StudentController extends Controller
{
    private ?Student $studentModel = null;

    public function index(): void
    {
        try {
            $students = $this->studentModel()->getAll();

            $this->view('students/index', [
                'title' => t('students.title'),
                'students' => $students,
                'success' => $this->getQueryParam('success'),
                'error' => $this->getQueryParam('error'),
            ]);
        } catch (Throwable $throwable) {
            $this->view('students/index', [
                'title' => t('students.title'),
                'students' => [],
                'error' => t('student_messages.load_error'),
            ]);
        }
    }

    public function create(): void
    {
        try {
            $groups = $this->studentModel()->getGroups();

            $this->view('students/create', [
                'title' => t('students.create_title'),
                'groups' => $groups,
                'error' => $this->getQueryParam('error'),
                'success' => $this->getQueryParam('success'),
                'oldFirstName' => $this->getQueryParam('first_name'),
                'oldLastName' => $this->getQueryParam('last_name'),
                'oldPersonalCode' => $this->getQueryParam('personal_code'),
                'oldGroupId' => $this->getQueryParam('group_id'),
            ]);
        } catch (Throwable $throwable) {
            $this->view('students/create', [
                'title' => t('students.create_title'),
                'groups' => [],
                'error' => t('student_messages.groups_load_error'),
                'oldFirstName' => $this->getQueryParam('first_name'),
                'oldLastName' => $this->getQueryParam('last_name'),
                'oldPersonalCode' => $this->getQueryParam('personal_code'),
                'oldGroupId' => $this->getQueryParam('group_id'),
            ]);
        }
    }

    public function store(): void
    {
        $firstName = trim((string) ($_POST['first_name'] ?? ''));
        $lastName = trim((string) ($_POST['last_name'] ?? ''));
        $personalCode = trim((string) ($_POST['personal_code'] ?? ''));
        $groupId = (int) ($_POST['group_id'] ?? 0);

        if ($firstName === '') {
            $this->redirect(
                '/students/create?error=' . urlencode(t('student_messages.first_name_required'))
                . '&last_name=' . urlencode($lastName)
                . '&personal_code=' . urlencode($personalCode)
                . '&group_id=' . urlencode((string) $groupId)
            );
        }

        if ($lastName === '') {
            $this->redirect(
                '/students/create?error=' . urlencode(t('student_messages.last_name_required'))
                . '&first_name=' . urlencode($firstName)
                . '&personal_code=' . urlencode($personalCode)
                . '&group_id=' . urlencode((string) $groupId)
            );
        }

        if ($personalCode === '') {
            $this->redirect(
                '/students/create?error=' . urlencode(t('student_messages.personal_code_required'))
                . '&first_name=' . urlencode($firstName)
                . '&last_name=' . urlencode($lastName)
                . '&group_id=' . urlencode((string) $groupId)
            );
        }

        if ($groupId <= 0) {
            $this->redirect(
                '/students/create?error=' . urlencode(t('student_messages.group_required'))
                . '&first_name=' . urlencode($firstName)
                . '&last_name=' . urlencode($lastName)
                . '&personal_code=' . urlencode($personalCode)
            );
        }

        try {
            if (! $this->studentModel()->groupExists($groupId)) {
                $this->redirect(
                    '/students/create?error=' . urlencode(t('student_messages.group_missing'))
                    . '&first_name=' . urlencode($firstName)
                    . '&last_name=' . urlencode($lastName)
                    . '&personal_code=' . urlencode($personalCode)
                    . '&group_id=' . urlencode((string) $groupId)
                );
            }

            if ($this->studentModel()->personalCodeExists($personalCode)) {
                $this->redirect(
                    '/students/create?error=' . urlencode(t('student_messages.personal_code_exists'))
                    . '&first_name=' . urlencode($firstName)
                    . '&last_name=' . urlencode($lastName)
                    . '&personal_code=' . urlencode($personalCode)
                    . '&group_id=' . urlencode((string) $groupId)
                );
            }

            $this->studentModel()->create($groupId, $firstName, $lastName, $personalCode);

            $this->redirect('/students?success=' . urlencode(t('student_messages.created')));
        } catch (Throwable $throwable) {
            $this->redirect(
                '/students/create?error=' . urlencode(t('student_messages.save_error'))
                . '&first_name=' . urlencode($firstName)
                . '&last_name=' . urlencode($lastName)
                . '&personal_code=' . urlencode($personalCode)
                . '&group_id=' . urlencode((string) $groupId)
            );
        }
    }

    public function edit(): void
    {
        $studentId = (int) ($_GET['id'] ?? 0);

        if ($studentId <= 0) {
            $this->redirect('/students?error=' . urlencode(t('student_messages.not_found')));
        }

        try {
            $student = $this->studentModel()->findById($studentId);

            if ($student === null) {
                $this->redirect('/students?error=' . urlencode(t('student_messages.not_found')));
            }

            $groups = $this->studentModel()->getGroups();

            $this->view('students/edit', [
                'title' => t('students.edit_title'),
                'student' => $student,
                'groups' => $groups,
                'error' => $this->getQueryParam('error'),
                'success' => $this->getQueryParam('success'),
                'oldFirstName' => $this->getQueryParam('first_name') ?? $student['first_name'],
                'oldLastName' => $this->getQueryParam('last_name') ?? $student['last_name'],
                'oldPersonalCode' => $this->getQueryParam('personal_code') ?? $student['personal_code'],
                'oldGroupId' => $this->getQueryParam('group_id') ?? (string) $student['group_id'],
            ]);
        } catch (Throwable $throwable) {
            $this->redirect('/students?error=' . urlencode(t('student_messages.load_one_error')));
        }
    }

    public function update(): void
    {
        $studentId = (int) ($_POST['id'] ?? 0);
        $firstName = trim((string) ($_POST['first_name'] ?? ''));
        $lastName = trim((string) ($_POST['last_name'] ?? ''));
        $personalCode = trim((string) ($_POST['personal_code'] ?? ''));
        $groupId = (int) ($_POST['group_id'] ?? 0);

        if ($studentId <= 0) {
            $this->redirect('/students?error=' . urlencode(t('student_messages.not_found')));
        }

        if ($firstName === '') {
            $this->redirect(
                '/students/edit?id=' . $studentId
                . '&error=' . urlencode(t('student_messages.first_name_required'))
                . '&last_name=' . urlencode($lastName)
                . '&personal_code=' . urlencode($personalCode)
                . '&group_id=' . urlencode((string) $groupId)
            );
        }

        if ($lastName === '') {
            $this->redirect(
                '/students/edit?id=' . $studentId
                . '&error=' . urlencode(t('student_messages.last_name_required'))
                . '&first_name=' . urlencode($firstName)
                . '&personal_code=' . urlencode($personalCode)
                . '&group_id=' . urlencode((string) $groupId)
            );
        }

        if ($personalCode === '') {
            $this->redirect(
                '/students/edit?id=' . $studentId
                . '&error=' . urlencode(t('student_messages.personal_code_required'))
                . '&first_name=' . urlencode($firstName)
                . '&last_name=' . urlencode($lastName)
                . '&group_id=' . urlencode((string) $groupId)
            );
        }

        if ($groupId <= 0) {
            $this->redirect(
                '/students/edit?id=' . $studentId
                . '&error=' . urlencode(t('student_messages.group_required'))
                . '&first_name=' . urlencode($firstName)
                . '&last_name=' . urlencode($lastName)
                . '&personal_code=' . urlencode($personalCode)
            );
        }

        try {
            $student = $this->studentModel()->findById($studentId);

            if ($student === null) {
                $this->redirect('/students?error=' . urlencode(t('student_messages.not_found')));
            }

            if (! $this->studentModel()->groupExists($groupId)) {
                $this->redirect(
                    '/students/edit?id=' . $studentId
                    . '&error=' . urlencode(t('student_messages.group_missing'))
                    . '&first_name=' . urlencode($firstName)
                    . '&last_name=' . urlencode($lastName)
                    . '&personal_code=' . urlencode($personalCode)
                    . '&group_id=' . urlencode((string) $groupId)
                );
            }

            if ($this->studentModel()->personalCodeExists($personalCode, $studentId)) {
                $this->redirect(
                    '/students/edit?id=' . $studentId
                    . '&error=' . urlencode(t('student_messages.personal_code_exists'))
                    . '&first_name=' . urlencode($firstName)
                    . '&last_name=' . urlencode($lastName)
                    . '&personal_code=' . urlencode($personalCode)
                    . '&group_id=' . urlencode((string) $groupId)
                );
            }

            $this->studentModel()->update($studentId, $groupId, $firstName, $lastName, $personalCode);

            $this->redirect('/students?success=' . urlencode(t('student_messages.updated')));
        } catch (Throwable $throwable) {
            $this->redirect(
                '/students/edit?id=' . $studentId
                . '&error=' . urlencode(t('student_messages.update_error'))
                . '&first_name=' . urlencode($firstName)
                . '&last_name=' . urlencode($lastName)
                . '&personal_code=' . urlencode($personalCode)
                . '&group_id=' . urlencode((string) $groupId)
            );
        }
    }

    public function delete(): void
    {
        $studentId = (int) ($_POST['id'] ?? 0);

        if ($studentId <= 0) {
            $this->redirect('/students?error=' . urlencode(t('student_messages.not_found')));
        }

        try {
            $student = $this->studentModel()->findById($studentId);

            if ($student === null) {
                $this->redirect('/students?error=' . urlencode(t('student_messages.not_found')));
            }

            $this->studentModel()->delete($studentId);

            $this->redirect('/students?success=' . urlencode(t('student_messages.deleted')));
        } catch (Throwable $throwable) {
            $this->redirect('/students?error=' . urlencode(t('student_messages.delete_error')));
        }
    }

    private function getQueryParam(string $key): ?string
    {
        $value = $_GET[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function studentModel(): Student
    {
        if ($this->studentModel === null) {
            $this->studentModel = new Student();
        }

        return $this->studentModel;
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
