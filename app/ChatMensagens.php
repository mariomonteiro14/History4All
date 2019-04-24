<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
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

    public $timestamps = false;
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
