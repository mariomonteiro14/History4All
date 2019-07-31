<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoricoGerenciadorCodigos extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'historico_gerenciador_codigos';
    public $timestamps = false;
    protected $fillable = [
        'email', 'codigo', 'data'
    ];

}
