<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ContractItem;

/**
 * Repositório para operações de acesso a dados de Itens do Contrato.
 */
class ContractItemRepository
{
    /**
     * Busca todos os itens de um contrato.
     */
    public function findByContractId(int $contractId): array
    {
        return ContractItem::with('service')
            ->where('contract_id', $contractId)
            ->get()
            ->toArray();
    }

    /**
     * Busca item por ID.
     */
    public function findById(int $id): ?ContractItem
    {
        return ContractItem::with('service')->find($id);
    }

    /**
     * Cria um novo item de contrato.
     */
    public function create(array $data): ContractItem
    {
        $item = ContractItem::create($data);
        return $item->load('service');
    }

    /**
     * Atualiza um item de contrato existente.
     */
    public function update(ContractItem $item, array $data): ContractItem
    {
        $item->update($data);
        return $item->fresh(['service']);
    }

    /**
     * Exclui um item de contrato.
     */
    public function delete(ContractItem $item): bool
    {
        return (bool) $item->delete();
    }
}
