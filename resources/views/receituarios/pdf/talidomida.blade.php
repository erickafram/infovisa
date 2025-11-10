<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cadastramento de Prescritores de Talidomida</title>
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
            padding: 15px;
        }
        
        .signature-boxes {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 10px;
            border: 1px solid #000;
            height: 150px;
            vertical-align: bottom;
        }
        
        .signature-label {
            font-size: 10pt;
            margin-top: 5px;
            font-weight: bold;
        }
        
        .declaration {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #000;
            text-align: justify;
        }
        
        .visa-section {
            margin-top: 15px;
            border: 1px solid #000;
            padding: 10px;
            background-color: #f5f5f5;
        }
        
        .visa-title {
            font-weight: bold;
            font-size: 10pt;
            writing-mode: vertical-lr;
            transform: rotate(180deg);
            float: left;
            margin-right: 10px;
            padding: 5px;
            background-color: #d0d0d0;
            border: 1px solid #000;
        }
        
        .visa-content {
            margin-left: 40px;
        }
        
        .visa-field {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            CADASTRAMENTO DE PRESCRITORES DE MEDICAMENTO A BASE DE TALIDOMIDA
        </div>

        <div class="section-title">
            DADOS PESSOAIS
        </div>

        <table>
            <tr>
                <td style="width: 60%;">
                    <div class="label">Nome do Profissional:</div>
                    <div class="value">{{ $receituario->nome ?? '' }}</div>
                </td>
                <td style="width: 40%;">
                    <div class="label">CPF:</div>
                    <div class="value">{{ $receituario->cpf ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td style="width: 50%;">
                    <div class="label">Especialidade:</div>
                    <div class="value">{{ $receituario->especialidade ?? '' }}</div>
                </td>
                <td style="width: 50%;">
                    <div class="label">Nº CRM:</div>
                    <div class="value">{{ $receituario->numero_crm ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="label">Endereço Residencial:</div>
                    <div class="value">{{ $receituario->endereco_residencial ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td style="width: 50%;">
                    <div class="label">Município:</div>
                    <div class="value">{{ $receituario->municipio->nome ?? '' }}</div>
                </td>
                <td style="width: 50%;">
                    <div class="label">CEP:</div>
                    <div class="value">{{ $receituario->cep ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td style="width: 50%;">
                    <div class="label">E-mail:</div>
                    <div class="value">{{ $receituario->email ?? '' }}</div>
                </td>
                <td style="width: 50%;">
                    <div class="label">Telefones:</div>
                    <div class="value">{{ $receituario->telefone ?? '' }} @if($receituario->telefone2) | {{ $receituario->telefone2 }}@endif</div>
                </td>
            </tr>
        </table>

        <div class="section-title">
            LOCAL DE TRABALHO
        </div>

        <table>
            @if($receituario->locais_trabalho && count($receituario->locais_trabalho) > 0)
                @foreach($receituario->locais_trabalho as $index => $local)
                    @if(!empty($local['nome']))
                    <tr>
                        <td style="width: 60%;">
                            <div class="label">Endereço do local de trabalho:</div>
                            <div class="value">{{ $local['nome'] ?? '' }}</div>
                        </td>
                        <td style="width: 40%;">
                            <div class="label">CEP:</div>
                            <div class="value">{{ $local['cep'] ?? '' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%;">
                            <div class="label">E-mail:</div>
                            <div class="value">&nbsp;</div>
                        </td>
                        <td style="width: 50%;">
                            <div class="label">Telefones:</div>
                            <div class="value">&nbsp;</div>
                        </td>
                    </tr>
                    @endif
                @endforeach
            @else
                <tr>
                    <td style="width: 60%;">
                        <div class="label">Endereço do local de trabalho:</div>
                        <div class="value">&nbsp;</div>
                    </td>
                    <td style="width: 40%;">
                        <div class="label">CEP:</div>
                        <div class="value">&nbsp;</div>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%;">
                        <div class="label">E-mail:</div>
                        <div class="value">&nbsp;</div>
                    </td>
                    <td style="width: 50%;">
                        <div class="label">Telefones:</div>
                        <div class="value">&nbsp;</div>
                    </td>
                </tr>
            @endif
        </table>

        <div class="declaration">
            Declaro conhecer os riscos e as normas que envolvem a prescrição do medicamento a base de TALIDOMIDA.
            <br><br>
            _________________________, TO, _____ / _____ / _________
        </div>

        <div class="signature-boxes">
            <div class="signature-box">
                <div style="height: 120px;"></div>
                <div class="signature-label">Espaço para carimbo do Profissional</div>
            </div>
            <div class="signature-box">
                <div style="height: 120px;"></div>
                <div class="signature-label">(Assinatura)</div>
            </div>
        </div>

        <div class="visa-section">
            <div class="visa-title">CAMPOS EXCLUSIVOS DA VISA</div>
            <div class="visa-content">
                <div class="visa-field">
                    <strong>Responsável pelo Credenciamento:</strong>
                    <div style="border-bottom: 1px solid #000; min-height: 20px; margin-top: 5px;"></div>
                </div>
                <div class="visa-field">
                    <strong>Matrícula:</strong>
                    <div style="border-bottom: 1px solid #000; min-height: 20px; margin-top: 5px;"></div>
                </div>
                <div class="visa-field">
                    <strong>Local e Data:</strong>
                    <div style="border-bottom: 1px solid #000; min-height: 20px; margin-top: 5px;"></div>
                </div>
                <div class="visa-field" style="margin-top: 15px;">
                    <strong>Observações:</strong>
                    <div style="border: 1px solid #000; min-height: 60px; margin-top: 5px; padding: 5px;"></div>
                </div>
                <div style="margin-top: 15px; text-align: center;">
                    <div style="border-bottom: 2px solid #000; width: 60%; margin: 0 auto; padding-top: 40px;"></div>
                    <div style="margin-top: 5px;"><strong>Carimbo e assinatura do responsável pelo credenciamento</strong></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
