<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Forum extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $patrimonios = $this->patrimonios();

        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'user_email' => $this->show_email ? $this->user_email : null,
            'patrimonios' => $patrimonios->exists() ? ShortPatrimonio::collection($patrimonios->get()->pluck('patrimonio')) : [],
            'numComentarios' => $this->comentarios()->count(),
            'data_criado' => $this->created_at,
            'data_ultima_atualizacao' => $this->updated_at,
            'data_ultima_atualizacao_comentario' => count($this->comentarios()->get()) > 0 ?
                $this->comentarios()->orderBy('updated_at')->pluck('updated_at')->first() : $this->updated_at,
        ];
    }


}
