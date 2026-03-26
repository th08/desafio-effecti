<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model de Item do Contrato.
 *
 * @property int   $id
 * @property int   $contract_id
 * @property int   $service_id
 * @property int   $quantity
 * @property float $unit_value
 * @property string $created_at
 * @property string $updated_at
 */
class ContractItem extends Model
{
    protected $table = 'contract_items';

    protected $fillable = [
        'contract_id',
        'service_id',
        'quantity',
        'unit_value',
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'unit_value' => 'decimal:2',
    ];

    /**
     * Atributos calculados adicionados automaticamente na serialização.
     */
    protected $appends = ['subtotal'];

    /**
     * Relação: item pertence a um contrato.
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Relação: item referencia um serviço.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Accessor: calcula o subtotal do item (quantidade * valor unitário).
     */
    public function getSubtotalAttribute(): string
    {
        return number_format($this->quantity * (float) $this->unit_value, 2, '.', '');
    }
}
