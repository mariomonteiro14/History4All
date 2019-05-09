<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Notificacao extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'notificacoes';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'mensagem', 'nova'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
