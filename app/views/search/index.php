<?php
$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = ($baseUrl === '/' || $baseUrl === '.') ? '' : rtrim($baseUrl, '/');

if (! function_exists('formatSearchStipendValue')) {
    function formatSearchStipendValue(mixed $value, array $result): string
    {
        if ((int) ($result['grade_count'] ?? 0) === 0) {
            return '-';
        }

        return (string) $value;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Search History', ENT_QUOTES, 'UTF-8'); ?></title>
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
                <h1 class="h3 mb-1">Search History</h1>
                <p class="text-muted mb-0">Search saved stipend results by year, period, group, or student.</p>
            </div>
            <a href="<?= htmlspecialchars($baseUrl . '/', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary">Home</a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm mb-4 info-card">
            <div class="card-body">
                <div class="section-card-title">Filters</div>
                <p class="section-card-text mb-4">Use one or more filters to narrow the saved stipend records.</p>
                <form method="GET" action="<?= htmlspecialchars($baseUrl . '/search', ENT_QUOTES, 'UTF-8'); ?>" class="search-filter-form">
                    <div class="row g-3 align-items-end search-filter-row">
                        <div class="col-lg-2 col-md-4">
                            <label for="year" class="form-label">Year</label>
                            <select class="form-select" id="year" name="year">
                                <option value="">All years</option>
                                <?php foreach ($years as $yearOption): ?>
                                    <option
                                        value="<?= htmlspecialchars((string) $yearOption['year'], ENT_QUOTES, 'UTF-8'); ?>"
                                        <?= ((string) $yearOption['year'] === (string) $selectedYear) ? 'selected' : ''; ?>
                                    >
                                        <?= htmlspecialchars((string) $yearOption['year'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label for="period" class="form-label">Period / Month</label>
                            <select class="form-select" id="period" name="period">
                                <option value="">All periods</option>
                                <?php foreach ($periods as $periodOption): ?>
                                    <option
                                        value="<?= htmlspecialchars($periodOption['period'], ENT_QUOTES, 'UTF-8'); ?>"
                                        <?= ((string) $periodOption['period'] === (string) $selectedPeriod) ? 'selected' : ''; ?>
                                    >
                                        <?= htmlspecialchars($periodOption['period'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label for="period_group" class="form-label">Period Group</label>
                            <select class="form-select" id="period_group" name="period_group">
                                <option value="">All period groups</option>
                                <?php foreach ($periodGroups as $periodGroupOption): ?>
                                    <option
                                        value="<?= htmlspecialchars($periodGroupOption['period_group'], ENT_QUOTES, 'UTF-8'); ?>"
                                        <?= ((string) $periodGroupOption['period_group'] === (string) $selectedPeriodGroup) ? 'selected' : ''; ?>
                                    >
                                        <?= htmlspecialchars($periodGroupOption['period_group'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label for="group_id" class="form-label">Group</label>
                            <select class="form-select" id="group_id" name="group_id">
                                <option value="">All groups</option>
                                <?php foreach ($groups as $group): ?>
                                    <option
                                        value="<?= (int) $group['id']; ?>"
                                        <?= ((string) $group['id'] === (string) $selectedGroupId) ? 'selected' : ''; ?>
                                    >
                                        <?= htmlspecialchars($group['group_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label for="student_query" class="form-label">Student Name Search</label>
                            <input
                                type="text"
                                class="form-control"
                                id="student_query"
                                name="student_query"
                                value="<?= htmlspecialchars((string) ($studentQuery ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                placeholder="Enter first name, last name, or full name"
                            >
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label for="student_id" class="form-label">Student</label>
                            <select class="form-select" id="student_id" name="student_id">
                                <option value="">All students</option>
                                <?php foreach ($students as $student): ?>
                                    <option
                                        value="<?= (int) $student['id']; ?>"
                                        <?= ((string) $student['id'] === (string) $selectedStudentId) ? 'selected' : ''; ?>
                                    >
                                        <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['personal_code'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="search-filter-actions">
                                <button type="submit" class="btn btn-primary search-action-btn">Search</button>
                                <a href="<?= htmlspecialchars($baseUrl . '/search', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary search-action-btn">Clear Filters</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Results</h2>

                <?php if ($hasActiveFilters && empty($results)): ?>
                    <div class="alert alert-warning mb-0">No results found for the selected filters.</div>
                <?php elseif (empty($results)): ?>
                    <p class="text-muted mb-0">Apply filters to view saved stipend results.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Stipend Period</th>
                                    <th>Group</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Personal Code</th>
                                    <th>Average Grade</th>
                                    <th>Failed Subjects</th>
                                    <th>Absence Count</th>
                                    <th>Activity Bonus</th>
                                    <th>Base Stipend</th>
                                    <th>Total Stipend</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $result): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($result['period'] . ' ' . $result['year'] . ' (' . $result['period_group'] . ')', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($result['group_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($result['first_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($result['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($result['personal_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars(formatSearchStipendValue($result['average_grade'], $result), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars(formatSearchStipendValue($result['failed_subjects_count'], $result), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars((string) $result['absences'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars((string) $result['activity_bonus'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars(formatSearchStipendValue($result['base_stipend'], $result), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars(formatSearchStipendValue($result['total_stipend'], $result), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars((string) $result['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <a
                                                href="<?= htmlspecialchars($baseUrl . '/stipend-results/show?id=' . (int) $result['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                                class="btn btn-sm btn-outline-primary"
                                            >
                                                View
                                            </a>
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
