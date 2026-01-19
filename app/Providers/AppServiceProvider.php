<?php

namespace App\Providers;

use App\Repositories\Contracts\PedidoRepositoryInterface;
use App\Repositories\PedidoRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            PedidoRepositoryInterface::class,
            PedidoRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
