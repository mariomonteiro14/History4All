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
            'assunto' => $this->assunto,
            'user_email' => $this->show_email ? $this->user_email : null,
            'patrimonios' => $patrimonios->exists() ? ShortPatrimonio::collection($patrimonios->get()->pluck('patrimonio')) : [],
           // 'comentarios' => Comentario::collection($this->comentarios()->get()),
            'data_criado' => $this->created_at,
            'data_ultima_atualizacao' => $this->updated_at,
        ];
    }


}
