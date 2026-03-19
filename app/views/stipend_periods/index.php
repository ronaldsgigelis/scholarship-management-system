<?php
$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = ($baseUrl === '/' || $baseUrl === '.') ? '' : rtrim($baseUrl, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Stipend Periods', ENT_QUOTES, 'UTF-8'); ?></title>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Stipend Periods</h1>
                <p class="text-muted mb-0">Manage documented stipend periods by year, period, and period group.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= htmlspecialchars($baseUrl . '/', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary">Home</a>
                <a href="<?= htmlspecialchars($baseUrl . '/stipend-periods/create', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary">Create Period</a>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (empty($periods)): ?>
                    <p class="text-muted mb-0">No stipend periods found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Year</th>
                                    <th>Period</th>
                                    <th>Period Group</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($periods as $period): ?>
                                    <tr>
                                        <td><?= (int) $period['id']; ?></td>
                                        <td><?= htmlspecialchars((string) $period['year'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($period['period'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($period['period_group'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($period['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a
                                                    href="<?= htmlspecialchars($baseUrl . '/stipend-periods/edit?id=' . (int) $period['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    class="btn btn-sm btn-outline-primary"
                                                >
                                                    Edit
                                                </a>
                                                <form
                                                    method="POST"
                                                    action="<?= htmlspecialchars($baseUrl . '/stipend-periods/delete', ENT_QUOTES, 'UTF-8'); ?>"
                                                    onsubmit="return confirm('Are you sure you want to delete this stipend period?');"
                                                >
                                                    <input type="hidden" name="id" value="<?= (int) $period['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
