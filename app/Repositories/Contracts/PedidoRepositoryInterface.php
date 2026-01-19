<?php

namespace App\Repositories\Contracts;

use App\Models\Pedido;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PedidoRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator;

    public function allByUser(int $userId, array $filters = []): LengthAwarePaginator;

    public function findById(int $id): Pedido;

    public function findByIdAndUser(int $id, int $userId): Pedido;

    public function create(array $data): Pedido;

    public function updateStatus(Pedido $pedido, string $status): Pedido;
}
