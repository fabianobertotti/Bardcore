<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Gerador de Código de Barras EAN-13</title>

  <!-- Biblioteca JS para gerar código de barras EAN-13 -->
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

  <!-- Biblioteca JS para gerar arquivos PDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

  <!-- Estilo da página -->
  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      margin-top: 50px;
    }

    input[type="text"] {
      padding: 10px;
      font-size: 18px;
      width: 250px;
    }

    button {
      padding: 10px 20px;
      font-size: 18px;
      margin: 10px 5px;
      cursor: pointer;
    }

    /* Container da área de impressão com fundo bege suave e tamanho fixo */
    #print-area {
      background-color: #fdf6e3; /* Fundo bege claro */
      width: 9cm;
      height: 3cm;
      margin: 20px auto;
      display: flex;
      justify-content: space-around;
      align-items: center;
      border: 1px dashed #999; /* Borda para destacar a área de impressão */
      border-radius: 8px;
      box-sizing: border-box;
    }

    /* Estilo das etiquetas */
    .etiqueta {
      width: 4cm;
      height: 2.5cm;
      background-color: white;
      border: 1px solid #ccc;
      border-radius: 6px;
      padding: 5px;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }

    .blocoResultado {
      font-size: 10px;
      white-space: pre-line;
      text-align: left;
      width: 100%;
    }

    svg {
      margin-top: 2px;
    }

    @media print {
      body * {
        visibility: hidden;
      }

      #print-area, #print-area * {
        visibility: visible;
      }

      #print-area {
        position: absolute;
        top: 50px;
        left: 0;
        right: 0;
        margin: auto;
      }
    }
  </style>
