<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Forum extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    use SoftDeletes;

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        'id', 'titulo', 'descricao', 'user_email', 'show_email'
    ];

    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'forum_id', 'id');
    }

    public function patrimonios()
    {
         return $this->hasMany(ForumPatrimonios::class, 'forum_id','id')->with('patrimonio');
    }


}
