#!/bin/bash
echo ">>> Subindo containers (Limpando volumes antigos)..."
sudo docker compose down -v
sudo docker compose up -d --build
sleep 2
sudo docker ps
echo "Verifique se o districhat_postgres está 'Up'. Se sim, rode ./setup-3-deps.sh"