<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model de Histórico de Alterações do Contrato.
 *
 * @property int         $id
 * @property int         $contract_id
 * @property string      $action
 * @property string|null $description
 * @property array|null  $changed_data
 * @property string      $created_at
 */
class ContractHistory extends Model
{
    protected $table = 'contract_history';

    // Apenas created_at (sem updated_at)
    public $timestamps = false;

    protected $fillable = [
        'contract_id',
        'action',
        'description',
        'changed_data',
        'created_at',
    ];

    protected $casts = [
        'changed_data' => 'array',
    ];

    /**
     * Relação: histórico pertence a um contrato.
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
