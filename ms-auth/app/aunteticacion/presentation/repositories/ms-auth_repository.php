<?php
declare(strict_types=1);

namespace App\Presentation\Repositories;

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthRepository
{
    public function login(Request $request, Response $response): Response
    {
        $body          = $request->getParsedBody();
        $identificador = $body['identificador'] ?? '';
        $contrasena    = $body['contrasena']    ?? '';

        if (empty($identificador) || empty($contrasena)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'El identificador y la contraseña son requeridos.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $usuario = Usuario::where('usuario', $identificador)
                          ->orWhere('correo', $identificador)
                          ->first();
        if (!$usuario || $usuario->contrasena !== $contrasena) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Credenciales incorrectas.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
        if ($usuario->estado !== 'activo') {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Usuario inactivo. Contacte al administrador.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }
        $token = bin2hex(random_bytes(32));
        $usuario->token        = $token;
        $usuario->sesion_activa = true;
        $usuario->save();
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Inicio de sesión exitoso.',
            'data' => [
                'token'   => $token,
                'usuario' => [
                    'id'      => $usuario->id,
                    'nombre'  => $usuario->nombre,
                    'correo'  => $usuario->correo,
                    'usuario' => $usuario->usuario,
                    'rol'     => $usuario->rol,
                ]
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
    public function logout(Request $request, Response $response): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        $token      = str_replace('Bearer ', '', $authHeader);

        if (empty($token)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Token no proporcionado.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $usuario = Usuario::where('token', $token)
                          ->where('sesion_activa', true)
                          ->first();

        if (!$usuario) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Token inválido o sesión ya cerrada.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
        $usuario->token         = null;
        $usuario->sesion_activa = false;
        $usuario->save();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Sesión cerrada correctamente.'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
    public function validarSesion(Request $request, Response $response): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        $token      = str_replace('Bearer ', '', $authHeader);

        if (empty($token)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Token no proporcionado.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $usuario = Usuario::where('token', $token)
                          ->where('sesion_activa', true)
                          ->first();

        if (!$usuario) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Sesión inválida o expirada.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Sesión válida.',
            'data' => [
                'id'     => $usuario->id,
                'nombre' => $usuario->nombre,
                'rol'    => $usuario->rol,
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}