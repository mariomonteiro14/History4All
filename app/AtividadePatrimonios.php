<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
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

    public $timestamps = false;
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
