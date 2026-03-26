<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ContractHistory;

/**
 * Repositório para operações de acesso a dados do Histórico de Contratos.
 */
class ContractHistoryRepository
{
    /**
     * Busca histórico de um contrato ordenado por data (mais recente primeiro).
     */
    public function findByContractId(int $contractId): array
    {
        return ContractHistory::where('contract_id', $contractId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Registra uma entrada no histórico.
     */
    public function create(array $data): ContractHistory
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return ContractHistory::create($data);
    }
}
