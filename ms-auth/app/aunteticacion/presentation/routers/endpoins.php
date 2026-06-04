<?php

use Slim\App;
use App\Presentation\Repositories\AuthRepository;

return function (App $app) {

    // POST http://localhost:8001/login  → sin token
    $app->post('/login', [AuthRepository::class, 'login']);

    // POST http://localhost:8001/logout → requiere token en header
    $app->post('/logout', [AuthRepository::class, 'logout']);

    // GET  http://localhost:8001/validar-sesion → requiere token en header
    $app->get('/validar-sesion', [AuthRepository::class, 'validarSesion']);
};