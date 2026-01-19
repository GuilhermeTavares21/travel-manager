<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PedidoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome_solicitante' => $this->nome_solicitante,
            'destino' => $this->destino,
            'data_ida' => $this->data_ida?->format('Y-m-d'),
            'data_volta' => $this->data_volta?->format('Y-m-d'),
            'status' => $this->status,
            'usuario' => new UserResource($this->whenLoaded('user')),
            'criado_em' => $this->created_at?->format('Y-m-d H:i:s'),
            'atualizado_em' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
