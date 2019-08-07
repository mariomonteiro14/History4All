<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Comentario extends JsonResource
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
            'forum_id' => $this->forum_id,
            'user_email' => $this->user_email,
            'comentario' => $this->comentario,
            'likes' => $this->likes,
            'dislikes' => $this->dislikes,
            'data' => $this->updated_at ? $this->updated_at->format('d/m/Y') : $this->created_at->format('d/m/Y'),
        ];
    }


}
