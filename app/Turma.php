<?php

namespace App;

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
        'nome', 'escola_id'
    ];

    public function users(){
        return $this->hasMany(User::class, 'turma_id','id');
    }

    public function escola(){
        return $this->belongsTo(Escola::class, 'escola_id','id');
    }

}
