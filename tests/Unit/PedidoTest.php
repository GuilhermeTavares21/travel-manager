<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

class PedidoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Gera um token JWT e define o header Authorization.
     */
    protected function actingAsJwt(User $user): self
    {
        $token = JWTAuth::fromUser($user);
        return $this->withHeader('Authorization', "Bearer {$token}");
    }

    public function test_user_can_create_pedido()
    {
        $user = User::factory()->create();

        $data = [
            'destino' => 'Onfly Jr',
            'data_ida' => '2025-12-01',
            'data_volta' => '2025-12-05',
        ];

        $response = $this->actingAsJwt($user)
            ->postJson('/api/pedidos', $data);

        $response->assertStatus(201)
                 ->assertJsonPath('data.destino', 'Onfly Jr');

        $this->assertDatabaseHas('pedidos', [
            'destino' => 'Onfly Jr',
            'user_id' => $user->id
        ]);
    }

    public function test_user_can_view_their_pedidos()
    {
        $user = User::factory()->create();
        $pedido = Pedido::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsJwt($user)
            ->getJson('/api/pedidos');

        $response->assertStatus(200)
                 ->assertJsonPath('data.0.id', $pedido->id);
    }

    public function test_admin_can_update_status()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $pedido = Pedido::factory()->create(['status' => 'solicitado']);

        $response = $this->actingAsJwt($admin)
            ->patchJson("/api/pedidos/{$pedido->id}/status", [
                'status' => 'aprovado'
            ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'aprovado');
    }

    public function test_non_admin_cannot_update_status()
    {
        $user = User::factory()->create(['is_admin' => 0]);
        $pedido = Pedido::factory()->create(['status' => 'solicitado']);

        $response = $this->actingAsJwt($user)
            ->patchJson("/api/pedidos/{$pedido->id}/status", [
                'status' => 'aprovado'
            ]);

        $response->assertStatus(403)
                 ->assertJsonFragment([
                     'error' => 'É necessário ser um administrador para alterar status de um pedido.'
                 ]);

        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'status' => 'solicitado'
        ]);
    }

    public function test_cannot_cancel_approved_pedido()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $pedido = Pedido::factory()->create(['status' => 'aprovado']);

        $response = $this->actingAsJwt($admin)
            ->patchJson("/api/pedidos/{$pedido->id}/status", [
                'status' => 'cancelado'
            ]);

        $response->assertStatus(400)
                 ->assertJsonFragment([
                     'error' => 'Não é possível cancelar um pedido já aprovado.'
                 ]);

        $this->assertDatabaseHas('pedidos', [
            'id' => $pedido->id,
            'status' => 'aprovado'
        ]);
    }

    public function test_user_cannot_view_other_user_pedido()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $pedido = Pedido::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAsJwt($user2)
            ->getJson("/api/pedidos/{$pedido->id}");

        $response->assertStatus(404);
    }

    public function test_admin_can_view_any_user_pedido()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $user = User::factory()->create();
        $pedido = Pedido::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsJwt($admin)
            ->getJson("/api/pedidos/{$pedido->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $pedido->id);
    }

    public function test_user_can_view_own_pedido()
    {
        $user = User::factory()->create();
        $pedido = Pedido::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsJwt($user)
            ->getJson("/api/pedidos/{$pedido->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $pedido->id);
    }

    public function test_filter_pedidos_by_status()
    {
        $user = User::factory()->create();
        Pedido::factory()->create(['user_id' => $user->id, 'status' => 'solicitado']);
        Pedido::factory()->create(['user_id' => $user->id, 'status' => 'aprovado']);
        Pedido::factory()->create(['user_id' => $user->id, 'status' => 'cancelado']);

        $response = $this->actingAsJwt($user)
            ->getJson('/api/pedidos?status=aprovado');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('aprovado', $data[0]['status']);
    }

    public function test_filter_pedidos_by_destino()
    {
        $user = User::factory()->create();
        Pedido::factory()->create(['user_id' => $user->id, 'destino' => 'São Paulo']);
        Pedido::factory()->create(['user_id' => $user->id, 'destino' => 'Rio de Janeiro']);
        Pedido::factory()->create(['user_id' => $user->id, 'destino' => 'Belo Horizonte']);

        $response = $this->actingAsJwt($user)
            ->getJson('/api/pedidos?destino=Paulo');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('São Paulo', $data[0]['destino']);
    }

    public function test_filter_pedidos_by_periodo()
    {
        $user = User::factory()->create();
        Pedido::factory()->create([
            'user_id' => $user->id,
            'data_ida' => '2025-06-01',
            'data_volta' => '2025-06-05'
        ]);
        Pedido::factory()->create([
            'user_id' => $user->id,
            'data_ida' => '2025-07-15',
            'data_volta' => '2025-07-20'
        ]);
        Pedido::factory()->create([
            'user_id' => $user->id,
            'data_ida' => '2025-08-01',
            'data_volta' => '2025-08-10'
        ]);

        $response = $this->actingAsJwt($user)
            ->getJson('/api/pedidos?inicio=2025-07-01&fim=2025-07-31');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('2025-07-15', $data[0]['data_ida']);
    }

    public function test_invalid_status_returns_validation_error()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $pedido = Pedido::factory()->create(['status' => 'solicitado']);

        $response = $this->actingAsJwt($admin)
            ->patchJson("/api/pedidos/{$pedido->id}/status", [
                'status' => 'invalido'
            ]);

        $response->assertStatus(422);
    }

    public function test_user_only_sees_own_pedidos_in_list()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Pedido::factory()->create(['user_id' => $user1->id, 'destino' => 'Destino User1']);
        Pedido::factory()->create(['user_id' => $user2->id, 'destino' => 'Destino User2']);

        $response = $this->actingAsJwt($user1)
            ->getJson('/api/pedidos');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Destino User1', $data[0]['destino']);
    }

    public function test_admin_sees_all_pedidos_in_list()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Pedido::factory()->create(['user_id' => $user1->id]);
        Pedido::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAsJwt($admin)
            ->getJson('/api/pedidos');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_list_returns_paginated_response()
    {
        $user = User::factory()->create();
        Pedido::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAsJwt($user)
            ->getJson('/api/pedidos');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'links',
                     'meta' => ['current_page', 'last_page', 'per_page', 'total']
                 ]);
    }
}
