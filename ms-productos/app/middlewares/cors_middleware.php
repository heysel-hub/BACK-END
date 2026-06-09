<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;

return function ($app) {
    $app->options('/{routes:.+}', fn($req, $res) => $res);

    $app->add(function (Request $request, $handler) {
        $response = $handler->handle($request);
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

        if ($request->getMethod() === 'OPTIONS') {
            return $response->withStatus(200);
        }

        return $response;
    });
};