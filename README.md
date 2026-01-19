# Onfly Backend - API de Pedidos de Viagem

API REST para gerenciamento de pedidos de viagem corporativa, desenvolvida em Laravel 12.

## Tecnologias

- PHP 8.2
- Laravel 12
- MySQL 8.0
- JWT (tymon/jwt-auth)
- Docker & Docker Compose
- PHPUnit

## Arquitetura

```
app/
├── Enums/                    # Enums PHP 8.1+
├── Exceptions/               # Exceptions customizadas
├── Http/
│   ├── Controllers/          # Controllers da API
│   ├── Requests/             # Form Requests (validação)
│   └── Resources/            # API Resources (formatação JSON)
├── Mail/                     # Mailables para notificações
├── Models/                   # Eloquent Models
├── Repositories/
│   ├── Contracts/            # Interfaces dos Repositories
│   └── *Repository.php       # Implementações
└── Services/                 # Camada de regras de negócio
```

---

## Configuração Rápida

### 1. Clonar e configurar ambiente

```bash
cd backend
cp .env.example .env
```

### 2. Configurar o `.env`

```ini
DB_HOST=db
DB_DATABASE=onfly_db
DB_USERNAME=laravel
DB_PASSWORD=laravel
```

### 3. Subir os containers

```bash
docker-compose up -d --build
```

### 4. Rodar os testes

```bash
docker exec -it onfly-app php artisan test
```

## Endpoints da API

**Base URL:** `http://localhost:8000/api`

### Autenticação

#### Registrar usuário

```
POST /api/register
```

| Campo                 | Tipo    | Obrigatório |
|-----------------------|---------|-------------|
| name                  | string  | Sim         |
| email                 | string  | Sim         |
| password              | string  | Sim         |
| password_confirmation | string  | Sim         |
| is_admin              | boolean | Não         |

> **Nota:** Para criar um usuário admin, é necessário passar `is_admin` como `true` na requisição. Caso contrário, o usuário será criado como usuário comum.

<details>
<summary>Copiar cURL</summary>

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Novo Usuario",
    "email": "novo@teste.com",
    "password": "123456",
    "password_confirmation": "123456",
    "is_admin": false
  }'
```
</details>

---

#### Login

```
POST /api/login
```

| Campo    | Tipo   | Obrigatório |
|----------|--------|-------------|
| email    | string | Sim         |
| password | string | Sim         |

<details>
<summary>Copiar cURL</summary>

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "user@adm.test",
    "password": "123456"
  }'
```
</details>

**Resposta:**
```json
{
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "user@adm.test",
    "is_admin": true
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

---

#### Obter usuário autenticado

```
GET /api/user
```

Requer: `Authorization: Bearer {token}`

<details>
<summary>Copiar cURL</summary>

```bash
curl -X GET http://localhost:8000/api/user \
  -H "Accept: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_JWT"
```
</details>

---

#### Logout

```
POST /api/logout
```

Requer: `Authorization: Bearer {token}`

<details>
<summary>Copiar cURL</summary>

```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_JWT"
```
</details>

---

### Pedidos de Viagem

> Todas as rotas de pedidos requerem autenticação: `Authorization: Bearer {token}`

#### Listar pedidos

```
GET /api/pedidos
```

**Filtros disponíveis (query params):**

| Param    | Descrição                              | Exemplo                |
|----------|----------------------------------------|------------------------|
| status   | Filtrar por status                     | `?status=aprovado`     |
| destino  | Filtrar por destino (parcial)          | `?destino=Paulo`       |
| inicio   | Data inicial do período                | `?inicio=2025-01-01`   |
| fim      | Data final do período                  | `?fim=2025-12-31`      |
| usuario  | Filtrar por nome do usuário (admin)    | `?usuario=João`        |

<details>
<summary>Copiar cURL - Listar todos</summary>

```bash
curl -X GET http://localhost:8000/api/pedidos \
  -H "Accept: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_JWT"
