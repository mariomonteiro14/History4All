<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Atividade extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $dates = ['data'];
    protected $fillable = [
        'nome', 'tipo', 'descricao', 'privado', 'coordenador', 'chat_id', 'data'
    ];

    public function coordenador(){
        return $this->belongsTo(User::class, 'coordenador','id');
    }

    public function chat(){
        return $this->hasOne(Chat::class, 'id', 'chat_id');
    }

    public function atividadeParticipantes(){
        return $this->hasMany(AtividadeParticipantes::class, 'atividade_id','id');
    }

    public function atividadePatrimonios(){
        return $this->hasMany(AtividadePatrimonios::class, 'patrimonio_id','id');
    }
}
