<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('patrimonios', 'PatrimonioControllerAPI@patrimoniosDataTable');
Route::get('patrimonios/distritos', 'PatrimonioControllerAPI@getAllDistritos');
Route::get('patrimonios/epocas', 'PatrimonioControllerAPI@getAllEpocas');
