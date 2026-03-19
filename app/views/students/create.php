<?php
$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = ($baseUrl === '/' || $baseUrl === '.') ? '' : rtrim($baseUrl, '/');
$hasGroups = !empty($groups);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Create Student', ENT_QUOTES, 'UTF-8'); ?></title>
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
                        <h1 class="h3 mb-1">Create Student</h1>
                        <p class="text-muted mb-0">Add a student with first name, last name, and personal code.</p>
                    </div>
                    <a href="<?= htmlspecialchars($baseUrl . '/students', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary">Back to Students</a>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <?php if (! $hasGroups): ?>
                    <div class="alert alert-warning">
                        Groups must be created first before adding students.
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="<?= htmlspecialchars($baseUrl . '/students/store', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="first_name"
                                        name="first_name"
                                        value="<?= htmlspecialchars((string) ($oldFirstName ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                        required
                                        <?= $hasGroups ? '' : 'disabled'; ?>
                                    >
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="last_name"
                                        name="last_name"
                                        value="<?= htmlspecialchars((string) ($oldLastName ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                        required
                                        <?= $hasGroups ? '' : 'disabled'; ?>
                                    >
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="personal_code" class="form-label">Personal Code</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="personal_code"
                                        name="personal_code"
                                        value="<?= htmlspecialchars((string) ($oldPersonalCode ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                        required
                                        <?= $hasGroups ? '' : 'disabled'; ?>
                                    >
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="group_id" class="form-label">Group</label>
                                <select
                                    class="form-select"
                                    id="group_id"
                                    name="group_id"
                                    required
                                    <?= $hasGroups ? '' : 'disabled'; ?>
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

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" <?= $hasGroups ? '' : 'disabled'; ?>>Save Student</button>
                                <a href="<?= htmlspecialchars($baseUrl . '/students', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
