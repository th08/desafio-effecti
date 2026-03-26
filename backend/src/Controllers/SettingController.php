<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Services\SettingService;

/**
 * Controller de Configurações.
 * Recebe requisições HTTP e delega para o SettingService.
 */
class SettingController
{
    private SettingService $service;

    public function __construct()
    {
        $this->service = new SettingService();
    }

    /**
     * GET /api/settings
     * Lista todas as configurações do sistema.
     */
    public function index(array $params, array $body): void
    {
        $settings = $this->service->list();
        Response::success($settings);
    }

    /**
     * PUT /api/settings/{key}
     * Atualiza uma configuração por chave.
     */
    public function update(array $params, array $body): void
    {
        $setting = $this->service->update($params['key'], $body);
        Response::success($setting, 200, 'Configuração atualizada com sucesso.');
    }
}
