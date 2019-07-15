<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EscolaEstatisticas extends JsonResource
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
            'nome' => $this->nome,
            'distrito' => $this->distrito,
            'numeroTurmas' => $this->turmas()->count(),
            'numeroAlunos' => $this->alunos()->count(),
            'numeroProfessores' => $this->professores()->count(),
            'numeroAtividades' => $this->atividades()->count(),
            'numeroAtividadesConcluidas' => $this->atividadesConcluidas()->count(),
            'numeroAtividadesADecorrer' => $this->atividadesADecorrer()->count(),
            'numeroAtividadesAgendadas' => $this->atividadesAgendadas()->count(),
        ];
    }


}
