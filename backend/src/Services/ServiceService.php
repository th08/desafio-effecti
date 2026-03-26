<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Repositories\ServiceRepository;
use App\Validators\ServiceValidator;
use Illuminate\Database\QueryException;

/**
 * Service de Serviços.
 * Contém a lógica de negócio para operações com serviços.
 */
class ServiceService
{
    private ServiceRepository $serviceRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
    }

    /**
     * Lista serviços com paginação e filtros.
     */
    public function list(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        return $this->serviceRepository->findAll($filters, $page, $perPage);
    }

    /**
     * Busca serviço por ID.
     */
    public function findById(int $id): array
    {
        $service = $this->serviceRepository->findById($id);
        if (!$service) {
            throw new NotFoundException('Serviço não encontrado.');
        }
        return $service->toArray();
    }

    /**
     * Cria um novo serviço.
     */
    public function create(array $data): array
    {
        ServiceValidator::validateCreate($data);

        $service = $this->serviceRepository->create($data);
        return $service->toArray();
    }

    /**
     * Atualiza um serviço existente.
     */
    public function update(int $id, array $data): array
    {
        $service = $this->serviceRepository->findById($id);
        if (!$service) {
            throw new NotFoundException('Serviço não encontrado.');
        }

        ServiceValidator::validateUpdate($data);

        $service = $this->serviceRepository->update($service, $data);
        return $service->toArray();
    }

    /**
     * Exclui um serviço.
     */
    public function delete(int $id): void
    {
        $service = $this->serviceRepository->findById($id);
        if (!$service) {
            throw new NotFoundException('Serviço não encontrado.');
        }

        // Verifica se o serviço está vinculado a qualquer contrato
        if ($this->serviceRepository->hasContracts($id)) {
            throw new BusinessException('Não é possível excluir um serviço vinculado a contratos.');
        }

        try {
            $this->serviceRepository->delete($service);
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) === '23503') {
                throw new BusinessException('Não é possível excluir um serviço vinculado a contratos.');
            }

            throw $exception;
        }
    }
}
