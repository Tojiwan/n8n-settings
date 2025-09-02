<?php
// routes/api.php
use App\Http\Controllers\AiBotController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('ping', fn () => response()->json(['ok' => true]));

// Your app-authenticated endpoints (protect however you like)
Route::post('/ai/bots',        [AiBotController::class, 'store']);
Route::patch('/ai/bots/{id}',  [AiBotController::class, 'updateContext']);
Route::delete('/ai/bots/{id}', [AiBotController::class, 'destroy']);

// Endpoints n8n calls (protected by bearer)
Route::middleware('ai.portal')->group(function () {
    Route::get('/ai/bots/{identifier}/context', [AiBotController::class, 'getContext']);
    Route::post('/api/ai/generate',             [AiChatController::class, 'generate']);
});

// Activate/deactivate using existing "enabled"
Route::post('ai/bots/{id}/activate',   [AiBotController::class, 'activate']);
Route::post('ai/bots/{id}/deactivate', [AiBotController::class, 'deactivate']);

// Sample Site 
Route::get('/site/{business:name_slug}', [SiteController::class, 'show'])->name('site.show');
