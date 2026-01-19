<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Exception;

class AuthService
{
    protected $repository;

    public function __construct(AuthRepository $repository)
    {
        $this->repository = $repository;
    }

    public function register(array $data)
    {
        // Simulando um tempo de espera para exibir os loadings no front
        sleep(1.5);

        $data['password'] = Hash::make($data['password']);
        $user = $this->repository->createUser($data);

        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function login(array $data)
    {
        if (! $token = JWTAuth::attempt($data)) {
            throw new Exception('Credenciais inválidas.');
        }

        return [
            'user' => auth()->user(),
            'token' => $token
        ];
    }

    public function logout($user)
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }
}
