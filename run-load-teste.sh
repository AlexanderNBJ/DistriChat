#!/bin/bash

echo "[DistriChat] Iniciando Teste de Carga e Concorrência com Grafana k6..."
echo "Cenário: 10 usuários virtuais simultâneos disparando mensagens por 30s."
echo ""

# Verifica se o arquivo de bateria de testes existe
if [ ! -f "bateria-testes.js" ]; then
    echo "[DistriChat] Erro: Arquivo 'bateria-testes.js' não encontrado na raiz!"
    exit 1
fi

sudo docker run --rm --network districhat_districhat_network -i grafana/k6 run - < bateria-testes.js

