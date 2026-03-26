<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model de Cliente.
 *
 * @property int    $id
 * @property string $name
 * @property string $document
 * @property string $email
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 */
class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'name',
        'document',
        'email',
        'status',
    ];

    /**
     * Relação: um cliente pode ter vários contratos.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Scope: filtra apenas clientes ativos.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }

    /**
     * Scope: filtra por status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Verifica se o cliente está ativo.
     */
    public function isActive(): bool
    {
        return $this->status === 'A';
    }
}
