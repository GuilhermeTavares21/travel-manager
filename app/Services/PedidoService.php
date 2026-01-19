<?php

namespace App\Services;

use App\Enums\PedidoStatus;
use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Mail\PedidoStatusAlteradoMail;
use App\Models\Pedido;
use App\Repositories\Contracts\PedidoRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PedidoService
{
    public function __construct(
        protected PedidoRepositoryInterface $repository
    ) {}

    public function list(array $filters = [])
    {
        if (Auth::user()->is_admin) {
            return $this->repository->all($filters);
        }

        return $this->repository->allByUser(Auth::id(), $filters);
    }

    public function create(array $data): Pedido
    {
        $data['user_id'] = Auth::id();
        $data['nome_solicitante'] = Auth::user()->name;
        $data['status'] = PedidoStatus::SOLICITADO;

        return $this->repository->create($data);
    }

    public function show(int $id): Pedido
    {
        if (Auth::user()->is_admin) {
            return $this->repository->findById($id);
        }

        return $this->repository->findByIdAndUser($id, Auth::id());
    }

    public function updateStatus(int $id, string $status): Pedido
    {
        if (!Auth::user()->is_admin) {
            throw new UnauthorizedException('É necessário ser um administrador para alterar status de um pedido.');
        }

        $pedido = $this->repository->findById($id);

        if ($pedido->status === PedidoStatus::APROVADO->value && $status === PedidoStatus::CANCELADO->value) {
            throw new BusinessException('Não é possível cancelar um pedido já aprovado.');
        }

        $pedidoAtualizado = $this->repository->updateStatus($pedido, $status);

        $this->enviarEmail($pedidoAtualizado);

        return $pedidoAtualizado;
    }

    protected function enviarEmail(Pedido $pedido): void
    {
        try {
            $user = $pedido->user;

            if ($user && $user->email) {
                Mail::to($user->email)->queue(new PedidoStatusAlteradoMail($pedido, $pedido->status));
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar e-mail do pedido: ' . $e->getMessage());
        }
    }
}
