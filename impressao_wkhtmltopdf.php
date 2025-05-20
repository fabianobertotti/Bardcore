<?php
// Impressão via wkhtmltopdf e comando shell
$htmlFile = "temp_etiqueta.html";
file_put_contents($htmlFile, "<html><body><h3>EAN-13: 7891234567895</h3></body></html>");
$pdfFile = "temp_etiqueta.pdf";
shell_exec("wkhtmltopdf $htmlFile $pdfFile");
shell_exec("print /D:\\\\10.7.30.140 $pdfFile");
echo "Impressão via wkhtmltopdf enviada.";
