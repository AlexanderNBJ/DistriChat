#!/bin/bash

echo "[DistriChat] Parando e removendo containers da infraestrutura..."

sudo docker compose down --remove-orphans

echo "[DistriChat] Todos os serviços foram interrompidos com sucesso."