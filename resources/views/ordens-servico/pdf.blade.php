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
            height: 297mm;
            margin: 0 auto;
            padding: 15mm;
            background: white;
        }
        
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .header-title {
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }
        
        .header-subtitle {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }
        
        .section {
            margin-bottom: 12px;
        }
        
        .section-title {
            background-color: #f0f0f0;
            padding: 6px 8px;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 8px;
            border-left: 3px solid #333;
        }
        
        .section-content {
            padding: 8px;
        }
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
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
            font-size: 9px;
            text-transform: uppercase;
        }
        
        .value {
            color: #000;
            margin-top: 2px;
            font-size: 11px;
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
            padding: 10px;
            margin-bottom: 8px;
            border-left: 3px solid #3b82f6;
        }
        
        .atividade-titulo {
            font-weight: bold;
            color: #1f2937;
            font-size: 11px;
            margin-bottom: 4px;
        }
        
        .atividade-info {
            font-size: 10px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .footer {
            border-top: 1px solid #ddd;
            margin-top: 20px;
            padding-top: 10px;
            font-size: 9px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="page">
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
                </div>
            </div>
        </div>

        {{-- Informações do Estabelecimento --}}
        @if($ordemServico->estabelecimento)
        <div class="section">
            <div class="section-title">DADOS DO ESTABELECIMENTO</div>
            <div class="section-content">
                <div class="info-row">
                    <div class="info-item">
                        <div class="label">Razão Social / Nome</div>
                        <div class="value">{{ $ordemServico->estabelecimento->razao_social ?? $ordemServico->estabelecimento->nome_fantasia }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">CNPJ / CPF</div>
                        <div class="value">
                            @if($ordemServico->estabelecimento->tipo_pessoa === 'fisica')
                                {{ $ordemServico->estabelecimento->cpf_formatado ?? '-' }}
                            @else
                                {{ $ordemServico->estabelecimento->cnpj_formatado ?? '-' }}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-item">
                        <div class="label">Endereço</div>
                        <div class="value">
                            {{ $ordemServico->estabelecimento->logradouro }}
                            @if($ordemServico->estabelecimento->numero), {{ $ordemServico->estabelecimento->numero }}@endif
                            @if($ordemServico->estabelecimento->complemento) - {{ $ordemServico->estabelecimento->complemento }}@endif
                            , {{ $ordemServico->estabelecimento->bairro }}
                            @if(is_object($ordemServico->estabelecimento->municipio)) - {{ $ordemServico->estabelecimento->municipio->nome }}/{{ $ordemServico->estabelecimento->municipio->uf }}@endif
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
            
            $atividadesEstabelecimento = $ordemServico->estabelecimento->getTodasAtividades();
            $exigeEquipamentos = false;
            foreach ($atividadesEstabelecimento as $codigo) {
                if (in_array($codigo, $codigosAtividadesRadiacao)) {
                    $exigeEquipamentos = true;
                    break;
                }
            }
        @endphp

        @if($exigeEquipamentos && $ordemServico->estabelecimento)
        <div class="section">
            <div class="section-title">EQUIPAMENTOS DE IMAGEM</div>
            <div class="section-content">
                @if($ordemServico->estabelecimento->equipamentosRadiacao()->count() > 0)
                    <div class="alert alert-green">
                        <strong>✓ Equipamentos Registrados</strong><br>
                        Este estabelecimento possui {{ $ordemServico->estabelecimento->equipamentosRadiacao()->count() }} equipamento(s) de imagem cadastrado(s).
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
                            @foreach($ordemServico->estabelecimento->equipamentosRadiacao()->get() as $equipamento)
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
                @elseif($ordemServico->estabelecimento->declaracao_sem_equipamentos_imagem)
                    <div class="alert alert-amber">
                        <strong>⚠ Declaração: Não Possui Equipamentos</strong><br>
                        O estabelecimento declarou formalmente que NÃO POSSUI equipamentos de imagem.
                        @if($ordemServico->estabelecimento->declaracao_sem_equipamentos_imagem_justificativa)
                        <br><br><strong>Justificativa:</strong> {{ $ordemServico->estabelecimento->declaracao_sem_equipamentos_imagem_justificativa }}
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
        @if($ordemServico->atividades && count($ordemServico->atividades) > 0)
        <div class="section">
            <div class="section-title">AÇÕES EXECUTADAS</div>
            <div class="section-content">
                @foreach($ordemServico->atividades as $index => $atividade)
                <div class="atividade">
                    <div class="atividade-titulo">{{ $index + 1 }}. {{ $atividade['tipo_acao'] ?? 'Ação Desconhecida' }}</div>
                    <div class="atividade-info">
                        <strong>Sub-ação:</strong> {{ $atividade['sub_acao'] ?? '-' }}
                    </div>
                    <div class="atividade-info">
                        <strong>Responsável:</strong> {{ $atividade['responsavel_nome'] ?? '-' }}
                    </div>
                    @if(isset($atividade['observacoes']) && $atividade['observacoes'])
                    <div class="atividade-info">
                        <strong>Observações:</strong> {{ $atividade['observacoes'] }}
                    </div>
                    @endif
                    @if(isset($atividade['status']) && $atividade['status'] === 'finalizada')
                    <div class="atividade-info">
                        <strong>Data de Finalização:</strong> {{ isset($atividade['finalizada_em']) ? \Carbon\Carbon::parse($atividade['finalizada_em'])->format('d/m/Y H:i') : '-' }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <p>Documento gerado automaticamente em {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Sistema de Gestão de Ordens de Serviço - INFOVISA</p>
        </div>
    </div>
</body>
</html>
