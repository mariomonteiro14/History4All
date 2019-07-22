<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
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
            'email' => $this->email,
            'foto' => $this->foto,
            'tipo' => $this->tipo,
            'escola' => $this->escola()->pluck('nome'),
            'turma' => $this->turma()->pluck('nome'),
            'turmas' => $this->turmas()->pluck('nome'),
        ];
    }


}
