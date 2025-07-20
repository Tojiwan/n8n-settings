<?php

use App\Http\Controllers\N8nWorkflowController;
use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes

    Route::get('workflows', [N8nWorkflowController::class, 'index']);
    Route::patch('workflows/{id}/toggle', [N8nWorkflowController::class, 'toggle']);
    Route::get('workflows/{id}/edit', [N8nWorkflowController::class, 'edit']);
    Route::post('workflows/{id}/update', [N8nWorkflowController::class, 'update']);

}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
