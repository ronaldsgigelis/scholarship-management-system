<?php
$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = ($baseUrl === '/' || $baseUrl === '.') ? '' : rtrim($baseUrl, '/');
$hasGrades = (int) ($result['grade_count'] ?? 0) > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Stipend Result Detail', ENT_QUOTES, 'UTF-8'); ?></title>
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
        <div class="d-flex justify-content-between align-items-center page-header">
            <div>
                <h1 class="h3 mb-1">Stipend Result Detail</h1>
                <p class="text-muted mb-0">Detailed view of one saved stipend result.</p>
            </div>
            <a href="<?= htmlspecialchars($baseUrl . '/search', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary">Back to Search</a>
        </div>

        <div class="detail-grid mb-4">
            <div class="detail-panel">
                <h3>Student Information</h3>
                <div class="detail-list">
                    <div class="detail-item"><strong>Stipend Period</strong><div><?= htmlspecialchars($result['period'] . ' ' . $result['year'] . ' (' . $result['period_group'] . ')', ENT_QUOTES, 'UTF-8'); ?></div></div>
                    <div class="detail-item"><strong>Group</strong><div><?= htmlspecialchars($result['group_name'], ENT_QUOTES, 'UTF-8'); ?></div></div>
                    <div class="detail-item"><strong>First Name</strong><div><?= htmlspecialchars($result['first_name'], ENT_QUOTES, 'UTF-8'); ?></div></div>
                    <div class="detail-item"><strong>Last Name</strong><div><?= htmlspecialchars($result['last_name'], ENT_QUOTES, 'UTF-8'); ?></div></div>
                    <div class="detail-item"><strong>Personal Code</strong><div><?= htmlspecialchars($result['personal_code'], ENT_QUOTES, 'UTF-8'); ?></div></div>
                </div>
            </div>
            <div class="detail-panel">
                <h3>Stipend Summary</h3>
                <div class="detail-list">
                    <div class="detail-item"><strong>Absence Count</strong><div><?= htmlspecialchars((string) $result['absences'], ENT_QUOTES, 'UTF-8'); ?></div></div>
                    <div class="detail-item"><strong>Activity Bonus</strong><div><?= htmlspecialchars((string) $result['activity_bonus'], ENT_QUOTES, 'UTF-8'); ?></div><div class="small text-muted mt-1">Activity bonus is stored for a 6-month period starting from the selected stipend month.</div></div>
                    <div class="detail-item"><strong>Average Grade</strong><div><?= htmlspecialchars($hasGrades ? (string) $result['average_grade'] : '-', ENT_QUOTES, 'UTF-8'); ?></div></div>
                    <div class="detail-item"><strong>Failed Subjects</strong><div><?= htmlspecialchars($hasGrades ? (string) $result['failed_subjects_count'] : '-', ENT_QUOTES, 'UTF-8'); ?></div></div>
                    <div class="detail-item"><strong>Base Stipend</strong><div><?= htmlspecialchars($hasGrades ? (string) $result['base_stipend'] : '-', ENT_QUOTES, 'UTF-8'); ?></div></div>
                    <div class="detail-item"><strong>Total Stipend</strong><div><?= htmlspecialchars($hasGrades ? (string) $result['total_stipend'] : '-', ENT_QUOTES, 'UTF-8'); ?></div></div>
                    <div class="detail-item"><strong>Created At</strong><div><?= htmlspecialchars((string) $result['created_at'], ENT_QUOTES, 'UTF-8'); ?></div></div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Grades by Subject</h2>

                <?php if (empty($grades)): ?>
                    <p class="text-muted mb-0">No grades were entered for this stipend result, so scholarship values were not calculated.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Subject Name</th>
                                    <th>Category Type</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grades as $grade): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($grade['subject_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($grade['category_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars((string) $grade['grade'], ENT_QUOTES, 'UTF-8'); ?></td>
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
