<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Serviço #{{ $ordemServico->numero }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            font-size: 11px;
        }
        
        .page {
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm;
            background: white;
        }
        
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 6px;
            margin-bottom: 8px;
            text-align: center;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-container img {
            max-height: 100px;
            max-width: 400px;
            height: auto;
            width: auto;
        }
        
        .header-title {
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }
        
        .header-subtitle {
            font-size: 9px;
            color: #666;
            margin-top: 2px;
        }
        
        .section {
            margin-bottom: 6px;
        }
        
        .section-title {
            background-color: #f0f0f0;
            padding: 4px 6px;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
            border-left: 3px solid #333;
        }
        
        .section-content {
            padding: 5px;
        }
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
            table-layout: fixed;
        }
        
        .info-item {
            display: table-cell;
            width: 20%;
            padding-right: 8px;
            vertical-align: top;
        }
        
        .label {
            font-weight: bold;
            color: #555;
            font-size: 8px;
            text-transform: uppercase;
        }
        
        .value {
            color: #000;
            margin-top: 1px;
            font-size: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-em-andamento {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-finalizada {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .status-cancelada {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .alert {
            padding: 10px;
            margin-bottom: 10px;
            border-left: 3px solid;
            font-size: 10px;
            line-height: 1.4;
        }
        
        .alert-green {
            background-color: #f0fdf4;
            border-color: #22c55e;
            color: #166534;
        }
        
        .alert-amber {
            background-color: #fffbeb;
            border-color: #f59e0b;
            color: #92400e;
        }
        
        .alert-red {
            background-color: #fef2f2;
            border-color: #ef4444;
            color: #991b1b;
        }
        
        .equipment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        
        .equipment-table th {
            background-color: #e5e7eb;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            border-bottom: 1px solid #999;
        }
        
        .equipment-table td {
            padding: 6px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
        }
        
        .equipment-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .atividade {
            background-color: #f9fafb;
            padding: 7px;
            margin-bottom: 5px;
            border-left: 3px solid #3b82f6;
        }
        
        .atividade-titulo {
            font-weight: bold;
            color: #1f2937;
            font-size: 10px;
            margin-bottom: 3px;
        }
        
        .atividade-info {
            font-size: 9px;
            color: #666;
            margin-bottom: 2px;
        }
        
        .footer {
            margin-top: 20px;
            padding: 15px 0;
            border-top: 2px solid #333;
            font-size: 7.5pt;
            color: #000;
            width: 100%;
            overflow: hidden;
        }

        .footer-content {
            width: 100%;
            overflow: hidden;
        }

        .footer-logo {
            float: left;
            width: 70px;
            padding-right: 12px;
        }

        .footer-logo img {
            max-height: 50px;
            max-width: 70px;
            height: auto;
            width: auto;
            display: block;
        }

        .footer-text {
            overflow: hidden;
            text-align: justify;
            line-height: 1.5;
            word-wrap: break-word;
        }

        .qrcode-section {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            text-align: center;
            background-color: #f9fafb;
        }

        .qrcode-section h4 {
            font-size: 10px;
            font-weight: bold;
            color: #374151;
            margin: 0 0 6px 0;
        }

        .qrcode-section img {
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }

        .qrcode-section p {
            font-size: 8px;
            color: #6b7280;
            margin: 5px 0 0 0;
        }
    </style>
</head>
<body>
    @php
        $estabelecimentoPdf = $estabelecimentoPdf ?? $ordemServico->estabelecimento;
        $processoPdf = $processoPdf ?? $ordemServico->processo;
    @endphp
    <div class="page">
        {{-- Logomarca --}}
        @if(isset($logomarca) && $logomarca)
            <div class="logo-container">
                @php
                    $logoPathRelativo = str_replace('storage/', '', $logomarca);
                    $logoPath = public_path('storage/' . $logoPathRelativo);

                    if (file_exists($logoPath)) {
                        try {
                            $imageData = file_get_contents($logoPath);
                            $imageInfo = getimagesize($logoPath);

                            if ($imageInfo && $imageData) {
                                $mimeType = $imageInfo['mime'];
                                $base64 = base64_encode($imageData);
                                echo '<img src="data:' . $mimeType . ';base64,' . $base64 . '" alt="Logomarca" style="max-width: 100%; height: auto;">';
                            }
                        } catch (\Exception $e) {
                            // Ignora erros
                        }
                    }
                @endphp
            </div>
        @endif

        {{-- Header --}}
        <div class="header">
            <div class="header-title">Ordem de Serviço #{{ str_pad($ordemServico->numero, 5, '0', STR_PAD_LEFT) }}</div>
            <div class="header-subtitle">
                @php
                    $statusMap = [
                        'em_andamento' => 'EM ANDAMENTO',
                        'finalizada' => 'FINALIZADA',
                        'cancelada' => 'CANCELADA'
                    ];
                @endphp
                Status: <span class="status-badge status-{{ $ordemServico->status }}">{{ $statusMap[$ordemServico->status] ?? 'DESCONHECIDO' }}</span>
                | Data: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>

        {{-- Informações da OS --}}
        <div class="section">
            <div class="section-title">INFORMAÇÕES DA ORDEM DE SERVIÇO</div>
            <div class="section-content">
                <div class="info-row">
                    <div class="info-item">
                        <div class="label">Número</div>
                        <div class="value">{{ str_pad($ordemServico->numero, 5, '0', STR_PAD_LEFT) }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Competência</div>
                        <div class="value">{{ ucfirst($ordemServico->competencia) }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Status</div>
                        <div class="value">{{ $statusMap[$ordemServico->status] ?? 'DESCONHECIDO' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Data de Criação</div>
                        <div class="value">{{ $ordemServico->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Município</div>
                        <div class="value">{{ $ordemServico->municipio?->nome ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Processo Vinculado</div>
                        <div class="value">{{ $processoPdf?->numero_processo ?? '-' }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-item" style="width: 100%;">
                        <div class="label">Descrição da OS</div>
                        <div class="value">{{ $ordemServico->observacoes ?? $ordemServico->descricao ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informações do Estabelecimento --}}
        @if($estabelecimentoPdf)
        <div class="section">
            <div class="section-title">DADOS DO ESTABELECIMENTO</div>
            <div class="section-content">
                <div class="info-row">
                    <div class="info-item">
                        <div class="label">Razão Social / Nome</div>
                        <div class="value">{{ $estabelecimentoPdf->razao_social ?? $estabelecimentoPdf->nome_fantasia }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Nome Fantasia</div>
                        <div class="value">{{ $estabelecimentoPdf->nome_fantasia ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">CNPJ / CPF</div>
                        <div class="value">
                            @if($estabelecimentoPdf->tipo_pessoa === 'fisica')
                                {{ $estabelecimentoPdf->cpf_formatado ?? '-' }}
                            @else
                                {{ $estabelecimentoPdf->cnpj_formatado ?? '-' }}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-item">
                        <div class="label">Endereço</div>
                        <div class="value">
                            {{ $estabelecimentoPdf->logradouro }}
                            @if($estabelecimentoPdf->numero), {{ $estabelecimentoPdf->numero }}@endif
                            @if($estabelecimentoPdf->complemento) - {{ $estabelecimentoPdf->complemento }}@endif
                            , {{ $estabelecimentoPdf->bairro }}
                            @if(is_object($estabelecimentoPdf->municipio)) - {{ $estabelecimentoPdf->municipio->nome }}/{{ $estabelecimentoPdf->municipio->uf }}@endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Status de Equipamentos de Imagem --}}
        @php
            $codigosAtividadesRadiacao = \App\Models\AtividadeEquipamentoRadiacao::where('ativo', true)
                ->pluck('codigo_atividade')
                ->map(fn($c) => preg_replace('/[^0-9]/', '', $c))
                ->unique()
                ->filter()
                ->toArray();
            
            $exigeEquipamentos = false;
            if ($estabelecimentoPdf) {
                $atividadesEstabelecimento = $estabelecimentoPdf->getTodasAtividades();
                foreach ($atividadesEstabelecimento as $codigo) {
                    if (in_array($codigo, $codigosAtividadesRadiacao)) {
                        $exigeEquipamentos = true;
                        break;
                    }
                }
            }
        @endphp

        @if($exigeEquipamentos && $estabelecimentoPdf)
        <div class="section">
            <div class="section-title">EQUIPAMENTOS DE IMAGEM</div>
            <div class="section-content">
                @if($estabelecimentoPdf->equipamentosRadiacao()->count() > 0)
                    <div class="alert alert-green">
                        <strong>✓ Equipamentos Registrados</strong><br>
                        Este estabelecimento possui {{ $estabelecimentoPdf->equipamentosRadiacao()->count() }} equipamento(s) de imagem cadastrado(s).
                    </div>
                    <table class="equipment-table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Fabricante</th>
                                <th>Modelo</th>
                                <th>Registro ANVISA</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($estabelecimentoPdf->equipamentosRadiacao()->get() as $equipamento)
                            <tr>
                                <td>{{ $equipamento->tipo_equipamento }}</td>
                                <td>{{ $equipamento->fabricante }}</td>
                                <td>{{ $equipamento->modelo }}</td>
                                <td>{{ $equipamento->registro_anvisa }}</td>
                                <td>{{ ucfirst($equipamento->status) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @elseif($estabelecimentoPdf->declaracao_sem_equipamentos_imagem)
                    <div class="alert alert-amber">
                        <strong>⚠ Declaração: Não Possui Equipamentos</strong><br>
                        O estabelecimento declarou formalmente que NÃO POSSUI equipamentos de imagem.
                        
                        @if($estabelecimentoPdf->declaracao_sem_equipamentos_opcoes)
                        @php
                            $opcoes = json_decode($estabelecimentoPdf->declaracao_sem_equipamentos_opcoes, true) ?? [];
                        @endphp
                        @if(count($opcoes) > 0)
                        <br><br><strong>Confirmações:</strong><br>
                        @if(in_array('opcao_1', $opcoes))
                        [X] Não executa atividades de diagnóstico por imagem neste estabelecimento<br>
                        @endif
                        @if(in_array('opcao_2', $opcoes))
                        [X] Não possui equipamentos de diagnóstico por imagem instalados no local<br>
                        @endif
                        @if(in_array('opcao_3', $opcoes))
                        [X] Os exames, quando necessários, são integralmente terceirizados ou realizados em outro estabelecimento regularmente licenciado<br>
                        @endif
                        @endif
                        @endif
                        
                        @if($estabelecimentoPdf->declaracao_sem_equipamentos_imagem_justificativa)
                        <br><strong>Justificativa:</strong> {{ $estabelecimentoPdf->declaracao_sem_equipamentos_imagem_justificativa }}
                        @endif
                    </div>
                @else
                    <div class="alert alert-red">
                        <strong>✗ Equipamentos Não Registrados</strong><br>
                        Este estabelecimento não possui equipamentos de imagem cadastrados e nem declaração formal.
                    </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Ações Executadas --}}
        @if($ordemServico->atividades_tecnicos && count($ordemServico->atividades_tecnicos) > 0)
        <div class="section">
            <div class="section-title">DETALHAMENTO DAS AÇÕES E TÉCNICOS RESPONSÁVEIS</div>
            <div class="section-content">
                @foreach($ordemServico->atividades_tecnicos as $index => $atividade)
                <div class="atividade">
                    <div class="atividade-titulo">{{ $index + 1 }}. {{ $atividade['nome_atividade'] ?? 'Atividade Desconhecida' }}</div>
                    
                    {{-- Técnicos Atribuídos --}}
                    @php
                        $responsavelId = $atividade['responsavel_id'] ?? null;
                        $tecnicosIds = $atividade['tecnicos'] ?? [];
                        $tecnicos = \App\Models\UsuarioInterno::whereIn('id', $tecnicosIds)->get();
                        $tecnicosListados = [];
                        
                        // Primeiro adiciona o responsável com destaque
                        if ($responsavelId) {
                            $responsavel = $tecnicos->firstWhere('id', $responsavelId);
                            if ($responsavel) {
                                $tecnicosListados[] = $responsavel->nome . ' - Técnico Responsável';
                            }
                        }
                        
                        // Depois adiciona os outros técnicos
                        foreach ($tecnicos as $tec) {
                            if ($tec->id != $responsavelId) {
                                $tecnicosListados[] = $tec->nome;
                            }
                        }
                    @endphp
                    
                    @if(count($tecnicosListados) > 0)
                    <div class="atividade-info">
                        <strong>Técnicos:</strong> {{ implode(', ', $tecnicosListados) }}
                    </div>
                    @endif
                    
                    {{-- Status --}}
                    @php
                        $statusAtividade = $atividade['status'] ?? 'pendente';
                        $statusLabel = $statusAtividade === 'finalizada' ? 'Finalizada' : 'Pendente';
                    @endphp
                    <div class="atividade-info">
                        <strong>Status:</strong> {{ $statusLabel }}
                    </div>
                    
                    @if(isset($atividade['observacoes']) && $atividade['observacoes'])
                    <div class="atividade-info">
                        <strong>Observações:</strong> {{ $atividade['observacoes'] }}
                    </div>
                    @endif
                    
                    @if($statusAtividade === 'finalizada' && isset($atividade['finalizada_em']))
                    <div class="atividade-info">
                        <strong>Data de Finalização:</strong> {{ \Carbon\Carbon::parse($atividade['finalizada_em'])->format('d/m/Y H:i') }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Ações Executadas (Compatibilidade com formato antigo) --}}

        {{-- QR Code Pesquisa de Satisfação --}}
        @if(!empty($qrCodePesquisaBase64))
        <div class="qrcode-section">
            <h4>Pesquisa de Satisfação</h4>
            <img src="data:image/png;base64,{{ $qrCodePesquisaBase64 }}" alt="QR Code Pesquisa">
            <p>Escaneie o QR Code acima para avaliar o atendimento desta Ordem de Serviço.</p>
            @if(!empty($linkPesquisaExterna))
            <p style="font-size: 7px; color: #9ca3af; margin-top: 2px;">{{ $linkPesquisaExterna }}</p>
            @endif
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <div class="footer-content">
                <div class="footer-logo">
                    @php
                        try {
                            $rodapeImagePath = public_path('img/rodape.jpeg');
                            if (file_exists($rodapeImagePath)) {
                                $imageData = file_get_contents($rodapeImagePath);
                                $imageInfo = getimagesize($rodapeImagePath);

                                if ($imageInfo && $imageData) {
                                    $mimeType = $imageInfo['mime'];
                                    $base64 = base64_encode($imageData);
                                    echo '<img src="data:' . $mimeType . ';base64,' . $base64 . '" alt="Rodapé" style="max-width: 100%; height: auto;">';
                                }
                            }
                        } catch (\Exception $e) {
                            // Ignora erros
                        }
                    @endphp
                </div>
                <div class="footer-text">
                    Diretoria de Vigilância Sanitária - Anexo I da Secretaria de Estado de Saúde - Qd. 104 Norte, Av. LO-02, Conj. 01, Lotes 20/30 - Ed. Luaro Knopp (3° Andar) - CEP 77.006-022 - Palmas-TO.<br>
                    Contatos: (63) 3027-4486 - (63) 3027-4475 - (63) 3027-4432 – tocantins.visa@gmail.com
                </div>
            </div>
        </div>
    </div>
</body>
</html>
