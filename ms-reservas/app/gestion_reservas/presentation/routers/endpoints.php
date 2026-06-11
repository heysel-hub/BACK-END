<?php
declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Presentation\Repositories\MesaRepository;
use App\Presentation\Repositories\ReservaRepository;

return function (App $app): void {
    $mesaRepository   = new MesaRepository();
    $reservaRepository = new ReservaRepository();

    $app->group('/mesas', function (RouteCollectorProxy $group) use ($mesaRepository) {
        $group->get('', [$mesaRepository, 'listar']);
        $group->post('', [$mesaRepository, 'crear']);
        $group->get('/{id}', [$mesaRepository, 'obtener']);
        $group->put('/{id}', [$mesaRepository, 'actualizar']);
        $group->patch('/{id}/estado', [$mesaRepository, 'cambiarEstado']);
    });

    $app->group('/reservas', function (RouteCollectorProxy $group) use ($reservaRepository) {
        $group->get('', [$reservaRepository, 'listar']);
        $group->post('', [$reservaRepository, 'crear']);
        $group->get('/fecha/{fecha}', [$reservaRepository, 'listarPorFecha']);
        $group->get('/cliente/{nombre}', [$reservaRepository, 'listarPorCliente']);
        $group->get('/estado/{estado}', [$reservaRepository, 'listarPorEstado']);
        $group->get('/{id}', [$reservaRepository, 'obtener']);
        $group->put('/{id}', [$reservaRepository, 'actualizar']);
        $group->patch('/{id}/cancelar', [$reservaRepository, 'cancelar']);
    });
};