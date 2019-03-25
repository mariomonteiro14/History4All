<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PatrimonioImagens extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'patrimonio_imagens';

    protected $fillable = [
        'foto'
    ];

    public function patrimonio(){
        return $this->belongsTo(Patrimonio::class, 'património_id','id');
    }

}