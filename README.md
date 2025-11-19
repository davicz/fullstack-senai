É necessário ter o docker instalado para rodar o projeto.
COMANDOS PARA RODAR A API:
  git clone https://github.com/davicz/fullstack-senai
  docker run --rm -v $(pwd):/var/www/html -w /var/www/html laravelsail/php83-composer:latest composer install
  touch .env
Colar no arquivo .env criado:
  '''APP_NAME=Laravel
  APP_ENV=local
  APP_KEY=base64:iuez8dwukdgXcV6MFPLyphxDH57HqV20zwPT2vWE14A=
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
  
  FRONTEND_URL=http://localhost/
  
  AWS_ACCESS_KEY_ID=
  AWS_SECRET_ACCESS_KEY=
  AWS_DEFAULT_REGION=us-east-1
  AWS_BUCKET=
  AWS_USE_PATH_STYLE_ENDPOINT=false
  
  VITE_APP_NAME="${APP_NAME}"'''

  ./vendor/bin/sail up -d
  ./vendor/bin/sail artisan key:generate
  ./vendor/bin/sail artisan migrate:fresh --seed

(se houver problemas de permissão, executar:
  sudo chown -R $USER:$USER .
)

COMANDOS PARA RODAR O FRONTEND:
  npm install
  npm install -g @angular/cli #caso nao tenha instalado
  ng serve --open
  

