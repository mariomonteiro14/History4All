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
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'descricao' => $this->descricao,
            'privado' => $this->privado,
            'coordenador' => $this->coordenador(),
            'chat' => $this->chat()->get(),
            'data' => $this->data,
            'patrimonios' => $this->atividadePatrimonios(),
            'participantes' => $this->atividadeParticipantes()->get(),
            'ciclo' => $this->ciclo(),
            'epoca' => $this->epoca(),
            'imagem' => $this->patrimonioImagem()
        ];
    }


}
