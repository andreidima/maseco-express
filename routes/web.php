<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FirmaController;
use App\Http\Controllers\CamionController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Auth::routes(['register' => false, 'password.request' => false, 'reset' => false]);

Route::redirect('/', '/acasa');


Route::group(['middleware' => 'auth'], function () {
    Route::view('acasa', 'acasa');

    Route::resource('/firme/{tipPartener}', FirmaController::class)->parameters(['{tipPartener}' => 'firma']);
    Route::resource('/camioane', CamionController::class)->parameters(['camioane' => 'camion']);
});
