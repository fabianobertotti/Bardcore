<?php
header('Content-Type: text/plain; charset=utf-8');

$codigo = trim($_GET['codigo'] ?? '');

// Validação: deve ter exatamente 13 dígitos
if (!preg_match('/^\d{13}$/', $codigo)) {
    echo "Código inválido!";
    exit;
}

// Remove zeros à esquerda e o dígito verificador
$codigoBase = ltrim(substr($codigo, 0, 12), '0');

$arquivo = 'Base_de_Dados.csv';

if (!file_exists($arquivo)) {
    echo "Arquivo de dados não encontrado!";
    exit;
}

$encontrado = false;

if (($handle = fopen($arquivo, "r")) !== false) {
    while (($linha = fgetcsv($handle)) !== false) {
        if (!isset($linha[0])) continue;

        // Remove zeros à esquerda e o dígito verificador do código no CSV
        $csvCodigo = ltrim(substr(trim($linha[0]), 0, 12), '0');

        if ($csvCodigo === $codigoBase) {
            $dados = array_slice($linha, 1);
            echo implode(" | ", $dados);
            $encontrado = true;
            break;
        }
    }
    fclose($handle);
}

if (!$encontrado) {
    echo "Código inexistente!";
}
?>
