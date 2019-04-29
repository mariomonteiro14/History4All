<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Atividade extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $timestamps = false;
    protected $dates = ['data'];
    protected $fillable = [
        'nome', 'tipo', 'descricao', 'privado', 'coordenador', 'chat_id', 'data'
    ];

    public function coordenador(){
        return $this->belongsTo(User::class, 'coordenador','id')
            ->select('id' ,'nome', 'email', 'foto')->first();
    }

    public function chat(){
        return $this->hasOne(Chat::class, 'id', 'chat_id');
    }

    public function atividadeParticipantes(){
        return $this->hasMany(AtividadeParticipantes::class, 'atividade_id','id')
            ->select('user_id' ,'estado');
    }

    public function atividadePatrimonios(){
        return $this->hasMany(AtividadePatrimonios::class, 'atividade_id','id')
            ->pluck('patrimonio_id');
    }

    public function ciclo(){
        return $this->join('atividade_patrimonios', 'atividade_id', 'id')
            ->join('patrimonios as p', 'patrimonio_id','p.id')
            ->where('atividade_id', $this->id)->distinct('ciclo')->orderBy('ciclo')->pluck('ciclo');
    }

    public function epoca(){
        return $this->join('atividade_patrimonios', 'atividade_id', 'id')
            ->join('patrimonios as p', 'patrimonio_id','p.id')
            ->where('atividade_id', $this->id)->distinct('epoca')->orderBy('epoca')->pluck('epoca');
    }

    public function patrimonioImagem(){
        return PatrimonioImagens::where('patrimonio_id', $this->atividadePatrimonios()->first())->pluck('foto')->first();
    }
}
