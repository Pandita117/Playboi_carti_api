<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/songs', function(){
    return view('songs');
})->name('songs');


Route::get('/albums' ,function() {
    return view('albums');
})->name('albums');