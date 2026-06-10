<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Mesa;

class Reserva extends Model
{
    protected $table = 'reservas';

    protected $fillable = [
        'nombre_cliente',
        'telefono_cliente',
        'cantidad_personas',
        'fecha',
        'hora',
        'observaciones',
        'estado',
        'mesa_id',
    ];

    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }

    public function estaCancelada(): bool
    {
        return $this->estado === 'cancelada';
    }
}