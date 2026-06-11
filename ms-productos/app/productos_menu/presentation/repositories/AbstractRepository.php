<?php
declare(strict_types=1);

namespace App\Presentation\Repositories;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class AbstractRepository
{
    protected function json(Response $response, $data, int $status = 200): Response
    {
        $payload = is_string($data)
            ? $data
            : json_encode($data, JSON_UNESCAPED_UNICODE);

        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    protected function jsonError(Response $response, Exception $exception): Response
    {
        $status = 400;

        if ($exception->getCode() === 404 || $exception->getCode() === 1) {
            $status = 404;
        } elseif ($exception->getCode() === 401) {
            $status = 401;
        } elseif ($exception->getCode() === 403) {
            $status = 403;
        }

        return $this->json($response, [
            'success' => false,
            'message' => $exception->getMessage(),
        ], $status);
    }

    protected function obtenerDatos(Request $request): array
    {
        $data = $request->getParsedBody();

        if (is_array($data)) {
            return $data;
        }

        $rawBody = (string) $request->getBody();
        $decoded = json_decode($rawBody, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function extraerToken(Request $request): string
    {
        $header = $request->getHeaderLine('Authorization');
        $token  = trim(str_replace('Bearer', '', $header));

        if (empty($token)) {
            throw new \Exception('Token no proporcionado.', 401);
        }

        return $token;
    }
}