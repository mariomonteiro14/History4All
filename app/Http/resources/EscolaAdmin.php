<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EscolaAdmin extends JsonResource
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
            'turmas' => TurmaAdmin::collection($this->turmas()),
            'professores' => User::collection($this->professores()),
        ];
    }


}
