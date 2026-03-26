<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Services\ContractService;

/**
 * Controller de Contratos.
 * Recebe requisições HTTP e delega para o ContractService.
 */
class ContractController
{
    private ContractService $service;

    public function __construct()
    {
        $this->service = new ContractService();
    }

    /**
     * GET /api/contracts
     * Lista contratos com paginação, filtros, itens e valor total.
     */
    public function index(array $params, array $body): void
    {
        $filters = [
            'client_id'   => $_GET['client_id'] ?? null,
            'status'      => $_GET['status'] ?? null,
            'client_name' => $_GET['client_name'] ?? null,
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPageParam = (int) ($_GET['per_page'] ?? 10);
        $perPage = $perPageParam === -1 ? -1 : min(100, max(1, $perPageParam));

        $result = $this->service->list($filters, $page, $perPage);

        Response::paginated($result['items'], $result['total'], $page, $perPage);
    }

    /**
     * GET /api/contracts/{id}
     * Retorna detalhes de um contrato com itens, valor calculado e histórico.
     */
    public function show(array $params, array $body): void
    {
        $contract = $this->service->findById((int) $params['id']);
        Response::success($contract);
    }

    /**
     * POST /api/contracts
     * Cria um novo contrato (com itens opcionais).
     */
    public function store(array $params, array $body): void
    {
        $contract = $this->service->create($body);
        Response::success($contract, 201, 'Contrato criado com sucesso.');
    }

    /**
     * PUT /api/contracts/{id}
     * Atualiza dados de um contrato existente.
     */
    public function update(array $params, array $body): void
    {
        $contract = $this->service->update((int) $params['id'], $body);
        Response::success($contract, 200, 'Contrato atualizado com sucesso.');
    }

    /**
     * DELETE /api/contracts/{id}
     * Exclui um contrato.
     */
    public function destroy(array $params, array $body): void
    {
        $this->service->delete((int) $params['id']);
        Response::success(null, 200, 'Contrato excluído com sucesso.');
    }

    /**
     * POST /api/contracts/{id}/items
     * Adiciona um serviço ao contrato.
     */
    public function addItem(array $params, array $body): void
    {
        $contract = $this->service->addItem((int) $params['id'], $body);
        Response::success($contract, 201, 'Item adicionado ao contrato com sucesso.');
    }

    /**
     * PUT /api/contracts/{id}/items/{itemId}
     * Atualiza um item do contrato.
     */
    public function updateItem(array $params, array $body): void
    {
        $contract = $this->service->updateItem(
            (int) $params['id'],
            (int) $params['itemId'],
            $body
        );
        Response::success($contract, 200, 'Item do contrato atualizado com sucesso.');
    }

    /**
     * DELETE /api/contracts/{id}/items/{itemId}
     * Remove um item do contrato.
     */
    public function removeItem(array $params, array $body): void
    {
        $contract = $this->service->removeItem(
            (int) $params['id'],
            (int) $params['itemId']
        );
        Response::success($contract, 200, 'Item removido do contrato com sucesso.');
    }

    /**
     * PATCH /api/contracts/{id}/cancel
     * Cancela um contrato.
     */
    public function cancel(array $params, array $body): void
    {
        $contract = $this->service->cancel((int) $params['id']);
        Response::success($contract, 200, 'Contrato cancelado com sucesso.');
    }

    /**
     * GET /api/contracts/{id}/history
     * Retorna o histórico de alterações de um contrato.
     */
    public function history(array $params, array $body): void
    {
        $history = $this->service->getHistory((int) $params['id']);
        Response::success($history);
    }
}
