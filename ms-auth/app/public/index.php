<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config/conexion.php';

$cors      = require __DIR__ . '/../middlewares/cors_middleware.php';
$endpoints = require __DIR__ . '/../autenticacion/presentation/routers/endpoints.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$cors($app);
$endpoints($app);

$app->run();