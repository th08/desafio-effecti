<?php

declare(strict_types=1);

namespace App\Services;

use App\BusinessRules\DiscountCalculator;
use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Repositories\ClientRepository;
use App\Repositories\ContractHistoryRepository;
use App\Repositories\ContractItemRepository;
use App\Repositories\ContractRepository;
use App\Repositories\ServiceRepository;
use App\Validators\ContractValidator;

/**
 * Service de Contratos.
 * Contém a lógica de negócio principal: CRUD de contratos, gestão de itens,
 * cálculo de valores com regras de desconto, e registro de histórico.
 */
class ContractService
{
    private ContractRepository $contractRepository;
    private ContractItemRepository $itemRepository;
    private ContractHistoryRepository $historyRepository;
    private ClientRepository $clientRepository;
    private ServiceRepository $serviceRepository;
    private DiscountCalculator $discountCalculator;

    public function __construct()
    {
        $this->contractRepository = new ContractRepository();
        $this->itemRepository = new ContractItemRepository();
        $this->historyRepository = new ContractHistoryRepository();
        $this->clientRepository = new ClientRepository();
        $this->serviceRepository = new ServiceRepository();
        $this->discountCalculator = new DiscountCalculator();
    }

    /**
     * Lista contratos com paginação, filtros, itens e valor total calculado.
     */
    public function list(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $result = $this->contractRepository->findAll($filters, $page, $perPage);

        // Adiciona cálculo de valor total em cada contrato
        foreach ($result['items'] as &$contract) {
            $calculation = $this->discountCalculator->calculate(
                $contract['items'] ?? [],
                $contract
            );
            $contract['calculation'] = $calculation;
        }

        return $result;
    }

    /**
     * Busca contrato por ID com itens, histórico e valor calculado.
     */
    public function findById(int $id): array
    {
        $contract = $this->contractRepository->findById($id);
        if (!$contract) {
            throw new NotFoundException('Contrato não encontrado.');
        }

        $data = $contract->toArray();

        // Calcula valor total com regras de desconto
        $data['calculation'] = $this->discountCalculator->calculate(
            $data['items'] ?? [],
            $data
        );

        return $data;
    }

    /**
     * Cria um novo contrato (com itens opcionais).
     */
    public function create(array $data): array
    {
        // Validação dos dados
        ContractValidator::validateCreate($data);

        // Verifica se o cliente existe e está ativo
        $client = $this->clientRepository->findById((int) $data['client_id']);
        if (!$client) {
            throw new NotFoundException('Cliente não encontrado.');
        }
        if (!$client->isActive()) {
            throw new BusinessException('Não é possível criar contrato para um cliente inativo.');
        }

        // Extrai itens antes de criar o contrato
        $items = $data['items'] ?? [];
        unset($data['items']);

        // Define status padrão
        if (!isset($data['status'])) {
            $data['status'] = 'A';
        }

        // Cria o contrato
        $contract = $this->contractRepository->create($data);

        // Adiciona os itens ao contrato
        foreach ($items as $itemData) {
            $this->createItem($contract->id, $itemData);
        }

        // Registra no histórico
        $this->historyRepository->create([
            'contract_id' => $contract->id,
            'action'      => 'created',
            'description' => 'Contrato criado.',
            'changed_data' => [
                'client_id'  => $data['client_id'],
                'start_date' => $data['start_date'],
                'end_date'   => $data['end_date'] ?? null,
                'items_count' => count($items),
            ],
        ]);

        return $this->findById($contract->id);
    }

    /**
     * Atualiza um contrato existente.
     */
    public function update(int $id, array $data): array
    {
        $contract = $this->contractRepository->findById($id);
        if (!$contract) {
            throw new NotFoundException('Contrato não encontrado.');
        }

        // Impede edição de contrato cancelado
        if ($contract->isCancelled()) {
            throw new BusinessException('Não é possível editar um contrato cancelado.');
        }

        // Validação dos dados
        ContractValidator::validateUpdate($data);

        // Se está mudando o cliente, verifica se o novo existe e está ativo
        if (isset($data['client_id'])) {
            $client = $this->clientRepository->findById((int) $data['client_id']);
            if (!$client) {
                throw new NotFoundException('Cliente não encontrado.');
            }
            if (!$client->isActive()) {
                throw new BusinessException('Não é possível vincular contrato a um cliente inativo.');
            }
        }

        // Guarda dados antigos para o histórico
        $oldData = $contract->toArray();

        // Remove itens e campos que não devem ser atualizados diretamente
        unset($data['items'], $data['status']);

        $contract = $this->contractRepository->update($contract, $data);

        // Registra no histórico
        $this->historyRepository->create([
            'contract_id' => $id,
            'action'      => 'updated',
            'description' => 'Contrato atualizado.',
            'changed_data' => [
                'old' => array_intersect_key($oldData, $data),
                'new' => $data,
            ],
        ]);

        return $this->findById($id);
    }

    /**
     * Exclui um contrato.
     */
    public function delete(int $id): void
    {
        $contract = $this->contractRepository->findById($id);
        if (!$contract) {
            throw new NotFoundException('Contrato não encontrado.');
        }

        $this->contractRepository->delete($contract);
    }

