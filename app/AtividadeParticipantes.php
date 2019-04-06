<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AtividadeParticipantes extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'atividade_id', 'user_id', 'estado'
    ];

    public function atividade(){
        return $this->belongsTo(Atividade::class, 'atividade_id','id');
    }

    public function users(){
        return $this->hasMany(User::class, 'id', 'user_id');
    }

}
