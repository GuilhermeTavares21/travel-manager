<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $service;

    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }

    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'is_admin' => 'boolean'
            ]);

            $user = $this->service->register($data);

            return response()->json($user, 201);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            return response()->json(['message' => $firstError], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao registrar usuário', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Erro ao registrar usuário'], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $login = $this->service->login($data);

            return response()->json($login, 200);
        } catch (ValidationException $e) {
            return response()->json(['message   ' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        try {
            $this->service->logout($request->user());
            return response()->json(['message' => 'Logout realizado.']);
        } catch (\Exception $e) {
            Log::error('Erro ao realizar logout', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Erro ao realizar logout'], 500);
        }
    }
}
