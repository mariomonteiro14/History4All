<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ChatMensagens extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'chat_id', 'user_id', 'mensagem'
    ];

    public function chat(){
        return $this->belongsTo(Chat::class, 'chat_id','id');
    }

    public function user(){
        return $this->hasMany(User::class, 'user_id','id');
    }

}
