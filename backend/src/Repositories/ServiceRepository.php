<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Service;

/**
 * Repositório para operações de acesso a dados de Serviços.
 */
class ServiceRepository
{
    /**
     * Lista serviços com paginação e filtros.
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $query = Service::query();

        // Filtro por nome (busca parcial)
        if (!empty($filters['name'])) {
            $query->where('name', 'ILIKE', '%' . $filters['name'] . '%');
        }

        $total = $query->count();
        $query->orderBy('name');

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
     * Busca serviço por ID.
     */
    public function findById(int $id): ?Service
    {
        return Service::find($id);
    }

    /**
     * Cria um novo serviço.
     */
    public function create(array $data): Service
    {
        return Service::create($data);
    }

    /**
     * Atualiza um serviço existente.
     */
    public function update(Service $service, array $data): Service
    {
        $service->update($data);
        return $service->fresh();
    }

    /**
     * Exclui um serviço.
     */
    public function delete(Service $service): bool
    {
        return (bool) $service->delete();
    }

    /**
     * Verifica se o serviço está vinculado a algum contrato ativo.
     */
    public function hasActiveContracts(int $serviceId): bool
    {
        return Service::where('id', $serviceId)
            ->whereHas('contractItems', function ($query) {
                $query->whereHas('contract', function ($q) {
                    $q->where('status', 'A');
                });
            })
            ->exists();
    }

    /**
     * Verifica se o serviço está vinculado a qualquer contrato.
     */
    public function hasContracts(int $serviceId): bool
    {
        return Service::where('id', $serviceId)
            ->whereHas('contractItems')
            ->exists();
    }
}
