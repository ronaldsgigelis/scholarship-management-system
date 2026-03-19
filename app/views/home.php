<?php
$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = ($baseUrl === '/' || $baseUrl === '.') ? '' : rtrim($baseUrl, '/');
$logoExists = file_exists(BASE_PATH . '/public/assets/images/img_rmpv.webp');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Scholarship Management System', ENT_QUOTES, 'UTF-8'); ?></title>
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
        <div class="d-flex justify-content-end lang-bar"><?= render_language_switch(); ?></div>

        <div class="card dashboard-hero mb-4">
            <div class="card-body p-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <h1 class="dashboard-title mb-3">Scholarship Management System</h1>
                        <p class="lead mb-2">Manage students, subjects, periods, and scholarship results in one system.</p>
                        <p class="dashboard-subtitle mb-0">Use the modules below to manage groups, students, subjects, stipend periods, and saved scholarship results.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <?php if ($logoExists): ?>
                            <img src="<?= htmlspecialchars($baseUrl . '/assets/images/img_rmpv.webp', ENT_QUOTES, 'UTF-8'); ?>" alt="School logo" class="dashboard-logo">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="module-grid">
            <a href="<?= htmlspecialchars($baseUrl . '/groups', ENT_QUOTES, 'UTF-8'); ?>" class="module-card">
                <div class="module-card-title">Groups</div>
                <p class="module-card-text">Manage study groups.</p>
            </a>
            <a href="<?= htmlspecialchars($baseUrl . '/students', ENT_QUOTES, 'UTF-8'); ?>" class="module-card">
                <div class="module-card-title">Students</div>
                <p class="module-card-text">Manage student records.</p>
            </a>
            <a href="<?= htmlspecialchars($baseUrl . '/subjects', ENT_QUOTES, 'UTF-8'); ?>" class="module-card">
                <div class="module-card-title">Subjects</div>
                <p class="module-card-text">Manage subjects and categories.</p>
            </a>
            <a href="<?= htmlspecialchars($baseUrl . '/group-subjects', ENT_QUOTES, 'UTF-8'); ?>" class="module-card">
                <div class="module-card-title">Group Subjects</div>
                <p class="module-card-text">Link subjects to groups.</p>
            </a>
            <a href="<?= htmlspecialchars($baseUrl . '/stipend-periods', ENT_QUOTES, 'UTF-8'); ?>" class="module-card">
                <div class="module-card-title">Stipend Periods</div>
                <p class="module-card-text">Manage stipend periods.</p>
            </a>
            <a href="<?= htmlspecialchars($baseUrl . '/stipend-entry', ENT_QUOTES, 'UTF-8'); ?>" class="module-card">
                <div class="module-card-title">Stipend Entry</div>
                <p class="module-card-text">Enter grades and absences.</p>
            </a>
            <a href="<?= htmlspecialchars($baseUrl . '/search', ENT_QUOTES, 'UTF-8'); ?>" class="module-card">
                <div class="module-card-title">Search / History</div>
                <p class="module-card-text">Search saved results.</p>
            </a>
        </div>
    </div>
</body>
</html>
