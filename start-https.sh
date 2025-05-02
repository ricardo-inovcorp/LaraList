#!/bin/bash

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Iniciando ambiente HTTPS para LaraList PWA${NC}"

# Verifica se stunnel está instalado
if ! command -v stunnel &> /dev/null; then
    echo -e "${RED}Stunnel não encontrado. Instalando...${NC}"
    brew install stunnel
fi

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

# Mata processos anteriores
echo -e "${YELLOW}Encerrando processos anteriores...${NC}"
pkill -f "stunnel" 2>/dev/null
pkill -f "php artisan serve" 2>/dev/null
lsof -t -i:8000 | xargs kill -9 2>/dev/null
lsof -t -i:8443 | xargs kill -9 2>/dev/null

# Cria arquivo .env.testing se não existir (para usar no emulador)
if [ ! -f ".env.testing" ]; then
    cp .env .env.testing
    echo "APP_URL=https://10.0.2.2:8443" >> .env.testing
    echo "SANCTUM_STATEFUL_DOMAINS=10.0.2.2:8443" >> .env.testing
    echo "SESSION_DOMAIN=10.0.2.2" >> .env.testing
fi

# Inicia o servidor Laravel com .env.testing
echo -e "${YELLOW}Iniciando servidor Laravel...${NC}"
php artisan serve --env=testing --host=0.0.0.0 --port=8000 > /tmp/laravel-server.log 2>&1 &
LARAVEL_PID=$!

# Aguarda o servidor iniciar
sleep 2

# Verifica se o servidor está rodando
if ! lsof -i:8000 -sTCP:LISTEN > /dev/null; then
    echo -e "${RED}Falha ao iniciar o servidor Laravel!${NC}"
    exit 1
fi

# Cria o arquivo de configuração do stunnel
cat > /tmp/stunnel.conf << EOL
debug = 7
output = /tmp/stunnel-laralist.log
pid = /tmp/stunnel.pid

[https]
client = no
accept = 0.0.0.0:8443
connect = 127.0.0.1:8000
cert = $(pwd)/storage/certs/cert.pem
key = $(pwd)/storage/certs/key.pem
; Opções mais permissivas para compatibilidade
options = NO_SSLv2
options = NO_SSLv3
sslVersion = TLSv1.2
ciphers = ALL:!ADH:!LOW:!EXP:!MD5:@STRENGTH
verifyChain = no
EOL

# Inicia o stunnel
echo -e "${YELLOW}Iniciando proxy HTTPS com stunnel...${NC}"
stunnel /tmp/stunnel.conf &
STUNNEL_PID=$!

# Aguarda o stunnel iniciar
sleep 2

echo -e "${GREEN}\nAmbiente HTTPS configurado com sucesso!${NC}"
echo -e "${GREEN}Acesse em seu navegador: https://localhost:8443${NC}"
echo -e "${GREEN}No emulador Android: https://10.0.2.2:8443${NC}"
echo ""
echo -e "${YELLOW}Os seguintes processos foram iniciados:${NC}"
echo -e "Laravel (PID: $LARAVEL_PID) - Porta HTTP 8000"
echo -e "Stunnel (PID: $STUNNEL_PID) - Porta HTTPS 8443"
echo ""
echo -e "${YELLOW}Para encerrar tudo:${NC} kill $LARAVEL_PID $STUNNEL_PID"
echo -e "${YELLOW}Para ver logs do Laravel:${NC} tail -f /tmp/laravel-server.log"
echo -e "${YELLOW}Para ver logs do stunnel:${NC} tail -f /tmp/stunnel-laralist.log"

# Fica rodando até Ctrl+C
trap 'echo -e "\n${RED}Encerrando servidores...${NC}"; kill $LARAVEL_PID $STUNNEL_PID 2>/dev/null; exit 0' INT
wait 