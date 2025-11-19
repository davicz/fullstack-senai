# Fullstack Project - SENAI

Projeto composto por uma API em **Laravel** e um Frontend em **Angular**.

## 📋 Pré-requisitos

- [Docker](https://www.docker.com/) instalado (Obrigatório para o Backend).
- [Node.js](https://nodejs.org/) e NPM instalados.
- [Angular CLI](https://angular.io/cli) (Será instalado nos passos abaixo se não houver).

---

## 🚀 Backend (API)

### 1. Instalação e Dependências

Clone o repositório e instale as dependências do PHP utilizando um container temporário (não é necessário ter PHP instalado na máquina local):

```bash
git clone [https://github.com/davicz/fullstack-senai](https://github.com/davicz/fullstack-senai)
cd fullstack-senai

docker run --rm \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php83-composer:latest composer install
```

### 2. Configuração de Ambiente (.env)

Crie um arquivo chamado `.env` na raiz do projeto e cole o conteúdo abaixo:

```dotenv
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=techsol-api
DB_USERNAME=sail
DB_PASSWORD=password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=aea81aa22d5802
MAIL_PASSWORD=065c7a36592973
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="nao-responda@senai.com.br"
MAIL_FROM_NAME="${APP_NAME}"

FRONTEND_URL=http://localhost:4200

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
```

### 3. Execução

Execute os comandos do **Laravel Sail** para subir o ambiente e configurar o banco de dados:

```bash
# Sobe os containers em background
./vendor/bin/sail up -d

# Gera a chave da aplicação
./vendor/bin/sail artisan key:generate

# Roda as migrações e popula o banco
./vendor/bin/sail artisan migrate:fresh --seed
```

> **Nota (Linux):** Se houver problemas de permissão de escrita, execute:
> `sudo chown -R $USER:$USER .`

---

## 💻 Frontend (Angular)

Abra um novo terminal na pasta do frontend (se o projeto estiver separado) ou na raiz apropriada e execute:

```bash
# Instala as dependências do projeto
npm install

# Instala o Angular CLI globalmente (caso não tenha)
npm install -g @angular/cli

# Roda o servidor de desenvolvimento e abre o navegador
ng serve --open
```
