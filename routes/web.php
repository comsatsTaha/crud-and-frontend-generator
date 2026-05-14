<?php

use App\Http\Controllers\BuilderController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CrudGeneratorController;
use App\Http\Controllers\FrontendGeneratorController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('comments/bulk-delete', [CommentController::class, 'bulkDestroy'])->name('comments.bulk-destroy');
    Route::resource('comments', CommentController::class);
    Route::delete('products/bulk-delete', [ProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');
    Route::resource('products', ProductController::class);

    Route::resource('builder', BuilderController::class)->parameters(['builder' => 'entity']);
    // Route for triggering the code generation
    Route::post('builder/{entity}/generate', [CrudGeneratorController::class, 'generate'])->name('builder.generate');
 Route::get('builder/{entity}/fields', [BuilderController::class, 'entityFields'])->name('builder.entity-fields');
    // Frontend Generator
    
    Route::get('frontend-generator', [FrontendGeneratorController::class, 'index'])->name('frontend-generator.index');
    Route::post('frontend-generator/generate', [FrontendGeneratorController::class, 'generate'])->name('frontend-generator.generate');
});

Route::get('/my-new-page', function () {
    return \Inertia\Inertia::render('Generated/MyNewPage');
});
