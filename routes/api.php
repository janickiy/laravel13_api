<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NoteController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::post('register', [AuthController::class, 'register'])->name('api.v1.register');
    Route::post('login', [AuthController::class, 'login'])->name('api.v1.login');
    Route::post('logout', [AuthController::class, 'logout'])->name('api.v1.logout');

    Route::middleware('auth:api')->group(function () {
        Route::group(['prefix' => 'notes'], function () {
            Route::get('', [NoteController::class, 'index'])->name('api.v1.notes.index');
            Route::get('{id}', [NoteController::class, 'show'])->name('api.v1.notes.show')->where('id', '[0-9]+');
            Route::post('store', [NoteController::class, 'store'])->name('api.v1.notes.store');
            Route::put('update/{id}', [NoteController::class, 'update'])->name('api.v1.notes.update')->where('id', '[0-9]+');
            Route::delete('delete/{id}', [NoteController::class, 'destroy'])->name('api.v1.notes.destroy')->where('id', '[0-9]+');
        });
    });
});
