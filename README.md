# Desafio Effecti ERP - Gestão de Contratos e Serviços

Sistema ERP para gestão de contratos e serviços, desenvolvido como desafio técnico da empresa Effecti.

## Tecnologias

### Backend
- **PHP 8.2** (puro, sem framework)
- **Eloquent ORM** (`illuminate/database`)
- **FastRoute** — roteamento leve
- **Phinx** — migrações e seeds
- **PHPUnit** — testes unitários

### Frontend
- **Vue 3** + **Vuetify 3** (Material Design)
- **Vue Router 4** + **Pinia** + **Axios**
- **Vite** — bundler

### Infraestrutura
- **PostgreSQL 16** (container Docker)
- **Nginx** — reverse proxy (Docker)
- **Docker Compose** — orquestração (PostgreSQL, PHP, Nginx, Frontend)

---

## Como Executar

### Pré-requisitos
- **Docker** e **Docker Compose** instalados

### 1. Configurar Variáveis de Ambiente

```bash
# Copie o arquivo de exemplo
cp .env.example .env

# Se necessário, ajuste as variáveis
```

**Exemplo de `.env`:**

```env
DB_HOST=postgres
DB_PORT=5432
DB_PORT_HOST=5432                # Use 5433 (ou outra) se 5432 estiver ocupada
DB_DATABASE=desafio_effecti
DB_USERNAME=erp_user
DB_PASSWORD=effecti2026!
AUTO_MIGRATE=true
AUTO_SEED=true
```

> **Nota:** `DB_PORT` é a porta interna entre containers. Para acesso externo no host, use `DB_PORT_HOST`.

### 2. Subir os Containers

```bash
docker compose up -d --build
```

O backend aguarda o PostgreSQL ficar saudável e executa migrations/seeds automaticamente (quando `AUTO_MIGRATE=true` e `AUTO_SEED=true`).

### 3. Comandos Manuais (opcionais)

```bash
# Criar as tabelas
docker compose exec php composer migrate

# Popular dados iniciais (serviços e configurações)
docker compose exec php composer seed

# Rollback da última migration
docker compose exec php composer rollback
```

### Acessos

| Serviço    | URL                         |
|------------|-----------------------------|
| Frontend   | http://localhost:5173       |
| API        | http://localhost:8080/api   |
| PostgreSQL | localhost:${DB_PORT_HOST}   |

### Parar o projeto

```bash
# Para os containers, mantém os dados do banco
docker compose down

# Para os containers E apaga os dados do banco (volume postgres_data)
# Use quando quiser resetar o banco do zero ou ao trocar credenciais
docker compose down -v
```

> **Importante:** Se você alterar `DB_USERNAME`, `DB_PASSWORD` ou `DB_DATABASE` no `.env` após já ter subido o projeto, o postgres **não atualiza as credenciais do volume existente** automaticamente. Nesse caso, execute `docker compose down -v` antes de subir novamente para recriar o banco com as novas configurações.

### Rodar testes

```bash
docker compose exec php vendor/bin/phpunit
```

### Dicas de operação

```bash
# Ver logs do backend (inclui migrate/seed no startup)
docker compose logs php

# Limpar banco e recriar tudo do zero
docker compose down -v
docker compose up -d --build
```

---

## Funcionalidades

### Clientes
- CRUD completo com validação de CPF/CNPJ (algoritmo de dígitos verificadores)
- Filtros por nome, documento e status
- Impede exclusão quando há contratos ativos

### Serviços
- CRUD com valor base mensal
- Impede exclusão quando vinculado a contratos ativos

### Contratos
- Criação, edição, cancelamento e exclusão
- Itens do contrato (add/edit/remove) com serviço, quantidade e valor unitário
- Cálculo automático de subtotal, desconto progressivo e total
- Histórico completo de alterações (timeline com tipo de ação e dados alterados em JSONB)

### Configurações
- Ativar/desativar desconto progressivo
- Configurar faixas de desconto por quantidade de itens (regras armazenadas no banco)

### Regra de Desconto Progressivo (Strategy Pattern)
| Qtd. Mínima de Itens | Desconto |
|-----------------------|----------|
| 3                     | 5%       |
| 5                     | 10%      |
| 10                    | 15%      |

As faixas são configuráveis pela interface, sem necessidade de alteração de código.

---

## Estrutura do Projeto

```
├── backend/
│   ├── db/
│   │   ├── migrations/          # Migrações Phinx
│   │   └── seeds/               # Seed inicial (serviços e configurações)
│   ├── public/
│   │   └── index.php            # Entry point (CORS, routing, error handling)
│   ├── src/
│   │   ├── BusinessRules/       # Strategy Pattern (DiscountRuleInterface, ProgressiveDiscountRule, DiscountCalculator)
│   │   ├── Config/              # Environment e Database (Eloquent standalone)
│   │   ├── Controllers/         # Controllers REST
│   │   ├── Exceptions/          # Exceções tipadas (Validation, Business, NotFound)
│   │   ├── Helpers/             # Response helper (JSON, pagination)
│   │   ├── Middleware/          # CORS
│   │   ├── Models/              # Eloquent Models (Client, Service, Contract, ContractItem, ContractHistory, Setting)
│   │   ├── Repositories/       # Camada de persistência
│   │   ├── Router/              # FastRoute + definição de rotas
│   │   ├── Services/            # Lógica de negócio (validações, cálculos, histórico)
│   │   └── Validators/         # Validadores (CPF/CNPJ, campos, regras)
│   └── tests/Unit/              # Testes unitários (Document, Discount)
├── frontend/
│   └── src/
│       ├── layouts/             # Layout padrão com drawer
│       ├── plugins/             # Vuetify config
│       ├── router/              # Vue Router
│       ├── services/            # Axios API services
│       └── views/               # Views organizadas por módulo
│           ├── clients/
│           ├── contracts/
│           ├── services/
│           └── settings/
├── docker/
│   └── nginx/default.conf
├── docker-compose.yml
├── .env.example
└── .gitignore
