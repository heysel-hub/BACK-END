<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
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

class ProductoController extends AbstractController
{
    public function listar(): array
    {
        return Producto::with('categoria')->get()->toArray();
    }

    public function listarPorCategoria(int $categoriaId): array
    {
        return Producto::with('categoria')
            ->where('categoria_id', $categoriaId)
            ->get()
            ->toArray();
    }

    public function listarDisponibles(): array
    {
        return Producto::with('categoria')
            ->where('disponible', true)
            ->get()
            ->toArray();
    }

    public function obtener(int $id): array
    {
        $producto = Producto::with('categoria')->find($id);
        if (!$producto) {
            throw new Exception('Producto no encontrado.', 404);
        }
        return $producto->toArray();
    }

    public function crear(array $data): array
    {
        $this->validarDatos($data);

       
        $existe = Producto::where('nombre', trim($data['nombre']))->first();
        if ($existe) {
            throw new Exception('Ya existe un producto con ese nombre.', 400);
        }

        $producto = Producto::create([
            'nombre'       => trim($data['nombre']),
            'descripcion'  => $data['descripcion'] ?? null,
            'precio'       => $data['precio'],
            'disponible'   => $data['disponible'] ?? true,
            'categoria_id' => $data['categoria_id'],
        ]);

        return $producto->toArray();
    }

    public function actualizar(int $id, array $data): array
    {
        $producto = Producto::find($id);
        if (!$producto) {
            throw new Exception('Producto no encontrado.', 404);
        }

        $this->validarDatos($data, true);

      
        $existe = Producto::where('nombre', trim($data['nombre']))
            ->where('id', '!=', $id)
            ->first();
        if ($existe) {
            throw new Exception('Ya existe un producto con ese nombre.', 400);
        }

        $producto->nombre       = trim($data['nombre']);
        $producto->descripcion  = $data['descripcion'] ?? null;
        $producto->precio       = $data['precio'];
        $producto->disponible   = $data['disponible'] ?? $producto->disponible;
        $producto->categoria_id = $data['categoria_id'];
        $producto->save();

        return $producto->toArray();
    }

    public function eliminar(int $id): void
    {
        $producto = Producto::find($id);
        if (!$producto) {
            throw new Exception('Producto no encontrado.', 404);
        }
        $producto->delete();
    }

    protected function validarDatos(array $data, bool $isUpdate = false): void
    {
        $this->validarRequerido($data, 'nombre', 'El nombre es obligatorio.');
        $this->validarRequerido($data, 'precio', 'El precio es obligatorio.');
        $this->validarRequerido($data, 'categoria_id', 'La categoría es obligatoria.');

        if (isset($data['precio']) && (float) $data['precio'] <= 0) {
            throw new Exception('El precio debe ser mayor a cero.', 400);
        }
    }
}