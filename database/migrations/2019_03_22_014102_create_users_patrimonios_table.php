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
            $table->string('distrito');
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
            $table->string('profile_photo')->nullable();
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
            $table->longText('descrição');
            $table->string('distrito');
            $table->enum('epoca', ['pré-história', 'idade antiga', 'idade média', 'idade contemporânea']);
            $table->enum('ciclo', ['1', '2', '3', 'sec']);
        });

        Schema::create('patrimonio_imagens', function (Blueprint $table) {
            $table->integer('património_id')->unsigned();
            $table->foreign('património_id')->references('id')->on('patrimonios');
            $table->string('foto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patrimonio_imagens');
        Schema::dropIfExists('patrimonios');
        Schema::dropIfExists('users');
        Schema::dropIfExists('turmas');
        Schema::dropIfExists('escolas');
    }
}
