<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Setting;

/**
 * Repositório para operações de acesso a dados de Configurações.
 */
class SettingRepository
{
    /**
     * Lista todas as configurações.
     */
    public function findAll(): array
    {
        return Setting::orderBy('key')->get()->toArray();
    }

    /**
     * Busca configuração por chave.
     */
    public function findByKey(string $key): ?Setting
    {
        return Setting::byKey($key)->first();
    }

    /**
     * Atualiza ou cria uma configuração.
     */
    public function upsert(string $key, string $value, ?string $description = null, string $type = 'string'): Setting
    {
        $setting = Setting::byKey($key)->first();

        if ($setting) {
            $setting->update(['value' => $value]);
            return $setting->fresh();
        }

        return Setting::create([
            'key'         => $key,
            'value'       => $value,
            'description' => $description,
            'type'        => $type,
        ]);
    }
}
