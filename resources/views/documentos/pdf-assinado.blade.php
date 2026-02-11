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
            padding-bottom: 100px;
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
            margin-top: 30px;
            padding: 10px 0;
            border-top: 2px solid #333;
            font-size: 7pt;
            color: #333;
            width: 100%;
        }
        
        .footer-content {
            display: table;
            width: 100%;
        }
        
        .footer-logo {
            display: table-cell;
            vertical-align: middle;
            width: 80px;
            padding-right: 10px;
        }
        
        .footer-logo img {
            max-height: 50px;
            max-width: 70px;
            height: auto;
            width: auto;
        }
        
        .footer-text {
            display: table-cell;
            vertical-align: middle;
            text-align: justify;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    {{-- QR Code --}}
    <div class="qrcode-container">
        <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code">
        <p>Verificar<br>Autenticidade</p>
    </div>

    {{-- Logomarca --}}
    @if(isset($logomarca) && $logomarca)
        <div class="logo-container">
            @php
                // Converte a logomarca para base64 para incluir no PDF
                // Remove 'storage/' do início se existir, pois public_path já aponta para 'public/'
                $logoPathRelativo = str_replace('storage/', '', $logomarca);
                $logoPath = public_path('storage/' . $logoPathRelativo);
                
                if (file_exists($logoPath)) {
                    $logoData = base64_encode(file_get_contents($logoPath));
                    $logoExtension = pathinfo($logoPath, PATHINFO_EXTENSION);
                    $logoMimeType = $logoExtension === 'svg' ? 'svg+xml' : $logoExtension;
                    echo '<img src="data:image/' . $logoMimeType . ';base64,' . $logoData . '" alt="Logomarca">';
                } else {
                    // Debug: mostra o caminho que tentou acessar
                    echo '<!-- Logomarca não encontrada: ' . $logoPath . ' -->';
                }
            @endphp
        </div>
    @endif

    {{-- Cabeçalho --}}
    <div class="header">
        <h1>{{ $documento->tipoDocumento->nome }}</h1>
        <div class="numero">{{ $documento->numero_documento }}</div>
    </div>

    {{-- Dados do Estabelecimento --}}
    @if($estabelecimento)
    <div class="section">
        <div class="section-title">Dados do Estabelecimento</div>
        <div class="info-grid">
            @php
                $responsavelLegal = $estabelecimento->responsaveis->where('pivot.tipo_vinculo', 'legal')->first();
                $responsavelTecnico = $estabelecimento->responsaveis->where('pivot.tipo_vinculo', 'tecnico')->first();
                
                // Formatar CEP (00000-000)
                $cepFormatado = $estabelecimento->cep;
                if (strlen($cepFormatado) === 8) {
                    $cepFormatado = substr($cepFormatado, 0, 5) . '-' . substr($cepFormatado, 5);
                }
                
                // Formatar telefone (00) 0000-0000 ou (00) 00000-0000
                $telefoneFormatado = '';
                if ($estabelecimento->telefone) {
                    $tel = preg_replace('/[^0-9]/', '', $estabelecimento->telefone);
                    if (strlen($tel) === 10) {
                        $telefoneFormatado = '(' . substr($tel, 0, 2) . ') ' . substr($tel, 2, 4) . '-' . substr($tel, 6);
                    } elseif (strlen($tel) === 11) {
                        $telefoneFormatado = '(' . substr($tel, 0, 2) . ') ' . substr($tel, 2, 5) . '-' . substr($tel, 7);
                    } else {
                        $telefoneFormatado = $estabelecimento->telefone;
                    }
                }
                
                // Formatar celular
                $celularFormatado = '';
                if ($estabelecimento->celular) {
                    $cel = preg_replace('/[^0-9]/', '', $estabelecimento->celular);
                    if (strlen($cel) === 10) {
                        $celularFormatado = '(' . substr($cel, 0, 2) . ') ' . substr($cel, 2, 4) . '-' . substr($cel, 6);
                    } elseif (strlen($cel) === 11) {
                        $celularFormatado = '(' . substr($cel, 0, 2) . ') ' . substr($cel, 2, 5) . '-' . substr($cel, 7);
                    } else {
                        $celularFormatado = $estabelecimento->celular;
                    }
                }
            @endphp
            
            <div class="two-columns">
                <div class="column">
                    <div class="info-row">
                        <span class="info-label">Nome Fantasia:</span>
                        <span class="info-value">{{ $estabelecimento->nome_fantasia ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Razão Social:</span>
                        <span class="info-value">{{ $estabelecimento->nome_razao_social }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">{{ $estabelecimento->tipo_pessoa === 'juridica' ? 'CNPJ' : 'CPF' }}:</span>
                        <span class="info-value">{{ $estabelecimento->documento_formatado }}</span>
                    </div>
                    @if($telefoneFormatado)
                    <div class="info-row">
                        <span class="info-label">Telefone:</span>
                        <span class="info-value">{{ $telefoneFormatado }}@if($celularFormatado), {{ $celularFormatado }}@endif</span>
                    </div>
                    @endif
                </div>
                
                <div class="column">
                    <div class="info-row">
                        <span class="info-label">CEP:</span>
                        <span class="info-value">{{ $cepFormatado }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Endereço:</span>
                        <span class="info-value">{{ $estabelecimento->endereco }}, {{ $estabelecimento->numero }}@if($estabelecimento->complemento), {{ $estabelecimento->complemento }}@endif</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Bairro:</span>
                        <span class="info-value">{{ $estabelecimento->bairro }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Cidade/UF:</span>
                        <span class="info-value">{{ $estabelecimento->cidade }}/{{ $estabelecimento->estado }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Responsáveis --}}
    @if($responsavelLegal || $responsavelTecnico)
    <div class="section">
        <div class="section-title">Responsáveis</div>
        <div class="info-grid">
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
    @endif

    {{-- Conteúdo do Documento --}}
    <div class="section">
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
        <div class="footer-content">
            <div class="footer-logo">
                @php
                    $logoVisaPath = public_path('img/logovisa.png');
                    if (file_exists($logoVisaPath)) {
                        $logoVisaData = base64_encode(file_get_contents($logoVisaPath));
                        echo '<img src="data:image/png;base64,' . $logoVisaData . '" alt="Logo VISA">';
                    }
                @endphp
            </div>
            <div class="footer-text">
                Superintendência de Vigilância em Saúde - Diretoria de Vigilância Sanitária - Anexo I da Secretaria de Estado de Saúde - Qd. 104 Norte, Av. LO-02, Conj. 01, Lotes 20/30 - Ed. Luaro Knopp (3° Andar) - CEP 77.006-022 - Palmas-TO. Contatos: (63) 3218-3264 – tocantins.visa@gmail.com
            </div>
        </div>
    </div>
</body>
</html>
