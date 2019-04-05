<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Chat extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'id', 'atividade_id', 'privado', 'assunto'
    ];

    public function atividade(){
        return $this->belongsTo(Atividade::class, 'atividade_id','id');
    }

    public function chatMensagens(){
        return $this->hasMany(ChatMensagens::class, 'chat_id','id');
    }

}