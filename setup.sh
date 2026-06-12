#!/bin/bash

# Cores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}>>> [Dependências] Instalando pacotes PHP e JS no Host...${NC}"

# Função para instalar Composer de forma segura
install_composer() {
    echo -e "${BLUE}-> Instalando Composer em $1...${NC}"
    if [ -d "$1" ]; then
        # --ignore-platform-reqs evita erros se o seu PHP local não tiver extensões que o container tem
        (cd "$1" && composer install --ignore-platform-reqs)
    else
        echo "Erro: Pasta $1 não encontrada."
    fi
}

# 1. Instala PHP em todos os microserviços
install_composer "auth-service"
install_composer "chat-service"
install_composer "frontend"

# 2. Instala Node no frontend
echo -e "${BLUE}-> Instalando pacotes NPM no Frontend...${NC}"
if [ -d "frontend" ]; then
    (cd "frontend" && npm install)
fi

echo ">>> Copiando .env e liberando permissões..."
cp auth-service/.env.example auth-service/.env
cp chat-service/.env.example chat-service/.env
cp frontend/.env.example frontend/.env

# Dá permissão total para as pastas de escrita (Obrigatório no Fedora)
sudo chmod -R 777 auth-service/storage chat-service/storage frontend/storage
sudo chmod -R 777 frontend/database
# Permite que o Docker escreva a APP_KEY no arquivo
chmod 666 auth-service/.env chat-service/.env frontend/.env

echo ">>> Subindo containers (Limpando volumes antigos)..."
sudo docker compose down -v
sudo docker compose up -d --build
sleep 2
sudo docker ps
echo "Verifique se o districhat_postgres está 'Up'."

echo ">>> Instalando dependências PHP (via Host)..."
(cd auth-service && composer install --ignore-platform-reqs)
(cd chat-service && composer install --ignore-platform-reqs)
(cd frontend && composer install --ignore-platform-reqs)

echo ">>> Gerando chaves e configurando apps..."
sudo docker exec districhat_auth php artisan key:generate --force
sudo docker exec districhat_chat_api php artisan key:generate --force
sudo docker exec districhat_frontend php artisan key:generate --force

echo ">>> Migrando Auth Service..."
sudo docker exec districhat_auth php artisan migrate:fresh --seed --force
sudo docker exec districhat_auth php artisan tinker --execute="DB::statement(\"SELECT setval('users_id_seq', (SELECT MAX(id) FROM users))\")"
sudo docker exec districhat_auth php artisan tinker --execute="DB::statement(\"SELECT setval('personal_access_tokens_id_seq', (SELECT MAX(id) FROM personal_access_tokens))\")"

echo ">>> Migrando Chat Service..."
sudo docker exec districhat_chat_api php artisan migrate:fresh --seed --force
sudo docker exec districhat_chat_api php artisan tinker --execute="DB::statement(\"SELECT setval('users_id_seq', (SELECT MAX(id) FROM users))\")"

echo ">>> Migrando Frontend (SQLite)..."
mkdir -p frontend/database && touch frontend/database/database.sqlite && chmod 777 frontend/database/database.sqlite
sudo docker exec districhat_frontend php artisan migrate --force

echo ">>> Compilando Assets do Frontend..."
cd frontend && npm install && npm run build && cd ..

echo "SISTEMA EXECUTANDO EM: http://localhost:8002"