<?php

namespace App\Http\Controllers;

use App\Http\Requests\PedidoRequest;
use App\Http\Resources\PedidoCollection;
use App\Http\Resources\PedidoResource;
use App\Services\PedidoService;
use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PedidoController extends Controller
{
    public function __construct(
        protected PedidoService $service
    ) {}

    public function index(Request $request)
    {
        $pedidos = $this->service->list($request->all());

        return new PedidoCollection($pedidos);
    }

    public function store(PedidoRequest $request)
    {
        $pedido = $this->service->create($request->validated());

        return (new PedidoResource($pedido))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        $pedido = $this->service->show($id);

        return new PedidoResource($pedido);
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:aprovado,cancelado'
        ], [
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser "aprovado" ou "cancelado".'
        ]);

        $pedido = $this->service->updateStatus($id, $request->status);

        return new PedidoResource($pedido);
    }
}
