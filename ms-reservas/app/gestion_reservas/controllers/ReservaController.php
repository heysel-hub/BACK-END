<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Mesa;
use App\Models\Reserva;
use Exception;

class ReservaController extends AbstractController
{
    public function listar(): array
    {
        return Reserva::with('mesa')->get()->toArray();
    }

    public function listarPorFecha(string $fecha): array
    {
        return Reserva::with('mesa')
            ->where('fecha', $fecha)
            ->get()
            ->toArray();
    }

    public function listarPorCliente(string $nombre): array
    {
        return Reserva::with('mesa')
            ->where('nombre_cliente', 'like', '%' . $nombre . '%')
            ->get()
            ->toArray();
    }

    public function listarPorEstado(string $estado): array
    {
        return Reserva::with('mesa')
            ->where('estado', $estado)
            ->get()
            ->toArray();
    }

    public function obtener(int $id): array
    {
        $reserva = Reserva::with('mesa')->find($id);
        if (!$reserva) {
            throw new Exception('Reserva no encontrada.', 404);
        }
        return $reserva->toArray();
    }

    public function crear(array $data): array
    {
        $this->validarDatos($data);

        if ($data['fecha'] < date('Y-m-d')) {
            throw new Exception('No se permiten reservas en fechas pasadas.', 400);
        }

        $mesa = Mesa::find($data['mesa_id']);
        if (!$mesa) {
            throw new Exception('Mesa no encontrada.', 404);
        }

        if ($mesa->estaFueraDeServicio()) {
            throw new Exception('No se puede reservar una mesa fuera de servicio.', 400);
        }

        if ((int) $data['cantidad_personas'] > $mesa->capacidad) {
            throw new Exception('La cantidad de personas supera la capacidad de la mesa.', 400);
        }

        $dobleReserva = Reserva::where('mesa_id', $data['mesa_id'])
            ->where('fecha', $data['fecha'])
            ->where('hora', $data['hora'])
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->first();

        if ($dobleReserva) {
            throw new Exception('Ya existe una reserva para esa mesa en esa fecha y hora.', 400);
        }

        $reserva = Reserva::create([
            'nombre_cliente'    => trim($data['nombre_cliente']),
            'telefono_cliente'  => trim($data['telefono_cliente']),
            'cantidad_personas' => (int) $data['cantidad_personas'],
            'fecha'             => $data['fecha'],
            'hora'              => $data['hora'],
            'observaciones'     => $data['observaciones'] ?? null,
            'estado'            => 'pendiente',
            'mesa_id'           => $data['mesa_id'],
        ]);

        $mesa->estado = 'reservada';
        $mesa->save();

        return $reserva->toArray();
    }

    public function actualizar(int $id, array $data): array
    {
        $reserva = Reserva::find($id);
        if (!$reserva) {
            throw new Exception('Reserva no encontrada.', 404);
        }

        if (isset($data['fecha']) && $data['fecha'] < date('Y-m-d')) {
            throw new Exception('No se permiten reservas en fechas pasadas.', 400);
        }

        $reserva->fecha             = $data['fecha']             ?? $reserva->fecha;
        $reserva->hora              = $data['hora']              ?? $reserva->hora;
        $reserva->mesa_id           = $data['mesa_id']           ?? $reserva->mesa_id;
        $reserva->cantidad_personas = $data['cantidad_personas'] ?? $reserva->cantidad_personas;
        $reserva->observaciones     = $data['observaciones']     ?? $reserva->observaciones;
        $reserva->save();

        return $reserva->toArray();
    }

    public function cancelar(int $id): array
    {
        $reserva = Reserva::find($id);
        if (!$reserva) {
            throw new Exception('Reserva no encontrada.', 404);
        }

        if ($reserva->estaCancelada()) {
            throw new Exception('La reserva ya está cancelada.', 400);
        }

        $reserva->estado = 'cancelada';
        $reserva->save();

        $mesa = Mesa::find($reserva->mesa_id);
        if ($mesa) {
            $mesa->estado = 'disponible';
            $mesa->save();
        }

        return $reserva->toArray();
    }

    protected function validarDatos(array $data, bool $isUpdate = false): void
    {
        $this->validarRequerido($data, 'nombre_cliente', 'El nombre del cliente es obligatorio.');
        $this->validarRequerido($data, 'telefono_cliente', 'El teléfono es obligatorio.');
        $this->validarRequerido($data, 'cantidad_personas', 'La cantidad de personas es obligatoria.');
        $this->validarRequerido($data, 'fecha', 'La fecha es obligatoria.');
        $this->validarRequerido($data, 'hora', 'La hora es obligatoria.');
        $this->validarRequerido($data, 'mesa_id', 'La mesa es obligatoria.');
    }
}