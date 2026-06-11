<?php
declare(strict_types=1);

namespace App\Presentation\Repositories;

use App\Controllers\UsuarioController;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthRepository extends AbstractRepository
{
    private UsuarioController $controller;

    public function __construct()
    {
        $this->controller = new UsuarioController();
    }

    public function login(Request $request, Response $response): Response
    {
        try {
            $data   = $this->obtenerDatos($request);
            $result = $this->controller->iniciarSesion($data);

            return $this->json($response, [
                'success' => true,
                'message' => 'Inicio de sesión exitoso.',
                'data'    => $result,
            ], 200);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }

    public function logout(Request $request, Response $response): Response
    {
        try {
            $token = $this->extraerToken($request);
            $this->controller->cerrarSesion($token);

            return $this->json($response, [
                'success' => true,
                'message' => 'Sesión cerrada correctamente.',
            ], 200);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }

    public function validarSesion(Request $request, Response $response): Response
    {
        try {
            $token  = $this->extraerToken($request);
            $result = $this->controller->validarSesion($token);

            return $this->json($response, [
                'success' => true,
                'message' => 'Sesión válida.',
                'data'    => $result,
            ], 200);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }
}