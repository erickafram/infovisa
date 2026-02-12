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
            border-bottom: 1px solid #d0d0d0;
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
        
        .info-grid {
            font-size: 8pt;
            line-height: 1.5;
        }

        .cabecalho-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #d0d0d0;
            font-size: 8pt;
        }

        .cabecalho-table td {
            padding: 6px 8px;
            vertical-align: top;
            word-wrap: break-word;
            border-bottom: 1px solid #eaeaea;
        }

        .cabecalho-table tr:last-child td {
            border-bottom: none;
        }

        .cabecalho-table td + td {
            border-left: 1px solid #eaeaea;
        }

        .cabecalho-label {
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .content {
            margin: 15px 0;
            padding: 10px;
            border: none;
            min-height: 150px;
            font-size: 8pt;
        }
        
        .preview-notice {
            margin-top: 20px;
            padding: 0;
            border: none;
            background: transparent;
            text-align: center;
        }
        
        .preview-notice-title {
            font-weight: bold;
            font-size: 6pt;
            color: #000;
            margin-bottom: 5px;
        }
        
        .preview-notice-text {
            font-size: 6pt;
            color: #000;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: none;
            text-align: center;
            font-size: 7.5pt;
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
    </div>

     {{-- Dados do Estabelecimento --}}
    @if($estabelecimento)
    <div class="section">
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
            
            <table class="cabecalho-table">
                <tr>
                    <td>
                        <span class="cabecalho-label">Razão Social:</span>
                        {{ $estabelecimento->nome_razao_social }}
                    </td>
                    <td>
                        <span class="cabecalho-label">Nome Fantasia:</span>
                        {{ $estabelecimento->nome_fantasia ?? 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="cabecalho-label">{{ $estabelecimento->tipo_pessoa === 'juridica' ? 'CNPJ' : 'CPF' }}:</span>
                        {{ $estabelecimento->documento_formatado }}
                    </td>
                    <td>
                        <span class="cabecalho-label">Município:</span>
                        {{ $estabelecimento->cidade ?? 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="cabecalho-label">Endereço:</span>
                        {{ $estabelecimento->endereco }}, {{ $estabelecimento->numero }}@if($estabelecimento->complemento), {{ $estabelecimento->complemento }}@endif
                    </td>
                    <td>
                        <span class="cabecalho-label">CEP:</span>
                        {{ $cepFormatado }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="cabecalho-label">Bairro:</span>
                        {{ $estabelecimento->bairro ?? 'N/A' }}
                    </td>
                    <td>
                        <span class="cabecalho-label">Telefone:</span>
                        {{ $telefoneFormatado }}@if($celularFormatado), {{ $celularFormatado }}@endif
                    </td>
                </tr>
                @if($responsavelLegal)
                <tr>
                    <td>
                        <span class="cabecalho-label">Responsável Legal:</span>
                        {{ $responsavelLegal->nome }}
                    </td>
                    <td>
                        <span class="cabecalho-label">CPF:</span>
                        {{ $responsavelLegal->cpf_formatado }}
                    </td>
                </tr>
                @endif
                @if($responsavelTecnico)
                <tr>
                    <td>
                        <span class="cabecalho-label">Responsável Técnico:</span>
                        {{ $responsavelTecnico->nome }}
                    </td>
                    <td>
                        <span class="cabecalho-label">CPF:</span>
                        {{ $responsavelTecnico->cpf_formatado }}
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>
    @endif

    {{-- Conteúdo do Documento --}}
    <div class="section">
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
