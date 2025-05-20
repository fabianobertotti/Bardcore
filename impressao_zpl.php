<?php
// Define o IP da impressora Zebra na rede
$printerIp = "10.7.30.140";
$printerPort = 9100; // Porta padrão da impressora Zebra

// Lê os dados enviados via POST
$data = json_decode(file_get_contents("php://input"), true);

// Verifica se os campos obrigatórios foram enviados
if (!isset($data['ean13']) || !isset($data['texto'])) {
    http_response_code(400);
    echo "Dados incompletos para impressão.";
    exit;
}

$ean13 = $data['ean13'];
$texto = $data['texto'];

// Converte o texto formatado para ZPL, com quebras de linha e formatação simples
$zpl = "^XA\n"; // Início do código ZPL
$zpl .= "^CF0,20\n"; // Fonte padrão, tamanho 20

// Insere o texto buscado linha por linha
$linhas = explode("\n", $texto);
$y = 30;
foreach ($linhas as $linha) {
    $zpl .= "^FO20,$y^FD" . strtoupper($linha) . "^FS\n"; // Posição e conteúdo
    $y += 25;
}

// Código de barras
$zpl .= "^BY2,2,50\n"; // Largura, espaço, altura
$zpl .= "^FO20,$y^BE,,Y,N\n"; // EAN-13
$zpl .= "^FD$ean13^FS\n";

// Finaliza o código
$zpl .= "^XZ";

// Conecta na impressora via socket
$fp = fsockopen($printerIp, $printerPort, $errno, $errstr, 5);
if (!$fp) {
    echo "Erro ao conectar na impressora: $errstr ($errno)";
    exit;
}

// Envia o ZPL
fwrite($fp, $zpl);
fclose($fp);

// Confirmação
echo "Impressão ZPL enviada com sucesso.";
?>
