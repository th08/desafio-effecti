<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Contract;

/**
 * Repositório para operações de acesso a dados de Contratos.
 * Utiliza eager loading para otimizar queries de relações.
 */
class ContractRepository
{
    /**
     * Lista contratos com paginação, filtros e eager loading.
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $query = Contract::with(['client', 'items.service']);

        // Filtro por cliente
        if (!empty($filters['client_id'])) {
            $query->byClient((int) $filters['client_id']);
        }

        // Filtro por status
        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        // Filtro por nome do cliente (busca parcial)
        if (!empty($filters['client_name'])) {
            $query->whereHas('client', function ($q) use ($filters) {
                $q->where('name', 'ILIKE', '%' . $filters['client_name'] . '%');
            });
        }

        $total = $query->count();
        $query->orderBy('created_at', 'desc');

        if ($perPage !== -1) {
            $query->offset(($page - 1) * $perPage)
                ->limit($perPage);
        }

        $items = $query->get()->toArray();

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    /**
     * Busca contrato por ID com todas as relações.
     */
    public function findById(int $id): ?Contract
    {
        return Contract::with(['client', 'items.service', 'history'])->find($id);
    }

    /**
     * Cria um novo contrato.
     */
    public function create(array $data): Contract
    {
        return Contract::create($data);
    }

    /**
     * Atualiza um contrato existente.
     */
    public function update(Contract $contract, array $data): Contract
    {
        $contract->update($data);
        return $contract->fresh(['client', 'items.service', 'history']);
    }

    /**
     * Exclui um contrato.
     */
    public function delete(Contract $contract): bool
    {
        return (bool) $contract->delete();
    }

    /**
     * Verifica se existe qualquer contrato vinculado ao cliente.
     */
    public function existsByClientId(int $clientId): bool
    {
        return Contract::where('client_id', $clientId)->exists();
    }
}
