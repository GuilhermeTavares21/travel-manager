<?php

namespace App\Models;

use App\Enums\PedidoStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nome_solicitante',
        'destino',
        'data_ida',
        'data_volta',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'data_ida' => 'date:Y-m-d',
            'data_volta' => 'date:Y-m-d',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
