<?php

/**
 * Definição de todas as rotas da API REST.
 * Arquivo carregado pelo Router dentro do FastRoute RouteCollector.
 *
 * Variável $r disponível via closure do Router::dispatch().
 * @var FastRoute\RouteCollector $r
 */

use App\Controllers\ClientController;
use App\Controllers\ServiceController;
use App\Controllers\ContractController;
use App\Controllers\SettingController;

// =============================================
// Clientes
// =============================================
$r->addRoute('GET', '/api/clients', ClientController::class . '@index');
$r->addRoute('POST', '/api/clients', ClientController::class . '@store');
$r->addRoute('GET', '/api/clients/{id:\d+}', ClientController::class . '@show');
$r->addRoute('PUT', '/api/clients/{id:\d+}', ClientController::class . '@update');
$r->addRoute('DELETE', '/api/clients/{id:\d+}', ClientController::class . '@destroy');

// =============================================
// Serviços
// =============================================
$r->addRoute('GET', '/api/services', ServiceController::class . '@index');
$r->addRoute('POST', '/api/services', ServiceController::class . '@store');
$r->addRoute('GET', '/api/services/{id:\d+}', ServiceController::class . '@show');
$r->addRoute('PUT', '/api/services/{id:\d+}', ServiceController::class . '@update');
$r->addRoute('DELETE', '/api/services/{id:\d+}', ServiceController::class . '@destroy');

// =============================================
// Contratos
// =============================================
$r->addRoute('GET', '/api/contracts', ContractController::class . '@index');
$r->addRoute('POST', '/api/contracts', ContractController::class . '@store');
$r->addRoute('GET', '/api/contracts/{id:\d+}', ContractController::class . '@show');
$r->addRoute('PUT', '/api/contracts/{id:\d+}', ContractController::class . '@update');
$r->addRoute('DELETE', '/api/contracts/{id:\d+}', ContractController::class . '@destroy');

// Itens do contrato
$r->addRoute('POST', '/api/contracts/{id:\d+}/items', ContractController::class . '@addItem');
$r->addRoute('PUT', '/api/contracts/{id:\d+}/items/{itemId:\d+}', ContractController::class . '@updateItem');
$r->addRoute('DELETE', '/api/contracts/{id:\d+}/items/{itemId:\d+}', ContractController::class . '@removeItem');

// Cancelar contrato
$r->addRoute('PATCH', '/api/contracts/{id:\d+}/cancel', ContractController::class . '@cancel');

// Histórico do contrato
$r->addRoute('GET', '/api/contracts/{id:\d+}/history', ContractController::class . '@history');

// =============================================
// Configurações
// =============================================
$r->addRoute('GET', '/api/settings', SettingController::class . '@index');
$r->addRoute('PUT', '/api/settings/{key}', SettingController::class . '@update');
