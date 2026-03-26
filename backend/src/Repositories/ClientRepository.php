<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Client;

/**
 * Repositório para operações de acesso a dados de Clientes.
 * Abstrai as queries do Eloquent para manter o Service desacoplado.
 */
class ClientRepository
{
    /**
     * Lista clientes com paginação e filtros.
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $query = Client::query();

        // Filtro por nome (busca parcial)
        if (!empty($filters['name'])) {
            $query->where('name', 'ILIKE', '%' . $filters['name'] . '%');
        }

        // Filtro por documento
        if (!empty($filters['document'])) {
            $query->where('document', 'LIKE', '%' . $filters['document'] . '%');
        }

        // Filtro por status
        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
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
     * Busca cliente por ID.
     */
    public function findById(int $id): ?Client
    {
        return Client::find($id);
    }

    /**
     * Busca cliente por documento (CPF/CNPJ).
     */
    public function findByDocument(string $document): ?Client
    {
        return Client::where('document', $document)->first();
    }

    /**
     * Busca cliente por email.
     */
    public function findByEmail(string $email): ?Client
    {
        return Client::where('email', $email)->first();
    }

    /**
     * Cria um novo cliente.
     */
    public function create(array $data): Client
    {
        return Client::create($data);
    }

    /**
     * Atualiza um cliente existente.
     */
    public function update(Client $client, array $data): Client
    {
        $client->update($data);
        return $client->fresh();
    }

    /**
     * Exclui um cliente.
     */
    public function delete(Client $client): bool
    {
        return (bool) $client->delete();
    }
}
