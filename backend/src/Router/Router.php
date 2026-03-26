<?php

declare(strict_types=1);

namespace App\Router;

use FastRoute;
use App\Helpers\Response;

/**
 * Router principal da aplicação.
 * Encapsula o FastRoute e despacha para os Controllers.
 */
class Router
{
    /**
     * Registra as rotas e despacha a requisição para o Controller correto.
     */
    public static function dispatch(string $method, string $uri, array $body): void
    {
        $dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            // Carrega definição de rotas
            require __DIR__ . '/routes.php';
        });

        $routeInfo = $dispatcher->dispatch($method, $uri);

        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                Response::error('Rota não encontrada.', 404);
                break;

            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                Response::error('Método não permitido.', 405);
                break;

            case FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                // Handler no formato "Controller@method"
                [$controllerClass, $action] = explode('@', $handler);
                $controller = new $controllerClass();

                // Passa parâmetros da rota e o body da requisição
                $controller->$action($vars, $body);
                break;
        }
    }
}
