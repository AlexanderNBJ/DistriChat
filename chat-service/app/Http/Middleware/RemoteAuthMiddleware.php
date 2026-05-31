<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;

class RemoteAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extrai o Bearer Token do cabeçalho 'Authorization'
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Token de autenticação não fornecido.'
            ], 401);
        }

        try {
            // Faz uma chamada HTTP síncrona para o microsserviço de autenticação
            $response = Http::withToken($token)
                ->acceptJson()
                ->get('http://127.0.0.1:8000/api/user');

            // Se o auth-service recusar o token (401, 403, etc), barramos aqui
            if ($response->failed()) {
                // Se o serviço explicitamente recusar as credenciais
                if ($response->status() === 401) {
                    return response()->json([
                        'message' => 'Token inválido ou expirado no serviço de autenticação.'
                    ], 401);
                }

                // Se o serviço retornar 500, 502, etc., consideramos o serviço instável
                return response()->json([
                    'message' => 'Serviço de autenticação indisponível no momento.'
                ], 503);
            }

            // Se deu bom, injetamos os dados do usuário na requisição atual.
            // Assim, qualquer Controller do chat-service pode acessar via $request->user_data
            $request->merge(['user_data' => $response->json()]);

            return $next($request);

        } catch (\Exception $e) {
            // Tratamento caso o auth-service esteja fora do ar
            return response()->json([
                'message' => 'Serviço de autenticação indisponível no momento.'
            ], 503);
        }
    }
}
