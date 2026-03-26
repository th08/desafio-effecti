# Documentação Técnica

## Arquitetura

O sistema segue uma arquitetura em camadas no backend, inspirada em Clean Architecture:

```
Request → Router → Controller → Service → Repository → Model/Eloquent → PostgreSQL
                                  ↑
                             Validator
                           BusinessRules
```

### Camadas

| Camada         | Responsabilidade                                                                 |
|----------------|----------------------------------------------------------------------------------|
| **Router**     | Mapeia URLs para controllers (FastRoute)                                         |
| **Controller** | Recebe request, monta parâmetros, chama service, retorna response JSON           |
| **Service**    | Lógica de negócio: validações, regras, orquestração de operações                 |
| **Validator**  | Validação de dados de entrada (campos obrigatórios, formatos, CPF/CNPJ)          |
| **BusinessRules** | Regras de negócio extensíveis (Strategy Pattern para descontos)               |
| **Repository** | Abstração sobre o Eloquent ORM para queries e persistência                       |
| **Model**      | Eloquent Models com relações, scopes e accessors                                 |

### Decisão: PHP Puro + Eloquent Standalone

O Eloquent ORM (`illuminate/database`) é utilizado **fora do Laravel**, configurado via `Capsule\Manager`. Isso oferece:

- Mapeamento objeto-relacional robusto
- Query builder e relações (`hasMany`, `belongsTo`)
- Scopes e accessors
- Sem acoplamento ao framework Laravel

A configuração está em `src/Config/Database.php`.

---

## Padrões de Projeto

### Strategy Pattern — Regras de Desconto

O sistema de desconto utiliza o **Strategy Pattern** para permitir adição de novas regras sem alterar código existente:

```
DiscountRuleInterface (interface)
├── ProgressiveDiscountRule (implementação padrão)
└── [NovaRegraFutura]       (pode ser adicionada sem alterar o sistema)

DiscountCalculator (orquestrador)
  → registra todas as regras
  → calcula subtotal, aplica regras ativas, retorna resultado consolidado
```

**Para adicionar uma nova regra de desconto:**
1. Cree uma classe que implementa `DiscountRuleInterface`
2. Registro-a no array `$rules` do `DiscountCalculator`

Nenhuma outra alteração é necessária.

### Configurações no Banco de Dados

As faixas de desconto são armazenadas na tabela `settings` como JSON, permitindo alteração via interface web sem necessidade de deploy. A configuração inclui:

- `discount_enabled` (boolean) — ativa/desativa o desconto
- `discount_rules` (JSON) — array de faixas `{ min_quantity, discount_percent }`

---

## Modelo de Dados (ER)

```
clients (1) ────── (N) contracts (1) ────── (N) contract_items (N) ────── (1) services
                        │
                        └── (N) contract_history

settings (tabela de configurações chave-valor)
```

### Tabelas

| Tabela              | Campos Principais                                                  |
|---------------------|--------------------------------------------------------------------|
| clients             | id, name, document(CPF/CNPJ), email, status(A/I)                  |
| services            | id, name, base_monthly_value                                       |
| contracts           | id, client_id(FK), start_date, end_date, status(A/C)              |
| contract_items      | id, contract_id(FK CASCADE), service_id(FK RESTRICT), quantity, unit_value |
| contract_history    | id, contract_id(FK CASCADE), action, description, changed_data(JSONB) |
| settings            | id, key(unique), value, description, type                          |

### Relacionamentos e Integridade Referencial

- `contracts.client_id` → `clients.id` (ON DELETE RESTRICT) — impede exclusão de cliente com contratos
- `contract_items.contract_id` → `contracts.id` (ON DELETE CASCADE) — remove itens ao excluir contrato
- `contract_items.service_id` → `services.id` (ON DELETE RESTRICT) — impede exclusão de serviço vinculado
- `contract_history.contract_id` → `contracts.id` (ON DELETE CASCADE) — remove histórico ao excluir contrato

---

## Validações

### CPF/CNPJ (`DocumentValidator`)
Implementação completa do algoritmo de verificação de dígitos:
- **CPF**: 11 dígitos, rejeita sequências de dígitos iguais, valida 1º e 2º dígitos verificadores
- **CNPJ**: 14 dígitos, mesma lógica com pesos diferentes

### Regras de Negócio

| Regra                                              | Localização          |
|----------------------------------------------------|----------------------|
| Cliente com contratos ativos não pode ser excluído  | `ClientService`      |
| Serviço com contratos ativos não pode ser excluído  | `ServiceService`     |
| Contrato só pode ser criado para cliente ativo      | `ContractService`    |
| Contrato cancelado não pode ser editado             | `ContractService`    |
| Todas as alterações no contrato geram histórico     | `ContractService`    |
| end_date deve ser posterior a start_date            | `ContractValidator`  |
| Desconto progressivo por quantidade é configurável  | `ProgressiveDiscountRule` |

---

## Histórico de Alterações (Audit Trail)

Todas as operações em contratos são registradas na tabela `contract_history`:

| Ação           | Quando                         | Dados Gravados (JSONB)              |
|----------------|--------------------------------|-------------------------------------|
| `created`      | Contrato criado                | Dados iniciais do contrato          |
| `updated`      | Contrato atualizado            | Campos alterados (antes/depois)     |
| `item_added`   | Item adicionado ao contrato    | Dados do item                       |
| `item_updated` | Item do contrato atualizado    | Campos alterados                    |
| `item_removed` | Item removido do contrato      | Dados do item removido              |
| `cancelled`    | Contrato cancelado             | Status anterior e motivo            |

---

## Testes

Os testes unitários cobrem as áreas mais críticas da aplicação:

| Teste                        | Foco                                               |
|------------------------------|-----------------------------------------------------|
| `DocumentValidatorTest`      | Validação de CPF/CNPJ (dígitos, formato, edge cases)|
| `DiscountCalculatorTest`     | Cálculo de desconto (faixas, boundary, desativado)   |

### Executar

```bash
docker compose exec php vendor/bin/phpunit
```

---

## API — Tratamento de Erros

Todas as respostas de erro seguem um formato padronizado:

```json
{
  "success": false,
  "message": "Mensagem descritiva do erro",
  "errors": { "campo": "detalhes da validação" }
}
```

| HTTP Status | Exceção              | Uso                        |
|-------------|----------------------|----------------------------|
| 422         | ValidationException  | Dados inválidos            |
| 400         | BusinessException    | Regra de negócio violada   |
| 404         | NotFoundException    | Recurso não encontrado     |
| 500         | Exception genérica   | Erro interno               |

---

## Melhorias Futuras

1. **Autenticação e autorização** — JWT ou sessões, controle de acesso por perfil
2. **Mais testes** — testes de integração (API), testes E2E com Cypress
3. **Paginação no frontend** — melhoria de performance para grandes volumes
4. **Cache** — Redis para configurações e resultados de cálculo
5. **Logs estruturados** — Monolog para rastreabilidade em produção
6. **Relatórios** — exportação de dados (PDF, Excel)
7. **Notificações** — alertas de vencimento de contrato
8. **Soft delete** — exclusão lógica para manter histórico completo
