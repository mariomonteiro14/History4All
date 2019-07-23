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
    protected $dates = ['data'];
    protected $fillable = [
        'user_id', 'mensagem', 'de', 'data', 'nova', 'link'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
