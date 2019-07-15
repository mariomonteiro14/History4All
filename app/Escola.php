<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Escola extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $timestamps = false;

    protected $fillable = [
        'id', 'nome', 'distrito', 'chat_professores_id'
    ];

    public function users(){
        return $this->hasMany(User::class, 'escola_id','id');
    }

    public function turmas(){
        return $this->hasMany(Turma::class, 'escola_id','id')->get();
    }

    public function professores(){
        return User::where('tipo', 'professor')->where('escola_id', $this->id)->get();
    }
    public function alunos(){
        return User::where('tipo', 'aluno')->where('escola_id', $this->id)->get();
    }

    public function chatProfessores(){
        return $this->hasOne(Chat::class, 'id','chat_professores_id');
    }


}
