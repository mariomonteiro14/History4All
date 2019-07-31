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
Route::get('patrimoniosShort', 'PatrimonioControllerAPI@patrimoniosShortDataTable');
Route::get('patrimonios/{id}', 'PatrimonioControllerAPI@find');
Route::post('login', 'UserControllerAPI@login')->name('login');
Route::post('register/activate/{id}', 'UserControllerAPI@activateAccount');
Route::post('register/novaPassword/{id}', 'UserControllerAPI@novaPassword');
Route::post('sendEmail/history4all', 'UserControllerAPI@contactHistory4all');
Route::post('sendEmail/resetPassword', 'UserControllerAPI@resetPassword');
Route::put('register/novoEmail/{id}', 'UserControllerAPI@novoEmail');

///////////////FORUMS///////////////////////////////////////
Route::get('forums', 'ForumControllerAPI@forums');
Route::post('forums', 'ForumControllerAPI@storeForum');
//Route::put('forums/{id}', 'ForumControllerAPI@updateForum');
Route::delete('forums/{id}', 'ForumControllerAPI@destroyForum');
Route::get('forums/{id}/comentarios', 'ForumControllerAPI@comments');
Route::post('forums/{id}/comentarios', 'ForumControllerAPI@storeComment');
//Route::put('comentarios/{id}', 'ForumControllerAPI@updateComment');
Route::put('comentarios/{id}/incrementLike', 'ForumControllerAPI@incrementLike');
Route::put('comentarios/{id}/decrementLike', 'ForumControllerAPI@decrementLike');
Route::put('comentarios/{id}/incrementDislike', 'ForumControllerAPI@incrementDislike');
Route::put('comentarios/{id}/decrementDislike', 'ForumControllerAPI@decrementDislike');
Route::delete('comentarios/{id}', 'ForumControllerAPI@destroyComment');
///Apenas se o user nao estiver autenticado
Route::post('forums/generateAccessCode', 'ForumControllerAPI@generateAccessCode');
//////////////////////////////////////////////////////////////////////////////////////////////
///
Route::group(['middleware' => 'auth:api'], function() {
    Route::get('logout', 'UserControllerAPI@logout');
    Route::get('users/me', 'UserControllerAPI@myProfile');
    Route::get('user/{id}', 'UserControllerAPI@getUser');
    Route::post('users/me', 'UserControllerAPI@editProfile');
    Route::get('atividades/publicas', 'AtividadeControllerAPI@atividadesPublicas');
    Route::get('me/atividades/', 'AtividadeControllerAPI@getMinhas');
    Route::get('users/{id}/atividades/pendentes', 'AtividadeControllerAPI@getPendentes');
    Route::get('users/{id}/atividades/concluidas', 'AtividadeControllerAPI@getConcluidas');
    Route::get('atividades/{id}', 'AtividadeControllerAPI@getAtividade');
    Route::post('chat', 'AtividadeControllerAPI@storeChatMensagem');
    Route::get('me/notificacoes', 'UserControllerAPI@notificacoes');
    Route::put('me/notificacoes', 'UserControllerAPI@updateNotificacoes');
    Route::put('me/notificacoes/{id}/lida', 'UserControllerAPI@updateNotificacaoLida');
    Route::put('me/notificacoes/{id}/naolida', 'UserControllerAPI@updateNotificacaoNaoLida');

    Route::get('atividade/{id}/testemunhos', 'AtividadeControllerAPI@getTestemunhos');
    Route::post('atividade/{id}/testemunho', 'AtividadeControllerAPI@novoTestemunho');
    Route::put('atividade/{id}/testemunho', 'AtividadeControllerAPI@editTestemunho');
    Route::delete('atividade/{id}/testemunho/{user_id}', 'AtividadeControllerAPI@removerTestemunho');

    Route::group(['middleware' => 'admin'], function() {
        Route::post('patrimonios/{id}', 'PatrimonioControllerAPI@update');
        Route::delete('patrimonios/{id}', 'PatrimonioControllerAPI@destroy');
        Route::post('patrimonios', 'PatrimonioControllerAPI@store');
        Route::get('users/professores', 'UserControllerAPI@professores');
        Route::get('users', 'UserControllerAPI@users');
        Route::get('users/alunos', 'UserControllerAPI@alunos');
        Route::get('usersTrashed', 'UserControllerAPI@usersTrashed');
        Route::put('users/{id}', 'UserControllerAPI@update');
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
        Route::post('patrimonios/{id}/imagens', 'PatrimonioControllerAPI@adicionarImagens');
        Route::put('atividade/{id}/testemunho/{user_id}', 'AtividadeControllerAPI@confirmarTestemunho');
        Route::get('chat/professores', 'UserControllerAPI@chatProfessores');
    });

    Route::group(['middleware' => 'adminOuProfessor'], function() {
        Route::get('escolas', 'EscolaControllerAPI@escolas');
        Route::post('escolas/{id}/turmas', 'EscolaControllerAPI@criarTurma');
        Route::put('escolas/turmas/{id}', 'EscolaControllerAPI@editarTurma');
        Route::delete('escolas/turmas/{id}', 'EscolaControllerAPI@destroyTurma');

        Route::get('users/alunos', 'UserControllerAPI@alunos');
        Route::get('me/escola/alunos', 'EscolaControllerAPI@myEscolaAlunos');
        Route::post('users', 'UserControllerAPI@store');
        Route::delete('users/{id}', 'UserControllerAPI@destroy');

        Route::post('notificacoes', 'UserControllerAPI@storeNotificacao');
    });

    Route::group(['middleware' => 'professorOuAluno'], function() {
        Route::get('me/escola/estatisticas', 'EscolaControllerAPI@myEscolaEstatisticas');
    });

    Route::group(['middleware' => 'aluno'], function() {
        Route::get('me/escola/turma', 'EscolaControllerAPI@myTurma');
    });


});

