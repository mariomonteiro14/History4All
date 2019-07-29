<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersPatrimoniosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('privado')->default(true);
            $table->string('assunto')->nullable();
        });

        Schema::create('chat_mensagens', function (Blueprint $table) {
            $table->integer('chat_id')->unsigned();
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned();
            $table->text('mensagem');
        });

        Schema::create('escolas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome')->unique();
            $table->enum('distrito', ['Aveiro', 'Beja', 'Braga', 'Bragança', 'Castelo Branco', 'Coimbra', 'Évora', 'Faro',
                'Guarda', 'Leiria', 'Lisboa', 'Portalegre', 'Porto', 'Santarém', 'Setúbal', 'Viana do Castelo',
                'Vila Real', 'Viseu', 'Açores', 'Madeira']);
            $table->integer('chat_professores_id')->unsigned();
            $table->foreign('chat_professores_id')->references('id')->on('chats')->onDelete('cascade');
        });

        Schema::create('turmas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
            $table->bigInteger('professor_id')->unsigned()->nullable();
            $table->integer('escola_id')->unsigned();
            $table->foreign('escola_id')->references('id')->on('escolas')->onDelete('cascade');
            $table->enum('ciclo', ['1º ciclo', '2º ciclo', '3º ciclo', 'secundário']);
        });

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('foto')->nullable();
            $table->enum('tipo', ['admin', 'professor', 'aluno']);
            $table->integer('escola_id')->unsigned()->nullable();
            $table->foreign('escola_id')->references('id')->on('escolas')->onDelete('cascade');
            $table->integer('turma_id')->unsigned()->nullable();
            $table->foreign('turma_id')->references('id')->on('turmas')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();
            $table->rememberToken();
        });

        Schema::create('notificacoes', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->unsigned();//destinatário
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('mensagem');
            $table->string('de');
            $table->dateTime('data');
            $table->boolean('nova');
            $table->string('link')->nullable();
        });

        Schema::create('patrimonios', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome')->unique();
            $table->longText('descricao');
            $table->enum('distrito', ['Aveiro', 'Beja', 'Braga', 'Bragança', 'Castelo Branco', 'Coimbra', 'Évora', 'Faro',
                'Guarda', 'Leiria', 'Lisboa', 'Portalegre', 'Porto', 'Santarém', 'Setúbal', 'Viana do Castelo',
                'Vila Real', 'Viseu', 'Açores', 'Madeira']);
            $table->enum('epoca', ['pré-história', 'idade antiga', 'idade média', 'idade contemporânea']);
            $table->enum('ciclo', ['1º ciclo', '2º ciclo', '3º ciclo', 'secundário']);
        });

        Schema::create('patrimonio_imagens', function (Blueprint $table) {
            $table->integer('patrimonio_id')->unsigned();
            $table->foreign('patrimonio_id')->references('id')->on('patrimonios')->onDelete('cascade');
            $table->string('foto');
        });



        Schema::create('atividades', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo');
            $table->string('titulo');
            $table->string('descricao', 1000);
            $table->integer('numeroElementos');
            $table->enum('visibilidade', ['privado', 'publico', 'visivel para a escola']);
            $table->bigInteger('coordenador')->unsigned();
            $table->foreign('coordenador')->references('id')->on('users')->onDelete('set null');
            $table->integer('chat_id')->unsigned()->nullable();
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('set null');
            $table->date('dataInicio')->nullable();
            $table->date('dataFinal')->nullable();
        });

        Schema::create('atividade_participantes', function (Blueprint $table) {
            $table->integer('atividade_id')->unsigned();
            $table->foreign('atividade_id')->references('id')->on('atividades')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->primary(['atividade_id', 'user_id']);
        });

        Schema::create('atividade_patrimonios', function (Blueprint $table) {
            $table->integer('atividade_id')->unsigned();
            $table->foreign('atividade_id')->references('id')->on('atividades')->onDelete('cascade');
            $table->integer('patrimonio_id')->unsigned();
            $table->foreign('patrimonio_id')->references('id')->on('patrimonios')->onDelete('cascade');
            $table->primary(['atividade_id', 'patrimonio_id']);
        });
        Schema::create('atividade_testemunhos', function (Blueprint $table) {
            $table->integer('atividade_id')->unsigned();
            $table->foreign('atividade_id')->references('id')->on('atividades')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('rate');
            $table->longText('texto');
            $table->boolean('confirmado')->default(false);
            $table->primary(['atividade_id', 'user_id']);
        });

        Schema::table('turmas', function (Blueprint $table) {
           // $table->bigInteger('professor_id')->unsigned();
            $table->foreign('professor_id')->references('id')->on('users')->onDelete('set null');
        });


        Schema::table('chat_mensagens', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('forums', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo');
            $table->longText('descricao');
            $table->string('user_email');
            $table->boolean('show_email');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('comentarios', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('forum_id')->unsigned();
            $table->foreign('forum_id')->references('id')->on('forums')->onDelete('cascade');
            $table->longText('comentario');
            $table->string('user_email')->nullable();
            $table->integer('likes')->default(0);
            $table->integer('dislikes')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('forum_patrimonios', function (Blueprint $table) {
            $table->integer('forum_id')->unsigned();
            $table->foreign('forum_id')->references('id')->on('forum')->onDelete('cascade');
            $table->integer('patrimonio_id')->unsigned();
            $table->foreign('patrimonio_id')->references('id')->on('patrimonios')->onDelete('cascade');
            $table->primary(['atividade_id', 'patrimonio_id']);
        });

        Schema::create('historico_gerenciador_codigos', function (Blueprint $table) {
            $table->string('email');
            $table->integer('codigo');
            $table->date('data');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('atividade_patrimonios');
        Schema::dropIfExists('atividade_participantes');
        Schema::dropIfExists('atividades');
        Schema::dropIfExists('chat_mensagens');
        Schema::dropIfExists('chats');
        Schema::dropIfExists('patrimonio_imagens');
        Schema::dropIfExists('patrimonios');
        Schema::dropIfExists('users');
        Schema::dropIfExists('turmas');
        Schema::dropIfExists('escolas');
    }
}
