<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model de Configuração do Sistema.
 *
 * @property int    $id
 * @property string $key
 * @property string $value
 * @property string $description
 * @property string $type
 * @property string $created_at
 * @property string $updated_at
 */
class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'description',
        'type',
    ];

    /**
     * Scope: busca por chave.
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Retorna o valor convertido de acordo com o tipo.
     */
    public function getTypedValue(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'number'  => is_numeric($this->value) ? (float) $this->value : 0,
            'json'    => json_decode($this->value, true),
            default   => $this->value,
        };
    }
}
