<?php
declare(strict_types=1);

use App\Presentation\Repositories\ProductosRepository;

return function ($app) {
    $repository = new ProductosRepository();

    // Listar todos los productos
    $app->get('/productos', [$repository, 'listar']);

    // Listar productos disponibles
    $app->get('/productos/disponibles', [$repository, 'listarDisponibles']);

    // Listar productos por categoría
    $app->get('/productos/categoria/{id}', [$repository, 'listarPorCategoria']);

    // Obtener un producto por ID
    $app->get('/productos/{id}', [$repository, 'obtener']);

    // Crear un producto
    $app->post('/productos', [$repository, 'crear']);

    // Actualizar un producto
    $app->put('/productos/{id}', [$repository, 'actualizar']);

    // Eliminar un producto
    $app->delete('/productos/{id}', [$repository, 'eliminar']);
};