<?php
declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Presentation\Repositories\ProductosRepository;

return function (App $app): void {
    $repository = new ProductosRepository();

    $app->group('/productos', function (RouteCollectorProxy $group) use ($repository) {
        $group->get('', [$repository, 'listar']);
        $group->post('', [$repository, 'crear']);
        $group->get('/disponibles', [$repository, 'listarDisponibles']);
        $group->get('/categoria/{id}', [$repository, 'listarPorCategoria']);
        $group->get('/{id}', [$repository, 'obtener']);
        $group->put('/{id}', [$repository, 'actualizar']);
        $group->delete('/{id}', [$repository, 'eliminar']);
    });
};