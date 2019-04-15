<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Escola extends JsonResource
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
            'distrito' => $this->distrito,
            'turmas' => $this->turmas()->pluck('nome'),
        ];
    }


}
