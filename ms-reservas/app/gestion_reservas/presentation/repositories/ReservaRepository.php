<?php
declare(strict_types=1);

namespace App\Presentation\Repositories;

use App\Controllers\ReservaController;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReservaRepository extends AbstractRepository
{
    private ReservaController $controller;

    public function __construct()
    {
        $this->controller = new ReservaController();
    }

    public function listar(Request $request, Response $response): Response
    {
        try {
            $result = $this->controller->listar();

            return $this->json($response, [
                'success' => true,
                'message' => 'Reservas obtenidas correctamente.',
                'data'    => $result,
            ], 200);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }

    public function listarPorFecha(Request $request, Response $response, array $args): Response
    {
        try {
            $result = $this->controller->listarPorFecha($args['fecha']);

            return $this->json($response, [
                'success' => true,
                'message' => 'Reservas obtenidas correctamente.',
                'data'    => $result,
            ], 200);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }

    public function listarPorCliente(Request $request, Response $response, array $args): Response
    {
        try {
            $result = $this->controller->listarPorCliente($args['nombre']);

            return $this->json($response, [
                'success' => true,
                'message' => 'Reservas obtenidas correctamente.',
                'data'    => $result,
            ], 200);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }

    public function listarPorEstado(Request $request, Response $response, array $args): Response
    {
        try {
            $result = $this->controller->listarPorEstado($args['estado']);

            return $this->json($response, [
                'success' => true,
                'message' => 'Reservas obtenidas correctamente.',
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
                'message' => 'Reserva obtenida correctamente.',
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
                'message' => 'Reserva creada correctamente.',
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
                'message' => 'Reserva actualizada correctamente.',
                'data'    => $result,
            ], 200);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }

    public function cancelar(Request $request, Response $response, array $args): Response
    {
        try {
            $result = $this->controller->cancelar((int) $args['id']);

            return $this->json($response, [
                'success' => true,
                'message' => 'Reserva cancelada correctamente.',
                'data'    => $result,
            ], 200);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }
}