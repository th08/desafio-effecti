<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model de Contrato.
 *
 * @property int         $id
 * @property int         $client_id
 * @property string      $start_date
 * @property string|null $end_date
 * @property string      $status
 * @property string      $created_at
 * @property string      $updated_at
 */
class Contract extends Model
{
    protected $table = 'contracts';

    protected $fillable = [
        'client_id',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date'   => 'date:Y-m-d',
    ];

    /**
     * Relação: contrato pertence a um cliente.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relação: contrato possui vários itens.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ContractItem::class);
    }

    /**
     * Relação: contrato possui histórico de alterações.
     */
    public function history(): HasMany
    {
        return $this->hasMany(ContractHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Scope: filtra contratos ativos.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }

    /**
     * Scope: filtra por cliente.
     */
    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope: filtra por status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Verifica se o contrato está ativo.
     */
    public function isActive(): bool
    {
        return $this->status === 'A';
    }

    /**
     * Verifica se o contrato está cancelado.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'C';
    }
}
