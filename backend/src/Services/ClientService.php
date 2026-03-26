<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Repositories\ClientRepository;
use App\Repositories\ContractRepository;
use App\Validators\ClientValidator;
use App\Validators\DocumentValidator;
use Illuminate\Database\QueryException;

/**
 * Service de Clientes.
 * Contém a lógica de negócio para operações com clientes.
 */
class ClientService
{
    private ClientRepository $clientRepository;
    private ContractRepository $contractRepository;

    public function __construct()
    {
        $this->clientRepository = new ClientRepository();
        $this->contractRepository = new ContractRepository();
    }

    /**
     * Lista clientes com paginação e filtros.
     */
    public function list(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        return $this->clientRepository->findAll($filters, $page, $perPage);
    }

    /**
     * Busca cliente por ID.
     */
    public function findById(int $id): array
    {
        $client = $this->clientRepository->findById($id);
        if (!$client) {
            throw new NotFoundException('Cliente não encontrado.');
        }
        return $client->toArray();
    }

    /**
     * Cria um novo cliente.
     */
    public function create(array $data): array
    {
        // Validação dos dados
        ClientValidator::validateCreate($data);

        // Sanitiza o documento (remove formatação)
        $data['document'] = DocumentValidator::sanitize($data['document']);

        // Verifica unicidade do documento
        $existing = $this->clientRepository->findByDocument($data['document']);
        if ($existing) {
            throw new BusinessException('Já existe um cliente com este CPF/CNPJ.');
        }

        // Verifica unicidade do email
        $existing = $this->clientRepository->findByEmail($data['email']);
        if ($existing) {
            throw new BusinessException('Já existe um cliente com este email.');
        }

        // Define status padrão se não informado
        if (!isset($data['status'])) {
            $data['status'] = 'A';
        }

        $client = $this->clientRepository->create($data);
        return $client->toArray();
    }

    /**
     * Atualiza um cliente existente.
     */
    public function update(int $id, array $data): array
    {
        $client = $this->clientRepository->findById($id);
        if (!$client) {
            throw new NotFoundException('Cliente não encontrado.');
        }

        // Validação dos dados
        ClientValidator::validateUpdate($data);

        // Se alterou documento, verifica unicidade
        if (isset($data['document'])) {
            $data['document'] = DocumentValidator::sanitize($data['document']);
            $existing = $this->clientRepository->findByDocument($data['document']);
            if ($existing && $existing->id !== $id) {
                throw new BusinessException('Já existe um cliente com este CPF/CNPJ.');
            }
        }

        // Se alterou email, verifica unicidade
        if (isset($data['email'])) {
            $existing = $this->clientRepository->findByEmail($data['email']);
            if ($existing && $existing->id !== $id) {
                throw new BusinessException('Já existe um cliente com este email.');
            }
        }

        $client = $this->clientRepository->update($client, $data);
        return $client->toArray();
    }

    /**
     * Exclui um cliente.
     */
    public function delete(int $id): void
    {
        $client = $this->clientRepository->findById($id);
        if (!$client) {
            throw new NotFoundException('Cliente não encontrado.');
        }

        // Verifica se o cliente possui qualquer contrato vinculado
        if ($this->contractRepository->existsByClientId($id)) {
            throw new BusinessException('Não é possível excluir um cliente que possui contratos vinculados.');
        }

        try {
            $this->clientRepository->delete($client);
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) === '23503') {
                throw new BusinessException('Não é possível excluir um cliente que possui contratos vinculados.');
            }

            throw $exception;
        }
    }
}
