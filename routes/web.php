<?php

declare(strict_types=1);

use App\Controllers\GroupController;
use App\Controllers\GroupSubjectController;
use App\Controllers\HomeController;
use App\Controllers\SearchController;
use App\Controllers\StipendEntryController;
use App\Controllers\StipendPeriodController;
use App\Controllers\StipendResultController;
use App\Controllers\StudentController;
use App\Controllers\SubjectController;
use App\Core\Router;

return static function (Router $router): void {
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/groups', [GroupController::class, 'index']);
    $router->get('/groups/create', [GroupController::class, 'create']);
    $router->get('/groups/edit', [GroupController::class, 'edit']);
    $router->post('/groups/store', [GroupController::class, 'store']);
    $router->post('/groups/update', [GroupController::class, 'update']);
    $router->post('/groups/delete', [GroupController::class, 'delete']);
    $router->get('/students', [StudentController::class, 'index']);
    $router->get('/students/create', [StudentController::class, 'create']);
    $router->get('/students/edit', [StudentController::class, 'edit']);
    $router->post('/students/store', [StudentController::class, 'store']);
    $router->post('/students/update', [StudentController::class, 'update']);
    $router->post('/students/delete', [StudentController::class, 'delete']);
    $router->get('/subjects', [SubjectController::class, 'index']);
    $router->get('/subjects/create', [SubjectController::class, 'create']);
    $router->get('/subjects/edit', [SubjectController::class, 'edit']);
    $router->post('/subjects/store', [SubjectController::class, 'store']);
    $router->post('/subjects/update', [SubjectController::class, 'update']);
    $router->post('/subjects/delete', [SubjectController::class, 'delete']);
    $router->get('/group-subjects', [GroupSubjectController::class, 'index']);
    $router->get('/group-subjects/create', [GroupSubjectController::class, 'create']);
    $router->post('/group-subjects/store', [GroupSubjectController::class, 'store']);
    $router->post('/group-subjects/delete', [GroupSubjectController::class, 'delete']);
    $router->get('/search', [SearchController::class, 'index']);
    $router->get('/stipend-entry', [StipendEntryController::class, 'index']);
    $router->post('/stipend-entry/save', [StipendEntryController::class, 'save']);
    $router->get('/stipend-results/show', [StipendResultController::class, 'show']);
    $router->get('/stipend-periods', [StipendPeriodController::class, 'index']);
    $router->get('/stipend-periods/create', [StipendPeriodController::class, 'create']);
    $router->get('/stipend-periods/edit', [StipendPeriodController::class, 'edit']);
    $router->post('/stipend-periods/store', [StipendPeriodController::class, 'store']);
    $router->post('/stipend-periods/update', [StipendPeriodController::class, 'update']);
    $router->post('/stipend-periods/delete', [StipendPeriodController::class, 'delete']);
};
