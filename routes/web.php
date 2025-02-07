<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route to display PHP configuration information
/* Route::get('/info', function () {
    return phpinfo();
}); */
