<?php

/**
 * Entry point da aplicação.
 * Configura autoload, ambiente, CORS, banco de dados, roteamento e tratamento de exceções.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Config\Environment;
use App\Middleware\CorsMiddleware;
use App\Router\Router;
use App\Helpers\Response;
use App\Exceptions\ValidationException;
use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use Illuminate\Database\QueryException;

// Carrega variáveis de ambiente
Environment::load();

// Configura tratamento global de exceções
set_exception_handler(function (Throwable $e) {
    if ($e instanceof ValidationException) {
        Response::validationError($e->getErrors());
        return;
    }

    if ($e instanceof BusinessException) {
        Response::error($e->getMessage(), 400);
        return;
    }

    if ($e instanceof NotFoundException) {
        Response::error($e->getMessage(), 404);
        return;
    }

    if ($e instanceof QueryException && (($e->errorInfo[0] ?? null) === '23503')) {
        Response::error('Operação não permitida porque existem registros vinculados.', 400);
        return;
    }

    // Erro inesperado
    $debug = Environment::get('APP_DEBUG', 'false') === 'true';
    $message = $debug ? $e->getMessage() : 'Erro interno do servidor.';
    $data = $debug ? ['trace' => $e->getTraceAsString()] : null;

    Response::error($message, 500, $data);
});

// Aplica headers CORS
CorsMiddleware::handle();

// Se for preflight (OPTIONS), encerra aqui
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Inicializa conexão com o banco de dados (Eloquent)
Database::boot();

// Lê o corpo da requisição JSON
$rawBody = file_get_contents('php://input');
$body = json_decode($rawBody ?: '', true) ?? [];

// Resolve a rota e despacha para o Controller
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Remove query string da URI para roteamento
if (($pos = strpos($uri, '?')) !== false) {
    $uri = substr($uri, 0, $pos);
}

// Remove trailing slash (exceto raiz)
$uri = rtrim($uri, '/') ?: '/';

Router::dispatch($method, $uri, $body);
