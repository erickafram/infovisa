<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processo na Íntegra - {{ $processo->numero_processo }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
            padding: 20px;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }
        
        .logo-container img {
            max-height: 80px;
            max-width: 300px;
            height: auto;
            width: auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .header .subtitle {
            font-size: 12pt;
            color: #555;
        }
        
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 8px 10px;
            margin-bottom: 10px;
            border-left: 4px solid #333;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 5px 10px 5px 0;
            width: 35%;
            vertical-align: top;
        }
        
        .info-value {
            display: table-cell;
            padding: 5px 0;
            vertical-align: top;
        }
        
        .document-item {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #fafafa;
            page-break-inside: avoid;
        }
        
        .document-header {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 5px;
            color: #333;
        }
        
        .document-meta {
            font-size: 9pt;
            color: #666;
            margin-bottom: 5px;
        }
        
        .document-content {
            margin-top: 10px;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #eee;
            font-size: 9pt;
            line-height: 1.5;
        }
        
        .signatures {
            margin-top: 10px;
            padding: 8px;
            background-color: #f9f9f9;
            border-left: 3px solid #4CAF50;
        }
        
        .signatures-title {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 5px;
        }
        
        .signature-item {
            font-size: 9pt;
            padding: 3px 0;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #666;
            padding: 10px;
            border-top: 1px solid #ddd;
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
        <h1>Processo na Íntegra</h1>
        <div class="subtitle">{{ $processo->tipo_nome ?? ($processo->tipoProcesso->nome ?? 'Processo') }}</div>
    </div>

    {{-- Dados do Processo --}}
    <div class="section">
        <div class="section-title">📋 DADOS DO PROCESSO</div>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Tipo de Processo:</span>
                <span class="info-value">{{ $processo->tipo_nome ?? ($processo->tipoProcesso->nome ?? 'N/A') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Número do Processo:</span>
                <span class="info-value">{{ $processo->numero_processo ?? 'Sem número' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">{{ ucfirst(str_replace('_', ' ', $processo->status)) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Ano:</span>
                <span class="info-value">{{ $processo->ano }}</span>
            </div>
        </div>
    </div>

    {{-- Dados do Estabelecimento --}}
    <div class="section">
        <div class="section-title">🏢 DADOS DO ESTABELECIMENTO</div>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">{{ $estabelecimento->tipo_pessoa === 'juridica' ? 'CNPJ' : 'CPF' }}:</span>
                <span class="info-value">
                    {{ $estabelecimento->tipo_pessoa === 'juridica' 
                        ? $estabelecimento->cnpj_formatado 
                        : $estabelecimento->cpf_formatado }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Nome do Estabelecimento:</span>
                <span class="info-value">{{ $estabelecimento->nome_fantasia ?? $estabelecimento->razao_social }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Endereço Completo:</span>
                <span class="info-value">
                    {{ $estabelecimento->endereco }}
                    @if($estabelecimento->numero), {{ $estabelecimento->numero }}@endif
                    @if($estabelecimento->complemento), {{ $estabelecimento->complemento }}@endif
                    @if($estabelecimento->bairro) - {{ $estabelecimento->bairro }}@endif
                    <br>
                    {{ $estabelecimento->cidade }} - {{ $estabelecimento->estado }}
                    @if($estabelecimento->cep), CEP: {{ $estabelecimento->cep }}@endif
                </span>
            </div>
        </div>
    </div>

    {{-- Documentos Digitais --}}
    @if($documentosDigitais->count() > 0)
    <div class="section">
        <div class="section-title">📄 DOCUMENTOS DIGITAIS ({{ $documentosDigitais->count() }})</div>
        @foreach($documentosDigitais as $index => $doc)
        <div class="document-item">
            <div class="document-header">{{ $index + 1 }}. {{ $doc->nome ?? ($doc->tipoDocumento->nome ?? 'Documento Digital') }}</div>
            <div class="document-meta">
                Nº: {{ $doc->numero_documento ?? 'N/A' }} | 
                Status: {{ ucfirst($doc->status) }} | 
                Data: {{ $doc->created_at->format('d/m/Y H:i') }}
                @if($doc->usuarioCriador)
                | Criado por: {{ $doc->usuarioCriador->nome }}
                @endif
            </div>
            @if($doc->assinaturas->count() > 0)
            <div class="signatures">
                <div class="signatures-title">Assinaturas:</div>
                @foreach($doc->assinaturas as $assinatura)
                <div class="signature-item">
                    ✓ {{ $assinatura->usuario->nome ?? 'Usuário' }} - {{ $assinatura->assinado_em ? $assinatura->assinado_em->format('d/m/Y H:i') : 'Pendente' }}
                </div>
                @endforeach
            </div>
            @endif
            @if($doc->respostas->count() > 0)
            <div class="document-meta" style="margin-top: 5px;">
                <strong>Respostas ({{ $doc->respostas->count() }}):</strong>
                @foreach($doc->respostas as $resposta)
                <div style="padding-left: 10px;">
                    📎 {{ $resposta->nome_original }} - {{ ucfirst($resposta->status) }} - {{ $resposta->created_at->format('d/m/Y H:i') }}
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Arquivos Anexados --}}
    @if($processo->documentos->count() > 0)
    <div class="section">
        <div class="section-title">📎 ARQUIVOS ANEXADOS ({{ $processo->documentos->count() }})</div>
        @foreach($processo->documentos as $index => $documento)
        <div class="document-item">
            <div class="document-header">{{ $index + 1 }}. {{ $documento->nome_original }}</div>
            <div class="document-meta">
                Tipo: {{ $documento->tipo_usuario === 'interno' ? 'Interno' : 'Externo' }} | 
                Extensão: {{ strtoupper($documento->extensao) }} | 
                Tamanho: {{ $documento->tamanho_formatado }} |
                Data: {{ $documento->created_at->format('d/m/Y H:i') }}
                @if($documento->status_aprovacao)
                | Status: {{ ucfirst($documento->status_aprovacao) }}
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Ordens de Serviço --}}
    @if(isset($ordensServico) && $ordensServico->count() > 0)
    <div class="section">
        <div class="section-title">📋 ORDENS DE SERVIÇO ({{ $ordensServico->count() }})</div>
        @foreach($ordensServico as $index => $os)
        <div class="document-item">
            <div class="document-header">{{ $index + 1 }}. OS #{{ str_pad($os->numero, 5, '0', STR_PAD_LEFT) }}</div>
            <div class="document-meta">
                Status: {{ ucfirst(str_replace('_', ' ', $os->status)) }} | 
                Data: {{ $os->data_abertura ? $os->data_abertura->format('d/m/Y') : $os->created_at->format('d/m/Y') }}
                @if($os->data_inicio) | Início: {{ $os->data_inicio->format('d/m/Y') }} @endif
                @if($os->data_fim) | Fim: {{ $os->data_fim->format('d/m/Y') }} @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Nota: Os PDFs serão mesclados automaticamente após esta página --}}

    {{-- Rodapé --}}
    <div class="footer">
        Documento gerado em {{ now()->format('d/m/Y H:i') }} | Processo: {{ $processo->numero_processo }}
    </div>
</body>
</html>
