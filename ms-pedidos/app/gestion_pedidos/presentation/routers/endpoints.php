<?php
declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Presentation\Repositories\PedidosRepository;

return function (App $app): void {
    $repository = new PedidosRepository();

    $app->get('/', function ($request, $response) {
    $response->getBody()->write(json_encode([
        'status' => 'ok',
        'microservicio' => 'Gestión de Pedidos funcionando'
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});

    $app->group('/pedidos', function (RouteCollectorProxy $group) use ($repository) {
        $group->get('', [$repository, 'listar']);
        $group->post('', [$repository, 'crear']);
        $group->get('/estado/{estado}', [$repository, 'listarPorEstado']);
        $group->get('/{id}', [$repository, 'obtener']);
        $group->patch('/{id}/estado', [$repository, 'actualizarEstado']);
        $group->delete('/{id}', [$repository, 'eliminar']);

        $group->post('/{id}/detalles', [$repository, 'agregarDetalle']);
        $group->put('/{id}/detalles/{detalle_id}', [$repository, 'actualizarDetalle']);
        $group->delete('/{id}/detalles/{detalle_id}', [$repository, 'eliminarDetalle']);
    });
};