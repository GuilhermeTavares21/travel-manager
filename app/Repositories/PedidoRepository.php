<?php

namespace App\Repositories;

use App\Exceptions\NotFoundException;
use App\Models\Pedido;
use App\Repositories\Contracts\PedidoRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class PedidoRepository implements PedidoRepositoryInterface
{
    protected int $perPage = 15;

    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = Pedido::with('user');

        $this->applyFilters($query, $filters);

        if (!empty($filters['usuario'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['usuario']}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
    }

    public function allByUser(int $userId, array $filters = []): LengthAwarePaginator
    {
        $query = Pedido::where('user_id', $userId);

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
    }

    public function findById(int $id): Pedido
    {
        $pedido = Pedido::with('user')->find($id);

        if (!$pedido) {
            throw new NotFoundException('Pedido não encontrado.');
        }

        return $pedido;
    }

    public function findByIdAndUser(int $id, int $userId): Pedido
    {
        $pedido = Pedido::where('user_id', $userId)->find($id);

        if (!$pedido) {
            throw new NotFoundException('Pedido não encontrado.');
        }

        return $pedido;
    }

    public function create(array $data): Pedido
    {
        return Pedido::create($data);
    }

    public function updateStatus(Pedido $pedido, string $status): Pedido
    {
        $pedido->update(['status' => $status]);

        return $pedido->fresh();
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['destino'])) {
            $query->where('destino', 'like', "%{$filters['destino']}%");
        }

        if (!empty($filters['inicio']) && !empty($filters['fim'])) {
            $query->whereBetween('data_ida', [$filters['inicio'], $filters['fim']]);
        }
    }
}
