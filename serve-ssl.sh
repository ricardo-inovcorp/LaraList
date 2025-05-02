#!/bin/bash

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Iniciando ambiente HTTPS para LaraList PWA${NC}"

# Mata processos anteriores
echo -e "${YELLOW}Encerrando processos anteriores...${NC}"
pkill -f "stunnel" 2>/dev/null
pkill -f "php artisan serve" 2>/dev/null
pkill -f "openssl s_server" 2>/dev/null
pkill -f "socat" 2>/dev/null
lsof -t -i:8000 | xargs kill -9 2>/dev/null
lsof -t -i:8443 | xargs kill -9 2>/dev/null

# Verifica se os certificados existem
if [ ! -f "storage/certs/cert.pem" ] || [ ! -f "storage/certs/key.pem" ]; then
    echo -e "${RED}Certificados não encontrados.${NC}"
    echo -e "${YELLOW}Criando certificados com mkcert...${NC}"
    
    # Verifica se mkcert está instalado
    if ! command -v mkcert &> /dev/null; then
        echo -e "${RED}mkcert não encontrado. Instalando...${NC}"
        brew install mkcert
        mkcert -install
    fi
    
    # Cria o diretório se não existir
    mkdir -p storage/certs
    
    # Cria os certificados para todos os hosts possíveis
    mkcert -key-file storage/certs/key.pem -cert-file storage/certs/cert.pem localhost 127.0.0.1 10.0.2.2 "*.local" "*.test"
fi

# Verifica se socat está instalado
if ! command -v socat &> /dev/null; then
    echo -e "${RED}socat não encontrado. Instalando...${NC}"
    brew install socat
fi

# Inicia o Laravel na porta 8000
echo -e "${YELLOW}Iniciando servidor Laravel...${NC}"
php artisan serve --host=0.0.0.0 --port=8000 > /tmp/laravel-server.log 2>&1 &
LARAVEL_PID=$!

# Aguarda o servidor iniciar
sleep 2

# Verifica se o servidor está rodando
if ! lsof -i:8000 -sTCP:LISTEN > /dev/null; then
    echo -e "${RED}Falha ao iniciar o servidor Laravel!${NC}"
    kill $LARAVEL_PID 2>/dev/null
    exit 1
fi

echo -e "${YELLOW}Iniciando proxy HTTPS com socat...${NC}"

# Inicia o socat para fazer o proxy SSL
socat -v OPENSSL-LISTEN:8443,fork,reuseaddr,cert=storage/certs/cert.pem,key=storage/certs/key.pem,verify=0 TCP:localhost:8000 > /tmp/socat.log 2>&1 &
SOCAT_PID=$!

# Aguarda o socat iniciar
sleep 2

# Verifica se o socat está rodando
if ! lsof -i:8443 -sTCP:LISTEN > /dev/null; then
    echo -e "${RED}Falha ao iniciar o proxy HTTPS!${NC}"
    kill $LARAVEL_PID 2>/dev/null
    kill $SOCAT_PID 2>/dev/null
    exit 1
fi

echo -e "${GREEN}\nAmbiente HTTPS configurado com sucesso!${NC}"
echo -e "${GREEN}Acesse: https://localhost:8443${NC}"
echo -e "${GREEN}Para emulador Android: https://10.0.2.2:8443${NC}"
echo ""
echo -e "${YELLOW}Os seguintes processos foram iniciados:${NC}"
echo -e "Laravel (PID: $LARAVEL_PID) - Porta HTTP 8000"
echo -e "SSL Proxy (PID: $SOCAT_PID) - Porta HTTPS 8443"
echo ""
echo -e "${YELLOW}Para encerrar:${NC} kill $LARAVEL_PID $SOCAT_PID"
echo -e "${YELLOW}Para ver logs do Laravel:${NC} tail -f /tmp/laravel-server.log"
echo -e "${YELLOW}Para ver logs do socat:${NC} tail -f /tmp/socat.log"

# Fica rodando até Ctrl+C
trap 'echo -e "\n${RED}Encerrando servidores...${NC}"; kill $LARAVEL_PID $SOCAT_PID 2>/dev/null; exit 0' INT
wait $LARAVEL_PID 