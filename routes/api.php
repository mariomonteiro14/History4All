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
    Route::get('atividades', 'AtividadeControllerAPI@getTodas');
    Route::get('users/{id}/atividades/pendentes', 'AtividadeControllerAPI@getPendentes');
    Route::get('users/{id}/atividades/concluidas', 'AtividadeControllerAPI@getConcluidas');

    Route::group(['middleware' => 'admin'], function() {
        Route::post('patrimonios/{id}', 'PatrimonioControllerAPI@update');
        Route::delete('patrimonios/{id}', 'PatrimonioControllerAPI@destroy');
        Route::post('patrimonios', 'PatrimonioControllerAPI@store');
        Route::get('users/professores', 'UserControllerAPI@professores');
        Route::get('users/alunos', 'UserControllerAPI@alunos');
        Route::get('users', 'UserControllerAPI@users');
        Route::delete('users/{id}', 'UserControllerAPI@destroy');

    });

    Route::group(['middleware' => 'professor'], function() {

    });

    Route::group(['middleware' => 'aluno'], function() {

    });


});

