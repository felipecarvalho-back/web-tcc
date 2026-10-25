# Sentinela FATEC — Sistema de Controle e Monitoramento de Acesso Veicular

O **Sentinela FATEC** é o sistema web e API central do Trabalho de Conclusão de Curso (TCC), desenvolvido para gerenciar e monitorar a entrada e saída de veículos nas cancelas da **Faculdade de Tecnologia (FATEC)**.

A aplicação unifica um **painel web reativo** (Laravel 13 + Livewire 4) para os operadores da guarita e administradores, uma **API REST autenticada via JWT** para o aplicativo mobile, integração com **API Python de Visão Computacional (OCR)** para leitura de placas e **armazenamento híbrido de imagens** (nuvem via Cloudinary ou armazenamento local no próprio projeto).

---

## Sumário

1. [Fluxo de Funcionamento](#fluxo-de-funcionamento)
2. [Funcionalidades e Módulos do Sistema](#funcionalidades-e-módulos-do-sistema)
3. [Armazenamento de Imagens (Nuvem ou Local)](#armazenamento-de-imagens-nuvem-ou-local)
4. [Tecnologias Utilizadas](#tecnologias-utilizadas)
5. [Estrutura do Banco de Dados](#estrutura-do-banco-de-dados)
6. [Rotas da API (Integração Mobile)](#rotas-da-api-integração-mobile)
7. [Configuração e Instalação (`.env`)](#configuração-e-instalação-env)
8. [Acesso Inicial (Seeder)](#acesso-inicial-seeder)

---

## Fluxo de Funcionamento

1. **Captura no App Mobile:** O guarda fotografa o veículo na cancela pelo aplicativo mobile e envia a imagem para `POST /api/enviar` usando seu token Bearer JWT.
2. **Detecção de Placa (API Python OCR):** O Laravel envia a imagem via `PlacaOcrService` para a API em Python (`POST /detectar-placa`), que retorna a placa lida. Caso a API Python esteja offline ou não consiga ler a placa, o sistema registra automaticamente como `SEM-PLACA` (`nao_cadastrado`) para verificação manual na guarita.
3. **Salvamento da Foto (Nuvem ou Local):** A imagem é salva no **Cloudinary** (se configurado no `.env`) ou diretamente no disco local do projeto (`storage/app/public/perfis`).
4. **Monitoramento em Tempo Real:** O registro entra na tabela `registros_acesso` e aparece automaticamente na tela de **Monitoramento da Portaria** (`/portaria`), que atualiza sozinha a cada 5 segundos (`wire:poll.5s.visible`).

---

## Funcionalidades e Módulos do Sistema

### 1. Módulo da Guarita / Portaria (`/portaria`) — *Integrado ao Banco de Dados*
- **Monitoramento em Tempo Real (`/portaria`):**
  - Indicadores diários calculados em tempo real (Total de Passagens, Leituras Automáticas OCR e Correções Manuais).
  - Filtros combinados por período (`Hoje`, `Ontem`, `Últimos 7 dias`, `Este Mês`), placa, categoria e status, com paginação dinâmica.
  - **Dupla Verificação de Acesso:** modal para corrigir manualmente placas não identificadas pelo OCR ou validar o código funcional do motorista, com registro de justificativa e adição de até **4 caronas/passageiros**.
  - **Confirmação de Condutor e Controle de Saída:** confirmação de identidade para condutores cadastrados e botão direto de **Marcar Saída** (para visitantes, o sistema exige apenas a marcação de saída, sem necessidade de código funcional).
- **Gestão e Controle de Visitantes (`/portaria/visitantes`):**
  - Cadastro e edição em modal dedicado (`modal-visitante.blade.php`) com máscara de CPF via Alpine.js (`x-mask="999.999.999-99"`) e validação matemática dos dígitos verificadores (`CpfValido`).
  - **Conferência Automática por CPF:** ao digitar os 11 números de um CPF que já visitou a FATEC, o sistema preenche automaticamente Nome, Placa, Modelo, Cor e Setor e exibe um alerta visual para o guarda conferir se o veículo na cancela é o mesmo.
  - Cada nova visita gera uma passagem individual em `registros_acesso`, com listagem paginada e filtros por busca textual, período e status (`No Campus` / `Finalizado`).

### 2. Módulo Administrativo (`/admin`)
- **Gestão de Usuários do Sistema (`/admin/usuarios`) — *Integrado ao Banco de Dados*:**
  - CRUD completo de Administradores, Supervisores e Operadores de Guarita.
  - Geração automática de código sequencial por perfil (`ADM-xxx`, `SUP-xxx`, `OP-xxx`), validação matemática de CPF, ativação/desativação rápida e exclusão lógica (`SoftDeletes`).
- **Telas Administrativas com Interface Estruturada:**
  - **Painel & Indicadores (`/admin/dashboard`):** visualização gerencial de métricas do campus.
  - **Gestão de Condutores e Veículos (`/admin/condutores`):** interface para cadastro de professores, funcionários e prestadores com múltiplos veículos.
  - **Relatórios & Exportação (`/admin/relatorios`):** interface de consulta de assiduidade e exportação para impressão/PDF.

---

## Armazenamento de Imagens (Nuvem ou Local)

O serviço `CloudinaryService` trabalha em conjunto com o `ImagemController` de forma **híbrida e tolerante a falhas**:

- **Com Cloudinary configurado:** Se as variáveis `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY` e `CLOUDINARY_API_SECRET` estiverem preenchidas no `.env`, as fotos são enviadas para a pasta `sentinela/placas` na nuvem e a API retorna `"armazenamento": "cloudinary"`.
- **Sem Cloudinary (Armazenamento Local no Projeto):** Se as variáveis do Cloudinary estiverem vazias no `.env` (ou se houver queda de internet/erro na nuvem), o sistema **salva automaticamente a imagem dentro do próprio projeto** em `storage/app/public/perfis` (acessível publicamente em `/storage/perfis/...`) e retorna `"armazenamento": "local"`.

---

## Tecnologias Utilizadas

- **Back-end:** PHP 8.3+ / Laravel 13
- **Front-end:** Livewire 4 + Alpine.js (`@alpinejs/mask`) + Tailwind CSS 4 + Vite
- **Banco de Dados:** PostgreSQL (`db_sentinela`)
- **Autenticação API:** JWT (`firebase/php-jwt`) — utiliza a própria `APP_KEY` do projeto como chave de assinatura caso `JWT_SECRET` não seja informada
- **Padronização e Testes:** Laravel Pint, Larastan (PHPStan) e Pest 5

---

## Estrutura do Banco de Dados

- `usuarios` — Operadores, supervisores e administradores (`codigo_operador`, `nome`, `cpf`, `email`, `senha`, `perfil`, `ativo`, `deleted_at`).
- `condutores` — Cadastro base de motoristas (`nome`, `cpf`, `tipo`, `validade_acesso`, `ativo`, `deleted_at`).
  - `condutores_visitantes` — Dados de visita (`condutor_id`, `motivo_visita`, `setor_destino`, `permanencia_estimada`).
  - `condutores_professores`, `condutores_funcionarios`, `condutores_prestadores` — Tabelas de especialização por categoria.
- `veiculos` — Veículos vinculados aos condutores (`condutor_id`, `placa`, `modelo`, `cor`, `ativo`, `deleted_at`).
- `registros_acesso` — Passagens registradas nas cancelas (`veiculo_id`, `condutor_id`, `operador_id`, `tipo_ocupante`, `passagem_origem_id`, `placa_registro`, `placa_corrigida`, `cancela`, `foto_entrada_path`, `data_hora_entrada`, `data_hora_saida`, `status`, `justificativa`, `deleted_at`).

---

## Rotas da API (Integração Mobile)

Todas as rotas possuem o prefixo `/api`:

| Método | Rota | Middleware | Descrição |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/login` | `throttle:10,1` | Recebe `email` e `senha`, valida se o usuário está ativo e retorna o token Bearer JWT + dados do operador (`UsuarioResource`). |
| `POST` | `/api/enviar` | `jwt.auth` | Recebe a imagem (`foto` JPG/PNG até 10MB), consulta a API Python (`/detectar-placa`), salva a foto (Cloudinary ou local) e registra a entrada em `registros_acesso`. |
| `GET` | `/api/historico` | `jwt.auth` | Lista todas as fotos capturadas no dia atual com URL da imagem, horário exato (`H:i:s`), placa identificada ou não e dados do condutor (`HistoricoFotoResource`). |

---

## Configuração e Instalação (`.env`)

### 1. Instalar dependências
```bash
composer install
npm install
```

### 2. Configurar o arquivo `.env`
Copie o arquivo `.env.example` para `.env`, gere a chave da aplicação e configure as variáveis abaixo conforme seu ambiente:

```bash
cp .env.example .env
php artisan key:generate
```

Principais variáveis utilizadas pelo sistema no `.env`:

```dotenv
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=America/Sao_Paulo
APP_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

# Banco de Dados PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=db_sentinela
DB_USERNAME=postgres
DB_PASSWORD=sua_senha_postgres

# Armazenamento em Nuvem (Opcional - se omitido, salva localmente em storage/app/public/perfis)
CLOUDINARY_CLOUD_NAME=
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=

# Integração com a API Python de OCR (Leitura de Placas)
API_URL_PYTHON="http://127.0.0.1:5000/"
API_TOKEN_PYTHON="seu_token_jwt_da_api_python"
```

### 3. Executar Migrations, Seeder e Link do Storage
> Certifique-se de que o banco `db_sentinela` foi criado no PostgreSQL antes de rodar as migrations.

```bash
php artisan migrate --seed
php artisan storage:link
```

### 4. Compilar os assets e iniciar o servidor
```bash
npm run build
php artisan serve
```

---

## Acesso Inicial (Seeder)

O `UsuariosSeeder` cria automaticamente o usuário Administrador padrão para o primeiro acesso:

| Perfil | Código | E-mail | Senha |
| :--- | :--- | :--- | :--- |
| **Administrador** | `ADM-001` | `admin@teste.com` | `admin123` |

> Após entrar com o Administrador em `/login`, acesse **Usuários do Sistema (`/admin/usuarios`)** para cadastrar os operadores da guarita (`OP-001`, `OP-002`, etc.) e supervisores (`SUP-001`).
