#!/bin/bash

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Iniciando ambiente HTTPS para LaraList PWA${NC}"

# Mata processos anteriores
echo -e "${YELLOW}Encerrando processos anteriores...${NC}"
pkill -f "php artisan serve" 2>/dev/null
pkill -f "ngrok" 2>/dev/null
lsof -t -i:8000 | xargs kill -9 2>/dev/null

# Verifica se ngrok está instalado
if ! command -v ngrok &> /dev/null; then
    echo -e "${RED}ngrok não encontrado. Instalando...${NC}"
    
    # Usa homebrew para instalar
    brew install ngrok/ngrok/ngrok
    
    echo -e "${YELLOW}ngrok instalado. Você precisa configurar seu token:${NC}"
    echo -e "${YELLOW}1. Crie uma conta em https://ngrok.com${NC}"
    echo -e "${YELLOW}2. Copie seu token de autenticação${NC}"
    echo -e "${YELLOW}3. Execute: ngrok config add-authtoken SEU_TOKEN${NC}"
    echo ""
    echo -e "${RED}Execute este script novamente após configurar o token.${NC}"
    exit 1
fi

# Cria um arquivo .env.ngrok para usar no ambiente
if [ ! -f ".env.ngrok" ]; then
    cp .env .env.ngrok
    echo "SANCTUM_STATEFUL_DOMAINS=*.ngrok-free.app" >> .env.ngrok
    echo "SESSION_DOMAIN=ngrok-free.app" >> .env.ngrok
fi

# Inicia o Laravel na porta 8000
echo -e "${YELLOW}Iniciando servidor Laravel...${NC}"
php artisan serve --env=ngrok --host=0.0.0.0 --port=8000 > /tmp/laravel-server.log 2>&1 &
LARAVEL_PID=$!

# Aguarda o servidor iniciar
sleep 2

# Verifica se o servidor está rodando
if ! lsof -i:8000 -sTCP:LISTEN > /dev/null; then
    echo -e "${RED}Falha ao iniciar o servidor Laravel!${NC}"
    kill $LARAVEL_PID 2>/dev/null
    exit 1
fi

# Inicia ngrok em segundo plano - configuração simples e segura
echo -e "${YELLOW}Iniciando túnel HTTPS com ngrok...${NC}"
ngrok http 8000 --log=stdout > /tmp/ngrok.log 2>&1 &
NGROK_PID=$!

# Aguarda o ngrok iniciar
sleep 5

# Obtém a URL pública do ngrok
NGROK_URL=$(curl -s http://localhost:4040/api/tunnels | grep -o "https://[a-zA-Z0-9\.\-]*\.ngrok-free.app")

if [ -z "$NGROK_URL" ]; then
    echo -e "${RED}Falha ao obter URL do ngrok!${NC}"
    echo -e "${YELLOW}Verifique os logs: tail -f /tmp/ngrok.log${NC}"
    kill $LARAVEL_PID 2>/dev/null
    kill $NGROK_PID 2>/dev/null
    exit 1
fi

# Atualiza o .env.ngrok com a URL do ngrok
sed -i '' "s#APP_URL=.*#APP_URL=$NGROK_URL#" .env.ngrok

echo -e "${GREEN}\nAmbiente HTTPS configurado com sucesso!${NC}"
echo -e "${GREEN}URL HTTPS para seu app: $NGROK_URL${NC}"
echo -e "${GREEN}Use esta URL no emulador Android${NC}"
echo ""
echo -e "${YELLOW}Os seguintes processos foram iniciados:${NC}"
echo -e "Laravel (PID: $LARAVEL_PID) - Porta HTTP 8000"
echo -e "ngrok (PID: $NGROK_PID) - Túnel HTTPS"
echo ""
echo -e "${YELLOW}Para encerrar:${NC} kill $LARAVEL_PID $NGROK_PID"
echo -e "${YELLOW}Para ver logs do Laravel:${NC} tail -f /tmp/laravel-server.log"
echo -e "${YELLOW}Para ver logs do ngrok:${NC} tail -f /tmp/ngrok.log"
echo -e "${YELLOW}Interface admin do ngrok:${NC} http://localhost:4040"

# Fica rodando até Ctrl+C
trap 'echo -e "\n${RED}Encerrando servidores...${NC}"; kill $LARAVEL_PID $NGROK_PID 2>/dev/null; exit 0' INT
wait $LARAVEL_PID 