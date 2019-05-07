<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Chat extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $timestamps = false;
    protected $fillable = [
        'privado', 'assunto'
    ];

    public function atividade(){
        return $this->belongsTo(Atividade::class, 'atividade_id','id');
    }

    public function chatMensagens(){
        return $this->hasMany(ChatMensagens::class, 'chat_id','id')->with('user');
    }

}
