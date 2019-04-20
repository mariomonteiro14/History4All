<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Turma extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'nome', 'escola_id', 'professor_id'
    ];

    public function alunos(){
        return $this->hasMany(User::class, 'turma_id','id')->get();
    }

    public function escola(){
        return $this->belongsTo(Escola::class, 'escola_id','id');
    }

    public function professor(){
        return $this->belongsTo(User::class, 'professor_id','id')->get();
    }

}
