<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\SongController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function() {
    //songas get songs 
    Route::get('songs', [SongController::class, 'readAll']);
    Route::get('songs/{id}', [SongController::class, 'readOne']);
    Route::post('songs', [SongController::class, 'create']);
    Route::put('songs/{id}', [SongController::class, 'update']);
    Route::delete('songs/{id}', [SongController::class, 'delete']);

    Route::get('albums', [AlbumController::class, 'readAll']);
    Route::get('albums/{id}', [AlbumController::class, 'readOne']);
    Route::post('albums', [AlbumController::class, 'create']);
    Route::put('albums/{id}', [AlbumController::class, 'update']);
    Route::delete('albums/{id}', [AlbumController::class,'delete']);
});