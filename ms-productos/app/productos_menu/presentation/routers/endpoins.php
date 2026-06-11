<?php
declare(strict_types=1);

use App\Presentation\Repositories\ProductosRepository;

return function ($app) {
    $repository = new ProductosRepository();


    $app->get('/productos', [$repository, 'listar']);

   
    $app->get('/productos/disponibles', [$repository, 'listarDisponibles']);

   
    $app->get('/productos/categoria/{id}', [$repository, 'listarPorCategoria']);

 
    $app->get('/productos/{id}', [$repository, 'obtener']);

   
    $app->post('/productos', [$repository, 'crear']);

  
    $app->put('/productos/{id}', [$repository, 'actualizar']);

    $app->delete('/productos/{id}', [$repository, 'eliminar']);
};
