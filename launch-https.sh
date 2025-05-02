#!/bin/bash

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Iniciando ambiente HTTPS para LaraList PWA${NC}"

# Verifica o caminho do ngrok
if [ -z "$1" ]; then
    # Primeiro verifica na pasta raiz do projeto
    if [ -f ./ngrok ]; then
        NGROK_PATH="./ngrok"
    # Tenta encontrar o ngrok no PATH
    elif command -v ngrok &> /dev/null; then
        NGROK_PATH="ngrok"
    # Verifica no desktop
    elif [ -f ~/Desktop/ngrok ]; then
        NGROK_PATH=~/Desktop/ngrok
    else
        echo -e "${RED}ngrok não encontrado!${NC}"
        echo -e "${YELLOW}Use: $0 /caminho/para/ngrok${NC}"
        echo -e "${YELLOW}Ou coloque o ngrok na pasta raiz do projeto.${NC}"
        exit 1
    fi
else
    # Usa o caminho fornecido pelo usuário
    NGROK_PATH="$1"
    if [ ! -f "$NGROK_PATH" ]; then
        echo -e "${RED}ngrok não encontrado em $NGROK_PATH${NC}"
        exit 1
    fi
fi

echo -e "${YELLOW}Usando ngrok em: $NGROK_PATH${NC}"

# Mata processos anteriores
echo -e "${YELLOW}Encerrando processos anteriores...${NC}"
pkill -f "php artisan serve" 2>/dev/null
pkill -f "ngrok" 2>/dev/null
lsof -t -i:8000 | xargs kill -9 2>/dev/null
lsof -t -i:4040 | xargs kill -9 2>/dev/null

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

# Verifica se ngrok tem permissão de execução
if [ ! -x "$NGROK_PATH" ]; then
    echo -e "${YELLOW}Adicionando permissão de execução ao ngrok...${NC}"
    chmod +x "$NGROK_PATH"
fi

# Inicia ngrok em segundo plano
echo -e "${YELLOW}Iniciando túnel HTTPS com ngrok...${NC}"
"$NGROK_PATH" http 8000 --log=stdout > /tmp/ngrok.log 2>&1 &
NGROK_PID=$!

# Aguarda o ngrok iniciar
sleep 5

# Obtém a URL pública do ngrok
NGROK_URL=$(curl -s http://localhost:4040/api/tunnels | grep -o "https://[a-zA-Z0-9\.\-]*\.ngrok-free.app")

if [ -z "$NGROK_URL" ]; then
    echo -e "${RED}Falha ao obter URL do ngrok!${NC}"
    echo -e "${YELLOW}Verificando logs do ngrok...${NC}"
    tail -10 /tmp/ngrok.log
    
    echo -e "${YELLOW}Talvez o ngrok precise ser configurado com seu authtoken.${NC}"
    echo -e "${YELLOW}Execute: $NGROK_PATH config add-authtoken SEU_TOKEN${NC}"
    
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