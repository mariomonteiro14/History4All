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
Route::get('users/token/{token}','UserControllerAPI@getUserByToken');
Route::get('patrimonios', 'PatrimonioControllerAPI@patrimoniosDataTable');
Route::get('patrimonios/{id}', 'PatrimonioControllerAPI@find');
Route::post('login', 'UserControllerAPI@login')->name('login');
Route::post('register/activate/{id}', 'UserControllerAPI@activateAccount');

Route::group(['middleware' => 'auth:api'], function() {
    Route::get('logout', 'UserControllerAPI@logout');
    Route::get('users/me', 'UserControllerAPI@myProfile');
    Route::post('users/me', 'UserControllerAPI@editProfile');
    Route::get('users/{id}/atividades/', 'AtividadeControllerAPI@getTodas');
    Route::get('me/atividades/', 'AtividadeControllerAPI@getMinhas');
    Route::get('users/{id}/atividades/pendentes', 'AtividadeControllerAPI@getPendentes');
    Route::get('users/{id}/atividades/concluidas', 'AtividadeControllerAPI@getConcluidas');

    Route::group(['middleware' => 'admin'], function() {
        Route::post('patrimonios/{id}', 'PatrimonioControllerAPI@update');
        Route::delete('patrimonios/{id}', 'PatrimonioControllerAPI@destroy');
        Route::post('patrimonios', 'PatrimonioControllerAPI@store');
        Route::get('users/professores', 'UserControllerAPI@professores');
        Route::get('users', 'UserControllerAPI@users');
        Route::get('users/alunos', 'UserControllerAPI@alunos');
        Route::get('usersTrashed', 'UserControllerAPI@usersTrashed');
        Route::put('users/{id}', 'UserControllerAPI@update');
        Route::delete('users/{id}', 'UserControllerAPI@destroy');
        Route::put('users/restaurar/{id}', 'UserControllerAPI@restore');

        Route::post('escolas', 'EscolaControllerAPI@store');
        Route::delete('escolas/{id}', 'EscolaControllerAPI@destroy');

    });

    Route::group(['middleware' => 'professor'], function() {
        Route::get('escolas/{id}/turmas', 'EscolaControllerAPI@escolaTurmas');
        Route::get('me/escola', 'EscolaControllerAPI@myEscola');
        Route::post('atividades', 'AtividadeControllerAPI@store');
        Route::put('atividades/{id}', 'AtividadeControllerAPI@update');
        Route::delete('atividades/{id}', 'AtividadeControllerAPI@destroy');
    });

    Route::group(['middleware' => 'adminOuProfessor'], function() {
        Route::get('escolas', 'EscolaControllerAPI@escolas');
        Route::post('escolas/{id}/turmas', 'EscolaControllerAPI@criarTurma');
        Route::put('escolas/turmas/{id}', 'EscolaControllerAPI@editarTurma');
        Route::delete('escolas/turmas/{id}', 'EscolaControllerAPI@destroyTurma');

        Route::get('users/alunos', 'UserControllerAPI@alunos');
        Route::get('me/escola/alunos', 'EscolaControllerAPI@myEscolaAlunos');
        Route::post('users', 'UserControllerAPI@store');
    });

    Route::group(['middleware' => 'aluno'], function() {

    });


});

