<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Repositories\SettingRepository;
use App\Validators\SettingValidator;

/**
 * Service de Configurações.
 * Gerencia as configurações do sistema (regras de desconto, etc.).
 */
class SettingService
{
    private SettingRepository $settingRepository;

    public function __construct()
    {
        $this->settingRepository = new SettingRepository();
    }

    /**
     * Lista todas as configurações.
     */
    public function list(): array
    {
        $settings = $this->settingRepository->findAll();

        // Adiciona o valor tipado em cada configuração
        foreach ($settings as &$setting) {
            $model = $this->settingRepository->findByKey($setting['key']);
            if ($model) {
                $setting['typed_value'] = $model->getTypedValue();
            }
        }

        return $settings;
    }

    /**
     * Atualiza uma configuração por chave.
     */
    public function update(string $key, array $data): array
    {
        $setting = $this->settingRepository->findByKey($key);
        if (!$setting) {
            throw new NotFoundException("Configuração '{$key}' não encontrada.");
        }

        // Valida o valor de acordo com o tipo
        SettingValidator::validateUpdate($data, $setting->type);

        // Converte arrays/objetos para JSON string se necessário
        $value = $data['value'];
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        $updated = $this->settingRepository->upsert($key, (string) $value);
        $result = $updated->toArray();
        $result['typed_value'] = $updated->getTypedValue();

        return $result;
    }
}
