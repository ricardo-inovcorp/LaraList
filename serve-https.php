<?php

// Verifica se os certificados existem
$certFile = __DIR__ . '/storage/certs/cert.pem';
$keyFile = __DIR__ . '/storage/certs/key.pem';

if (!file_exists($certFile) || !file_exists($keyFile)) {
    die("Erro: Certificados não encontrados em storage/certs/\n");
}

// Instalando stunnel se não existir
exec('which stunnel', $output, $return_var);
if ($return_var !== 0) {
    echo "Stunnel não encontrado. Instalando...\n";
    system('brew install stunnel');
}

// Configuração do stunnel
$stunnel_config = <<<EOT
pid = /tmp/stunnel-laralist.pid
output = /tmp/stunnel-laralist.log
cert = $certFile
key = $keyFile
[https]
accept = 0.0.0.0:443
connect = 127.0.0.1:8000
EOT;

// Grava o arquivo de configuração
file_put_contents('/tmp/stunnel-laralist.conf', $stunnel_config);

// Mata qualquer stunnel anterior
system('pkill -f "stunnel /tmp/stunnel-laralist.conf" 2>/dev/null');

// Inicia o stunnel em background
system('stunnel /tmp/stunnel-laralist.conf &');

// Configura o servidor Laravel
$host = '127.0.0.1';
$port = 8000;
$docRoot = __DIR__ . '/public';

// Mata qualquer servidor PHP anterior na porta 8000
system('lsof -t -i:8000 | xargs kill -9 2>/dev/null');

// Executa o servidor PHP
$command = sprintf(
    'php -S %s:%d -t %s %s/router.php',
    $host,
    $port,
    $docRoot,
    __DIR__
);

echo "Servidor HTTPS configurado!\n";
echo "Acesse: https://localhost (ou https://10.0.2.2 no emulador Android)\n";
echo "Pressione Ctrl+C para encerrar\n\n";

system($command); 