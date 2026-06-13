<?php
declare(strict_types=1);

use Slim\App;
use App\Presentation\Repositories\AuthRepository;

return function (App $app): void {
    $repository = new AuthRepository();

$app->get('/', function ($request, $response) {
    $response->getBody()->write(json_encode([
        'status' => 'ok',
        'mensaje' => 'Microservicio de autenticación funcionando'
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});

    $app->post('/login', [$repository, 'login']);
    $app->post('/logout', [$repository, 'logout']);
    $app->get('/validar', [$repository, 'validarSesion']);
};