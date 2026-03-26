<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model de Serviço.
 *
 * @property int    $id
 * @property string $name
 * @property float  $base_monthly_value
 * @property string $created_at
 * @property string $updated_at
 */
class Service extends Model
{
    protected $table = 'services';

    protected $fillable = [
        'name',
        'base_monthly_value',
    ];

    protected $casts = [
        'base_monthly_value' => 'decimal:2',
    ];

    /**
     * Relação: um serviço pode estar em vários itens de contrato.
     */
    public function contractItems(): HasMany
    {
        return $this->hasMany(ContractItem::class);
    }
}
