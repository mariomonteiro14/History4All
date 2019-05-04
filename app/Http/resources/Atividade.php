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
        $patrimonios = $this->atividadePatrimonios();
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'visibilidade' => $this->visibilidade,
            'numeroElementos' => $this->numeroElementos,
            'coordenador' => $this->coordenador(),
            'chat' => $this->chat()->first(),
            'data' => $this->data,
            'patrimonios' => $patrimonios->exists() ? Patrimonio::collection($patrimonios->get()->pluck('patrimonios')) : [],
            'participantes' => $participantes->exists() ? User::collection($participantes->get()->pluck('users')) : [],
            'ciclo' => $this->ciclo(),
            'epoca' => $this->epoca(),
            'imagem' => $this->patrimonioImagem()
        ];
    }


}
