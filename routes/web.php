<?php

use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pipeline', [PipelineController::class, 'index'])->name('pipeline.index');
    Route::patch('/contacts/{contact}/status', [PipelineController::class, 'updateStatus'])->name('contacts.updateStatus');
    Route::get('/broadcast', [BroadcastController::class, 'index'])->name('broadcast.index');
    Route::post('/broadcast/ai-generate', [BroadcastController::class, 'generateAiEmail'])->name('broadcast.ai.generate');
    Route::post('/broadcast/templates', [BroadcastController::class, 'storeTemplate'])->name('broadcast.templates.store');
    Route::delete('/broadcast/templates/{emailTemplate}', [BroadcastController::class, 'destroyTemplate'])->name('broadcast.templates.destroy');
    Route::post('/broadcast/send', [BroadcastController::class, 'send'])->name('broadcast.send');
    Route::get('/contacts/export', [ContactController::class, 'export'])->name('contacts.export');
    Route::resource('contacts', ContactController::class)->except('show');
    Route::get('contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
    Route::post('contacts/{contact}/notes', [ContactController::class, 'storeNote'])->name('contacts.notes.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
