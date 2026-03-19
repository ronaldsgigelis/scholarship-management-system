<?php
$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = ($baseUrl === '/' || $baseUrl === '.') ? '' : rtrim($baseUrl, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Create Subject', ENT_QUOTES, 'UTF-8'); ?></title>
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
            <div class="col-lg-6">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1">Create Subject</h1>
                        <p class="text-muted mb-0">Add a subject and choose its category type.</p>
                    </div>
                    <a href="<?= htmlspecialchars($baseUrl . '/subjects', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary">Back to Subjects</a>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="<?= htmlspecialchars($baseUrl . '/subjects/store', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="mb-3">
                                <label for="subject_name" class="form-label">Subject Name</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="subject_name"
                                    name="subject_name"
                                    value="<?= htmlspecialchars((string) ($oldSubjectName ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label for="category_type" class="form-label">Category Type</label>
                                <select class="form-select" id="category_type" name="category_type" required>
                                    <option value="">Select category type</option>
                                    <option value="P" <?= (($oldCategoryType ?? '') === 'P') ? 'selected' : ''; ?>>P - Professional</option>
                                    <option value="V" <?= (($oldCategoryType ?? '') === 'V') ? 'selected' : ''; ?>>V - General Education</option>
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Save Subject</button>
                                <a href="<?= htmlspecialchars($baseUrl . '/subjects', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
