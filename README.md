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
- **PostgreSQL 14+** (instalação local)
- **Nginx** — reverse proxy (Docker)
- **Docker Compose** — orquestração (PHP, Nginx, Frontend)

---

## Como Executar

### Pré-requisitos
- **PostgreSQL 14+** instalado e rodando
- **Docker** e **Docker Compose** instalados

### 1. Criar o Banco de Dados

Crie um banco de dados no seu PostgreSQL:

```sql
CREATE DATABASE <DB_DATABASE>;
```

### 2. Configurar Variáveis de Ambiente

```bash
# Copie o arquivo de exemplo
cp .env.example .env

# Edite o .env com as credenciais do seu PostgreSQL
```

**Exemplo de `.env`:**

```env
DB_HOST=172.17.0.1              # Padrão recomendado no Linux
DB_PORT=5432                     # Ou 5433 se já tiver outro serviço na 5432
DB_DATABASE=<DB_DATABASE>
DB_USERNAME=<DB_USERNAME>
DB_PASSWORD=<DB_PASSWORD>
```

> **Nota:** Em Linux, use `172.17.0.1` como padrão para conectar ao PostgreSQL do host a partir dos containers Docker. Se necessário, tente `host.docker.internal` como alternativa.

### 3. Subir os Containers

```bash
docker compose up -d --build
```

### 4. Rodar Migrations e Seeds

```bash
# Criar as tabelas
docker compose exec php vendor/bin/phinx migrate -c phinx.php

# Popular dados iniciais (serviços e configurações)
docker compose exec php vendor/bin/phinx seed:run -c phinx.php
```

### Acessos

| Serviço    | URL                         |
|------------|-----------------------------|
| Frontend   | http://localhost:5173       |
| API        | http://localhost:8080/api   |

### Parar o projeto

```bash
docker compose down
```

### Rodar testes

```bash
docker compose exec php vendor/bin/phpunit
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
