<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Registro de novos usuários
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Cria o token inicial do Sanctum para o usuário
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuário registrado com sucesso!',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Autenticação / Login de usuários existentes
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Tenta autenticar com as credenciais fornecidas
        if (!Auth::attempt($validated)) {
            return response()->json([
                'message' => 'Credenciais inválidas. Verifique seu e-mail e senha.'
            ], 401);
        }

        $user = User::where('email', $validated['email'])->firstOrFail();

        // Revoga tokens antigos se quiser permitir apenas um login simultâneo por dispositivo
        $user->tokens()->delete();

        // Gera o novo token de acesso
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso!',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    /**
     * Retorna o usuário autenticado.
     */
    public function user(Request $request)
    {
        return response()->json($request->user(), 200);
    }

    /**
     * Revoga o token atual (Logout).
     */
    public function logout(Request $request)
    {
        // Remove o token que está sendo usado na requisição atual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso e token revogado.'
        ], 200);
    }
}
