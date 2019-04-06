<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
//use Illuminate\Foundation\Auth\User as Authenticatable;

class PatrimonioImagens extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'patrimonio_imagens';
    public $timestamps = false;

    protected $fillable = [
        'patrimonio_id','foto'
    ];

    public function patrimonio(){
        return $this->belongsTo(Patrimonio::class, 'patrimonio_id','id');
    }

}
