<?php
declare(strict_types=1);

namespace App\Presentation\Repositories;

use App\Controllers\ProductoController;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductosRepository extends AbstractRepository
{
    private ProductoController $controller;

    public function __construct()
    {
        $this->controller = new ProductoController();
    }

    public function listar(Request $request, Response $response): Response
    {
        try {
            $result = $this->controller->listar();

            return $this->json($response, [
                'success' => true,
                'message' => 'Productos obtenidos correctamente.',
                'data'    => $result,
            ], 200);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }

    public function listarPorCategoria(Request $request, Response $response, array $args): Response
    {
        try {
            $result = $this->controller->listarPorCategoria((int) $args['id']);

            return $this->json($response, [
                'success' => true,
                'message' => 'Productos obtenidos correctamente.',
                'data'    => $result,
            ], 200);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }

    public function listarDisponibles(Request $request, Response $response): Response
    {
        try {
            $result = $this->controller->listarDisponibles();

            return $this->json($response, [
                'success' => true,
                'message' => 'Productos disponibles obtenidos correctamente.',
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
                'message' => 'Producto obtenido correctamente.',
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
                'message' => 'Producto creado correctamente.',
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
                'message' => 'Producto actualizado correctamente.',
                'data'    => $result,
            ], 200);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }

    public function eliminar(Request $request, Response $response, array $args): Response
    {
        try {
            $this->controller->eliminar((int) $args['id']);

            return $this->json($response, [
                'success' => true,
                'message' => 'Producto eliminado correctamente.',
            ], 200);

        } catch (Exception $e) {
            return $this->jsonError($response, $e);
        }
    }
}