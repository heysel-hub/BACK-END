<?php
use Slim\Factory\AppFactory;
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config/conexion.php';
$app = AppFactory::create();
$cors = require __DIR__ . '/../middlewares/cors_middleware.php';
$cors($app);
$endpoints = require __DIR__ . '/../aunteticacion/presentation/routers/endpoins.php';
$endpoints($app);
$app->run();

