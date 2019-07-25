<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ForumPatrimonios extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $timestamps = false;
    protected $fillable = [
        'forum_id', 'patrimonio_id'
    ];

    public function patrimonio(){
        return $this->belongsTo(Patrimonio::class, 'patrimonio_id', 'id');
    }

    public function forum(){
        return $this->belongsTo(Forum::class, 'forum_id','id');
    }

}
