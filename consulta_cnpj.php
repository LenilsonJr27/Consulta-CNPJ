<?php

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta CNPJ</title>
    <style>
        body {
            font-family: system-ui, Arial, sans-serif;
            margin: 24px;
        }

        .row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        input {
            padding: 10px;
            font-size: 16px;
            width: 220px;
        }

        button {
            padding: 10px 14px;
            font-size: 16px;
            cursor: pointer;
        }

        .card {
            margin-top: 16px;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        .muted {
            color: #666;
        }

        .grid {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 8px;
            margin-top: 10px;
        }

        pre {
            margin-top: 14px;
            background: #f7f7f7;
            padding: 12px;
            border-radius: 10px;
            overflow: auto;
        }

        .error {
            color: #b00020;
        }
    </style>
</head>
</head>

<body>
    <h1>Consulta CNPJ (BrasilAPI)</h1>

    <div class="row">
        <label for="cnpj" class="muted">CNPJ:</label>
        <input id="cnpj" name="cnpj" placeholder="Digite o CNPJ" />
        <button id="btn">Consultar</button>
        <span id="status" class="muted"></span>
    </div>

    <div id="result" class="card" style="display: none;"></div>

    <script>
        const $ = (sel) => document.querySelector(sel);

        const inputCNPJ = $("#cnpj");
        const btn = $("#btn");
        const statusEl = $("#status");
        const resultEl = $("#result");

        function onlyDigits(str) {
            return (str || "").replace(/\D/g, "");
        }

        function setStatus(text, isError = false) {
            statusEl.textContent = text;
            statusEl.className = isError ? "error" : "muted";
        }

        function renderSuccess(data) {
            resultEl.style.display = "block";
            resultEl.innerHTML = `
                <strong>Empresa encontrada</strong>
                <div class="grid">
                    <div class="muted">UF</div><div>${data.uf ?? "-"}</div>
                    <div class="muted">CEP</div><div>${data.cep ?? "-"}</div>
                    <div class="muted">CNPJ</div><div>${data.cnpj ?? "-"}</div>
                    <div class="muted">País</div><div>${data.pais ?? "-"}</div>
                    <div class="muted">E-mail</div><div>${data.email ?? "-"}</div>
                    <div class="muted">Porte</div><div>${data.porte ?? "-"}</div>
                    <div class="muted">Bairro</div><div>${data.bairro ?? "-"}</div>
                    <div class="muted">Número de endereço</div><div>${data.numero ?? "-"}</div>
                    <div class="muted">Município</div><div>${data.municipio ?? "-"}</div>
                    <div class="muted">Logradouro</div><div>${data.logradouro ?? "-"}</div>
                    <div class="muted">CNAE Fiscal</div><div>${data.cnae_fiscal ?? "-"}</div>
                    <div class="muted">Código país</div><div>${data.codigo_pais ?? "-"}</div>
                    <div class="muted">Complemento</div><div>${data.complemento ?? "-"}</div>
                    <div class="muted">Código Porte</div><div>${data.codigo_porte ?? "-"}</div>
                    <div class="muted">Razão Social</div><div>${data.razao_social ?? "-"}</div>
                    <div class="muted">Nome Fantasia</div><div>${data.nome_fantasia ?? "-"}</div>
                    <div class="muted">Capital Social</div><div>${data.capital_social ?? "-"}</div>
                    <div class="muted">DDD Telefone 1</div><div>${data.ddd_telefone1 ?? "-"}</div>
                    <div class="muted">DDD Telefone 2</div><div>${data.ddd_telefone2 ?? "-"}</div>
                    <div class="muted">Opcão pelo MEI</div><div>${data.opcao_pelo_mei ?? "-"}</div>
                    <div class="muted">Código Município</div><div>${data.codigo_municipio ?? "-"}</div>
                    <div class="muted">Natureza Jurídica</div><div>${data.natureza_juridica ?? "-"}</div>
                    <div class="muted">Situacao Cadastral</div><div>${data.situacao_cadastral ?? "-"}</div>
                    <div class="muted">CNAE Fiscal Descrição</div><div>${data.cnae_fiscal_descricao ?? "-"}</div>
                    <div class="muted">Código Município IBGE</div><div>${data.codigo_municipio_ibge ?? "-"}</div>
                    <div class="muted">Data de Início Atividade</div><div>${data.data_inicio_atividade ?? "-"}</div>
                    <div class="muted">Data Situação Cadastral</div><div>${data.data_situacao_cadastral ?? "-"}</div>
                    <div class="muted">Código Natureza Jurídica</div><div>${data.codigo_natureza_juridica ?? "-"}</div>
                    <div class="muted">Identificador Matriz Filial</div><div>${data.identificador_matriz_filial ?? "-"}</div>
                    <div class="muted">Qualificão do Responsável</div><div>${data.qualificacao_do_responsavel ?? "-"}</div>
                    <div class="muted">Descrição Situação Cadastral</div><div>${data.descricao_situacao_cadastral ?? "-"}</div>
                    <div class="muted">Descrição Tipo de Logradouro</div><div>${data.descricao_tipo_de_logradouro ?? "-"}</div>
                    <div class="muted">Descrição Identificador Matriz Filial</div><div>${data.descricao_identificador_matriz_filial ?? "-"}</div>
                </div>

                <div class="muted" style="margin-top:12px;">JSON bruto (para debug):</div>
                <pre>${escapeHTML(JSON.stringify(data, null, 2))}</pre>
            `;
        }

        function renderError(message, detailsObj) {
            resultEl.style.display = "block";
            resultEl.innerHTML = `
                <strong class="error">Não foi possível consultar</strong>
                <p class="error">${escapeHtml(message)}</p>
                ${detailsObj ? `<pre>${escapeHtml(JSON.stringify(detailsObj, null, 2))}</pre>` : ""}
            `;
        }

        function escapeHTML(str) {
            return String(str)
                .replaceAll("&", "&amp;")
                .replaceAll("<", "&lt;")
                .replaceAll(">", "&gt;")
                .replaceAll('"', '&quot;')
                .replaceAll("'", "&#039;");
        }

        async function consultarCNPJ() {
            const cnpjDigits = onlyDigits(inputCNPJ.value);
            if (cnpjDigits.length !== 14) {
                setStatus("CEP inválido: use 14 dígitos.", true);
                resultEl.style.display = "none";
                return;
            }

            const url = `https://brasilapi.com.br/api/cnpj/v1/${cnpjDigits}`;

            setStatus("Carregando....");
            resultEl.style.display = "none";

            try {
                const res = await fetch(url, {
                    headers: {
                        "Accept": "application/json"
                    }
                });

                const text = await res.text();
                let body;
                try {
                    body = JSON.parse(text);
                } catch {
                    body = {
                        raw: text
                    };
                }

                if (!res.ok) {
                    const msg = body?.message || `HTTP ${res.status} ao consultar o CNPJ.`;
                    setStatus("Erro.", true);
                    renderError(msg, body);
                    return;
                }

                setStatus("OK");
                renderSuccess(body);
            } catch (err) {
                setStatus("Erro de rede.", true);
                renderError("Falha da rede ou CORS ao chamar a API.", {
                    error: String(err)
                });
            }
        }

        btn.addEventListener("click", consultarCNPJ);
        inputCNPJ.addEventListener("keydown", (e) => {
            if (e.key === "Enter") consultarCNPJ();
        });
    </script>



</body>

</html>