<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Group;
use Throwable;

class GroupController extends Controller
{
    private ?Group $groupModel = null;

    public function index(): void
    {
        try {
            $groups = $this->groupModel()->getAll();

            $this->view('groups/index', [
                'title' => t('groups.title'),
                'groups' => $groups,
                'success' => $this->getQueryParam('success'),
                'error' => $this->getQueryParam('error'),
            ]);
        } catch (Throwable $throwable) {
            $this->view('groups/index', [
                'title' => t('groups.title'),
                'groups' => [],
                'error' => t('group_messages.load_error'),
            ]);
        }
    }

    public function create(): void
    {
        $this->view('groups/create', [
            'title' => t('groups.create_title'),
            'error' => $this->getQueryParam('error'),
            'success' => $this->getQueryParam('success'),
            'oldGroupName' => $this->getQueryParam('group_name'),
        ]);
    }

    public function store(): void
    {
        $groupName = trim((string) ($_POST['group_name'] ?? ''));

        if ($groupName === '') {
            $this->redirect('/groups/create?error=' . urlencode(t('group_messages.name_required')));
        }

        try {
            if ($this->groupModel()->existsByName($groupName)) {
                $this->redirect(
                    '/groups/create?error=' . urlencode(t('group_messages.name_exists'))
                    . '&group_name=' . urlencode($groupName)
                );
            }

            $this->groupModel()->create($groupName);

            $this->redirect('/groups?success=' . urlencode(t('group_messages.created')));
        } catch (Throwable $throwable) {
            $this->redirect(
                '/groups/create?error=' . urlencode(t('group_messages.save_error'))
                . '&group_name=' . urlencode($groupName)
            );
        }
    }

    public function edit(): void
    {
        $groupId = (int) ($_GET['id'] ?? 0);

        if ($groupId <= 0) {
            $this->redirect('/groups?error=' . urlencode(t('group_messages.not_found')));
        }

        try {
            $group = $this->groupModel()->findById($groupId);

            if ($group === null) {
                $this->redirect('/groups?error=' . urlencode(t('group_messages.not_found')));
            }

            $this->view('groups/edit', [
                'title' => t('groups.edit_title'),
                'group' => $group,
                'error' => $this->getQueryParam('error'),
                'success' => $this->getQueryParam('success'),
                'oldGroupName' => $this->getQueryParam('group_name') ?? $group['group_name'],
            ]);
        } catch (Throwable $throwable) {
            $this->redirect('/groups?error=' . urlencode(t('group_messages.load_one_error')));
        }
    }

    public function update(): void
    {
        $groupId = (int) ($_POST['id'] ?? 0);
        $groupName = trim((string) ($_POST['group_name'] ?? ''));

        if ($groupId <= 0) {
            $this->redirect('/groups?error=' . urlencode(t('group_messages.not_found')));
        }

        if ($groupName === '') {
            $this->redirect(
                '/groups/edit?id=' . $groupId
                . '&error=' . urlencode(t('group_messages.name_required'))
                . '&group_name=' . urlencode($groupName)
            );
        }

        try {
            $group = $this->groupModel()->findById($groupId);

            if ($group === null) {
                $this->redirect('/groups?error=' . urlencode(t('group_messages.not_found')));
            }

            if ($this->groupModel()->existsByNameExcludingId($groupName, $groupId)) {
                $this->redirect(
                    '/groups/edit?id=' . $groupId
                    . '&error=' . urlencode(t('group_messages.name_exists'))
                    . '&group_name=' . urlencode($groupName)
                );
            }

            $this->groupModel()->update($groupId, $groupName);

            $this->redirect('/groups?success=' . urlencode(t('group_messages.updated')));
        } catch (Throwable $throwable) {
            $this->redirect(
                '/groups/edit?id=' . $groupId
                . '&error=' . urlencode(t('group_messages.update_error'))
                . '&group_name=' . urlencode($groupName)
            );
        }
    }

    public function delete(): void
    {
        $groupId = (int) ($_POST['id'] ?? 0);

        if ($groupId <= 0) {
            $this->redirect('/groups?error=' . urlencode(t('group_messages.not_found')));
        }

        try {
            $group = $this->groupModel()->findById($groupId);

            if ($group === null) {
                $this->redirect('/groups?error=' . urlencode(t('group_messages.not_found')));
            }

            $this->groupModel()->delete($groupId);

            $this->redirect('/groups?success=' . urlencode(t('group_messages.deleted')));
        } catch (Throwable $throwable) {
            $this->redirect('/groups?error=' . urlencode(t('group_messages.delete_error')));
        }
    }

    private function getQueryParam(string $key): ?string
    {
        $value = $_GET[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function groupModel(): Group
    {
        if ($this->groupModel === null) {
            $this->groupModel = new Group();
        }

        return $this->groupModel;
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
