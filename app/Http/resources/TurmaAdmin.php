<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TurmaAdmin extends JsonResource
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
            'professor' => $this->professor()->select('id','nome', 'email', 'foto')->get(),
            'ciclo' => $this->ciclo,
            'numeroAlunos' => $this->alunos()->count(),
        ];
    }


}
