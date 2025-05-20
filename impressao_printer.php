<?php
// Impressão via extensão php_printer.dll
$printer = printer_open("NomeDaImpressora");
printer_start_doc($printer, "Etiqueta EAN");
printer_start_page($printer);
printer_draw_text($printer, "Código EAN-13: 7891234567895", 100, 100);
printer_end_page($printer);
printer_end_doc($printer);
printer_close($printer);
echo "Impressão via php_printer.dll enviada.";
