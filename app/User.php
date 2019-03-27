<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    use SoftDeletes;
    protected $dates = ['email_verified_at', 'created_at', 'updated_at', 'deleted_at'];
    protected $fillable = [
        'nome', 'email', 'password', 'profile_photo', 'tipo', 'escola_id', 'turma_id'
    ];

    public function escola(){
        return $this->belongsTo(Escola::class, 'escola_id','id');
    }

    public function turma(){
        return $this->belongsTo(Turma::class, 'turma_id','id');
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
