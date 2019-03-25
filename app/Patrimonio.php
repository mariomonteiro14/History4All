<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Patrimonio extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'nome', 'descricao', 'distrito', 'epoca', 'ciclo'
    ];

    public function imgagens(){
        return $this->hasMany(PatrimonioImagens::class, 'património_id','id');
    }
}