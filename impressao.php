<?php
// Verifica se o conteúdo HTML foi enviado
if (!isset($_POST['html']) || empty($_POST['html'])) {
  die("Nenhum conteúdo recebido para impressão.");
}

$html = $_POST['html'];

// Caminho temporário para salvar o HTML como PDF
$tempHtml = tempnam(sys_get_temp_dir(), 'etiqueta') . '.html';
file_put_contents($tempHtml, $html);

// Caminho do PDF
$pdfPath = tempnam(sys_get_temp_dir(), 'etiqueta') . '.pdf';

// Converte o HTML para PDF usando wkhtmltopdf (requer instalação prévia)
exec("wkhtmltopdf --page-width 90mm --page-height 30mm $tempHtml $pdfPath");

// Envia o PDF para a impressora via linha de comando do Windows
$printerIP = "10.23.30.119"; // ou IP de teste: 10.7.30.140
$printerName = "\\\\$printerIP\\Etiqueta"; // Substitua "Etiqueta" pelo nome real da impressora

// Comando de envio usando o comando nativo do Windows
exec("print /d:$printerName $pdfPath");

// Remove arquivos temporários
unlink($tempHtml);
unlink($pdfPath);

echo "Impressão enviada com sucesso.";
?>
