<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comentario extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    use SoftDeletes;

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        'id','forum_id', 'comentario', 'user_email', 'likes', ' dislikes'
    ];

    public function forum()
    {
        return $this->belongsTo(Forum::class, 'forum_id', 'id');
    }



}
