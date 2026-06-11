<?php
declare(strict_types=1);

use Slim\App;
use App\Presentation\Repositories\AuthRepository;

return function (App $app): void {
    $repository = new AuthRepository();

    $app->post('/login', [$repository, 'login']);
    $app->post('/logout', [$repository, 'logout']);
    $app->get('/validar', [$repository, 'validarSesion']);
};