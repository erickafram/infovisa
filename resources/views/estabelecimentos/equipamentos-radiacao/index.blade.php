@extends('layouts.admin')

@section('title', 'Equipamentos de Imagem')
@section('page-title', 'Equipamentos de Imagem')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Equipamentos de Imagem</h2>
            <p class="text-sm text-gray-600 mt-1">
                <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}" class="text-blue-600 hover:text-blue-700">
                    {{ $estabelecimento->nome_razao_social }}
                </a>
                <span class="text-gray-400 mx-2">•</span>
                <span class="font-mono text-gray-500">{{ $estabelecimento->documento_formatado }}</span>
            </p>
        </div>

        <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}"
           class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar
        </a>
    </div>

    {{-- Alerta de Declaração de Não Possui Equipamentos --}}
    @if($estabelecimento->declaracao_sem_equipamentos_imagem)
    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-amber-800">Declaração de Não Possui Equipamentos</h3>
                <p class="text-sm text-amber-700 mt-1">
                    Este estabelecimento declarou que <strong>não possui equipamentos de imagem</strong>, mesmo possuindo atividades que normalmente exigem esse tipo de equipamento.
                </p>
                @if($estabelecimento->declaracao_sem_equipamentos_imagem_justificativa)
                <div class="mt-2 p-2 bg-amber-100/50 rounded text-sm text-amber-800">
                    <strong>Justificativa:</strong> {{ $estabelecimento->declaracao_sem_equipamentos_imagem_justificativa }}
                </div>
                @endif
                <p class="text-xs text-amber-600 mt-2">
                    Declaração feita em: {{ $estabelecimento->declaracao_sem_equipamentos_imagem_data?->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Estatísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $equipamentos->count() }}</p>
                    <p class="text-xs text-gray-500">Total de Equipamentos</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $equipamentos->where('status', 'ativo')->count() }}</p>
                    <p class="text-xs text-gray-500">Ativos</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $equipamentos->where('status', 'em_manutencao')->count() }}</p>
                    <p class="text-xs text-gray-500">Em Manutenção</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $equipamentos->whereIn('status', ['inativo', 'descartado'])->count() }}</p>
                    <p class="text-xs text-gray-500">Inativos/Descartados</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de Equipamentos --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-orange-100 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                Equipamentos Cadastrados
            </h3>
        </div>

        @if($equipamentos->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Equipamento</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fabricante / Modelo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número de Série</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localização</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registro ANVISA</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calibração</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cadastrado em</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($equipamentos as $equipamento)
                    <tr class="hover:bg-gray-50" x-data="{ expanded: false }">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $equipamento->tipo_equipamento }}</div>
                            @if($equipamento->ano_fabricacao)
                            <div class="text-xs text-gray-500">Fabricado em: {{ $equipamento->ano_fabricacao }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $equipamento->fabricante ?: '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $equipamento->modelo ?: '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-mono text-gray-900">{{ $equipamento->numero_serie ?: '-' }}</div>
                            @if($equipamento->numero_patrimonio)
                            <div class="text-xs text-gray-500">Patrimônio: {{ $equipamento->numero_patrimonio }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $equipamento->setor_localizacao ?: '-' }}</div>
                            @if($equipamento->sala)
                            <div class="text-xs text-gray-500">Sala: {{ $equipamento->sala }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-mono text-gray-900">{{ $equipamento->registro_anvisa ?: '-' }}</div>
                            @if($equipamento->registro_cnen)
                            <div class="text-xs text-gray-500">CNEN: {{ $equipamento->registro_cnen }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($equipamento->data_ultima_calibracao)
                            <div class="text-sm text-gray-900">
                                Última: {{ $equipamento->data_ultima_calibracao->format('d/m/Y') }}
                            </div>
                            @endif
                            @if($equipamento->data_proxima_calibracao)
                            @php
                                $hoje = now();
                                $proxima = $equipamento->data_proxima_calibracao;
                                $diasParaVencer = $hoje->diffInDays($proxima, false);
                            @endphp
                            <div class="text-xs {{ $diasParaVencer < 0 ? 'text-red-600 font-medium' : ($diasParaVencer <= 30 ? 'text-yellow-600' : 'text-gray-500') }}">
                                Próxima: {{ $proxima->format('d/m/Y') }}
                                @if($diasParaVencer < 0)
                                    <span class="text-red-600">(Vencida)</span>
                                @elseif($diasParaVencer <= 30)
                                    <span>({{ $diasParaVencer }} dias)</span>
                                @endif
                            </div>
                            @else
                            <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'ativo' => 'bg-green-100 text-green-800',
                                    'inativo' => 'bg-gray-100 text-gray-800',
                                    'em_manutencao' => 'bg-yellow-100 text-yellow-800',
                                    'descartado' => 'bg-red-100 text-red-800',
                                ];
                                $statusLabels = [
                                    'ativo' => 'Ativo',
                                    'inativo' => 'Inativo',
                                    'em_manutencao' => 'Em Manutenção',
                                    'descartado' => 'Descartado',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$equipamento->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $statusLabels[$equipamento->status] ?? $equipamento->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $equipamento->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $equipamento->created_at->format('H:i') }}</div>
                        </td>
                    </tr>
                    @if($equipamento->responsavel_tecnico || $equipamento->observacoes)
                    <tr class="bg-gray-50">
                        <td colspan="8" class="px-6 py-3">
                            <div class="flex flex-wrap gap-6 text-sm">
                                @if($equipamento->responsavel_tecnico)
                                <div>
                                    <span class="font-medium text-gray-700">Responsável Técnico:</span>
                                    <span class="text-gray-600 ml-1">{{ $equipamento->responsavel_tecnico }}</span>
                                </div>
                                @endif
                                @if($equipamento->observacoes)
                                <div class="flex-1">
                                    <span class="font-medium text-gray-700">Observações:</span>
                                    <span class="text-gray-600 ml-1">{{ $equipamento->observacoes }}</span>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-16 w-16 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhum equipamento cadastrado</h3>
            <p class="mt-2 text-sm text-gray-500 max-w-md mx-auto">
                Este estabelecimento possui atividades que exigem o cadastro de equipamentos de imagem, 
                mas ainda não há equipamentos registrados pelo usuário externo.
            </p>
        </div>
        @endif
    </div>
</div>
@endsection
