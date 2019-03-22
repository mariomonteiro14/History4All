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
        });

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('profile_photo')->nullable();
            $table->enum('tipo', ['admin', 'professor', 'aluno']);
            $table->integer('id_escola')->unsigned()->nullable();;
            $table->foreign('id_escola')->references('id')->on('escolas');
            $table->integer('id_turma')->unsigned()->nullable();;
            $table->foreign('id_turma')->references('id')->on('turmas');
            $table->softDeletes();
            $table->timestamps();
            $table->rememberToken();
        });

        Schema::create('patrimonios', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome')->unique();
            $table->longText('descrição');
            $table->string('distrito');
        });

        Schema::create('patrimonio_imagens', function (Blueprint $table) {
            $table->integer('id_património')->unsigned();
            $table->foreign('id_património')->references('id')->on('patrimonios');
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
