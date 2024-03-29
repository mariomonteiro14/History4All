<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Patrimonio extends JsonResource
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
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'distrito' => $this->distrito,
            'epoca' => $this->epoca,
            'ciclo' => $this->ciclo,
            'imagens' => $this->imagens()->pluck('foto')
        ];
    }


}
