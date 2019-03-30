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
    public $timestamps = false;

    protected $fillable = [
        'nome', 'descricao', 'distrito', 'epoca', 'ciclo'
    ];

    public function imagens(){
        return $this->hasMany(PatrimonioImagens::class, 'patrimonio_id','id');
    }

    public function primeiraImagem(){
        return PatrimonioImagens::where('patrimonio_id', $this->id)->pluck('foto')->first();
    }
}