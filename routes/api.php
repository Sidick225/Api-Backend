<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('/auth/login', [AuthController::class, 'login'])->name('login');
Route::post('/auth/register', [AuthController::class, 'register'])->name('register');
Route::delete('/auth/lagout', [AuthController::class, 'lagout'])->name('lagout');


Route::middleware(['auth'])->group(function () {
    Route::get('/posts', [PostController::class, 'index'])->name('get.posts');
    Route::post('/posts', [PostController::class, 'store'])->name('store.post');
    Route::get('/posts/{id}', [PostController::class, 'showById'])->name('show.post.id');
    Route::get('/posts/{slug}', [PostController::class, 'showBySlug'])->name('show.post.slug');
    Route::put('/posts/{id}', [PostController::class, 'update'])->name('update.post');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('destroy.post');
});
