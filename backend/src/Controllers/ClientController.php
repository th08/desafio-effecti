<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Services\ClientService;

/**
 * Controller de Clientes.
 * Recebe requisições HTTP e delega para o ClientService.
 */
class ClientController
{
    private ClientService $service;

    public function __construct()
    {
        $this->service = new ClientService();
    }

    /**
     * GET /api/clients
     * Lista clientes com paginação e filtros.
     */
    public function index(array $params, array $body): void
    {
        $filters = [
            'name'     => $_GET['name'] ?? null,
            'document' => $_GET['document'] ?? null,
            'status'   => $_GET['status'] ?? null,
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPageParam = (int) ($_GET['per_page'] ?? 10);
        $perPage = $perPageParam === -1 ? -1 : min(100, max(1, $perPageParam));

        $result = $this->service->list($filters, $page, $perPage);

        Response::paginated($result['items'], $result['total'], $page, $perPage);
    }

    /**
     * GET /api/clients/{id}
     * Retorna detalhes de um cliente.
     */
    public function show(array $params, array $body): void
    {
        $client = $this->service->findById((int) $params['id']);
        Response::success($client);
    }

    /**
     * POST /api/clients
     * Cria um novo cliente.
     */
    public function store(array $params, array $body): void
    {
        $client = $this->service->create($body);
        Response::success($client, 201, 'Cliente criado com sucesso.');
    }

    /**
     * PUT /api/clients/{id}
     * Atualiza um cliente existente.
     */
    public function update(array $params, array $body): void
    {
        $client = $this->service->update((int) $params['id'], $body);
        Response::success($client, 200, 'Cliente atualizado com sucesso.');
    }

    /**
     * DELETE /api/clients/{id}
     * Exclui um cliente.
     */
    public function destroy(array $params, array $body): void
    {
        $this->service->delete((int) $params['id']);
        Response::success(null, 200, 'Cliente excluído com sucesso.');
    }
}
