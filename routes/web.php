<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::get('/test-webhook', function () {
    $response = Http::post('https://n8n.tigernethost.com/form/5f0f3785-f332-4d47-bd83-abfa3446a1f2', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    return $response->body(); // for debugging
});

require __DIR__.'/auth.php';
