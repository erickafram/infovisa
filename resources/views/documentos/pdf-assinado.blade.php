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
        
        .qrcode-container {
            position: absolute;
            top: 10px;
            right: 15px;
            text-align: center;
        }
        
        .qrcode-container img {
            width: 80px;
            height: 80px;
        }
        
        .qrcode-container p {
            font-size: 6pt;
            margin-top: 2px;
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
        
        .signatures {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #ccc;
        }
        
        .signature-item {
            margin-bottom: 2px;
            padding: 3px 5px;
            border: 1px solid #ccc;
            font-size: 7pt;
            line-height: 1.2;
        }
        
        .authenticity {
            margin-top: 12px;
            padding: 6px 8px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }
        
        .authenticity-title {
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 8pt;
        }
        
        .authenticity-text {
            font-size: 6pt;
            line-height: 1.2;
        }
        
        .authenticity-code {
            font-family: 'Courier New', monospace;
            padding: 2px 3px;
            border: 1px solid #ccc;
            background-color: #fff;
            display: inline-block;
            margin-top: 2px;
            font-size: 6pt;
            word-break: break-all;
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
    {{-- QR Code --}}
    <div class="qrcode-container">
        <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code">
        <p>Verificar<br>Autenticidade</p>
    </div>

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

    {{-- Assinaturas Eletrônicas --}}
    @if($assinaturas->count() > 0)
    <div class="signatures">
        <div class="section-title">Assinaturas Eletrônicas</div>
        @foreach($assinaturas as $assinatura)
        <div class="signature-item">
            <strong>Assinado por {{ $assinatura->usuarioInterno->nome }}</strong> em {{ $assinatura->assinado_em->format('d/m/Y') }} às {{ $assinatura->assinado_em->format('H:i:s') }}
        </div>
        @endforeach
    </div>
    @endif

    {{-- Autenticidade --}}
    <div class="authenticity">
        <div class="authenticity-title">🔒 Verificação de Autenticidade</div>
        <div class="authenticity-text">
            A autenticidade deste documento pode ser conferida através do link:<br>
            <strong>{{ $urlAutenticidade }}</strong>
            <br><br>
            Caso necessário, o código do documento é:<br>
            <span class="authenticity-code">{{ $codigoAutenticidade }}</span>
        </div>
    </div>

    {{-- Rodapé --}}
    <div class="footer">
        Documento gerado eletronicamente pelo Sistema InfoVISA em {{ $documento->created_at->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