```
</details>

<details>
<summary>Copiar cURL - Com filtros</summary>

```bash
curl -X GET "http://localhost:8000/api/pedidos?status=aprovado&destino=Paulo&inicio=2025-01-01&fim=2025-12-31" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_JWT"
```
</details>

**Resposta (paginada):**
```json
{
  "data": [
    {
      "id": 1,
      "nome_solicitante": "Admin",
      "destino": "São Paulo",
      "data_ida": "2025-06-01",
      "data_volta": "2025-06-05",
      "status": "solicitado",
      "usuario": {
        "id": 1,
        "name": "Admin",
        "email": "user@adm.test",
        "is_admin": true
      },
      "criado_em": "2025-01-19 10:00:00",
      "atualizado_em": "2025-01-19 10:00:00"
    }
  ],
  "links": { ... },
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

---

#### Criar pedido

```
POST /api/pedidos
```

| Campo      | Tipo   | Obrigatório | Validação                    |
|------------|--------|-------------|------------------------------|
| destino    | string | Sim         | -                            |
| data_ida   | date   | Sim         | Formato: Y-m-d               |
| data_volta | date   | Sim         | Deve ser >= data_ida         |

<details>
<summary>Copiar cURL</summary>

```bash
curl -X POST http://localhost:8000/api/pedidos \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -d '{
    "destino": "São Paulo",
    "data_ida": "2025-06-01",
    "data_volta": "2025-06-05"
  }'
```
</details>

---

#### Consultar pedido

```
GET /api/pedidos/{id}
```

- Usuário comum: só pode ver seus próprios pedidos
- Admin: pode ver qualquer pedido

<details>
<summary>Copiar cURL</summary>

```bash
curl -X GET http://localhost:8000/api/pedidos/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_JWT"
```
</details>

---

#### Atualizar status (Admin)

```
PATCH /api/pedidos/{id}/status
```

| Campo  | Tipo   | Valores aceitos          |
|--------|--------|--------------------------|
| status | string | `aprovado`, `cancelado`  |

**Regras de negócio:**
- Apenas admins podem alterar status
- Não é possível cancelar um pedido já aprovado
- Ao alterar status, um email é enviado ao solicitante

<details>
<summary>Copiar cURL - Aprovar</summary>

```bash
curl -X PATCH http://localhost:8000/api/pedidos/1/status \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -d '{
    "status": "aprovado"
  }'
```
</details>

<details>
<summary>Copiar cURL - Cancelar</summary>

```bash
curl -X PATCH http://localhost:8000/api/pedidos/1/status \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -d '{
    "status": "cancelado"
  }'
```
</details>

---

## Códigos de Resposta

| Código | Descrição                                      |
|--------|------------------------------------------------|
| 200    | Sucesso                                        |
| 201    | Criado com sucesso                             |
| 400    | Erro de regra de negócio                       |
| 401    | Não autenticado                                |
| 403    | Não autorizado (permissão negada)              |
| 404    | Recurso não encontrado                         |
| 422    | Erro de validação                              |
| 500    | Erro interno do servidor                       |

---

## Testes

Para rodar todos os testes:

```bash
docker exec -it onfly-app php artisan test
```

Para rodar um teste específico:

```bash
docker exec -it onfly-app php artisan test --filter=test_user_can_create_pedido
```

### Testes disponíveis

- `test_user_can_create_pedido` - Usuário pode criar pedido
- `test_user_can_view_their_pedidos` - Usuário pode ver seus pedidos
- `test_admin_can_update_status` - Admin pode atualizar status
- `test_non_admin_cannot_update_status` - Não-admin não pode atualizar status
- `test_cannot_cancel_approved_pedido` - Não pode cancelar pedido aprovado
- `test_user_cannot_view_other_user_pedido` - Usuário não pode ver pedido de outro
- `test_admin_can_view_any_user_pedido` - Admin pode ver qualquer pedido
- `test_filter_pedidos_by_status` - Filtro por status funciona
- `test_filter_pedidos_by_destino` - Filtro por destino funciona
- `test_filter_pedidos_by_periodo` - Filtro por período funciona
- `test_invalid_status_returns_validation_error` - Status inválido retorna erro
- `test_user_only_sees_own_pedidos_in_list` - Usuário só vê seus pedidos na lista
- `test_admin_sees_all_pedidos_in_list` - Admin vê todos os pedidos
- `test_list_returns_paginated_response` - Lista retorna resposta paginada

---

## Comandos Úteis

```bash
# Limpar cache de configuração
docker exec -it onfly-app php artisan config:clear

# Limpar cache geral
docker exec -it onfly-app php artisan cache:clear

# Ver logs em tempo real
docker exec -it onfly-app tail -f storage/logs/laravel.log

# Acessar o container
docker exec -it onfly-app bash

# Parar os containers
docker-compose down

# Rebuild completo
docker-compose down && docker-compose up -d --build
```
