<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class PedidoFactory extends Factory
{
    protected $model = \App\Models\Pedido::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'nome_solicitante' => $this->faker->name(),
            'destino' => $this->faker->city(),
            'data_ida' => $this->faker->date(),
            'data_volta' => $this->faker->date(),
            'status' => 'solicitado',
        ];
    }
}
