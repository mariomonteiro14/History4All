<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Escola extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'nome', 'distrito'
    ];

    public function users(){
        return $this->hasMany(User::class, 'escola_id','id');
    }

    public function turmas(){
        return $this->hasMany(Turma::class, 'escola_id','id');
    }

}