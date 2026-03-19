<?php
$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = ($baseUrl === '/' || $baseUrl === '.') ? '' : rtrim($baseUrl, '/');
$hasGroups = !empty($groups);
$hasSubjects = !empty($subjects);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Create Assignment', ENT_QUOTES, 'UTF-8'); ?></title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >
    <link href="<?= htmlspecialchars($baseUrl . '/assets/css/app.css', ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-end mb-3"><?= render_language_switch(); ?></div>
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1">Create Assignment</h1>
                        <p class="text-muted mb-0">Assign a subject to a group.</p>
                    </div>
                    <a href="<?= htmlspecialchars($baseUrl . '/group-subjects', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary">Back to Assignments</a>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <?php if (! $hasGroups): ?>
                    <div class="alert alert-warning">
                        Groups must be created first before making assignments.
                    </div>
                <?php endif; ?>

                <?php if (! $hasSubjects): ?>
                    <div class="alert alert-warning">
                        Subjects must be created first before making assignments.
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="<?= htmlspecialchars($baseUrl . '/group-subjects/store', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="mb-3">
                                <label for="group_id" class="form-label">Group</label>
                                <select
                                    class="form-select"
                                    id="group_id"
                                    name="group_id"
                                    required
                                    <?= ($hasGroups && $hasSubjects) ? '' : 'disabled'; ?>
                                >
                                    <option value="">Select a group</option>
                                    <?php foreach ($groups as $group): ?>
                                        <option
                                            value="<?= (int) $group['id']; ?>"
                                            <?= ((string) $group['id'] === (string) ($oldGroupId ?? '')) ? 'selected' : ''; ?>
                                        >
                                            <?= htmlspecialchars($group['group_name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="subject_id" class="form-label">Subject</label>
                                <select
                                    class="form-select"
                                    id="subject_id"
                                    name="subject_id"
                                    required
                                    <?= ($hasGroups && $hasSubjects) ? '' : 'disabled'; ?>
                                >
                                    <option value="">Select a subject</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option
                                            value="<?= (int) $subject['id']; ?>"
                                            <?= ((string) $subject['id'] === (string) ($oldSubjectId ?? '')) ? 'selected' : ''; ?>
                                        >
                                            <?= htmlspecialchars($subject['subject_name'] . ' (' . $subject['category_type'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" <?= ($hasGroups && $hasSubjects) ? '' : 'disabled'; ?>>Save Assignment</button>
                                <a href="<?= htmlspecialchars($baseUrl . '/group-subjects', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
