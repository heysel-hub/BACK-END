<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
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

class PedidoController extends AbstractController
{
    public function listar(): array
    {
        return Pedido::with('detalles')->get()->toArray();
    }

    public function listarPorEstado(string $estado): array
    {
        return Pedido::with('detalles')
            ->where('estado', $estado)
            ->get()
            ->toArray();
    }

    public function obtener(int $id): array
    {
        $pedido = Pedido::with('detalles')->find($id);
        if (!$pedido) {
            throw new Exception('Pedido no encontrado.', 404);
        }
        return $pedido->toArray();
    }

    public function crear(array $data): array
    {
        $this->validarDatos($data);

        // Validar que venga con detalles
        if (empty($data['detalles']) || !is_array($data['detalles'])) {
            throw new Exception('El pedido debe tener al menos un producto.', 400);
        }

        // Validar cada detalle
        foreach ($data['detalles'] as $detalle) {
            if (!isset($detalle['cantidad']) || (int) $detalle['cantidad'] < 1) {
                throw new Exception('La cantidad de cada producto debe ser mayor a cero.', 400);
            }
            if (empty($detalle['nombre_producto'])) {
                throw new Exception('El nombre del producto es obligatorio.', 400);
            }
            if (!isset($detalle['precio_unitario']) || (float) $detalle['precio_unitario'] <= 0) {
                throw new Exception('El precio unitario debe ser mayor a cero.', 400);
            }
        }

        // Calcular subtotal y total automáticamente
        $subtotal = 0;
        foreach ($data['detalles'] as $detalle) {
            $subtotal += (float) $detalle['precio_unitario'] * (int) $detalle['cantidad'];
        }

        $pedido = Pedido::create([
            'mesa_id'  => $data['mesa_id'],
            'fecha'    => $data['fecha'],
            'hora'     => $data['hora'],
            'subtotal' => $subtotal,
            'total'    => $subtotal,
            'estado'   => 'pendiente',
        ]);

        // Crear los detalles
        foreach ($data['detalles'] as $detalle) {
            $subtotalDetalle = (float) $detalle['precio_unitario'] * (int) $detalle['cantidad'];
            DetallePedido::create([
                'pedido_id'       => $pedido->id,
                'producto_id'     => $detalle['producto_id'],
                'nombre_producto' => trim($detalle['nombre_producto']),
                'cantidad'        => (int) $detalle['cantidad'],
                'precio_unitario' => (float) $detalle['precio_unitario'],
                'subtotal'        => $subtotalDetalle,
            ]);
        }

        return Pedido::with('detalles')->find($pedido->id)->toArray();
    }

    public function agregarDetalle(int $pedidoId, array $data): array
    {
        $pedido = Pedido::with('detalles')->find($pedidoId);
        if (!$pedido) {
            throw new Exception('Pedido no encontrado.', 404);
        }

        if (!isset($data['cantidad']) || (int) $data['cantidad'] < 1) {
            throw new Exception('La cantidad debe ser mayor a cero.', 400);
        }

        $subtotalDetalle = (float) $data['precio_unitario'] * (int) $data['cantidad'];

        DetallePedido::create([
            'pedido_id'       => $pedidoId,
            'producto_id'     => $data['producto_id'],
            'nombre_producto' => trim($data['nombre_producto']),
            'cantidad'        => (int) $data['cantidad'],
            'precio_unitario' => (float) $data['precio_unitario'],
            'subtotal'        => $subtotalDetalle,
        ]);

        // Recalcular totales
        $this->recalcularTotales($pedido);

        return Pedido::with('detalles')->find($pedidoId)->toArray();
    }

    public function actualizarDetalle(int $pedidoId, int $detalleId, array $data): array
    {
        $pedido = Pedido::with('detalles')->find($pedidoId);
        if (!$pedido) {
            throw new Exception('Pedido no encontrado.', 404);
        }

        $detalle = DetallePedido::where('id', $detalleId)
            ->where('pedido_id', $pedidoId)
            ->first();
        if (!$detalle) {
            throw new Exception('Detalle no encontrado.', 404);
        }

        if (!isset($data['cantidad']) || (int) $data['cantidad'] < 1) {
            throw new Exception('La cantidad debe ser mayor a cero.', 400);
        }

        $detalle->cantidad        = (int) $data['cantidad'];
        $detalle->precio_unitario = (float) $data['precio_unitario'];
        $detalle->subtotal        = (float) $data['precio_unitario'] * (int) $data['cantidad'];
        $detalle->save();

        // Recalcular totales
        $this->recalcularTotales($pedido);

        return Pedido::with('detalles')->find($pedidoId)->toArray();
    }

    public function eliminarDetalle(int $pedidoId, int $detalleId): array
    {
        $pedido = Pedido::with('detalles')->find($pedidoId);
        if (!$pedido) {
            throw new Exception('Pedido no encontrado.', 404);
        }

        $detalle = DetallePedido::where('id', $detalleId)
            ->where('pedido_id', $pedidoId)
            ->first();
        if (!$detalle) {
            throw new Exception('Detalle no encontrado.', 404);
        }

        // No permitir dejar el pedido vacío
        if ($pedido->detalles->count() <= 1) {
            throw new Exception('No se puede eliminar el único producto del pedido.', 400);
        }

        $detalle->delete();

        // Recalcular totales
        $pedido->refresh();
        $this->recalcularTotales($pedido);

        return Pedido::with('detalles')->find($pedidoId)->toArray();
    }

    public function actualizarEstado(int $id, array $data): array
    {
        $pedido = Pedido::find($id);
        if (!$pedido) {
            throw new Exception('Pedido no encontrado.', 404);
        }

        $this->validarRequerido($data, 'estado', 'El estado es obligatorio.');
        $pedido->estado = $data['estado'];
        $pedido->save();

        return $pedido->toArray();
    }

    public function eliminar(int $id): void
    {
        $pedido = Pedido::find($id);
        if (!$pedido) {
            throw new Exception('Pedido no encontrado.', 404);
        }
        DetallePedido::where('pedido_id', $id)->delete();

        $pedido->delete();
    }

    private function recalcularTotales(Pedido $pedido): void
    {
        $pedido->refresh();
        $subtotal = $pedido->detalles->sum('subtotal');
        $pedido->subtotal = $subtotal;
        $pedido->total    = $subtotal;
        $pedido->save();
    }

    protected function validarDatos(array $data, bool $isUpdate = false): void
    {
        $this->validarRequerido($data, 'mesa_id', 'La mesa es obligatoria.');
        $this->validarRequerido($data, 'fecha', 'La fecha es obligatoria.');
        $this->validarRequerido($data, 'hora', 'La hora es obligatoria.');
    }
}
