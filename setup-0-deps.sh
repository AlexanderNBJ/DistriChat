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

echo -e "${GREEN}>>> Tudo pronto! Agora os containers não vão mais crashar.${NC}"
echo "Siga para: ./setup-1-files.sh e depois ./setup-2-docker.sh"
