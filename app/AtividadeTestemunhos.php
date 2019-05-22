<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AtividadeTestemunhos extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $timestamps = false;

    protected $fillable = [
        'atividade_id', 'user_id', 'rate', 'texto'
    ];

    public function atividade(){
        return $this->belongsTo(Atividade::class, 'atividade_id','id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id')->select('id', 'nome', 'foto');
    }

}
