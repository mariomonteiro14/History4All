<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;

class Escola extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $timestamps = false;

    protected $fillable = [
        'id', 'nome', 'distrito', 'chat_professores_id'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'escola_id', 'id');
    }

    public function turmas()
    {
        return $this->hasMany(Turma::class, 'escola_id', 'id')->get();
    }

    public function professores()
    {
        return User::where('tipo', 'professor')->where('escola_id', $this->id)->get();
    }

    public function alunos()
    {
        return User::where('tipo', 'aluno')->where('escola_id', $this->id)->get();
    }

    public function atividades()
    {
        return Atividade::select('atividades.*')
            ->join('users', 'users.id', 'coordenador')
            ->where('users.escola_id', $this->id)
            ->distinct();
    }

    public function atividadesConcluidas()
    {
        return Atividade::select('atividades.*')
            ->join('users', 'users.id', 'coordenador')
            ->where('users.escola_id', $this->id)
            ->where(
                function ($q) {
                    $q->where(function ($p) {
                        $dt = Carbon::now()->toDateString();
                        $p->whereNull('dataFinal')
                            ->where('dataInicio', '<', $dt);
                    })
                        ->orWhere(function ($p) {
                            $dt = Carbon::now()->toDateString();
                            $p->whereNotNull('dataFinal')
                                ->where('dataFinal', '<', $dt);
                        });

            })
            ->distinct();
    }

    public function atividadesADecorrer()
    {
        return Atividade::select('atividades.*')
            ->join('users', 'users.id', 'coordenador')
            ->where('users.escola_id', $this->id)
            ->where(
                function ($q) {
                    $q->where(function ($p) {
                        $dt = Carbon::now()->toDateString();
                        $p->whereNull('dataFinal')
                            ->where('dataInicio', $dt);
                    })
                        ->orWhere(function ($p) {
                            $dt = Carbon::now()->toDateString();
                            $p->whereNotNull('dataFinal')
                                ->where('dataFinal', '>=', $dt)
                                ->where('dataInicio', '<=', $dt);
                        });

                })
            ->distinct();
    }

    public function atividadesAgendadas()
    {

        $dt = Carbon::now()->toDateString();
        return Atividade::select('atividades.*')
            ->join('users', 'users.id', 'coordenador')
            ->where('users.escola_id', $this->id)
            ->where('dataInicio', '>', $dt)
            ->distinct();
    }



    public function chatProfessores()
    {
        return $this->hasOne(Chat::class, 'id', 'chat_professores_id');
    }


}
