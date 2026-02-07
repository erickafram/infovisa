<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $documento->tipoDocumento->nome }} - {{ $documento->numero_documento }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.3;
            color: #000;
            padding: 15px;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .logo-container img {
            max-height: 60px;
            max-width: 200px;
            height: auto;
            width: auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #ccc;
        }
        
        .header h1 {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .header .numero {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .header .processo {
            font-size: 8pt;
        }
        
        .section {
            margin-bottom: 10px;
        }
        
        .section-title {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 5px;
            padding-bottom: 2px;
            border-bottom: 1px solid #ccc;
        }
        
        .info-grid {
            font-size: 7pt;
            line-height: 1.5;
        }
        
        .info-row {
            margin-bottom: 3px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        
        .info-value {
            display: inline;
        }
        
        .two-columns {
            width: 100%;
            display: table;
            table-layout: fixed;
        }
        
        .two-columns .column {
            width: 50%;
            display: table-cell;
            vertical-align: top;
            padding-right: 10px;
        }
        
        .two-columns .column:last-child {
            padding-right: 0;
            padding-left: 10px;
        }
        
        .content {
            margin: 15px 0;
            padding: 10px;
            border: 1px solid #ccc;
            min-height: 150px;
            font-size: 8pt;
        }
        
        .preview-notice {
            margin-top: 20px;
            padding: 10px;
            border: 2px solid #f59e0b;
            background-color: #fef3c7;
            text-align: center;
        }
        
        .preview-notice-title {
            font-weight: bold;
            font-size: 9pt;
            color: #92400e;
            margin-bottom: 5px;
        }
        
        .preview-notice-text {
            font-size: 7pt;
            color: #92400e;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 6pt;
            color: #666;
        }
    </style>
</head>
<body>
    {{-- Logomarca --}}
    @if(isset($logomarca) && $logomarca)
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
        <h1>{{ $documento->tipoDocumento->nome }}</h1>
        <div class="numero">{{ $documento->numero_documento }}</div>
        @if($processo)
            <div class="processo">
                @php
                    $tipoNome = $processo->tipo_nome ?? ($processo->tipoProcesso->nome ?? 'Processo');
                    $numeroProcesso = $processo->numero_processo ?? 'S/N';
                @endphp
                {{ $tipoNome }}: <strong>{{ $numeroProcesso }}</strong>
            </div>
        @endif
    </div>

    {{-- Dados do Estabelecimento --}}
    @if($estabelecimento)
    <div class="section">
        <div class="section-title">Dados do Estabelecimento</div>
        <div class="info-grid">
            @php
                $responsavelLegal = $estabelecimento->responsaveis->where('pivot.tipo_vinculo', 'legal')->first();
                $responsavelTecnico = $estabelecimento->responsaveis->where('pivot.tipo_vinculo', 'tecnico')->first();
            @endphp
            
            <div class="two-columns">
                <div class="column">
                    <div class="info-row">
                        <span class="info-label">Nome Fantasia:</span>
                        <span class="info-value">{{ $estabelecimento->nome_fantasia ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">{{ $estabelecimento->tipo_pessoa === 'juridica' ? 'CNPJ' : 'CPF' }}:</span>
                        <span class="info-value">{{ $estabelecimento->documento_formatado }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">CEP:</span>
                        <span class="info-value">{{ $estabelecimento->cep }}</span>
                    </div>
                    @if($estabelecimento->telefone)
                    <div class="info-row">
                        <span class="info-label">Telefone:</span>
                        <span class="info-value">{{ $estabelecimento->telefone }}@if($estabelecimento->celular), {{ $estabelecimento->celular }}@endif</span>
                    </div>
                    @endif
                </div>
                
                <div class="column">
                    <div class="info-row">
                        <span class="info-label">Razão Social:</span>
                        <span class="info-value">{{ $estabelecimento->nome_razao_social }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Endereço:</span>
                        <span class="info-value">{{ $estabelecimento->logradouro }}, {{ $estabelecimento->numero }}@if($estabelecimento->complemento), {{ $estabelecimento->complemento }}@endif</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Bairro/Cidade:</span>
                        <span class="info-value">{{ $estabelecimento->bairro }} - {{ $estabelecimento->cidade }}/{{ $estabelecimento->estado }}</span>
                    </div>
                </div>
            </div>
            
            @if($responsavelLegal)
            <div class="info-row">
                <span class="info-label">Responsável Legal:</span>
                <span class="info-value">{{ $responsavelLegal->nome }} - CPF: {{ $responsavelLegal->cpf_formatado }}</span>
            </div>
            @endif
            
            @if($responsavelTecnico)
            <div class="info-row">
                <span class="info-label">Responsável Técnico:</span>
                <span class="info-value">{{ $responsavelTecnico->nome }} - CPF: {{ $responsavelTecnico->cpf_formatado }}@if($responsavelTecnico->conselho_profissional && $responsavelTecnico->numero_registro) - {{ $responsavelTecnico->conselho_profissional }}: {{ $responsavelTecnico->numero_registro }}@endif</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Conteúdo do Documento --}}
    <div class="section">
        <div class="section-title">Conteúdo do Documento</div>
        <div class="content">
            {!! $documento->conteudo !!}
        </div>
    </div>

    {{-- Aviso de Preview --}}
    <div class="preview-notice">
        <div class="preview-notice-title">⚠️ DOCUMENTO EM VISUALIZAÇÃO</div>
        <div class="preview-notice-text">
            Este é um preview do documento. Após a assinatura, o documento final incluirá as assinaturas eletrônicas e código de autenticidade.
        </div>
    </div>

    {{-- Rodapé --}}
    <div class="footer">
        Documento gerado eletronicamente pelo Sistema InfoVISA em {{ $documento->created_at->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
