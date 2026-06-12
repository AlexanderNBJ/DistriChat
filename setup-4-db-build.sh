#!/bin/bash
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

echo "SISTEMA PRONTO: http://localhost:8002"