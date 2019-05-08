<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatMensagens extends JsonResource
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
            'chat_id' => $this->chat_id,
            'mensagem' => $this->mensagem,
            'user' => $this->user()->first(),
        ];
    }


}
