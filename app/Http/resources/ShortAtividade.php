<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShortAtividade extends JsonResource
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
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'tipo' => $this->tipo,
            'coordenador' => new ShortUser($this->coordenador()),
            'ciclo' => $this->ciclo(),
            'epoca' => $this->epoca(),
            'imagem' => $this->imagem()
        ];
    }


}
