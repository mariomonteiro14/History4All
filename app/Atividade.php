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
    protected $fillable = [
        'titulo', 'descricao', 'tipo', 'numeroElementos', 'visibilidade', 'coordenador', 'chat_id', 'dataInicio', 'dataFinal'
    ];

    public function coordenador(){
        return $this->belongsTo(User::class, 'coordenador','id')->first();
    }

    public function chat(){
        return $this->hasOne(Chat::class, 'id', 'chat_id')->with('chatMensagens');
    }

    public function atividadeParticipantes(){
        return $this->hasMany(AtividadeParticipantes::class, 'atividade_id','id')->with('user');
    }

    public function atividadePatrimonios(){
        return $this->hasMany(AtividadePatrimonios::class, 'atividade_id','id')->with('patrimonio');
    }

    public function testemunhos(){
        return $this->hasMany(AtividadeTestemunhos::class, 'atividade_id','id');
    }

    public function ciclo(){
        return $this->join('atividade_patrimonios', 'atividade_id', 'id')
            ->join('patrimonios as p', 'patrimonio_id','p.id')
            ->where('atividade_id', $this->id)->distinct('ciclo')->pluck('ciclo');
    }

    public function epoca(){
        return $this->join('atividade_patrimonios', 'atividade_id', 'id')
            ->join('patrimonios as p', 'patrimonio_id','p.id')
            ->where('atividade_id', $this->id)->distinct('epoca')->pluck('epoca');
    }

    public function imagem(){
        return PatrimonioImagens::where('patrimonio_id', AtividadePatrimonios::where('atividade_id', $this->id)
            ->pluck('patrimonio_id')->first())->pluck('foto')->first();
    }
}
