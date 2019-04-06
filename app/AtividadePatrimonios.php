<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AtividadePatrimonios extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'atividade_id', 'patrimonio_id'
    ];

    public function patrimonios(){
        return $this->belongsTo(Patrimonio::class, 'patrimonio_id', 'id');
    }

    public function atividades(){
        return $this->belongsTo(Atividade::class, 'atividade_id','id');
    }

}
