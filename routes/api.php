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
Route::get('patrimonios/{id}', 'PatrimonioControllerAPI@find');
Route::post('login', 'UserControllerAPI@login')->name('login');
Route::group(['middleware' => 'auth:api'], function() {
    Route::get('logout', 'UserControllerAPI@logout');
    Route::get('users/me', 'UserControllerAPI@myProfile');
});
Route::middleware('auth:api', 'admin')->put('patrimonios/{id}', 'PatrimonioControllerAPI@update');

