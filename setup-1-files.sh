#!/bin/bash
echo ">>> Copiando .env e liberando permissões..."
cp auth-service/.env.example auth-service/.env
cp chat-service/.env.example chat-service/.env
cp frontend/.env.example frontend/.env

# Dá permissão total para as pastas de escrita (Obrigatório no Fedora)
sudo chmod -R 777 auth-service/storage chat-service/storage frontend/storage
sudo chmod -R 777 frontend/database
# Permite que o Docker escreva a APP_KEY no arquivo
chmod 666 auth-service/.env chat-service/.env frontend/.env

echo "Feito. Agora rode o ./setup-2-docker.sh"
