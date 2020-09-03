<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    $beautymail = app()->make(\Snowfire\Beautymail\Beautymail::class);
    $beautymail->send('emails.welcome', [], function ($message) {
        $message
            ->from('dev@worthboard.com')
            ->to('wilhoitdavid393.com', 'testing David')
            ->subject('Welcome!');
    });
});
