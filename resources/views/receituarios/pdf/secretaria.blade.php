<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ficha Cadastral - Secretaria de Saúde</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.3;
        }
        
        .container {
            padding: 20px;
        }
        
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin-bottom: 15px;
            padding: 8px;
            border: 2px solid #000;
            background-color: #e0e0e0;
        }
        
        .section-title {
            background-color: #d0d0d0;
            padding: 6px;
            font-weight: bold;
            font-size: 11pt;
            text-align: center;
            border: 1px solid #000;
            margin-top: 10px;
            margin-bottom: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }
        
        .label {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .value {
            font-size: 11pt;
            min-height: 18px;
        }
        
        .signature-boxes {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        
        .signature-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 5px;
            border: 1px solid #000;
            height: 120px;
            vertical-align: bottom;
        }
        
        .signature-label {
            font-size: 9pt;
            margin-top: 5px;
        }
        
        .note {
            font-size: 9pt;
            font-style: italic;
            margin-top: 10px;
            padding: 8px;
            border: 1px solid #000;
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            FICHA CADASTRAL PARA SECRETARIA DE SAÚDE E VIGILÂNCIA SANITÁRIA
        </div>

        <div class="section-title">
            DADOS DA INSTITUIÇÃO
        </div>

        <table>
            <tr>
                <td style="width: 60%;">
                    <div class="label">Razão Social:</div>
                    <div class="value">{{ $receituario->razao_social ?? '' }}</div>
                </td>
                <td style="width: 40%;">
                    <div class="label">CNPJ:</div>
                    <div class="value">{{ $receituario->cnpj ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td style="width: 50%;">
                    <div class="label">Município:</div>
                    <div class="value">{{ $receituario->municipio?->nome ?? $receituario->municipio ?? '' }}</div>
                </td>
                <td style="width: 50%;">
                    <div class="label">CEP:</div>
                    <div class="value">{{ $receituario->cep ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="label">Endereço:</div>
                    <div class="value">{{ $receituario->endereco ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td style="width: 50%;">
                    <div class="label">E-mail:</div>
                    <div class="value">{{ $receituario->email ?? '' }}</div>
                </td>
                <td style="width: 50%;">
                    <div class="label">Telefone:</div>
                    <div class="value">{{ $receituario->telefone ?? '' }}</div>
                </td>
            </tr>
        </table>

        <div class="section-title">
            DADOS DO(A) SECRETÁRIO(A) / COORDENADOR(A) DA VISA
        </div>

        <table>
            <tr>
                <td style="width: 70%;">
                    <div class="label">Nome:</div>
                    <div class="value">{{ $receituario->responsavel_nome ?? '' }}</div>
                </td>
                <td style="width: 30%;">
                    <div class="label">CPF:</div>
                    <div class="value">{{ $receituario->responsavel_cpf ?? '' }}</div>
                </td>
            </tr>
        </table>

        <div class="section-title">
            ASSINATURAS
        </div>

        <div class="signature-boxes">
            <div class="signature-box">
                <div style="height: 100px;"></div>
                <div class="signature-label">Assinatura sem carimbar</div>
            </div>
            <div class="signature-box">
                <div style="height: 100px;"></div>
                <div class="signature-label">Assinatura sem carimbar</div>
            </div>
            <div class="signature-box">
                <div style="height: 100px;"></div>
                <div class="signature-label">Assinatura sem carimbar</div>
            </div>
        </div>

        <div class="note">
            <strong>Atenção:</strong> As assinaturas desta ficha devem ser semelhante a do documento de identificação ou deve ser reconhecida em cartório.
        </div>
    </div>
</body>
</html>
