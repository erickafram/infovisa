<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ficha Cadastral - Médico/Dentista/Veterinário</title>
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
        
        .signature-section {
            margin-top: 15px;
            border: 1px solid #000;
            padding: 10px;
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
        
        .two-columns {
            display: table;
            width: 100%;
        }
        
        .column {
            display: table-cell;
            width: 50%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            FICHA CADASTRAL PARA MÉDICO, CIR. DENTISTA E MÉDICO VETERINÁRIO
        </div>

        <div class="section-title">
            *DADOS PESSOAIS QUE DEVEM SER IMPRESSOS NA NOTIFICAÇÃO DE RECEITA port. 344/98 Art. 55 alínea a.
        </div>

        <table>
            <tr>
                <td style="width: 60%;">
                    <div class="label">Nome:</div>
                    <div class="value">{{ $receituario->nome ?? '' }}</div>
                </td>
                <td style="width: 40%;">
                    <div class="label">CPF:</div>
                    <div class="value">{{ $receituario->cpf ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td style="width: 40%;">
                    <div class="label">Especialidade:</div>
                    <div class="value">{{ $receituario->especialidade ?? '' }}</div>
                </td>
                <td style="width: 30%;">
                    <div class="label">Telefone:</div>
                    <div class="value">{{ $receituario->telefone ?? '' }}</div>
                </td>
                <td style="width: 30%;">
                    <div class="label">Nº / Cons. de Classe:</div>
                    <div class="value">{{ $receituario->numero_conselho_classe ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td style="width: 50%;">
                    <div class="label">Endereço:</div>
                    <div class="value">{{ $receituario->endereco ?? '' }}</div>
                </td>
                <td style="width: 25%;">
                    <div class="label">CEP:</div>
                    <div class="value">{{ $receituario->cep ?? '' }}</div>
                </td>
                <td style="width: 25%;">
                    <div class="label">Município:</div>
                    <div class="value">{{ $receituario->municipio->nome ?? '' }}</div>
                </td>
            </tr>
        </table>

        <div class="section-title">
            LOCAIS DE TRABALHO
        </div>

        <table>
            @if($receituario->locais_trabalho && count($receituario->locais_trabalho) > 0)
                @foreach($receituario->locais_trabalho as $local)
                    @if(!empty($local['nome']))
                    <tr>
                        <td style="width: 70%;">
                            <div class="label">Nome:</div>
                            <div class="value">{{ $local['nome'] ?? '' }}</div>
                        </td>
                        <td style="width: 30%;">
                            <div class="label">Município:</div>
                            <div class="value">{{ $local['municipio'] ?? '' }}</div>
                        </td>
                    </tr>
                    @endif
                @endforeach
                @if(count($receituario->locais_trabalho) < 2)
                    @for($i = count($receituario->locais_trabalho); $i < 2; $i++)
                    <tr>
                        <td style="width: 70%;">
                            <div class="label">Nome:</div>
                            <div class="value">&nbsp;</div>
                        </td>
                        <td style="width: 30%;">
                            <div class="label">Município:</div>
                            <div class="value">&nbsp;</div>
                        </td>
                    </tr>
                    @endfor
                @endif
            @else
                <tr>
                    <td style="width: 70%;">
                        <div class="label">Nome:</div>
                        <div class="value">&nbsp;</div>
                    </td>
                    <td style="width: 30%;">
                        <div class="label">Município:</div>
                        <div class="value">&nbsp;</div>
                    </td>
                </tr>
                <tr>
                    <td style="width: 70%;">
                        <div class="label">Nome:</div>
                        <div class="value">&nbsp;</div>
                    </td>
                    <td style="width: 30%;">
                        <div class="label">Município:</div>
                        <div class="value">&nbsp;</div>
                    </td>
                </tr>
            @endif
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
