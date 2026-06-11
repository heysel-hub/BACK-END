<?php
declare(strict_types=1);

namespace App\Presentation\Repositories;

use App\Controllers\MesaController;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MesaRepository extends AbstractRepository
{
    private MesaController $controller;

    public function __construct()
    {
        $this->controller = new MesaController();
    }

    public function listar(Request $request, Response $response): Response
    {
        try {
            $result = $this->controller->listar();

            return $this->json($response, [
                'success' => true,
                'message' => 'Mesas obtenidas correctamente.',
                'data'    => $result,
            ], 200);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }

    public function obtener(Request $request, Response $response, array $args): Response
    {
        try {
            $result = $this->controller->obtener((int) $args['id']);

            return $this->json($response, [
                'success' => true,
                'message' => 'Mesa obtenida correctamente.',
                'data'    => $result,
            ], 200);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }

    public function crear(Request $request, Response $response): Response
    {
        try {
            $data   = $this->obtenerDatos($request);
            $result = $this->controller->crear($data);

            return $this->json($response, [
                'success' => true,
                'message' => 'Mesa creada correctamente.',
                'data'    => $result,
            ], 201);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }

    public function actualizar(Request $request, Response $response, array $args): Response
    {
        try {
            $data   = $this->obtenerDatos($request);
            $result = $this->controller->actualizar((int) $args['id'], $data);

            return $this->json($response, [
                'success' => true,
                'message' => 'Mesa actualizada correctamente.',
                'data'    => $result,
            ], 200);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }

    public function cambiarEstado(Request $request, Response $response, array $args): Response
    {
        try {
            $data   = $this->obtenerDatos($request);
            $result = $this->controller->cambiarEstado((int) $args['id'], $data['estado']);

            return $this->json($response, [
                'success' => true,
                'message' => 'Estado de mesa actualizado correctamente.',
                'data'    => $result,
            ], 200);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }
}