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
        Schema::create('escolas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome')->unique();
            $table->enum('distrito', ['Aveiro', 'Beja', 'Braga', 'Bragança', 'Castelo Branco', 'Coimbra', 'Évora', 'Faro',
                'Guarda', 'Leiria', 'Lisboa', 'Portalegre', 'Porto', 'Santarém', 'Setúbal', 'Viana do Castelo',
                'Vila Real', 'Viseu', 'Açores', 'Madeira']);
        });

        Schema::create('turmas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
            $table->integer('escola_id')->unsigned();
            $table->foreign('escola_id')->references('id')->on('escolas');
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
            $table->foreign('escola_id')->references('id')->on('escolas');
            $table->integer('turma_id')->unsigned()->nullable();
            $table->foreign('turma_id')->references('id')->on('turmas');
            $table->softDeletes();
            $table->timestamps();
            $table->rememberToken();
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
            $table->foreign('patrimonio_id')->references('id')->on('patrimonios');
            $table->string('foto');
        });

        Schema::create('chats', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('privado')->default(true);
            $table->integer('atividade_id')->unsigned();
            $table->foreign('atividade_id')->references('id')->on('atividades')->onDelete('cascade');
            $table->string('assunto')->nullable();
        });

        Schema::create('chat_mensagens', function (Blueprint $table) {
            $table->integer('chat_id')->unsigned();
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->longText('mensagem');
        });

        Schema::create('atividades', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('tipo', ['visita de estudo', 'trabalho em familia', 'trabalho de pesquisa', 'definir tipos de patrimonio']);
            $table->string('descricao');
            $table->boolean('privado')->default(true);
            $table->bigInteger('coordenador')->unsigned();
            $table->foreign('coordenador')->references('id')->on('users');
            $table->integer('chat_id')->unsigned()->nullable();
            $table->foreign('chat_id')->references('id')->on('chats');
            $table->date('data')->nullable();
        });

        Schema::create('atividade_participantes', function (Blueprint $table) {
            $table->integer('atividade_id')->unsigned();
            $table->foreign('atividade_id')->references('id')->on('atividades')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->enum('estado', ['pedentes', 'concluida']);
            $table->primary(['atividade_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
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
