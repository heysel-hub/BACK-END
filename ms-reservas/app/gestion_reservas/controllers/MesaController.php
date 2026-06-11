<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Mesa;
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
}

class MesaController extends AbstractController
{
    public function listar(): array
    {
        return Mesa::all()->toArray();
    }

    public function obtener(int $id): array
    {
        $mesa = Mesa::find($id);
        if (!$mesa) {
            throw new Exception('Mesa no encontrada.', 404);
        }
        return $mesa->toArray();
    }

    public function crear(array $data): array
    {
        $this->validarDatos($data);

        $existe = Mesa::where('numero', trim($data['numero']))->first();
        if ($existe) {
            throw new Exception('Ya existe una mesa con ese número.', 400);
        }

        $mesa = Mesa::create([
            'numero'    => trim($data['numero']),
            'capacidad' => (int) $data['capacidad'],
            'estado'    => 'disponible',
        ]);

        return $mesa->toArray();
    }

    public function actualizar(int $id, array $data): array
    {
        $mesa = Mesa::find($id);
        if (!$mesa) {
            throw new Exception('Mesa no encontrada.', 404);
        }

        if (isset($data['capacidad'])) {
            if ((int) $data['capacidad'] <= 0) {
                throw new Exception('La capacidad debe ser mayor a cero.', 400);
            }
            $mesa->capacidad = (int) $data['capacidad'];
        }

        if (isset($data['estado'])) {
            $mesa->estado = $data['estado'];
        }

        $mesa->save();
        return $mesa->toArray();
    }

    public function cambiarEstado(int $id, string $estado): array
    {
        $mesa = Mesa::find($id);
        if (!$mesa) {
            throw new Exception('Mesa no encontrada.', 404);
        }

        $mesa->estado = $estado;
        $mesa->save();
        return $mesa->toArray();
    }

    protected function validarDatos(array $data, bool $isUpdate = false): void
    {
        $this->validarRequerido($data, 'numero', 'El número de mesa es obligatorio.');
        $this->validarRequerido($data, 'capacidad', 'La capacidad es obligatoria.');

        if (isset($data['capacidad']) && (int) $data['capacidad'] <= 0) {
            throw new Exception('La capacidad debe ser mayor a cero.', 400);
        }
    }
}