    /**
     * Cancela um contrato (muda status para C).
     */
    public function cancel(int $id): array
    {
        $contract = $this->contractRepository->findById($id);
        if (!$contract) {
            throw new NotFoundException('Contrato não encontrado.');
        }

        if ($contract->isCancelled()) {
            throw new BusinessException('O contrato já está cancelado.');
        }

        $contract = $this->contractRepository->update($contract, ['status' => 'C']);

        // Registra no histórico
        $this->historyRepository->create([
            'contract_id' => $id,
            'action'      => 'cancelled',
            'description' => 'Contrato cancelado.',
            'changed_data' => ['status' => 'C'],
        ]);

        return $this->findById($id);
    }

    /**
     * Adiciona um item (serviço) ao contrato.
     */
    public function addItem(int $contractId, array $data): array
    {
        $contract = $this->contractRepository->findById($contractId);
        if (!$contract) {
            throw new NotFoundException('Contrato não encontrado.');
        }

        if ($contract->isCancelled()) {
            throw new BusinessException('Não é possível adicionar itens a um contrato cancelado.');
        }

        // Valida dados do item
        ContractValidator::validateItem($data);

        // Verifica se o serviço existe
        $service = $this->serviceRepository->findById((int) $data['service_id']);
        if (!$service) {
            throw new NotFoundException('Serviço não encontrado.');
        }

        $itemData = [
            'contract_id' => $contractId,
            'service_id'  => (int) $data['service_id'],
            'quantity'    => (int) $data['quantity'],
            'unit_value'  => (float) $data['unit_value'],
        ];

        $item = $this->itemRepository->create($itemData);

        // Registra no histórico
        $this->historyRepository->create([
            'contract_id' => $contractId,
            'action'      => 'item_added',
            'description' => "Serviço \"{$service->name}\" adicionado ao contrato.",
            'changed_data' => $itemData,
        ]);

        return $this->findById($contractId);
    }

    /**
     * Atualiza um item do contrato.
     */
    public function updateItem(int $contractId, int $itemId, array $data): array
    {
        $contract = $this->contractRepository->findById($contractId);
        if (!$contract) {
            throw new NotFoundException('Contrato não encontrado.');
        }

        if ($contract->isCancelled()) {
            throw new BusinessException('Não é possível editar itens de um contrato cancelado.');
        }

        $item = $this->itemRepository->findById($itemId);
        if (!$item || $item->contract_id !== $contractId) {
            throw new NotFoundException('Item do contrato não encontrado.');
        }

        // Valida campos atualizáveis
        $updateData = [];
        if (isset($data['quantity'])) {
            if (!is_numeric($data['quantity']) || (int) $data['quantity'] < 1) {
                throw new BusinessException('A quantidade deve ser no mínimo 1.');
            }
            $updateData['quantity'] = (int) $data['quantity'];
        }
        if (isset($data['unit_value'])) {
            if (!is_numeric($data['unit_value']) || (float) $data['unit_value'] < 0) {
                throw new BusinessException('O valor unitário deve ser maior ou igual a zero.');
            }
            $updateData['unit_value'] = (float) $data['unit_value'];
        }

        $oldData = $item->toArray();
        $this->itemRepository->update($item, $updateData);

        // Registra no histórico
        $this->historyRepository->create([
            'contract_id' => $contractId,
            'action'      => 'item_updated',
            'description' => 'Item do contrato atualizado.',
            'changed_data' => [
                'item_id' => $itemId,
                'old'     => array_intersect_key($oldData, $updateData),
                'new'     => $updateData,
            ],
        ]);

        return $this->findById($contractId);
    }

    /**
     * Remove um item do contrato.
     */
    public function removeItem(int $contractId, int $itemId): array
    {
        $contract = $this->contractRepository->findById($contractId);
        if (!$contract) {
            throw new NotFoundException('Contrato não encontrado.');
        }

        if ($contract->isCancelled()) {
            throw new BusinessException('Não é possível remover itens de um contrato cancelado.');
        }

        $item = $this->itemRepository->findById($itemId);
        if (!$item || $item->contract_id !== $contractId) {
            throw new NotFoundException('Item do contrato não encontrado.');
        }

        $itemData = $item->toArray();
        $serviceName = $item->service ? $item->service->name : 'Desconhecido';
        $this->itemRepository->delete($item);

        // Registra no histórico
        $this->historyRepository->create([
            'contract_id' => $contractId,
            'action'      => 'item_removed',
            'description' => "Serviço \"{$serviceName}\" removido do contrato.",
            'changed_data' => $itemData,
        ]);

        return $this->findById($contractId);
    }

    /**
     * Retorna o histórico de alterações de um contrato.
     */
    public function getHistory(int $contractId): array
    {
        $contract = $this->contractRepository->findById($contractId);
        if (!$contract) {
            throw new NotFoundException('Contrato não encontrado.');
        }

        return $this->historyRepository->findByContractId($contractId);
    }

    /**
     * Cria um item individual (usado internamente na criação do contrato).
     */
    private function createItem(int $contractId, array $data): void
    {
        // Verifica se o serviço existe
        $service = $this->serviceRepository->findById((int) $data['service_id']);
        if (!$service) {
            throw new NotFoundException("Serviço ID {$data['service_id']} não encontrado.");
        }

        $this->itemRepository->create([
            'contract_id' => $contractId,
            'service_id'  => (int) $data['service_id'],
            'quantity'    => (int) ($data['quantity'] ?? 1),
            'unit_value'  => (float) ($data['unit_value'] ?? $service->base_monthly_value),
        ]);
    }
}
