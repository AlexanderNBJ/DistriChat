#!/bin/bash
echo ">>> Instalando dependências PHP (via Host)..."
(cd auth-service && composer install --ignore-platform-reqs)
(cd chat-service && composer install --ignore-platform-reqs)
(cd frontend && composer install --ignore-platform-reqs)

echo ">>> Gerando chaves e configurando apps..."
sudo docker exec districhat_auth php artisan key:generate --force
sudo docker exec districhat_chat_api php artisan key:generate --force
sudo docker exec districhat_frontend php artisan key:generate --force

echo "Feito. Agora o passo final: ./setup-4-db-build.sh"