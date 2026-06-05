<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Usuario;
use Exception;

abstract class AbstractController
{
    abstract protected function validarDatos(array $data, bool $isUpdate = false): void;

    protected function validarRequerido(array $data, string $campo, string $mensaje): void
    {
        if (!isset($data[$campo]) || trim((string) $data[$campo]) === '') {
            throw new Exception($mensaje, 400);
        }
    }

    protected function limpiarTexto(?string $valor): ?string
    {
        if ($valor === null)
            return null;
        $valor = trim($valor);
        return $valor === '' ? null : $valor;
    }
}

class UsuarioController extends AbstractController
{
    public function iniciarSesion(array $data): array
    {
        $this->validarDatos($data);

        $usuario = Usuario::where('usuario', trim($data['identificador']))
            ->orWhere('correo', trim($data['identificador']))
            ->first();

        if (!$usuario || trim($data['contrasena']) !== $usuario->contrasena) {
            throw new Exception('Credenciales incorrectas.', 401);
        }

        if (!$usuario->estaActivo()) {
            throw new Exception('Usuario inactivo. Contacte al administrador.', 403);
        }

        $usuario->token = bin2hex(random_bytes(32));
        $usuario->sesion_activa = true;
        $usuario->save();

        return [
            'token' => $usuario->token,
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'correo' => $usuario->correo,
                'usuario' => $usuario->usuario,
                'rol' => $usuario->rol,
            ],
        ];
    }

    public function cerrarSesion(string $token): void
    {
        $usuario = Usuario::where('token', $token)
            ->where('sesion_activa', true)
            ->first();

        if (!$usuario) {
            throw new Exception('Token inválido o sesión ya cerrada.', 401);
        }

        $usuario->token = null;
        $usuario->sesion_activa = false;
        $usuario->save();
    }

    public function validarSesion(string $token): array
    {
        $usuario = Usuario::where('token', $token)
            ->where('sesion_activa', true)
            ->first();

        if (!$usuario) {
            throw new Exception('Sesión inválida o expirada.', 401);
        }

        return [
            'id' => $usuario->id,
            'nombre' => $usuario->nombre,
            'rol' => $usuario->rol,
        ];
    }

    protected function validarDatos(array $data, bool $isUpdate = false): void
    {
        $this->validarRequerido($data, 'identificador', 'El usuario o correo es obligatorio.');
        $this->validarRequerido($data, 'contrasena', 'La contraseña es obligatoria.');
    }
}