<?php
$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = ($baseUrl === '/' || $baseUrl === '.') ? '' : rtrim($baseUrl, '/');
$hasPeriods = !empty($periods);
$hasGroups = !empty($groups);
$canLoad = $hasPeriods && $hasGroups;
$hasSelection = !empty($selectedPeriod) && !empty($selectedGroup);
$canSave = $hasSelection && !empty($students) && !empty($subjects);

if (! function_exists('formatStipendDisplayValue')) {
    function formatStipendDisplayValue(mixed $value, bool $hasGrades): string
    {
        if (! $hasGrades) {
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
    <title><?= htmlspecialchars($title ?? 'Stipend Entry', ENT_QUOTES, 'UTF-8'); ?></title>
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
                <h1 class="h3 mb-1">Stipend Entry</h1>
                <p class="text-muted mb-0">Load students and subjects for the future stipend form.</p>
            </div>
            <a href="<?= htmlspecialchars($baseUrl . '/', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary">Home</a>
        </div>

        <?php if (!empty($warning)): ?>
            <div class="alert alert-warning"><?= htmlspecialchars($warning, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (! $hasPeriods): ?>
            <div class="alert alert-warning">No stipend periods found. Create stipend periods first.</div>
        <?php endif; ?>

        <?php if (! $hasGroups): ?>
            <div class="alert alert-warning">No groups found. Create groups first.</div>
        <?php endif; ?>

        <div class="card shadow-sm mb-4 info-card">
            <div class="card-body">
                <div class="section-card-title">Selection</div>
                <p class="section-card-text mb-4">Choose a stipend period and a group before loading student entry rows.</p>
                <form method="GET" action="<?= htmlspecialchars($baseUrl . '/stipend-entry', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label for="period_id" class="form-label">Stipend Period</label>
                            <select
                                class="form-select"
                                id="period_id"
                                name="period_id"
                                <?= $canLoad ? '' : 'disabled'; ?>
                            >
                                <option value="">Select stipend period</option>
                                <?php foreach ($periods as $period): ?>
                                    <option
                                        value="<?= (int) $period['id']; ?>"
                                        <?= ((string) $period['id'] === (string) ($selectedPeriodId ?? '')) ? 'selected' : ''; ?>
                                    >
                                        <?= htmlspecialchars($period['period'] . ' ' . $period['year'] . ' (' . $period['period_group'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label for="group_id" class="form-label">Group</label>
                            <select
                                class="form-select"
                                id="group_id"
                                name="group_id"
                                <?= $canLoad ? '' : 'disabled'; ?>
                            >
                                <option value="">Select group</option>
                                <?php foreach ($groups as $group): ?>
                                    <option
                                        value="<?= (int) $group['id']; ?>"
                                        <?= ((string) $group['id'] === (string) ($selectedGroupId ?? '')) ? 'selected' : ''; ?>
                                    >
                                        <?= htmlspecialchars($group['group_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100" <?= $canLoad ? '' : 'disabled'; ?>>Load</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($hasSelection): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">Selected Data</h2>
                    <div class="summary-grid">
                        <div class="summary-item">
                            <span class="summary-label">Stipend Period</span>
                            <span class="summary-value"><?= htmlspecialchars($selectedPeriod['period'] . ' ' . $selectedPeriod['year'] . ' (' . $selectedPeriod['period_group'] . ')', ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Group</span>
                            <span class="summary-value"><?= htmlspecialchars($selectedGroup['group_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (empty($students)): ?>
                <div class="alert alert-warning">This group has no students.</div>
            <?php endif; ?>

            <?php if (empty($subjects)): ?>
                <div class="alert alert-warning">This group has no assigned subjects.</div>
            <?php endif; ?>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">Assigned Subjects</h2>

                    <?php if (empty($subjects)): ?>
                        <div class="alert alert-warning mb-0">This group has no assigned subjects.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Subject Name</th>
                                        <th>Category Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subjects as $subject): ?>
                                        <tr>
                                            <td><?= (int) $subject['id']; ?></td>
                                            <td><?= htmlspecialchars($subject['subject_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($subject['category_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($canSave): ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Student Input</h2>
                        <p class="text-muted small">
                            Scholarship is calculated only when at least one grade is entered for the student. Rows without grades show neutral values.
                        </p>

                        <form method="POST" action="<?= htmlspecialchars($baseUrl . '/stipend-entry/save', ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="period_id" value="<?= (int) $selectedPeriod['id']; ?>">
                            <input type="hidden" name="group_id" value="<?= (int) $selectedGroup['id']; ?>">

                            <div class="table-responsive stipend-entry-table-wrap">
                                <table class="table table-bordered table-hover table-sm align-middle mb-0 stipend-entry-table">
                                    <thead>
                                        <tr>
                                            <th class="student-col">Student</th>
                                            <th class="compact-col">Abs.</th>
                                            <th class="compact-col">Bonus</th>
                                            <th class="compact-col">Avg.</th>
                                            <th class="compact-col">Fails</th>
                                            <th class="compact-col">Base</th>
                                            <th class="compact-col">Total</th>
                                            <?php foreach ($subjects as $subject): ?>
                                                <th class="subject-grade-col">
                                                    <div class="subject-name"><?= htmlspecialchars($subject['subject_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div class="small text-muted subject-type-badge"><?= htmlspecialchars($subject['category_type'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <?php $savedEntry = $savedEntries[(int) $student['id']] ?? null; ?>
                                            <?php $hasSavedGrades = (bool) ($savedEntry['has_grades'] ?? false); ?>
                                            <tr>
                                                <td class="student-cell">
                                                    <strong><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    <div class="student-meta">
                                                        <span>Code: <?= htmlspecialchars($student['personal_code'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <span>ID: <?= (int) $student['id']; ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        step="1"
                                                        class="form-control compact-input"
                                                        name="entries[<?= (int) $student['id']; ?>][absence_count]"
                                                        value="<?= htmlspecialchars((string) ($savedEntry['absence_count'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?>"
                                                    >
                                                </td>
                                                <td>
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        step="0.01"
                                                        class="form-control compact-input"
                                                        name="entries[<?= (int) $student['id']; ?>][activity_bonus]"
                                                        value="<?= htmlspecialchars((string) ($savedEntry['activity_bonus'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?>"
                                                    >
                                                </td>
                                                <td>
                                                    <input
                                                        type="text"
                                                        class="form-control compact-input calc-input"
                                                        value="<?= htmlspecialchars(formatStipendDisplayValue($savedEntry['average_grade'] ?? '0.00', $hasSavedGrades), ENT_QUOTES, 'UTF-8'); ?>"
                                                        readonly
                                                    >
                                                </td>
                                                <td>
                                                    <input
                                                        type="text"
                                                        class="form-control compact-input calc-input"
                                                        value="<?= htmlspecialchars(formatStipendDisplayValue($savedEntry['failed_subjects_count'] ?? '0', $hasSavedGrades), ENT_QUOTES, 'UTF-8'); ?>"
                                                        readonly
                                                    >
                                                </td>
                                                <td>
                                                    <input
                                                        type="text"
                                                        class="form-control compact-input calc-input"
                                                        value="<?= htmlspecialchars(formatStipendDisplayValue($savedEntry['base_stipend'] ?? '0.00', $hasSavedGrades), ENT_QUOTES, 'UTF-8'); ?>"
                                                        readonly
                                                    >
                                                </td>
                                                <td>
                                                    <input
                                                        type="text"
                                                        class="form-control compact-input calc-input"
                                                        value="<?= htmlspecialchars(formatStipendDisplayValue($savedEntry['total_stipend'] ?? '0.00', $hasSavedGrades), ENT_QUOTES, 'UTF-8'); ?>"
                                                        readonly
                                                    >
                                                </td>
                                                <?php foreach ($subjects as $subject): ?>
                                                    <td>
                                                        <input
                                                            type="number"
                                                            min="0"
                                                            max="10"
                                                            step="0.01"
                                                            class="form-control compact-input grade-input"
                                                            name="entries[<?= (int) $student['id']; ?>][grades][<?= (int) $subject['id']; ?>]"
                                                            value="<?= htmlspecialchars((string) (($savedEntry['grades'][(int) $subject['id']] ?? '')), ENT_QUOTES, 'UTF-8'); ?>"
                                                        >
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Save Entry Data</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
