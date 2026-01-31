@extends('layouts.admin')

@section('title', 'Declarações - Sem Equipamentos de Imagem')
@section('page-title', 'Estabelecimentos que Declararam Não Ter Equipamentos de Imagem')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Declarações de Não Possuem Equipamentos</h2>
            <p class="text-sm text-gray-600 mt-1">
                Estabelecimentos que declararam não possuir equipamentos de imagem, mesmo com atividades que normalmente exigem
            </p>
        </div>
        <a href="{{ route('admin.relatorios.equipamentos-radiacao') }}"
           class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar
        </a>
    </div>

    {{-- Contagem Total --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-amber-100 rounded-lg">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-amber-600">{{ $declaracoes->total() }}</p>
                <p class="text-sm text-gray-500">Total de declarações</p>
            </div>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Estabelecimento</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Documento</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Município</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Data da Declaração</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Declarado por</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($declaracoes as $estabelecimento)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-900">{{ $estabelecimento->nome_fantasia ?: $estabelecimento->nome_razao_social }}</p>
                                <p class="text-xs text-gray-500">{{ $estabelecimento->cnpj_formatado }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $estabelecimento->cpf ?: $estabelecimento->cnpj }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $estabelecimento->municipio?->nome ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $estabelecimento->declaracao_sem_equipamentos_imagem_data?->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $estabelecimento->declaracaoSemEquipamentosUsuario?->nome ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}"
                               class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3"/>
                                </svg>
                                Ver
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-gray-600 font-medium">Nenhuma declaração encontrada</p>
                            <p class="text-gray-500 text-sm">Todos os estabelecimentos que possuem atividades relacionadas já informaram seus equipamentos.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginação --}}
    @if ($declaracoes->hasPages())
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 px-6 py-4">
        {{ $declaracoes->links() }}
    </div>
    @endif

    {{-- Detalhes da Justificativa (se houver) --}}
    @if ($declaracoes->count() > 0)
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">Justificativas Registradas</h3>
        @foreach ($declaracoes as $estabelecimento)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="font-semibold text-gray-900">{{ $estabelecimento->nome_fantasia ?: $estabelecimento->nome_razao_social }}</p>
                    <p class="text-xs text-gray-500 mb-3">{{ $estabelecimento->cnpj_formatado }}</p>
                    
                    @if($estabelecimento->declaracao_sem_equipamentos_opcoes)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                        <p class="text-xs font-medium text-blue-800 mb-2">Confirmações marcadas:</p>
                        <div class="space-y-1.5">
                            @php
                                $opcoes = json_decode($estabelecimento->declaracao_sem_equipamentos_opcoes, true) ?? [];
                            @endphp
                            @if(in_array('opcao_1', $opcoes))
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-xs text-blue-800">Não executa atividades de diagnóstico por imagem neste estabelecimento</span>
                            </div>
                            @endif
                            @if(in_array('opcao_2', $opcoes))
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-xs text-blue-800">Não possui equipamentos de diagnóstico por imagem instalados no local</span>
                            </div>
                            @endif
                            @if(in_array('opcao_3', $opcoes))
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-xs text-blue-800">Os exames são integralmente terceirizados ou realizados em outro estabelecimento regularmente licenciado</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    @if($estabelecimento->declaracao_sem_equipamentos_imagem_justificativa)
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm">
                        <p class="text-xs font-medium text-amber-800 mb-1">Justificativa:</p>
                        <p class="text-gray-700">{{ $estabelecimento->declaracao_sem_equipamentos_imagem_justificativa }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
