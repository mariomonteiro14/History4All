<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Atividade extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $participantes = $this->atividadeParticipantes();
        if ($participantes->exists()){
            $participantes = $participantes->get()->pluck('user');
            for($i = 0; $i < count($participantes); $i++) {
                if ($participantes[$i] == null) {
                    unset($participantes[$i]);
                }
            }
        }else{
            $participantes = null;
        }
        $patrimonios = $this->atividadePatrimonios();
        $turmasParticipantes = $this->turmasParticipantes();
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'visibilidade' => $this->visibilidade,
            'numeroElementos' => $this->numeroElementos,
            'coordenador' => new User($this->coordenador()),
            'chat' => $this->chat()->first(),
            'data' => $this->data,
            'patrimonios' => $patrimonios->exists() ? ShortPatrimonio::collection($patrimonios->get()->pluck('patrimonio')) : [],
            'participantes' => $participantes ? ShortUser::collection($participantes) : [],
            'turmas_participantes' => $turmasParticipantes->exists() ? Turma::collection($turmasParticipantes->get()) : [],
            'ciclo' => $this->ciclo(),
            'epoca' => $this->epoca(),
            'dataInicio' => $this->dataInicio,
            'dataFinal' => $this->dataFinal,
            'testemunhos' => $this->testemunhos()->exists() ? $this->testemunhos()->get() : [],
        ];
    }


}
