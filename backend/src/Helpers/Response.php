<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Helper para padronizar respostas JSON da API.
 */
class Response
{
    /**
     * Retorna resposta de sucesso.
     */
    public static function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Retorna resposta de sucesso com dados.
     */
    public static function success(mixed $data = null, int $code = 200, string $message = ''): void
    {
        $response = ['success' => true];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        self::json($response, $code);
    }

    /**
     * Retorna resposta de erro.
     */
    public static function error(string $message, int $code = 400, mixed $data = null): void
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        self::json($response, $code);
    }

    /**
     * Retorna resposta de erro de validação (422).
     */
    public static function validationError(array $errors): void
    {
        self::json([
            'success' => false,
            'message' => 'Erro de validação.',
            'errors'  => $errors,
        ], 422);
    }

    /**
     * Retorna resposta paginada.
     */
    public static function paginated(array $items, int $total, int $page, int $perPage): void
    {
        self::json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'last_page'    => $perPage === -1 ? 1 : (int) ceil($total / max(1, $perPage)),
            ],
        ]);
    }
}