</head>
<body>

  <!-- Título da página -->
  <h2>Gerador de Código de Barras (EAN-13)</h2>

  <!-- Área de formulário -->
  <div id="form-area">
    <!-- Campo de entrada para até 13 dígitos numéricos -->
    <input type="text" id="inputEAN"
           placeholder="Digite até 13 dígitos"
           maxlength="13"
           inputmode="numeric"
           pattern="\d{1,13}">
    <!-- Botões de ação -->
	<button onclick="imprimirCodigo()">Imprimir Código</button>
    <button onclick="exportarParaPDF()">Exportar para PDF</button>
    <button onclick="imprimirZPL()">Imprimir em ZPL</button>
	<button onclick="impressaoDireta('impressao_printer.php')">Impressão (php_printer.dll)</button>
	<button onclick="impressaoDireta('impressao_wkhtmltopdf.php')">Impressão (wkhtmltopdf)</button>
	<button onclick="impressaoDireta('impressao_shell_exec.php')">Impressão (shell_exec)</button>
  </div>

  <!-- Área que será impressa/exportada -->
  <div id="print-area">
    <!-- Primeira etiqueta -->
    <div class="etiqueta">
      <div class="blocoResultado" id="resultado1"></div>
      <svg id="barcode1"></svg>
    </div>

    <!-- Segunda etiqueta -->
    <div class="etiqueta">
      <div class="blocoResultado" id="resultado2"></div>
      <svg id="barcode2"></svg>
    </div>
  </div>

  <!-- Scripts da lógica -->
  <script>
    // Função para calcular o dígito verificador EAN-13 a partir dos 12 primeiros dígitos
    function calcularDigitoVerificador(ean12) {
      let soma = 0;
      for (let i = 0; i < 12; i++) {
        const num = parseInt(ean12[i]);
        soma += (i % 2 === 0) ? num : num * 3;
      }
      const resto = soma % 10;
      return (resto === 0) ? 0 : (10 - resto);
    }

    // Função principal que gera o código de barras e faz a busca
    function gerarCodigoEAN(eanInput) {
      let ean = eanInput.trim().replace(/\D/g, ""); // Remove tudo que não for número

      // Validação básica: apenas números, no máximo 13 dígitos
      if (!/^\d{1,13}$/.test(ean)) {
        alert("Digite apenas números, com até 13 dígitos.");
        return;
      }

      // Completa e valida o código EAN com o dígito verificador
      if (ean.length < 12) {
        ean = ean.padStart(12, '0');
        ean += calcularDigitoVerificador(ean);
      } else if (ean.length === 12) {
        ean += calcularDigitoVerificador(ean);
      } else if (ean.length === 13) {
        const corpo = ean.substring(0, 12);
        const dvInformado = parseInt(ean[12]);
        const dvCalculado = calcularDigitoVerificador(corpo);
        if (dvInformado !== dvCalculado) {
          alert("Código EAN-13 inválido. Dígito verificador incorreto.");
          return;
        }
      }

      // Gera visualmente o código de barras nas duas etiquetas
      JsBarcode("#barcode1", ean, {
        format: "EAN13",
        displayValue: true,
        lineColor: "#000",
        width: 1.5,
        height: 40,
        fontSize: 12
      });

      JsBarcode("#barcode2", ean, {
        format: "EAN13",
        displayValue: true,
        lineColor: "#000",
        width: 1.5,
        height: 40,
        fontSize: 12
      });

      // Armazena o EAN para uso posterior (ZPL)
      window.ultimoEAN = ean;

      // Realiza a busca dos dados relacionados ao código
      buscarDadosCSV(ean);
    }

    // Função para buscar dados no servidor (buscar.php)
    function buscarDadosCSV(ean13) {
      fetch("buscar.php?codigo=" + encodeURIComponent(ean13))
        .then(res => res.text())
        .then(dados => {
          // Nova lógica: se o retorno for "Código inexistente!", exibe uma mensagem formatada
          let conteudoFormatado;
          if (dados.trim() === "Código inexistente!") {
            conteudoFormatado = "CÓDIGO\nINEXISTENTE"; // quebra linha entre as palavras
          } else {
            conteudoFormatado = dados.replace(/ \| /g, '\n');
          }

          document.getElementById("resultado1").innerText = conteudoFormatado;
          document.getElementById("resultado2").innerText = conteudoFormatado;

          // Armazena o texto da busca para uso no botão de impressão ZPL
          window.ultimoTexto = conteudoFormatado;
        })
        .catch(err => {
          document.getElementById("resultado1").innerText = "Erro ao buscar dados.";
          document.getElementById("resultado2").innerText = "Erro ao buscar dados.";
        });
    }

    // Função para imprimir o conteúdo do código de barras
    function imprimirCodigo() {
      if (!document.getElementById("barcode1").innerHTML.trim()) {
        alert("Gere um código de barras antes de imprimir.");
        return;
      }
      window.print();
    }

    // Função para exportar a área de impressão para PDF (formato 9x3cm)
    async function exportarParaPDF() {
      const printArea = document.getElementById("print-area");

      if (!printArea || printArea.innerHTML.trim() === "") {
        alert("Gere um código de barras antes de exportar.");
        return;
      }

      const canvas = await html2canvas(printArea, { scale: 3 });
      const imgData = canvas.toDataURL("image/png");

      const { jsPDF } = window.jspdf;
      const doc = new jsPDF({
        orientation: "landscape",
        unit: "mm",
        format: [90, 30] // 9x3 cm
      });

      doc.addImage(imgData, "PNG", 0, 0, 90, 30);
      doc.save("etiquetas_codigo_ean13.pdf");
    }

    // Função para envio de dados ao backend para gerar e imprimir ZPL
    function imprimirZPL() {
      if (!window.ultimoEAN || !window.ultimoTexto) {
        alert("Gere um código de barras antes de imprimir.");
        return;
      }

      // Envia os dados para o arquivo ZPL do backend
      fetch("impressao_zpl.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          ean13: window.ultimoEAN,
          texto: window.ultimoTexto
        })
      })
      .then(res => res.text())
      .then(msg => alert(msg))
      .catch(err => alert("Erro ao enviar para impressão ZPL."));
    }

    // Elemento do campo de entrada
    const inputField = document.getElementById("inputEAN");

    // Aciona a geração ao pressionar Enter
    inputField.addEventListener("keydown", function (event) {
      if (event.key === "Enter") {
        event.preventDefault();
        gerarCodigoEAN(this.value);
      }
    });

    // Quando 13 dígitos forem preenchidos, gera automaticamente
    inputField.addEventListener("input", function () {
      const valor = this.value.replace(/\D/g, ""); // Remove letras e símbolos
      this.value = valor; // Atualiza o campo com somente números
      if (valor.length === 13) {
        gerarCodigoEAN(valor);
      }
    });

    // Foca automaticamente no campo de entrada ao carregar a página
    window.addEventListener("DOMContentLoaded", function () {
      // Quando o DOM estiver carregado, o foco será colocado no campo de entrada
      document.getElementById("inputEAN").focus();
    });
  </script>

</body>
</html>
