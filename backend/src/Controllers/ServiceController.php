<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Services\ServiceService;

/**
 * Controller de Serviços.
 * Recebe requisições HTTP e delega para o ServiceService.
 */
class ServiceController
{
    private ServiceService $service;

    public function __construct()
    {
        $this->service = new ServiceService();
    }

    /**
     * GET /api/services
     * Lista serviços com paginação e filtros.
     */
    public function index(array $params, array $body): void
    {
        $filters = [
            'name' => $_GET['name'] ?? null,
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPageParam = (int) ($_GET['per_page'] ?? 10);
        $perPage = $perPageParam === -1 ? -1 : min(100, max(1, $perPageParam));

        $result = $this->service->list($filters, $page, $perPage);

        Response::paginated($result['items'], $result['total'], $page, $perPage);
    }

    /**
     * GET /api/services/{id}
     * Retorna detalhes de um serviço.
     */
    public function show(array $params, array $body): void
    {
        $service = $this->service->findById((int) $params['id']);
        Response::success($service);
    }

    /**
     * POST /api/services
     * Cria um novo serviço.
     */
    public function store(array $params, array $body): void
    {
        $service = $this->service->create($body);
        Response::success($service, 201, 'Serviço criado com sucesso.');
    }

    /**
     * PUT /api/services/{id}
     * Atualiza um serviço existente.
     */
    public function update(array $params, array $body): void
    {
        $service = $this->service->update((int) $params['id'], $body);
        Response::success($service, 200, 'Serviço atualizado com sucesso.');
    }

    /**
     * DELETE /api/services/{id}
     * Exclui um serviço.
     */
    public function destroy(array $params, array $body): void
    {
        $this->service->delete((int) $params['id']);
        Response::success(null, 200, 'Serviço excluído com sucesso.');
    }
}
