#!/bin/bash

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Iniciando ambiente LaraList PWA com HTTPS${NC}"

# Verifica se o ngrok existe no PATH ou na pasta atual
if [ -f ./ngrok ]; then
    NGROK_PATH="./ngrok"
elif command -v ngrok &> /dev/null; then
    NGROK_PATH="ngrok"
else
    echo -e "${RED}ngrok não encontrado.${NC}"
    echo -e "${YELLOW}Certifique-se de que o arquivo ngrok está na pasta raiz do projeto.${NC}"
    exit 1
fi

# Mata processos anteriores
echo -e "${YELLOW}Encerrando processos anteriores...${NC}"
pkill -f "ngrok" 2>/dev/null
pkill -f "php artisan serve" 2>/dev/null
lsof -t -i:8000 | xargs kill -9 2>/dev/null
lsof -t -i:4040 | xargs kill -9 2>/dev/null

echo -e "${YELLOW}Verificando o token do ngrok...${NC}"
AUTH_STATUS=$($NGROK_PATH config check)
if [[ "$AUTH_STATUS" == *"error"* ]]; then
    echo -e "${RED}Erro na configuração do ngrok.${NC}"
    echo -e "${YELLOW}Execute: $NGROK_PATH config add-authtoken SEU_TOKEN${NC}"
    exit 1
fi

# Inicia o servidor Laravel
echo -e "${YELLOW}Limpando cache do Laravel...${NC}"
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Inicia o ngrok com uma configuração que evita mudanças frequentes de URL
echo -e "${YELLOW}Iniciando túnel HTTPS com ngrok...${NC}"
$NGROK_PATH http 8000 --log=stdout > /tmp/ngrok.log 2>&1 &
NGROK_PID=$!

# Aguarda o ngrok iniciar
echo -e "${YELLOW}Aguardando inicialização do ngrok...${NC}"
sleep 5

# Obtém a URL pública do ngrok
NGROK_URL=$(curl -s http://localhost:4040/api/tunnels | grep -o "https://[a-zA-Z0-9\.\-]*\.ngrok-free.app")
if [ -z "$NGROK_URL" ]; then
    echo -e "${RED}Falha ao obter URL do ngrok!${NC}"
    echo -e "${YELLOW}Verificando logs do ngrok...${NC}"
    tail -10 /tmp/ngrok.log
    kill $NGROK_PID 2>/dev/null
    exit 1
fi

# Cria um .env.pwa com as configurações para o ngrok
echo -e "${YELLOW}Criando arquivo de configuração para o PWA...${NC}"
cp .env .env.pwa
sed -i '' "s#APP_URL=.*#APP_URL=$NGROK_URL#" .env.pwa
echo "SESSION_DOMAIN=ngrok-free.app" >> .env.pwa
echo "SANCTUM_STATEFUL_DOMAINS=*.ngrok-free.app" >> .env.pwa

# Inicia o servidor com environment pwa
echo -e "${YELLOW}Iniciando servidor Laravel...${NC}"
php artisan serve --env=pwa --host=0.0.0.0 --port=8000 > /tmp/laravel-server.log 2>&1 &
LARAVEL_PID=$!

# Aguarda o servidor iniciar
sleep 2
if ! lsof -i:8000 -sTCP:LISTEN > /dev/null; then
    echo -e "${RED}Falha ao iniciar o servidor Laravel!${NC}"
    kill $LARAVEL_PID 2>/dev/null
    kill $NGROK_PID 2>/dev/null
    exit 1
fi

echo -e "${GREEN}\nAmbiente PWA com HTTPS configurado com sucesso!${NC}"
echo -e "${GREEN}URL HTTPS: $NGROK_URL${NC}"
echo -e "${GREEN}URL para acessar no emulador Android: ${NGROK_URL}/tarefas${NC}"
echo ""
echo -e "${YELLOW}Os seguintes processos estão rodando:${NC}"
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