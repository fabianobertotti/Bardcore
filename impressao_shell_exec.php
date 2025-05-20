<?php
// Impressão via shell_exec diretamente
$arquivo = "temp.txt";
file_put_contents($arquivo, "EAN-13: 7891234567895");
shell_exec("print /D:\\\\10.7.30.140 $arquivo");
echo "Impressão via shell_exec enviada.";
