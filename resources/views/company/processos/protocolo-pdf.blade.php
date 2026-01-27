<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protocolo de Abertura - {{ $processo->numero_processo }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
            padding: 30px 40px;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #1e40af;
        }
        
        .logo-container img {
            max-height: 80px;
            max-width: 300px;
            height: auto;
            width: auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f0f7ff;
            border: 2px solid #1e40af;
            border-radius: 8px;
        }
        
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .header .subtitle {
            font-size: 12pt;
            color: #374151;
            margin-top: 5px;
        }
        
        .processo-numero {
            font-size: 24pt;
            font-weight: bold;
            color: #1e40af;
            margin: 15px 0;
            padding: 10px;
            background-color: #fff;
            border-radius: 5px;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            background-color: #e5e7eb;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-left: 4px solid #1e40af;
            color: #1f2937;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 8px 15px 8px 0;
            width: 35%;
            vertical-align: top;
            color: #4b5563;
        }
        
        .info-value {
            display: table-cell;
            padding: 8px 0;
            vertical-align: top;
            color: #111827;
        }
        
        .aviso-container {
            margin-top: 40px;
            padding: 20px;
            background-color: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 8px;
        }
        
        .aviso-titulo {
            font-size: 12pt;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .aviso-texto {
            font-size: 10pt;
            color: #78350f;
            line-height: 1.6;
        }
        
        .aviso-texto strong {
            color: #92400e;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            font-size: 9pt;
            color: #6b7280;
        }
        
        .footer .codigo {
            font-family: monospace;
            font-size: 8pt;
            color: #9ca3af;
            margin-top: 10px;
        }
        
        .qrcode-container {
            text-align: center;
            margin-top: 30px;
        }
        
        .qrcode-container img {
            width: 100px;
            height: 100px;
        }
        
        .qrcode-texto {
            font-size: 8pt;
            color: #6b7280;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    {{-- Logomarca --}}
    @if($logomarca)
        <div class="logo-container">
            @php
                $logoPathRelativo = str_replace('storage/', '', $logomarca);
                $logoPath = public_path('storage/' . $logoPathRelativo);
                
                if (file_exists($logoPath)) {
                    $logoData = base64_encode(file_get_contents($logoPath));
                    $logoExtension = pathinfo($logoPath, PATHINFO_EXTENSION);
                    $logoMimeType = $logoExtension === 'svg' ? 'svg+xml' : $logoExtension;
                    echo '<img src="data:image/' . $logoMimeType . ';base64,' . $logoData . '" alt="Logomarca">';
                }
            @endphp
        </div>
    @endif

    {{-- Cabeçalho --}}
    <div class="header">
        <h1>Protocolo de Abertura de Processo</h1>
        <div class="processo-numero">{{ $processo->numero_processo }}</div>
        <div class="subtitle">{{ $processo->tipo_nome ?? ($processo->tipoProcesso->nome ?? 'Processo') }}</div>
    </div>

    {{-- Dados do Processo --}}
    <div class="section">
        <div class="section-title">DADOS DO PROCESSO</div>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Número do Processo:</span>
                <span class="info-value">{{ $processo->numero_processo }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tipo de Processo:</span>
                <span class="info-value">{{ $processo->tipo_nome ?? ($processo->tipoProcesso->nome ?? 'N/A') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Data de Abertura:</span>
                <span class="info-value">{{ $processo->created_at->format('d/m/Y') }} às {{ $processo->created_at->format('H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Situação Atual:</span>
                <span class="info-value">{{ $processo->status_nome }}</span>
            </div>
        </div>
    </div>

    {{-- Dados do Estabelecimento --}}
    <div class="section">
        <div class="section-title">DADOS DO ESTABELECIMENTO</div>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">{{ $estabelecimento->tipo_pessoa === 'juridica' ? 'CNPJ' : 'CPF' }}:</span>
                <span class="info-value">
                    {{ $estabelecimento->tipo_pessoa === 'juridica' 
                        ? $estabelecimento->cnpj_formatado 
                        : $estabelecimento->cpf_formatado }}
                </span>
            </div>
            @if($estabelecimento->tipo_pessoa === 'juridica' && $estabelecimento->razao_social)
            <div class="info-row">
                <span class="info-label">Razão Social:</span>
                <span class="info-value">{{ $estabelecimento->razao_social }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Nome Fantasia:</span>
                <span class="info-value">{{ $estabelecimento->nome_fantasia ?? $estabelecimento->razao_social ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Endereço:</span>
                <span class="info-value">
                    {{ $estabelecimento->endereco }}
                    @if($estabelecimento->numero), {{ $estabelecimento->numero }}@endif
                    @if($estabelecimento->complemento), {{ $estabelecimento->complemento }}@endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Bairro:</span>
                <span class="info-value">{{ $estabelecimento->bairro ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Cidade/UF:</span>
                <span class="info-value">{{ $estabelecimento->cidade ?? 'N/A' }} - {{ $estabelecimento->estado ?? 'N/A' }}</span>
            </div>
            @if($estabelecimento->cep)
            <div class="info-row">
                <span class="info-label">CEP:</span>
                <span class="info-value">{{ $estabelecimento->cep }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Aviso Importante --}}
    <div class="aviso-container">
        <div class="aviso-titulo">
            ⚠️ AVISO IMPORTANTE
        </div>
        <div class="aviso-texto">
            <strong>Este documento é apenas um PROTOCOLO DE ABERTURA DE PROCESSO</strong> e comprova que o estabelecimento 
            acima identificado deu entrada em um processo junto à Vigilância Sanitária na data informada.
            <br><br>
            <strong>ESTE DOCUMENTO NÃO SUBSTITUI E NÃO TEM VALIDADE COMO ALVARÁ SANITÁRIO, LICENÇA SANITÁRIA 
            OU QUALQUER OUTRO DOCUMENTO DE AUTORIZAÇÃO DE FUNCIONAMENTO.</strong>
            <br><br>
            O estabelecimento somente estará autorizado a funcionar após a conclusão da análise do processo 
            e emissão do documento de licenciamento sanitário correspondente pela autoridade sanitária competente.
        </div>
    </div>

    {{-- Rodapé --}}
    <div class="footer">
        <p>Documento gerado eletronicamente em {{ now()->format('d/m/Y') }} às {{ now()->format('H:i') }}</p>
        <p>Sistema de Vigilância Sanitária - InfoVisa</p>
        <p class="codigo">Código de verificação: {{ strtoupper(substr(md5($processo->id . $processo->numero_processo . $processo->created_at), 0, 16)) }}</p>
    </div>
</body>
</html>
