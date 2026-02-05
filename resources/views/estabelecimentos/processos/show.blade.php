@extends('layouts.admin')

@section('title', 'Detalhes do Processo')
@section('page-title', 'Detalhes do Processo')

@section('content')
<div class="max-w-8xl mx-auto" x-data="processoData()">
    {{-- Botão Voltar --}}
    <div class="mb-6">
        <a href="{{ route('admin.estabelecimentos.processos.index', $estabelecimento->id) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
    </div>

    {{-- Mensagens --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- Modal de Notificação de Atribuição (aparece apenas para o responsável que ainda não viu) --}}
    @if($processo->responsavel_atual_id === auth('interno')->id() && !$processo->responsavel_ciente_em && ($processo->motivo_atribuicao || $processo->prazo_atribuicao))
    <div x-data="{ 
        showNotificacao: true,
        marcarCiente() {
            fetch('{{ route("admin.estabelecimentos.processos.ciente", [$estabelecimento->id, $processo->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotificacao = false;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                this.showNotificacao = false;
            });
        }
    }" x-show="showNotificacao" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            {{-- Overlay --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            {{-- Modal Panel --}}
            <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all sm:max-w-lg sm:w-full mx-auto overflow-hidden">
                {{-- Header --}}
                <div class="px-6 py-5 bg-gradient-to-r from-cyan-600 to-cyan-700">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <h3 class="text-xl font-bold text-white">Processo Atribuído a Você</h3>
                            <p class="text-cyan-100 text-sm">{{ $processo->numero_processo }}</p>
                        </div>
                    </div>
                </div>
                
                {{-- Content --}}
                <div class="px-6 py-5 space-y-4">
                    @if($processo->motivo_atribuicao)
                    <div class="bg-gray-50 rounded-xl p-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            Motivo da Atribuição
                        </h4>
                        <p class="text-gray-700">{{ $processo->motivo_atribuicao }}</p>
                    </div>
                    @endif
                    
                    @if($processo->prazo_atribuicao)
                    @php
                        $prazo = \Carbon\Carbon::parse($processo->prazo_atribuicao);
                        $hoje = \Carbon\Carbon::today();
                        $diasRestantes = $hoje->diffInDays($prazo, false);
                        $vencido = $diasRestantes < 0;
                        $proximo = $diasRestantes >= 0 && $diasRestantes <= 3;
                    @endphp
                    <div class="rounded-xl p-4 {{ $vencido ? 'bg-red-50 border border-red-200' : ($proximo ? 'bg-amber-50 border border-amber-200' : 'bg-cyan-50 border border-cyan-200') }}">
                        <h4 class="text-sm font-semibold mb-2 flex items-center gap-2 {{ $vencido ? 'text-red-700' : ($proximo ? 'text-amber-700' : 'text-cyan-700') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Prazo para Resolução
                        </h4>
                        <p class="text-lg font-bold {{ $vencido ? 'text-red-800' : ($proximo ? 'text-amber-800' : 'text-cyan-800') }}">
                            {{ $prazo->format('d/m/Y') }}
                            @if($vencido)
                                <span class="text-sm font-medium">(Vencido há {{ abs($diasRestantes) }} dia(s))</span>
                            @elseif($diasRestantes == 0)
                                <span class="text-sm font-medium">(Vence hoje!)</span>
                            @elseif($proximo)
                                <span class="text-sm font-medium">({{ $diasRestantes }} dia(s) restante(s))</span>
                            @endif
                        </p>
                    </div>
                    @endif
                    
                    <div class="text-sm text-gray-500 text-center pt-2">
                        Atribuído em {{ $processo->responsavel_desde ? $processo->responsavel_desde->format('d/m/Y \à\s H:i') : '-' }}
                    </div>
                </div>
                
                {{-- Footer --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <button type="button" 
                            @click="marcarCiente()"
                            class="w-full px-4 py-3 bg-cyan-600 text-white font-semibold rounded-xl hover:bg-cyan-700 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Estou Ciente
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Alerta de Processo Parado --}}
    @if($processo->status === 'parado')
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-3 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-red-800">⚠️ Processo Parado</h3>
                    <p class="text-xs text-red-700 mt-0.5"><strong>Motivo:</strong> {{ $processo->motivo_parada }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 text-xs text-red-600">
                <span>📅 Parado em: {{ $processo->data_parada->format('d/m/Y H:i') }}</span>
                @if($processo->usuarioParada)
                <span>👤 Por: {{ $processo->usuarioParada->nome }}</span>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-red-800 mb-2">Erro ao enviar arquivo:</p>
                    <ul class="list-disc list-inside text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Aviso de Prazo da Fila Pública --}}
    @if($avisoFilaPublica)
        @php
            $dias = $avisoFilaPublica['dias_restantes'];
            $corBg = $avisoFilaPublica['atrasado'] ? 'bg-red-50' : ($dias <= 5 ? 'bg-amber-50' : 'bg-cyan-50');
            $corBorda = $avisoFilaPublica['atrasado'] ? 'border-red-400' : ($dias <= 5 ? 'border-amber-400' : 'border-cyan-400');
            $corTexto = $avisoFilaPublica['atrasado'] ? 'text-red-700' : ($dias <= 5 ? 'text-amber-700' : 'text-cyan-700');
        @endphp
        <div class="mb-4 {{ $corBg }} border-l-4 {{ $corBorda }} px-4 py-2.5 rounded-r-lg">
            <div class="flex items-center gap-2 {{ $corTexto }} text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>
                    @if($avisoFilaPublica['atrasado'])
                        <strong>Prazo vencido!</strong> Atrasado há {{ abs($dias) }} {{ abs($dias) == 1 ? 'dia' : 'dias' }} (docs completos em {{ $avisoFilaPublica['data_documentos_completos']->format('d/m/Y') }})
                    @elseif($dias <= 5)
                        <strong>Prazo próximo!</strong> Restam {{ $dias }} {{ $dias == 1 ? 'dia' : 'dias' }} para análise (docs completos em {{ $avisoFilaPublica['data_documentos_completos']->format('d/m/Y') }})
                    @else
                        Documentação completa em {{ $avisoFilaPublica['data_documentos_completos']->format('d/m/Y') }} • Prazo: {{ $avisoFilaPublica['prazo'] }} dias • <strong>Restam {{ $dias }} dias</strong>
                    @endif
                </span>
            </div>
        </div>
    @endif

    {{-- Card Superior: Dados do Estabelecimento e Processo --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Dados do Estabelecimento --}}
            <div>
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Nome do Estabelecimento
                </h2>
                <div class="space-y-3">
                    <div>
                        <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}" class="text-lg font-bold text-blue-600 hover:text-blue-800 hover:underline">{{ $estabelecimento->nome_fantasia ?? $estabelecimento->nome_razao_social }}</a>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">{{ $estabelecimento->tipo_pessoa === 'juridica' ? 'CNPJ' : 'CPF' }}</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $estabelecimento->documento_formatado }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">Telefone(s)</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $estabelecimento->telefone_formatado ?? 'Não informado' }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Endereço</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $estabelecimento->endereco }}, {{ $estabelecimento->numero }}{{ $estabelecimento->complemento ? ', ' . $estabelecimento->complemento : '' }} - {{ $estabelecimento->bairro }}, {{ $estabelecimento->cidade }} - {{ $estabelecimento->estado }}</p>
                    </div>
                </div>
            </div>

            {{-- Dados do Processo --}}
            <div>
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Dados do Processo
                </h2>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">Tipo de Processo</label>
                            <p class="text-sm text-gray-900 font-medium mt-1">{{ $processo->tipo_nome }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">Número do Processo</label>
                            <p class="text-sm text-gray-900 font-medium mt-1">{{ $processo->numero_processo }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">Status</label>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                    @if($processo->status_cor === 'blue') bg-blue-100 text-blue-800
                                    @elseif($processo->status_cor === 'yellow') bg-yellow-100 text-yellow-800
                                    @elseif($processo->status_cor === 'orange') bg-orange-100 text-orange-800
                                    @elseif($processo->status_cor === 'green') bg-green-100 text-green-800
                                    @elseif($processo->status_cor === 'red') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $processo->status_nome }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">Ano</label>
                            <p class="text-sm text-gray-900 font-medium mt-1">{{ $processo->ano }}</p>
                        </div>
                    </div>
                    
                    @if($processo->observacoes)
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">Observações</label>
                            <p class="text-sm text-gray-700 mt-1">{{ $processo->observacoes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Botão Acompanhar --}}
        <div class="mt-6 pt-6 border-t border-gray-200">
            <form action="{{ route('admin.estabelecimentos.processos.toggleAcompanhamento', [$estabelecimento->id, $processo->id]) }}" method="POST" class="inline-block">
                @csrf
                @if($processo->estaAcompanhadoPor(Auth::guard('interno')->user()->id))
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                        Parar de Acompanhar
                    </button>
                @else
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Acompanhar Processo
                    </button>
                @endif
            </form>
        </div>
    </div>

    {{-- Card Setor/Responsável Atual --}}
    <div class="bg-white rounded-xl shadow-sm border {{ $processo->setor_atual || $processo->responsavel_atual_id ? 'border-cyan-200' : 'border-gray-200' }} p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg {{ $processo->setor_atual || $processo->responsavel_atual_id ? 'bg-cyan-100' : 'bg-gray-100' }} flex items-center justify-center">
                    <svg class="w-5 h-5 {{ $processo->setor_atual || $processo->responsavel_atual_id ? 'text-cyan-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Com (Setor/Responsável)</p>
                    @if($processo->setor_atual || $processo->responsavel_atual_id)
                        <div class="flex items-center gap-2 mt-0.5">
                            @if($processo->setor_atual)
                                <span class="text-sm font-semibold text-cyan-700">{{ $processo->setor_atual_nome }}</span>
                            @endif
                            @if($processo->responsavelAtual)
                                <span class="text-sm text-gray-700">
                                    {{ $processo->setor_atual ? '- ' : '' }}{{ $processo->responsavelAtual->nome }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 mt-0.5">
                            @if($processo->responsavel_desde)
                                <p class="text-xs text-gray-500">
                                    desde {{ $processo->responsavel_desde->format('d/m/Y H:i') }} ({{ $processo->responsavel_desde->diffForHumans() }})
                                </p>
                            @endif
                            @if($processo->prazo_atribuicao)
                                @php
                                    $prazoVencido = $processo->prazo_atribuicao->isPast();
                                    $prazoProximo = !$prazoVencido && $processo->prazo_atribuicao->diffInDays(now()) <= 3;
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium {{ $prazoVencido ? 'bg-red-100 text-red-700' : ($prazoProximo ? 'bg-amber-100 text-amber-700' : 'bg-cyan-100 text-cyan-700') }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Prazo: {{ $processo->prazo_atribuicao->format('d/m/Y') }}
                                    @if($prazoVencido)
                                        (Vencido)
                                    @endif
                                </span>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500 italic">Não atribuído</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                {{-- Botão Ver Histórico de Atribuições --}}
                <button @click="modalHistoricoAtribuicoes = true" 
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                        title="Ver histórico de atribuições">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Histórico
                </button>
                @if($processo->status !== 'arquivado')
                <button @click="modalAtribuir = true" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-cyan-700 bg-cyan-50 hover:bg-cyan-100 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Tramitar Processo
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Alerta de Documentos com Prazo em Aberto --}}
    @php
        $documentosComPrazoAberto = $documentosDigitais->filter(function($doc) {
            return $doc->prazo_dias && !$doc->isPrazoFinalizado() && $doc->status === 'assinado';
        });
    @endphp
    @if($documentosComPrazoAberto->count() > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-amber-800">
                    {{ $documentosComPrazoAberto->count() }} documento(s) com prazo em aberto
                </h3>
                <p class="text-xs text-amber-700 mt-1">
                    Existem documentos aguardando resposta do estabelecimento ou que precisam ser marcados como resolvidos.
                </p>
                <div class="mt-3 space-y-1.5">
                    @foreach($documentosComPrazoAberto as $doc)
                        @php
                            $temResposta = $doc->respostas->where('status', '!=', 'rejeitado')->count() > 0;
                        @endphp
                        <div class="flex items-center justify-between text-xs bg-white/60 rounded-lg px-3 py-2">
                            <div class="flex items-center gap-2">
                                <span class="text-amber-800 font-medium">{{ $doc->tipoDocumento->nome ?? 'Documento' }}</span>
                                <span class="text-amber-600">- {{ $doc->texto_status_prazo }}</span>
                                @if($temResposta)
                                    <span class="px-1.5 py-0.5 bg-green-100 text-green-700 rounded text-[10px] font-medium">Respondido</span>
                                @endif
                            </div>
                            @if($temResposta)
                                <span class="text-amber-600 text-[10px]">Aguardando marcar como resolvido</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Duas Colunas: Menu/Ações (esquerda) e Documentos (direita) --}}
    <style>
        @media (max-width: 768px) {
            .processo-container {
                flex-direction: column !important;
            }
            .processo-menu {
                width: 100% !important;
                min-width: unset !important;
            }
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>
    <div class="processo-container" style="display: flex; gap: 1.5rem;">
        {{-- Coluna Esquerda: Menus e Ações --}}
        <div class="processo-menu space-y-6" style="width: 25%; min-width: 280px;">
            {{-- Checklist de Documentos Obrigatórios --}}
            @if(isset($documentosObrigatorios) && $documentosObrigatorios->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-data="{ checklistAberto: false }">
                <div class="flex items-center justify-between cursor-pointer" @click="checklistAberto = !checklistAberto">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        @php
                            $tipoProcessoCodigo = $processo->tipoProcesso->codigo ?? $processo->tipo ?? 'licenciamento';
                            $tituloChecklist = match($tipoProcessoCodigo) {
                                'projeto_arquitetonico' => 'Docs. Projeto Arq.',
                                'analise_rotulagem' => 'Docs. Rotulagem',
                                default => 'Docs. Licenciamento'
                            };
                        @endphp
                        {{ $tituloChecklist }}
                        @php
                            $totalObrigatorios = $documentosObrigatorios->count();
                            $totalOk = $documentosObrigatorios->where('status', 'aprovado')->count();
                            $totalPendente = $documentosObrigatorios->where('status', 'pendente')->count();
                            $totalRejeitado = $documentosObrigatorios->where('status', 'rejeitado')->count();
                            $totalNaoEnviado = $documentosObrigatorios->whereNull('status')->count();
                            // Barra só aumenta com aprovados
                            $percentualAprovados = $totalObrigatorios > 0 ? round(($totalOk / $totalObrigatorios) * 100) : 0;
                            $todosAprovados = ($totalOk == $totalObrigatorios && $totalObrigatorios > 0);
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-medium rounded {{ $totalOk === $totalObrigatorios ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $totalOk }}/{{ $totalObrigatorios }}
                        </span>
                    </h3>
                    <svg class="w-4 h-4 text-gray-500 transition-transform" :class="{ 'rotate-180': checklistAberto }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                {{-- Barra de Progresso Compacta --}}
                <div class="mt-3 px-1">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-medium text-gray-600">Progresso de Aprovação</span>
                        <span class="text-xs font-bold px-1.5 py-0.5 rounded {{ $todosAprovados ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $percentualAprovados }}%
                        </span>
                    </div>
                    <div class="relative mb-2">
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden shadow-inner">
                            <div class="h-full rounded-full transition-all duration-500 ease-out {{ $todosAprovados ? 'bg-gradient-to-r from-green-400 to-green-600' : 'bg-gradient-to-r from-blue-400 to-blue-600' }}" 
                                 style="width: {{ $percentualAprovados }}%">
                            </div>
                        </div>
                        @if($todosAprovados)
                        <div class="absolute -top-0.5 -right-0.5">
                            <span class="flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500 items-center justify-center">
                                    <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                            </span>
                        </div>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-1.5 text-[10px]">
                        @if($totalOk > 0)
                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-green-50 text-green-700 rounded-full border border-green-200">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $totalOk }} aprovado{{ $totalOk > 1 ? 's' : '' }}
                        </span>
                        @endif
                        @if($totalPendente > 0)
                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-amber-50 text-amber-700 rounded-full border border-amber-200">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $totalPendente }} pendente{{ $totalPendente > 1 ? 's' : '' }}
                        </span>
                        @endif
                        @if($totalRejeitado > 0)
                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-red-50 text-red-700 rounded-full border border-red-200">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            {{ $totalRejeitado }} rejeitado{{ $totalRejeitado > 1 ? 's' : '' }}
                        </span>
                        @endif
                        @if($totalNaoEnviado > 0)
                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-gray-50 text-gray-600 rounded-full border border-gray-200">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            {{ $totalNaoEnviado }} não enviado{{ $totalNaoEnviado > 1 ? 's' : '' }}
                        </span>
                        @endif
                    </div>
                </div>

                <div x-show="checklistAberto" x-transition class="mt-4 space-y-2">
                    {{-- Resumo --}}
                    <div class="flex flex-wrap gap-2 mb-3 text-xs">
                        @if($totalOk > 0)
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full">✓ {{ $totalOk }} OK</span>
                        @endif
                        @if($totalPendente > 0)
                        <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded-full">⏳ {{ $totalPendente }} Pendente(s)</span>
                        @endif
                        @if($totalRejeitado > 0)
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full">✗ {{ $totalRejeitado }} Rejeitado(s)</span>
                        @endif
                        @if($totalNaoEnviado > 0)
                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full">○ {{ $totalNaoEnviado }} Não enviado(s)</span>
                        @endif
                    </div>

                    {{-- Lista de Documentos --}}
                    @foreach($documentosObrigatorios as $doc)
                    @php
                        $statusDoc = $doc['status'] ?? null;
                        $isAprovado = $statusDoc === 'aprovado';
                        $isPendente = $statusDoc === 'pendente';
                        $isRejeitado = $statusDoc === 'rejeitado';
                    @endphp
                    <div class="flex items-center gap-2 p-2 rounded-lg text-sm
                        {{ $isAprovado ? 'bg-green-50' : '' }}
                        {{ $isPendente ? 'bg-amber-50' : '' }}
                        {{ $isRejeitado ? 'bg-red-50' : '' }}
                        {{ !$statusDoc ? 'bg-gray-50' : '' }}">
                        {{-- Ícone de Status --}}
                        <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0
                            {{ $isAprovado ? 'bg-green-100' : '' }}
                            {{ $isPendente ? 'bg-amber-100' : '' }}
                            {{ $isRejeitado ? 'bg-red-100' : '' }}
                            {{ !$statusDoc ? 'bg-gray-200' : '' }}">
                            @if($isAprovado)
                                <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            @elseif($isPendente)
                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @elseif($isRejeitado)
                                <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            @else
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            @endif
                        </div>
                        
                        {{-- Nome do Documento --}}
                        <div class="flex-1">
                            <span class="text-gray-900 block text-sm leading-tight break-words">{{ $doc['nome'] }}</span>
                            @if($doc['obrigatorio'])
                            <span class="text-[10px] text-red-500">Obrigatório</span>
                            @endif
                        </div>
                        
                        {{-- Badge de Status --}}
                        <div class="flex-shrink-0">
                            @if($isAprovado)
                                <span class="text-xs text-green-600 font-medium">✓ OK</span>
                            @elseif($isPendente)
                                <span class="text-xs text-amber-600 font-medium">Pendente</span>
                            @elseif($isRejeitado)
                                <span class="text-xs text-red-600 font-medium">Rejeitado</span>
                            @else
                                <span class="text-xs text-gray-500">Não enviado</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Menu de Opções --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    Menu de Opções
                </h3>
                <div class="space-y-2">
                    @if($processo->status !== 'arquivado')
                    <button @click="modalUpload = true" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Upload de Arquivos
                    </button>
                    <a href="{{ route('admin.documentos.create', ['processo_id' => $processo->id]) }}" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Criar Documento Digital
                    </a>
                    @endif
                    @if(auth('interno')->user()->isAdmin() || auth('interno')->user()->isGestor())
                    <a href="{{ route('admin.ordens-servico.create', ['estabelecimento_id' => $estabelecimento->id, 'processo_id' => $processo->id]) }}" 
                       class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Ordem de Serviço
                    </a>
                    @endif
                    <button @click="modalAlertas = true" 
                            class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Alertas
                        @if($alertas->where('status', 'pendente')->count() > 0)
                        <span class="ml-auto px-2 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                            {{ $alertas->where('status', 'pendente')->count() }}
                        </span>
                        @endif
                    </button>
                </div>
            </div>

            {{-- Ações do Processo --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Ações do Processo
                </h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.estabelecimentos.processos.integra', [$estabelecimento->id, $processo->id]) }}" 
                       class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Processo na Íntegra
                    </a>
                    
                    @if($processo->status !== 'arquivado')
                    <button @click="modalAtribuir = true" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-cyan-700 bg-cyan-50 hover:bg-cyan-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        Tramitar Processo
                    </button>
                    
                    <button @click="modalPastas = true; carregarPastas()" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        Pastas Processo
                    </button>
                    
                    @if($processo->status === 'parado')
                    <form action="{{ route('admin.estabelecimentos.processos.reiniciar', [$estabelecimento->id, $processo->id]) }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Reiniciar Processo
                        </button>
                    </form>
                    @else
                    <button @click="modalParar = true" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Parar Processo
                    </button>
                    @endif
                    @endif
                    @if($processo->status === 'arquivado')
                    <form action="{{ route('admin.estabelecimentos.processos.desarquivar', [$estabelecimento->id, $processo->id]) }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Desarquivar Processo
                        </button>
                    </form>
                    @else
                    <button @click="modalArquivar = true" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-orange-700 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        Arquivar Processo
                    </button>
                    @endif
                               <button @click="modalHistorico = true" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Histórico
                    </button>
                    @if(auth('interno')->user()->isAdmin())
                    <button @click="modalExcluirProcesso = true" 
                            class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Excluir Processo
                    </button>
                    @endif
                </div>
            </div>

        </div>

        {{-- Coluna Direita: Lista de Documentos/Arquivos --}}
        <div style="flex: 1;">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                {{-- Header da Lista de Documentos --}}
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Lista de Documentos/Arquivos
                        </h2>
                    </div>
                </div>

                {{-- Tabs de Documentos --}}
                <div class="border-b border-gray-200 bg-gray-50">
                    <nav class="flex px-6 overflow-x-auto" aria-label="Tabs">
                        <button @click="pastaAtiva = null" 
                                :class="pastaAtiva === null ? 'text-blue-600 border-blue-600' : 'text-gray-600 border-transparent hover:text-gray-800 hover:border-gray-300'"
                                class="px-4 py-4 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap">
                            Todos
                            <span class="ml-2 px-2.5 py-0.5 text-xs font-semibold rounded-full"
                                  :class="pastaAtiva === null ? 'bg-blue-100 text-blue-700' : 'bg-gray-200 text-gray-700'">
                                {{ $documentosDigitais->count() + $processo->documentos->where('tipo_documento', '!=', 'documento_digital')->count() }}
                            </span>
                        </button>
                        
                        {{-- Pastas Dinâmicas --}}
                        <template x-for="pasta in pastas" :key="pasta.id">
                            <button @click="pastaAtiva = pasta.id"
                                    :class="pastaAtiva === pasta.id ? 'border-b-2' : 'text-gray-600 border-transparent hover:text-gray-800 hover:border-gray-300'"
                                    :style="pastaAtiva === pasta.id ? `color: ${pasta.cor}; border-color: ${pasta.cor}` : ''"
                                    class="px-4 py-4 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                                <span x-text="pasta.nome"></span>
                                <span class="ml-1 px-2.5 py-0.5 text-xs font-semibold rounded-full"
                                      :style="`background-color: ${pasta.cor}20; color: ${pasta.cor}`"
                                      x-text="contarDocumentosPorPasta(pasta.id)">
                                </span>
                            </button>
                        </template>
                    </nav>
                </div>

                {{-- Lista de Documentos --}}
                <div class="p-4">
                    @if($todosDocumentos->isEmpty())
                        <div class="text-center py-16">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-base font-semibold text-gray-700 mb-2">Nenhum documento anexado</p>
                            <p class="text-sm text-gray-500">Comece a adicionar documentos usando o menu de opções</p>
                        </div>
                    @else
                        <div class="space-y-2">
                            {{-- Lista Unificada de Documentos (Digitais e Arquivos Externos) --}}
                            @foreach($todosDocumentos as $item)
                                @if($item['tipo'] === 'digital')
                                    @php
                                        $docDigital = $item['documento'];
                                        $assinaturasPendentes = $docDigital->assinaturas()->where('status', 'pendente')->count();
                                        $todasAssinaturas = $docDigital->assinaturas()->count();
                                        $temAssinaturasPendentes = $assinaturasPendentes > 0;
                                        
                                        // Verificar se o usuário logado precisa assinar este documento
                                        $usuarioLogado = auth('interno')->user();
                                        $assinaturaUsuario = $docDigital->assinaturas()
                                            ->where('usuario_interno_id', $usuarioLogado->id)
                                            ->where('status', 'pendente')
                                            ->first();
                                        $usuarioPrecisaAssinar = $assinaturaUsuario !== null && $docDigital->status !== 'rascunho';
                                    @endphp
                                @php
                                    // Determinar cor da borda baseado no status do documento
                                    $temPrazoAberto = $docDigital->prazo_dias && !$docDigital->isPrazoFinalizado() && $docDigital->todasAssinaturasCompletas() && $docDigital->status === 'assinado';
                                    $temRespostasPendentes = $docDigital->respostas && $docDigital->respostas->where('status', 'pendente')->count() > 0;
                                    $temRespostasAprovadas = $docDigital->respostas && $docDigital->respostas->where('status', 'aprovado')->count() > 0;
                                    $totalRespostas = $docDigital->respostas ? $docDigital->respostas->count() : 0;
                                    $prazoFinalizado = $docDigital->isPrazoFinalizado();
                                    
                                    // Definir status geral do documento para exibição
                                    if ($docDigital->status === 'rascunho') {
                                        $corBorda = 'border-gray-300';
                                        $statusGeral = 'rascunho';
                                    } elseif ($temAssinaturasPendentes) {
                                        $corBorda = 'border-orange-500';
                                        $statusGeral = 'aguardando_assinatura';
                                    } elseif ($temRespostasPendentes) {
                                        $corBorda = 'border-yellow-500';
                                        $statusGeral = 'resposta_pendente';
                                    } elseif ($temPrazoAberto) {
                                        $corBorda = 'border-amber-500';
                                        $statusGeral = 'prazo_aberto';
                                    } elseif ($prazoFinalizado && $temRespostasAprovadas) {
                                        $corBorda = 'border-green-500';
                                        $statusGeral = 'resolvido';
                                    } elseif ($docDigital->status === 'assinado' && $docDigital->todasAssinaturasCompletas()) {
                                        $corBorda = 'border-green-500';
                                        $statusGeral = 'concluido';
                                    } else {
                                        $corBorda = 'border-gray-300';
                                        $statusGeral = 'outros';
                                    }
                                @endphp
                                <div x-data="{ pastaDocumento: {{ $docDigital->pasta_id ?? 'null' }}, expanded: false }"
                                     x-show="pastaAtiva === null || pastaAtiva === pastaDocumento"
                                     class="bg-white rounded-lg border border-gray-200 border-l-4 {{ $corBorda }} hover:shadow-md transition-all"
                                     style="border-top-color: #e5e7eb; border-right-color: #e5e7eb; border-bottom-color: #e5e7eb;">
                                    
                                    {{-- Layout Flex Principal --}}
                                    <div class="p-3 flex items-center justify-between gap-3">
                                        {{-- ESQUERDA: Ícone + Nome + Data --}}
                                        <div class="flex items-center gap-2 min-w-0 flex-1">
                                            {{-- Ícone com indicador de status --}}
                                            <div class="relative flex-shrink-0">
                                                <div class="w-9 h-9 rounded-lg flex items-center justify-center
                                                    @if($statusGeral === 'resolvido') bg-green-100
                                                    @elseif($statusGeral === 'resposta_pendente') bg-yellow-100
                                                    @elseif($statusGeral === 'prazo_aberto') bg-amber-100
                                                    @elseif($statusGeral === 'aguardando_assinatura') bg-orange-100
                                                    @else bg-gray-100
                                                    @endif">
                                                    @if($statusGeral === 'resolvido')
                                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    @elseif($totalRespostas > 0)
                                                        <svg class="w-5 h-5 {{ $temRespostasPendentes ? 'text-yellow-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                                @if($totalRespostas > 0)
                                                    <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full text-[9px] font-bold flex items-center justify-center
                                                        {{ $temRespostasPendentes ? 'bg-yellow-500 text-white' : 'bg-green-500 text-white' }}">
                                                        {{ $totalRespostas }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            {{-- Nome, Status e Data --}}
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2">
                                                    @if($docDigital->status === 'rascunho')
                                                        <a href="{{ route('admin.documentos.edit', $docDigital->id) }}" class="text-sm font-semibold text-gray-900 hover:text-blue-600 truncate">{{ $docDigital->nome ?? $docDigital->tipoDocumento->nome }}</a>
                                                    @elseif($docDigital->arquivo_pdf && !$temAssinaturasPendentes)
                                                        <span @click="pdfUrl = '{{ route('admin.estabelecimentos.processos.visualizar', [$estabelecimento->id, $processo->id, $docDigital->id]) }}'; modalVisualizador = true" class="text-sm font-semibold text-gray-900 hover:text-blue-600 cursor-pointer truncate">{{ $docDigital->nome ?? $docDigital->tipoDocumento->nome }}</span>
                                                    @else
                                                        <span class="text-sm font-semibold text-gray-900 truncate">{{ $docDigital->nome ?? $docDigital->tipoDocumento->nome }}</span>
                                                    @endif
                                                    <span class="text-[11px] text-gray-400 flex-shrink-0">{{ $docDigital->created_at->format('d/m/Y') }}</span>
                                                </div>
                                                
                                                <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                                                    <span class="text-xs text-gray-500">{{ $docDigital->numero_documento }}</span>
                                                    
                                                    {{-- Badge de Status Principal --}}
                                                    @if($statusGeral === 'rascunho')
                                                        <span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-semibold">✏️ Rascunho</span>
                                                    @elseif($statusGeral === 'aguardando_assinatura')
                                                        <span class="px-1.5 py-0.5 bg-orange-100 text-orange-700 rounded text-[10px] font-semibold">🖊️ {{ $assinaturasPendentes }}/{{ $todasAssinaturas }}</span>
                                                    @elseif($statusGeral === 'resolvido')
                                                        <span class="px-1.5 py-0.5 bg-green-100 text-green-700 rounded text-[10px] font-semibold">✅ Resolvido</span>
                                                    @elseif($statusGeral === 'resposta_pendente')
                                                        <span class="px-1.5 py-0.5 bg-yellow-100 text-yellow-700 rounded text-[10px] font-semibold animate-pulse">⏳ Avaliar {{ $docDigital->respostas->where('status', 'pendente')->count() }}</span>
                                                    @elseif($statusGeral === 'prazo_aberto')
                                                        <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded text-[10px] font-semibold" title="Aguardando resposta do estabelecimento">📬 Ag. Resposta</span>
                                                    @elseif($statusGeral === 'concluido')
                                                        <span class="px-1.5 py-0.5 bg-green-100 text-green-700 rounded text-[10px] font-semibold">✓ {{ $todasAssinaturas }}</span>
                                                    @endif
                                                    
                                                    {{-- Indicador de visualização --}}
                                                    @if($docDigital->primeiraVisualizacao && $statusGeral !== 'rascunho' && $statusGeral !== 'aguardando_assinatura')
                                                        <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[10px] font-medium" title="Visto por {{ $docDigital->primeiraVisualizacao->usuarioExterno->nome ?? 'N/D' }}">👁</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- DIREITA: Opções --}}
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            {{-- Botão Expandir Detalhes --}}
                                            <button @click.stop="expanded = !expanded" 
                                                    class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors"
                                                    title="Ver detalhes">
                                                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                            
                                            @if($usuarioPrecisaAssinar)
                                                <button type="button"
                                                   @click="abrirModalAssinar({{ $docDigital->id }}, '{{ addslashes($docDigital->nome ?? $docDigital->tipoDocumento->nome) }}', '{{ $docDigital->numero_documento }}', '{{ $assinaturaUsuario->ordem }}', {{ json_encode($docDigital->assinaturas->map(fn($a) => ['nome' => $a->usuarioInterno->nome ?? 'Usuário', 'status' => $a->status, 'ordem' => $a->ordem, 'isCurrentUser' => $a->usuario_interno_id === auth('interno')->id()])->sortBy('ordem')->values()) }})"
                                                   class="p-1.5 text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded transition-colors"
                                                   title="Assinar documento">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                    </svg>
                                                </button>
                                            @endif
                                            
                                            @if($docDigital->prazo_dias && !$docDigital->isPrazoFinalizado() && $docDigital->respostas && $docDigital->respostas->where('status', 'aprovado')->count() > 0)
                                                <form action="{{ route('admin.estabelecimentos.processos.documento-digital.finalizar-prazo', [$estabelecimento->id, $processo->id, $docDigital->id]) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="p-1.5 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition-colors"
                                                            onclick="return confirm('Marcar prazo como resolvido?')"
                                                            title="Marcar como resolvido">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($docDigital->status !== 'rascunho' && $docDigital->arquivo_pdf)
                                                <a href="{{ route('admin.documentos.pdf', $docDigital->id) }}" 
                                                   class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors"
                                                   title="Download">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                </a>
                                            @endif
                                            
                                            {{-- Menu 3 Pontos --}}
                                            <div class="relative" x-data="{ menuAberto: false }">
                                                <button @click.stop="menuAberto = !menuAberto"
                                                        class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors"
                                                        title="Mais opções">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                                                    </svg>
                                                </button>
                                                <div x-show="menuAberto" @click.away="menuAberto = false" x-transition
                                                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-[9999] py-1"
                                                     style="display: none;">
                                                    @if($docDigital->status === 'rascunho')
                                                        <a href="{{ route('admin.documentos.edit', $docDigital->id) }}"
                                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Editar</a>
                                                    @endif
                                                    @if($docDigital->status !== 'rascunho')
                                                        <button @click="moverDocumentoDigitalParaPasta({{ $docDigital->id }}, null, $el); menuAberto = false"
                                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mover para pasta</button>
                                                    @endif
                                                    @if($docDigital->prazo_dias || $docDigital->data_vencimento)
                                                        @if($docDigital->isPrazoFinalizado())
                                                            <form action="{{ route('admin.estabelecimentos.processos.documento-digital.reabrir-prazo', [$estabelecimento->id, $processo->id, $docDigital->id]) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" onclick="return confirm('Reabrir prazo?')"
                                                                        class="w-full text-left px-4 py-2 text-sm text-orange-600 hover:bg-orange-50">Reabrir Prazo</button>
                                                            </form>
                                                        @elseif(!$docDigital->respostas || $docDigital->respostas->where('status', 'aprovado')->count() == 0)
                                                            @if($docDigital->primeiraVisualizacao)
                                                                <form action="{{ route('admin.estabelecimentos.processos.documento-digital.finalizar-prazo', [$estabelecimento->id, $processo->id, $docDigital->id]) }}" method="POST">
                                                                    @csrf
                                                                    <button type="submit" onclick="return confirm('Finalizar prazo?')"
                                                                            class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-green-50">Finalizar Prazo</button>
                                                                </form>
                                                            @else
                                                                <span class="block px-4 py-2 text-sm text-gray-400 cursor-not-allowed" title="O documento precisa ser visualizado pelo estabelecimento antes de finalizar o prazo">
                                                                    Finalizar Prazo (aguardando visualização)
                                                                </span>
                                                            @endif
                                                        @endif
                                                    @endif
                                                    <button @click="excluirDocumentoDigital({{ $docDigital->id }}, '{{ addslashes($docDigital->nome ?? $docDigital->tipoDocumento->nome) }} - {{ $docDigital->numero_documento }}'); menuAberto = false"
                                                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Excluir</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Seção Expandível: Timeline do Documento --}}
                                    <div x-show="expanded" x-collapse class="border-t border-gray-100 bg-gray-50">
                                        <div class="p-3">
                                            {{-- Timeline Visual --}}
                                            <div class="relative">
                                                {{-- Linha vertical da timeline --}}
                                                <div class="absolute left-3 top-2 bottom-2 w-0.5 bg-gray-200"></div>
                                                
                                                <div class="space-y-3">
                                                    {{-- Evento: Criação do Documento --}}
                                                    <div class="flex items-start gap-3 relative">
                                                        <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center z-10 flex-shrink-0">
                                                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                            </svg>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-xs font-medium text-gray-900">Documento criado</p>
                                                            <p class="text-[10px] text-gray-500">{{ $docDigital->created_at->format('d/m/Y H:i') }} • {{ $docDigital->usuarioCriador->nome }}</p>
                                                        </div>
                                                    </div>
                                                    
                                                    {{-- Evento: Assinaturas --}}
                                                    @if($todasAssinaturas > 0)
                                                    <div class="flex items-start gap-3 relative">
                                                        <div class="w-6 h-6 rounded-full {{ $temAssinaturasPendentes ? 'bg-orange-100' : 'bg-green-100' }} flex items-center justify-center z-10 flex-shrink-0">
                                                            <svg class="w-3 h-3 {{ $temAssinaturasPendentes ? 'text-orange-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                            </svg>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-xs font-medium text-gray-900">
                                                                @if($temAssinaturasPendentes)
                                                                    Aguardando {{ $assinaturasPendentes }} de {{ $todasAssinaturas }} assinatura(s)
                                                                @else
                                                                    ✓ Todas {{ $todasAssinaturas }} assinatura(s) coletadas
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    
                                                    {{-- Evento: Visualização --}}
                                                    @if($docDigital->primeiraVisualizacao)
                                                    <div class="flex items-start gap-3 relative">
                                                        <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center z-10 flex-shrink-0">
                                                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                            </svg>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-xs font-medium text-gray-900">Visualizado pelo estabelecimento</p>
                                                            <p class="text-[10px] text-gray-500">{{ $docDigital->primeiraVisualizacao->created_at->format('d/m/Y H:i') }} • {{ $docDigital->primeiraVisualizacao->usuarioExterno->nome ?? 'N/D' }}</p>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    
                                                    {{-- Eventos: Respostas --}}
                                                    @if($docDigital->respostas && $docDigital->respostas->count() > 0)
                                                        @foreach($docDigital->respostas->sortBy('created_at') as $resposta)
                                                        <div class="flex items-start gap-3 relative" x-data="{ showRejeitar: false }">
                                                            <div class="w-6 h-6 rounded-full flex items-center justify-center z-10 flex-shrink-0
                                                                {{ $resposta->status === 'pendente' ? 'bg-yellow-100' : ($resposta->status === 'aprovado' ? 'bg-green-100' : 'bg-red-100') }}">
                                                                @if($resposta->status === 'pendente')
                                                                    <svg class="w-3 h-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                    </svg>
                                                                @elseif($resposta->status === 'aprovado')
                                                                    <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                    </svg>
                                                                @else
                                                                    <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                    </svg>
                                                                @endif
                                                            </div>
                                                            <div class="flex-1 min-w-0 bg-white rounded-lg border {{ $resposta->status === 'pendente' ? 'border-yellow-200' : ($resposta->status === 'aprovado' ? 'border-green-200' : 'border-red-200') }} p-2">
                                                                <div class="flex items-center justify-between gap-2">
                                                                    <div class="min-w-0 flex-1">
                                                                        <div class="flex items-center gap-1.5 flex-wrap">
                                                                            <button type="button" 
                                                                                    @click="pdfUrl = '{{ route('admin.estabelecimentos.processos.documento-digital.resposta.visualizar', [$estabelecimento->id, $processo->id, $docDigital->id, $resposta->id]) }}'; modalVisualizador = true"
                                                                                    class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline truncate">
                                                                                📎 {{ $resposta->nome_original }}
                                                                            </button>
                                                                            <span class="text-[10px] px-1.5 py-0.5 rounded font-medium
                                                                                {{ $resposta->status === 'pendente' ? 'bg-yellow-100 text-yellow-700' : ($resposta->status === 'aprovado' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">
                                                                                {{ $resposta->status === 'pendente' ? 'Pendente' : ($resposta->status === 'aprovado' ? 'Aprovado' : 'Rejeitado') }}
                                                                            </span>
                                                                        </div>
                                                                        <p class="text-[10px] text-gray-500 mt-0.5">
                                                                            {{ $resposta->created_at->format('d/m/Y H:i') }} • {{ $resposta->usuarioExterno->nome ?? 'N/D' }}
                                                                            @if($resposta->status === 'aprovado' && $resposta->avaliadoPor)
                                                                                • <span class="text-green-600">✓ {{ $resposta->avaliadoPor->nome }}</span>
                                                                            @endif
                                                                        </p>
                                                                    </div>
                                                                    
                                                                    {{-- Ações da resposta --}}
                                                                    <div class="flex items-center gap-0.5 flex-shrink-0">
                                                                        <button type="button"
                                                                               @click="pdfUrl = '{{ route('admin.estabelecimentos.processos.documento-digital.resposta.visualizar', [$estabelecimento->id, $processo->id, $docDigital->id, $resposta->id]) }}'; modalVisualizador = true"
                                                                               class="p-1 text-blue-600 hover:bg-blue-100 rounded transition-colors" title="Visualizar">
                                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                            </svg>
                                                                        </button>
                                                                        <a href="{{ route('admin.estabelecimentos.processos.documento-digital.resposta.download', [$estabelecimento->id, $processo->id, $docDigital->id, $resposta->id]) }}"
                                                                           class="p-1 text-gray-600 hover:bg-gray-200 rounded transition-colors" title="Download">
                                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                                            </svg>
                                                                        </a>
                                                                        @if($resposta->status === 'pendente')
                                                                        <form action="{{ route('admin.estabelecimentos.processos.documento-digital.resposta.aprovar', [$estabelecimento->id, $processo->id, $docDigital->id, $resposta->id]) }}" method="POST" class="inline">
                                                                            @csrf
                                                                            <button type="submit" 
                                                                                    class="p-1 text-green-600 hover:bg-green-100 rounded transition-colors" 
                                                                                    title="Aprovar"
                                                                                    onclick="return confirm('Aprovar esta resposta?')">
                                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                                </svg>
                                                                            </button>
                                                                        </form>
                                                                        <button @click="showRejeitar = !showRejeitar" 
                                                                                class="p-1 text-red-600 hover:bg-red-100 rounded transition-colors" 
                                                                                title="Rejeitar">
                                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                            </svg>
                                                                        </button>
                                                                        @endif
                                                                        <button type="button"
                                                                                @click="abrirModalExclusao('resposta', {{ $resposta->id }}, '{{ addslashes($resposta->nome_arquivo) }}', '{{ route('admin.estabelecimentos.processos.documento-digital.resposta.excluir', [$estabelecimento->id, $processo->id, $docDigital->id, $resposta->id]) }}')"
                                                                                class="p-1 text-gray-400 hover:bg-red-100 hover:text-red-600 rounded transition-colors" 
                                                                                title="Excluir">
                                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                
                                                                {{-- Formulário de rejeição inline --}}
                                                                <div x-show="showRejeitar" x-transition class="mt-2 pt-2 border-t border-gray-100">
                                                                    <form action="{{ route('admin.estabelecimentos.processos.documento-digital.resposta.rejeitar', [$estabelecimento->id, $processo->id, $docDigital->id, $resposta->id]) }}" method="POST">
                                                                        @csrf
                                                                        <textarea name="motivo_rejeicao" rows="2" class="w-full text-xs border border-gray-300 rounded px-2 py-1 focus:ring-1 focus:ring-red-500 focus:border-red-500" placeholder="Motivo da rejeição..." required></textarea>
                                                                        <div class="flex justify-end gap-1 mt-1">
                                                                            <button type="button" @click="showRejeitar = false" class="px-2 py-1 text-[10px] text-gray-600 hover:bg-gray-100 rounded">Cancelar</button>
                                                                            <button type="submit" class="px-2 py-1 text-[10px] bg-red-600 text-white rounded hover:bg-red-700">Rejeitar</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    @endif
                                                    
                                                    {{-- Evento: Prazo Finalizado --}}
                                                    @if($prazoFinalizado)
                                                    <div class="flex items-start gap-3 relative">
                                                        <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center z-10 flex-shrink-0">
                                                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-xs font-medium text-green-700">✅ Documento resolvido/finalizado</p>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                @elseif($item['tipo'] === 'ordem_servico')
                                    @php
                                        $os = $item['documento'];
                                    @endphp
                                <div class="p-3 bg-blue-50 rounded-lg border border-gray-200 border-l-4 border-l-blue-600 hover:shadow-md transition-all"
                                     style="border-top-color: #e5e7eb; border-right-color: #e5e7eb; border-bottom-color: #e5e7eb;">
                                    
                                    {{-- Layout Flex: Título+Data | Opções --}}
                                    <div class="flex items-center justify-between gap-3">
                                        {{-- ESQUERDA: Ícone + Nome + Data --}}
                                        <a href="{{ route('admin.ordens-servico.show', $os) }}" class="flex items-center gap-2 min-w-0 flex-1">
                                            <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-semibold text-gray-900 hover:text-blue-600 truncate">OS #{{ $os->numero }}</span>
                                                        <span class="text-[11px] text-gray-400 flex-shrink-0">{{ $os->created_at->format('d/m/Y') }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                                                        {!! $os->status_badge !!}
                                                        {!! $os->competencia_badge !!}
                                                        @if($os->municipio)
                                                        <span class="text-xs text-gray-500">{{ $os->municipio->nome }}/{{ $os->municipio->uf }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>
                                        
                                        {{-- DIREITA: Opções --}}
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            <a href="{{ route('admin.ordens-servico.show', $os) }}" 
                                               class="px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-semibold rounded transition-colors flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                Ver OS
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @elseif($item['tipo'] === 'arquivo')
                                    @php
                                        $documento = $item['documento'];
                                        $isCorrecao = $documento->isSubstituicao();
                                        $historicoRejeicoes = $isCorrecao ? $documento->getHistoricoRejeicoes() : collect();
                                    @endphp
                                <div x-data="{ pastaDocumento: {{ $documento->pasta_id ?? 'null' }}, showHistorico: false }"
                                     x-show="pastaAtiva === null || pastaAtiva === pastaDocumento"
                                     class="p-3 bg-white rounded-lg border border-gray-200 border-l-4 {{ $documento->status_aprovacao === 'rejeitado' ? 'border-l-red-500' : ($documento->status_aprovacao === 'pendente' ? 'border-l-orange-500' : 'border-l-green-500') }} hover:shadow-md transition-all"
                                     style="border-top-color: #e5e7eb; border-right-color: #e5e7eb; border-bottom-color: #e5e7eb;">
                                    
                                    {{-- Layout Flex: Título+Data | Opções --}}
                                    <div class="flex items-start justify-between gap-3">
                                        {{-- ESQUERDA: Ícone + Nome + Data --}}
                                        <div @click="abrirVisualizadorAnotacoes({{ $documento->id }}, '{{ route('admin.estabelecimentos.processos.visualizar', [$estabelecimento->id, $processo->id, $documento->id]) }}', {{ $documento->tipo_usuario === 'externo' && $documento->status_aprovacao === 'pendente' ? 'true' : 'false' }})" 
                                             class="flex items-start gap-2 cursor-pointer min-w-0 flex-1">
                                            {{-- Ícone --}}
                                            <div class="w-8 h-8 {{ $documento->tipo_usuario === 'externo' ? 'bg-blue-100' : 'bg-gray-100' }} rounded flex items-center justify-center flex-shrink-0 mt-0.5">
                                                <span class="text-sm">📎</span>
                                            </div>
                                            {{-- Nome, Status e Data --}}
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-gray-900 hover:text-blue-600 break-words leading-tight">{{ $documento->nome_original }}</p>
                                                <div class="flex items-center gap-1.5 flex-wrap mt-1">
                                                    <span class="text-[11px] text-gray-400">{{ $documento->created_at->format('d/m/Y') }}</span>
                                                    <span class="text-xs text-gray-500">{{ $documento->tamanho_formatado }}</span>
                                                    <span class="px-1.5 py-0.5 text-[10px] rounded {{ $documento->tipo_usuario === 'interno' ? 'bg-gray-200 text-gray-700 font-semibold' : 'bg-blue-100 text-blue-700 font-semibold' }}">
                                                        {{ $documento->tipo_usuario === 'interno' ? 'Int' : 'Ext' }}
                                                    </span>
                                                    @if($documento->tipo_usuario === 'externo' && $documento->status_aprovacao)
                                                        @if($documento->status_aprovacao === 'pendente')
                                                            <span class="px-1.5 py-0.5 bg-yellow-100 text-yellow-700 text-[10px] rounded font-semibold">Pendente</span>
                                                            @if($isCorrecao)
                                                                <span class="px-1.5 py-0.5 bg-orange-100 text-orange-700 text-[10px] rounded font-semibold">Correção #{{ $documento->tentativas_envio ?? 1 }}</span>
                                                            @endif
                                                        @elseif($documento->status_aprovacao === 'aprovado')
                                                            <span class="px-1.5 py-0.5 bg-green-100 text-green-700 text-[10px] rounded font-semibold" title="{{ $documento->aprovadoPor ? 'Aprovado por ' . $documento->aprovadoPor->nome . ($documento->aprovado_em ? ' em ' . \Carbon\Carbon::parse($documento->aprovado_em)->format('d/m/Y H:i') : '') : '' }}">✓ Aprovado{{ $documento->aprovadoPor ? ' - ' . Str::upper(Str::words($documento->aprovadoPor->nome, 1, '')) : '' }}</span>
                                                        @elseif($documento->status_aprovacao === 'rejeitado')
                                                            <span class="px-1.5 py-0.5 bg-red-100 text-red-700 text-[10px] rounded font-semibold">Rejeitado</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- DIREITA: Opções --}}
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            @if($documento->tipo_usuario === 'externo' && $documento->status_aprovacao === 'pendente')
                                            <form action="{{ route('admin.estabelecimentos.processos.documento.aprovar', [$estabelecimento->id, $processo->id, $documento->id]) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-green-600 hover:bg-green-50 rounded transition-colors" title="Aprovar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                            </form>
                                            <button type="button" @click="documentoRejeitando = {{ $documento->id }}; modalRejeitar = true" class="p-1.5 text-red-600 hover:bg-red-50 rounded transition-colors" title="Rejeitar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                            @elseif($documento->tipo_usuario === 'externo' && $documento->status_aprovacao === 'aprovado')
                                            <form action="{{ route('admin.estabelecimentos.processos.documento.revalidar', [$estabelecimento->id, $processo->id, $documento->id]) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Deseja revalidar este documento? Ele voltará para o status Pendente.')">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded transition-colors" title="Revalidar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                </button>
                                            </form>
                                            @elseif($documento->tipo_usuario === 'externo' && $documento->status_aprovacao === 'rejeitado')
                                            <form action="{{ route('admin.estabelecimentos.processos.documento.revalidar', [$estabelecimento->id, $processo->id, $documento->id]) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Deseja revalidar este documento rejeitado? Ele voltará para o status Pendente.')">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded transition-colors" title="Revalidar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                </button>
                                            </form>
                                            @endif
                                            
                                            <a href="{{ route('admin.estabelecimentos.processos.download', [$estabelecimento->id, $processo->id, $documento->id]) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition-colors" title="Download">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                            </a>
                                            
                                            {{-- Menu 3 pontos --}}
                                            <div class="relative" x-data="{ menuAberto: false }">
                                                <button @click.stop="menuAberto = !menuAberto" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors" title="Mais opções">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                                                    </svg>
                                                </button>
                                                <div x-show="menuAberto" @click.away="menuAberto = false" x-transition class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-xl border z-[9999] py-1" style="display: none;">
                                                    <button @click="moverParaPasta({{ $documento->id }}, 'arquivo', null, $el); menuAberto = false" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">Remover da pasta</button>
                                                    <template x-for="pasta in pastas" :key="pasta.id">
                                                        <button @click="moverParaPasta({{ $documento->id }}, 'arquivo', pasta.id, $el); menuAberto = false" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                            <span class="w-2 h-2 rounded-full" :style="`background-color: ${pasta.cor}`"></span>
                                                            <span x-text="pasta.nome"></span>
                                                        </button>
                                                    </template>
                                                    <hr class="my-1">
                                                    <button @click="documentoEditando = {{ $documento->id }}; nomeEditando = '{{ $documento->nome_original }}'; modalEditarNome = true; menuAberto = false" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">Renomear</button>
                                                    <button type="button" 
                                                            @click="abrirModalExclusao('documento', {{ $documento->id }}, '{{ addslashes($documento->nome_original) }}', '{{ route('admin.estabelecimentos.processos.deleteArquivo', [$estabelecimento->id, $processo->id, $documento->id]) }}'); menuAberto = false"
                                                            class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50">Excluir</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Motivo da Rejeição e Histórico --}}
                                    @if($documento->status_aprovacao === 'rejeitado' && $documento->motivo_rejeicao)
                                    <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded-lg ml-10">
                                        <p class="text-xs text-red-700"><span class="font-semibold">Motivo:</span> {{ $documento->motivo_rejeicao }}</p>
                                    </div>
                                    @endif
                                    @if($isCorrecao && $historicoRejeicoes->count() > 0)
                                    <button @click.stop="showHistorico = !showHistorico" class="ml-10 mt-1 text-xs text-red-600 hover:text-red-700 font-semibold flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Ver histórico ({{ $historicoRejeicoes->count() }})
                                        <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': showHistorico }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    @endif
                                    
                                    {{-- Histórico Expandido --}}
                                    @if($isCorrecao && $historicoRejeicoes->count() > 0)
                                    <div x-show="showHistorico" x-transition class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg" style="display: none;">
                                        <p class="text-xs font-semibold text-red-700 mb-2">Histórico de Rejeições:</p>
                                        @foreach($historicoRejeicoes as $docRejeitado)
                                        <div class="p-2 bg-white border border-red-100 rounded text-xs mb-2 last:mb-0">
                                            <div class="flex items-center justify-between">
                                                <span class="font-semibold text-gray-700">{{ $docRejeitado->nome_original }}</span>
                                                <span class="text-gray-500">{{ $docRejeitado->created_at ? $docRejeitado->created_at->format('d/m/Y H:i') : '' }}</span>
                                            </div>
                                            @if($docRejeitado->motivo_rejeicao)
                                            <p class="text-red-600 mt-1"><strong>Motivo:</strong> {{ $docRejeitado->motivo_rejeicao }}</p>
                                            @endif
                                            @if(isset($docRejeitado->id))
                                            <a href="{{ route('admin.estabelecimentos.processos.visualizar', [$estabelecimento->id, $processo->id, $docRejeitado->id]) }}" target="_blank" class="text-blue-600 hover:underline mt-1 inline-block">Ver documento →</a>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Upload --}}
    <template x-teleport="body">
        <div x-show="modalUpload" 
             x-cloak
             @keydown.escape.window="modalUpload = false"
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
            
            {{-- Overlay --}}
            <div @click="modalUpload = false"
                 style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5);"></div>
            
            {{-- Modal Content --}}
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; max-width: 500px; padding: 0 1rem;">
                <div class="bg-white rounded-xl shadow-2xl p-6" @click.stop>
                    {{-- Close Button --}}
                    <button @click="modalUpload = false"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    {{-- Header --}}
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Upload de Arquivo</h3>
                        <p class="text-sm text-gray-600 mt-1">Envie um arquivo PDF para este processo</p>
                    </div>

                    {{-- Form --}}
                    <form method="POST" action="{{ route('admin.estabelecimentos.processos.upload', [$estabelecimento->id, $processo->id]) }}" enctype="multipart/form-data">
                        @csrf
                        
                        {{-- Upload de Arquivo --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Arquivo PDF <span class="text-red-500">*</span>
                            </label>
                            <input type="file" 
                                   name="arquivo" 
                                   accept=".pdf"
                                   required
                                   id="inputArquivoUpload"
                                   onchange="validarTamanhoArquivo(this)"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <p class="mt-1 text-xs text-gray-500">
                                Apenas arquivos PDF. Tamanho máximo: 10MB
                            </p>
                            <p id="erroTamanhoArquivo" class="mt-1 text-xs text-red-600 hidden"></p>
                        </div>

                        {{-- Info --}}
                        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-xs text-blue-700">
                                    O arquivo será identificado como "Arquivo Externo" na lista de documentos.
                                </p>
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div class="flex items-center gap-3">
                            <button type="button"
                                    @click="modalUpload = false"
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    id="btnEnviarArquivo"
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                Enviar Arquivo
                            </button>
                        </div>
                        
                        <script>
                        function validarTamanhoArquivo(input) {
                            const maxSize = 10 * 1024 * 1024; // 10MB
                            const erroEl = document.getElementById('erroTamanhoArquivo');
                            const btnEnviar = document.getElementById('btnEnviarArquivo');
                            
                            if (input.files && input.files[0]) {
                                const file = input.files[0];
                                const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                                
                                if (file.size > maxSize) {
                                    erroEl.textContent = `Arquivo muito grande (${sizeMB}MB). O tamanho máximo permitido é 10MB.`;
                                    erroEl.classList.remove('hidden');
                                    btnEnviar.disabled = true;
                                    input.value = '';
                                } else if (!file.name.toLowerCase().endsWith('.pdf')) {
                                    erroEl.textContent = 'Apenas arquivos PDF são permitidos.';
                                    erroEl.classList.remove('hidden');
                                    btnEnviar.disabled = true;
                                    input.value = '';
                                } else {
                                    erroEl.classList.add('hidden');
                                    btnEnviar.disabled = false;
                                }
                            }
                        }
                        </script>
                    </form>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Visualização de PDF --}}
    <template x-teleport="body">
        <div x-show="modalVisualizador" 
             x-cloak
             @keydown.escape.window="modalVisualizador = false"
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
            
            {{-- Overlay --}}
            <div @click="modalVisualizador = false"
                 style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.75);"></div>
            
            {{-- Modal Content --}}
            <div style="position: absolute; top: 2%; left: 2%; right: 2%; bottom: 2%; max-width: 1200px; margin: 0 auto;">
                <div class="bg-white rounded-xl shadow-2xl h-full flex flex-col" @click.stop>
                    {{-- Header --}}
                    <div class="flex items-center justify-between p-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Visualizar Documento</h3>
                        <button @click="modalVisualizador = false"
                                class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- PDF Viewer --}}
                    <div class="flex-1 overflow-hidden">
                        <iframe :src="pdfUrl" 
                                class="w-full h-full border-0"
                                style="min-height: 500px;">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Visualização de Documento com Respostas - Layout Melhorado --}}
    <template x-teleport="body">
        <div x-show="modalRespostas" 
             x-data="{ 
                respostaVisualizandoUrl: null,
                respostaVisualizandoNome: '',
                respostaVisualizandoId: null,
                showRejeitar: false,
                respostaRejeitandoId: null
             }"
             x-cloak
             @keydown.escape.window="modalRespostas = false; respostaVisualizandoUrl = null"
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
            
            {{-- Overlay --}}
            <div @click="modalRespostas = false; respostaVisualizandoUrl = null"
                 style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.85);"></div>
            
            {{-- Modal Content - Tela Quase Toda --}}
            <div style="position: absolute; top: 1%; left: 1%; right: 1%; bottom: 1%;">
                <div class="bg-white rounded-xl shadow-2xl h-full flex flex-col" @click.stop>
                    {{-- Header --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-amber-50 to-yellow-50">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-amber-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900" x-text="respostasDocumentoNome"></h3>
                                <p class="text-xs text-gray-500" x-text="respostasDocumentoNumero"></p>
                            </div>
                        </div>
                        <button @click="modalRespostas = false; respostaVisualizandoUrl = null"
                                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Conteúdo Principal --}}
                    <div class="flex-1 flex overflow-hidden">
                        {{-- Coluna Esquerda: Documento Original (40%) --}}
                        <div class="w-2/5 border-r border-gray-200 flex flex-col">
                            <div class="px-3 py-2 bg-blue-50 border-b border-blue-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-xs font-semibold text-blue-800">Documento Original</span>
                            </div>
                            <div class="flex-1 overflow-hidden bg-gray-100">
                                <iframe :src="respostasDocumentoPdfUrl" class="w-full h-full border-0"></iframe>
                            </div>
                        </div>

                        {{-- Coluna Direita: Respostas + Visualização (60%) --}}
                        <div class="w-3/5 flex flex-col bg-gray-50">
                            {{-- Se tem resposta selecionada, mostra visualização --}}
                            <template x-if="respostaVisualizandoUrl">
                                <div class="flex-1 flex flex-col">
                                    {{-- Header da Resposta Visualizada --}}
                                    <div class="px-3 py-2 bg-emerald-50 border-b border-emerald-200 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span class="text-xs font-semibold text-emerald-800">Resposta:</span>
                                            <span class="text-xs text-emerald-700" x-text="respostaVisualizandoNome"></span>
                                        </div>
                                        <button @click="respostaVisualizandoUrl = null; respostaVisualizandoNome = ''"
                                                class="px-2 py-1 text-xs font-medium text-gray-600 bg-white hover:bg-gray-100 rounded border border-gray-300 transition-colors flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                            </svg>
                                            Voltar à lista
                                        </button>
                                    </div>
                                    {{-- Visualização do PDF da Resposta --}}
                                    <div class="flex-1 overflow-hidden bg-gray-100">
                                        <iframe :src="respostaVisualizandoUrl" class="w-full h-full border-0"></iframe>
                                    </div>
                                </div>
                            </template>

                            {{-- Se não tem resposta selecionada, mostra lista --}}
                            <template x-if="!respostaVisualizandoUrl">
                                <div class="flex-1 flex flex-col">
                                    <div class="px-3 py-2 bg-amber-50 border-b border-amber-200 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                        <span class="text-xs font-semibold text-amber-800">Respostas do Estabelecimento</span>
                                        <span class="text-[10px] text-amber-600">(clique para visualizar)</span>
                                    </div>
                                    <div class="flex-1 overflow-y-auto p-3 space-y-2">
                                        @foreach($documentosDigitais as $docDigital)
                                            @if($docDigital->respostas && $docDigital->respostas->count() > 0)
                                                <template x-if="respostasDocumentoId === {{ $docDigital->id }}">
                                                    <div class="space-y-2">
                                                        @foreach($docDigital->respostas as $resposta)
                                                        <div class="bg-white rounded-lg border {{ $resposta->status === 'pendente' ? 'border-yellow-300' : ($resposta->status === 'aprovado' ? 'border-green-300' : 'border-red-300') }} shadow-sm overflow-hidden">
                                                            {{-- Linha Principal Clicável --}}
                                                            <div class="px-3 py-2 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition-colors"
                                                                 @click="respostaVisualizandoUrl = '{{ route('admin.estabelecimentos.processos.documento-digital.resposta.visualizar', [$estabelecimento->id, $processo->id, $docDigital->id, $resposta->id]) }}'; respostaVisualizandoNome = '{{ addslashes($resposta->nome_original) }}'; respostaVisualizandoId = {{ $resposta->id }}">
                                                                {{-- Ícone de Status --}}
                                                                <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 {{ $resposta->status === 'pendente' ? 'bg-yellow-100' : ($resposta->status === 'aprovado' ? 'bg-green-100' : 'bg-red-100') }}">
                                                                    @if($resposta->status === 'pendente')
                                                                        <svg class="w-3.5 h-3.5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                        </svg>
                                                                    @elseif($resposta->status === 'aprovado')
                                                                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                        </svg>
                                                                    @else
                                                                        <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                        </svg>
                                                                    @endif
                                                                </div>
                                                                {{-- Info --}}
                                                                <div class="flex-1 min-w-0">
                                                                    <p class="font-medium text-gray-900 text-sm truncate">{{ $resposta->nome_original }}</p>
                                                                    <p class="text-[10px] text-gray-500">
                                                                        {{ $resposta->tamanho_formatado }} • {{ $resposta->created_at->format('d/m/Y H:i') }} • {{ $resposta->usuarioExterno->nome ?? 'N/D' }}
                                                                    </p>
                                                                </div>
                                                                {{-- Badge Status --}}
                                                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full flex-shrink-0 {{ $resposta->status === 'pendente' ? 'bg-yellow-100 text-yellow-700' : ($resposta->status === 'aprovado' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">
                                                                    {{ $resposta->status === 'pendente' ? 'Pendente' : ($resposta->status === 'aprovado' ? 'Aprovado' : 'Rejeitado') }}
                                                                </span>
                                                                {{-- Ícone de Visualizar --}}
                                                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                                </svg>
                                                            </div>
                                                            
                                                            {{-- Ações (sempre visíveis) --}}
                                                            <div class="px-3 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-2">
                                                                <a href="{{ route('admin.estabelecimentos.processos.documento-digital.resposta.download', [$estabelecimento->id, $processo->id, $docDigital->id, $resposta->id]) }}"
                                                                   class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-medium text-gray-600 bg-white hover:bg-gray-100 rounded border border-gray-200 transition-colors">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                                    </svg>
                                                                    Download
                                                                </a>
                                                                
                                                                @if($resposta->status === 'pendente')
                                                                <div class="flex items-center gap-1">
                                                                    <form action="{{ route('admin.estabelecimentos.processos.documento-digital.resposta.aprovar', [$estabelecimento->id, $processo->id, $docDigital->id, $resposta->id]) }}" method="POST" class="inline">
                                                                        @csrf
                                                                        <button type="submit" 
                                                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-green-600 hover:bg-green-700 rounded transition-colors"
                                                                                onclick="return confirm('Aprovar esta resposta?')">
                                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                            </svg>
                                                                            Aprovar
                                                                        </button>
                                                                    </form>
                                                                    <div class="relative">
                                                                        <button @click="showRejeitar = !showRejeitar; respostaRejeitandoId = {{ $resposta->id }}" 
                                                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded transition-colors">
                                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                            </svg>
                                                                            Rejeitar
                                                                        </button>
                                                                        {{-- Dropdown de Rejeição - Fixed Position Centralizado --}}
                                                                        <template x-if="showRejeitar && respostaRejeitandoId === {{ $resposta->id }}">
                                                                            <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 50; display: flex; align-items: center; justify-content: center;">
                                                                                {{-- Overlay --}}
                                                                                <div @click="showRejeitar = false"
                                                                                     style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 40;"></div>
                                                                                
                                                                                {{-- Modal de Rejeição --}}
                                                                                <div style="position: relative; z-index: 50;" class="w-96 bg-white rounded-lg shadow-2xl border border-gray-200 p-5" @click.stop>
                                                                                    <div class="flex items-center justify-between mb-4">
                                                                                        <h4 class="text-base font-bold text-red-700">Rejeitar Resposta</h4>
                                                                                        <button type="button" @click="showRejeitar = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded p-1 transition-colors">
                                                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                                            </svg>
                                                                                        </button>
                                                                                    </div>
                                                                                    <form action="{{ route('admin.estabelecimentos.processos.documento-digital.resposta.rejeitar', [$estabelecimento->id, $processo->id, $docDigital->id, $resposta->id]) }}" method="POST">
                                                                                        @csrf
                                                                                        <label class="block text-sm font-semibold text-gray-800 mb-2">Motivo da Rejeição *</label>
                                                                                        <textarea name="motivo_rejeicao" required rows="5" 
                                                                                                  class="w-full px-4 py-3 text-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none"
                                                                                                  placeholder="Descreva detalhadamente o motivo da rejeição..."></textarea>
                                                                                        <div class="flex gap-3 mt-4">
                                                                                            <button type="submit" class="flex-1 px-4 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors">
                                                                                                Confirmar Rejeição
                                                                                            </button>
                                                                                            <button type="button" @click="showRejeitar = false" class="flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                                                                                                Cancelar
                                                                                            </button>
                                                                                        </div>
                                                                                    </form>
                                                                                </div>
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                                @elseif($resposta->status === 'aprovado')
                                                                <span class="text-[10px] text-green-600">
                                                                    ✓ Aprovado por {{ $resposta->avaliadoPor->nome ?? 'N/D' }}
                                                                </span>
                                                                @else
                                                                <span class="text-[10px] text-red-600 truncate max-w-[200px]" title="{{ $resposta->motivo_rejeicao }}">
                                                                    ✗ {{ Str::limit($resposta->motivo_rejeicao, 30) }}
                                                                </span>
                                                                @endif
                                                            </div>

                                                            {{-- Histórico de Rejeições --}}
                                                            @if($resposta->historico_rejeicao && count($resposta->historico_rejeicao) > 0)
                                                            <div class="px-3 py-1.5 bg-red-50 border-t border-red-100">
                                                                <p class="text-[10px] font-medium text-red-700">Histórico:</p>
                                                                @foreach($resposta->historico_rejeicao as $rejeicao)
                                                                <p class="text-[10px] text-red-600">• {{ \Carbon\Carbon::parse($rejeicao['rejeitado_em'])->format('d/m H:i') }}: {{ Str::limit($rejeicao['motivo'], 40) }}</p>
                                                                @endforeach
                                                            </div>
                                                            @endif
                                                        </div>
                                                        @endforeach

                                                        {{-- Mensagem se todas avaliadas --}}
                                                        @if($docDigital->respostas->where('status', 'pendente')->count() === 0)
                                                        <div class="text-center py-3 text-xs text-gray-500">
                                                            <svg class="w-6 h-6 mx-auto text-green-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            Todas as respostas foram avaliadas!
                                                        </div>
                                                        @endif
                                                    </div>
                                                </template>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Visualização de PDF com Anotações --}}
    <template x-teleport="body">
        <div x-show="modalVisualizadorAnotacoes" 
             x-cloak
             @keydown.escape.window="fecharModalPDF()"
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
            
            {{-- Modal Content - Tela Toda --}}
            <div class="bg-white h-full flex flex-col" @click.stop>
                    {{-- Header Compacto --}}
                    <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Visualizar PDF
                        </h3>
                        
                        <div class="flex items-center gap-2">
                            {{-- Botões Aprovar/Rejeitar (só aparecem se documento é externo e pendente) --}}
                            <template x-if="documentoPendente">
                                <div class="flex items-center gap-2">
                                    <form :action="`{{ url('admin/estabelecimentos/' . $estabelecimento->id . '/processos/' . $processo->id . '/documentos') }}/${documentoIdAnotacoes}/aprovar`" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition-colors flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Aprovar
                                        </button>
                                    </form>
                                    <button type="button" 
                                            @click="documentoRejeitando = documentoIdAnotacoes; modalRejeitar = true; fecharModalPDF()"
                                            class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition-colors flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Rejeitar
                                    </button>
                                </div>
                            </template>
                            
                            <button @click="fecharModalPDF()"
                                    class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- PDF Viewer com Anotações --}}
                    <div class="flex-1 overflow-hidden">
                        <template x-if="documentoIdAnotacoes && pdfUrlAnotacoes">
                            <div x-data="pdfViewerAnotacoes(documentoIdAnotacoes, pdfUrlAnotacoes, [])" 
                                 x-init="init()"
                                 class="pdf-viewer-container h-full flex flex-col">
                                @include('components.pdf-viewer-anotacoes-compact')
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Editar Nome --}}
    <template x-teleport="body">
        <div x-show="modalEditarNome" 
             x-cloak
             @keydown.escape.window="modalEditarNome = false"
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
            
            {{-- Overlay --}}
            <div @click="modalEditarNome = false"
                 style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5);"></div>
            
            {{-- Modal Content --}}
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; max-width: 500px; padding: 0 1rem;">
                <div class="bg-white rounded-xl shadow-2xl p-6" @click.stop>
                    {{-- Close Button --}}
                    <button @click="modalEditarNome = false"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    {{-- Header --}}
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Editar Nome do Arquivo</h3>
                        <p class="text-sm text-gray-600 mt-1">Altere o nome de exibição do arquivo</p>
                    </div>

                    {{-- Form --}}
                    <form method="POST" :action="`{{ route('admin.estabelecimentos.processos.show', [$estabelecimento->id, $processo->id]) }}`.replace('/processos/{{ $processo->id }}', `/processos/{{ $processo->id }}/documentos/${documentoEditando}/nome`)">
                        @csrf
                        @method('PATCH')
                        
                        {{-- Nome do Arquivo --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nome do Arquivo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nome_original" 
                                   x-model="nomeEditando"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   placeholder="Ex: Relatório Anual 2025.pdf">
                            <p class="mt-1 text-xs text-gray-500">
                                Este é o nome que aparecerá na lista de documentos
                            </p>
                        </div>

                        {{-- Buttons --}}
                        <div class="flex items-center gap-3">
                            <button type="button"
                                    @click="modalEditarNome = false"
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Criar Documento Digital --}}
    <template x-teleport="body">
        <div x-show="modalDocumentoDigital" 
             x-cloak
             @keydown.escape.window="modalDocumentoDigital = false"
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
            
            {{-- Overlay --}}
            <div @click="modalDocumentoDigital = false"
                 style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5);"></div>
            
            {{-- Modal Content --}}
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; max-width: 600px; padding: 0 1rem;">
                <div class="bg-white rounded-xl shadow-2xl p-6" @click.stop>
                    {{-- Close Button --}}
                    <button @click="modalDocumentoDigital = false"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    {{-- Header --}}
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Criar Documento Digital
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Selecione um modelo para gerar o documento</p>
                    </div>

                    {{-- Form --}}
                    <form method="POST" action="{{ route('admin.estabelecimentos.processos.gerarDocumento', [$estabelecimento->id, $processo->id]) }}">
                        @csrf
                        
                        {{-- Selecionar Modelo --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Modelo de Documento <span class="text-red-500">*</span>
                            </label>
                            <select name="modelo_documento_id" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">Selecione um modelo</option>
                                @foreach($modelosDocumento as $modelo)
                                    <option value="{{ $modelo->id }}">
                                        {{ $modelo->tipoDocumento->nome }}
                                        @if($modelo->descricao)
                                            - {{ Str::limit($modelo->descricao, 40) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                O documento será gerado em PDF e adicionado à lista de arquivos
                            </p>
                        </div>

                        {{-- Buttons --}}
                        <div class="flex items-center gap-3">
                            <button type="button"
                                    @click="modalDocumentoDigital = false"
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                                Gerar Documento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Pastas do Processo --}}
    <template x-if="modalPastas">
        <div class="fixed inset-0 z-50 overflow-y-auto" x-show="modalPastas" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="modalPastas = false"></div>

                {{-- Modal --}}
                <div class="inline-block w-full max-w-4xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    {{-- Header --}}
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-600 to-purple-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                                Gerenciar Pastas do Processo
                            </h3>
                            <button @click="modalPastas = false" class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4">
                        {{-- Formulário de Nova Pasta --}}
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">
                                <span x-show="!pastaEditando">Nova Pasta</span>
                                <span x-show="pastaEditando">Editar Pasta</span>
                            </h4>
                            <form @submit.prevent="salvarPasta()" class="space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Nome da Pasta *</label>
                                        <input type="text" x-model="nomePasta" required
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                               placeholder="Ex: Documentos Técnicos">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Cor</label>
                                        <input type="color" x-model="corPasta"
                                               class="w-full h-10 px-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Descrição</label>
                                    <textarea x-model="descricaoPasta" rows="2"
                                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                              placeholder="Descrição opcional da pasta"></textarea>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors">
                                        <span x-show="!pastaEditando">Criar Pasta</span>
                                        <span x-show="pastaEditando">Salvar Alterações</span>
                                    </button>
                                    <button type="button" x-show="pastaEditando" @click="cancelarEdicao()"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Lista de Pastas --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Pastas Criadas</h4>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                <template x-if="pastas.length === 0">
                                    <div class="text-center py-8 text-gray-500">
                                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                        </svg>
                                        <p class="text-sm">Nenhuma pasta criada ainda</p>
                                    </div>
                                </template>
                                <template x-for="pasta in pastas" :key="pasta.id">
                                    <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg hover:shadow-sm transition-shadow">
                                        <div class="flex items-center gap-3 flex-1">
                                            <div class="w-10 h-10 rounded-lg flex items-center justify-center" :style="`background-color: ${pasta.cor}20`">
                                                <svg class="w-5 h-5" :style="`color: ${pasta.cor}`" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h5 class="text-sm font-medium text-gray-900" x-text="pasta.nome"></h5>
                                                <p class="text-xs text-gray-500" x-text="pasta.descricao || 'Sem descrição'"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button @click="editarPasta(pasta)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <button @click="excluirPasta(pasta.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <button @click="modalPastas = false" class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Parar Processo --}}
    <template x-if="modalParar">
        <div class="fixed inset-0 z-50 overflow-y-auto" x-show="modalParar" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="modalParar = false"></div>

                {{-- Modal --}}
                <div class="inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    <form action="{{ route('admin.estabelecimentos.processos.parar', [$estabelecimento->id, $processo->id]) }}" method="POST">
                        @csrf
                        
                        {{-- Header --}}
                        <div class="px-6 py-4 bg-gradient-to-r from-red-600 to-red-700 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Parar Processo
                            </h3>
                            <button type="button" @click="modalParar = false" class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Conteúdo --}}
                        <div class="px-6 py-6">
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-4">
                                    Você está prestes a parar o processo <strong>{{ $processo->numero_processo }}</strong>. 
                                    Por favor, informe o motivo da parada.
                                </p>
                                
                                <label for="motivo_parada" class="block text-sm font-medium text-gray-700 mb-2">
                                    Motivo da Parada <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    name="motivo_parada" 
                                    id="motivo_parada" 
                                    rows="4"
                                    required
                                    minlength="10"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none"
                                    placeholder="Descreva o motivo da parada (mínimo 10 caracteres)..."></textarea>
                                
                                @error('motivo_parada')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            <strong>Atenção:</strong> O processo será marcado como parado e esta ação ficará registrada no histórico.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-3">
                            <button type="button" @click="modalParar = false" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                                Parar Processo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Arquivar Processo --}}
    <template x-if="modalArquivar">
        <div class="fixed inset-0 z-50 overflow-y-auto" x-show="modalArquivar" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="modalArquivar = false"></div>

                {{-- Modal --}}
                <div class="inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    <form action="{{ route('admin.estabelecimentos.processos.arquivar', [$estabelecimento->id, $processo->id]) }}" method="POST">
                        @csrf
                        
                        {{-- Header --}}
                        <div class="px-6 py-4 bg-gradient-to-r from-orange-600 to-orange-700 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                                Arquivar Processo
                            </h3>
                            <button type="button" @click="modalArquivar = false" class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Conteúdo --}}
                        <div class="px-6 py-6">
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-4">
                                    Você está prestes a arquivar o processo <strong>{{ $processo->numero_processo }}</strong>. 
                                    Por favor, informe o motivo do arquivamento.
                                </p>
                                
                                <label for="motivo_arquivamento" class="block text-sm font-medium text-gray-700 mb-2">
                                    Motivo do Arquivamento <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    name="motivo_arquivamento" 
                                    id="motivo_arquivamento" 
                                    rows="4"
                                    required
                                    minlength="10"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none"
                                    placeholder="Descreva o motivo do arquivamento (mínimo 10 caracteres)..."></textarea>
                                
                                @error('motivo_arquivamento')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            <strong>Atenção:</strong> O processo será marcado como arquivado e esta ação ficará registrada no histórico.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-3">
                            <button type="button" @click="modalArquivar = false" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition-colors">
                                Arquivar Processo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Excluir Processo --}}
    <template x-if="modalExcluirProcesso">
        <div class="fixed inset-0 z-50 overflow-y-auto" x-show="modalExcluirProcesso" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="modalExcluirProcesso = false"></div>

                {{-- Modal --}}
                <div class="inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    <form action="{{ route('admin.estabelecimentos.processos.destroy', [$estabelecimento->id, $processo->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        
                        {{-- Header --}}
                        <div class="px-6 py-4 bg-gradient-to-r from-red-600 to-red-700 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Excluir Processo
                            </h3>
                            <button type="button" @click="modalExcluirProcesso = false" class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Conteúdo --}}
                        <div class="px-6 py-6">
                            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded mb-4">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm font-bold text-red-800">ATENÇÃO: Esta ação é irreversível!</p>
                                    </div>
                                </div>
                            </div>

                            <p class="text-sm text-gray-600 mb-4">
                                Você está prestes a excluir permanentemente o processo <strong class="text-red-600">{{ $processo->numero_processo }}</strong>.
                            </p>
                            
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <p class="text-sm font-medium text-gray-700 mb-2">Serão excluídos:</p>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        O processo e todos os seus dados
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Todos os arquivos/documentos vinculados
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Arquivos físicos do storage
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Histórico e eventos do processo
                                    </li>
                                </ul>
                            </div>

                            <p class="text-sm text-gray-500 italic">
                                Esta ação não pode ser desfeita. Certifique-se de que realmente deseja excluir este processo.
                            </p>
                        </div>

                        {{-- Footer --}}
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-3">
                            <button type="button" @click="modalExcluirProcesso = false" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                                Excluir Permanentemente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Assinatura de Documento --}}
    <template x-if="modalAssinar">
        <div class="fixed inset-0 z-50 overflow-y-auto" x-show="modalAssinar" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="modalAssinar = false"></div>

                {{-- Modal --}}
                <div class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    {{-- Header --}}
                    <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Assinar Documento</h3>
                                <p class="text-xs text-gray-500 mt-0.5" x-text="assinarDocumentoNome"></p>
                            </div>
                            <button @click="modalAssinar = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Info do documento --}}
                    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/50">
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <span class="text-gray-400">Documento</span>
                                <p class="text-gray-700 font-medium" x-text="assinarDocumentoNumero"></p>
                            </div>
                            <div>
                                <span class="text-gray-400">Sua posição</span>
                                <p class="text-gray-700 font-medium"><span x-text="assinarOrdem"></span>º assinante</p>
                            </div>
                        </div>
                    </div>

                    {{-- Lista de assinantes --}}
                    <div class="px-5 py-3 border-b border-gray-100">
                        <p class="text-xs text-gray-500 mb-2">Assinantes:</p>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="ass in assinarAssinaturas" :key="ass.ordem">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs"
                                      :class="ass.status === 'assinado' ? 'bg-green-50 text-green-700' : (ass.isCurrentUser ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-200' : 'bg-gray-100 text-gray-500')">
                                    <template x-if="ass.status === 'assinado'">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    </template>
                                    <template x-if="ass.status !== 'assinado' && ass.isCurrentUser">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                    </template>
                                    <span x-text="ass.nome"></span>
                                </span>
                            </template>
                        </div>
                    </div>

                    {{-- Formulário --}}
                    <div class="px-5 py-4">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Senha de Assinatura</label>
                        <input type="password" 
                               x-model="assinarSenha"
                               @keydown.enter.prevent="processarAssinatura()"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               :class="assinarErro ? 'border-red-400' : ''"
                               placeholder="••••••••"
                               autofocus>
                        <p class="mt-1 text-xs text-red-500" x-show="assinarErro" x-text="assinarErro"></p>

                        {{-- Botões --}}
                        <div class="flex items-center gap-2 mt-4">
                            <button type="button" 
                                    @click="processarAssinatura()"
                                    :disabled="assinarCarregando"
                                    class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                <template x-if="assinarCarregando">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <template x-if="!assinarCarregando">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </template>
                                <span x-text="assinarCarregando ? 'Assinando...' : 'Assinar'"></span>
                            </button>
                            <button type="button" 
                                    @click="modalAssinar = false"
                                    :disabled="assinarCarregando"
                                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-50">
                                Cancelar
                            </button>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-5 py-2.5 bg-gray-50 border-t border-gray-100">
                        <p class="text-[10px] text-gray-400 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Assinatura digital protegida por criptografia
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Exclusão com Senha de Assinatura --}}
    <template x-if="modalExcluirComSenha">
        <div class="fixed inset-0 z-50 overflow-y-auto" x-show="modalExcluirComSenha" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="modalExcluirComSenha = false"></div>

                {{-- Modal --}}
                <div class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    {{-- Header --}}
                    <div class="px-6 py-4 bg-gradient-to-r from-red-600 to-red-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Confirmar Exclusão
                        </h3>
                        <button type="button" @click="modalExcluirComSenha = false" class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Conteúdo --}}
                    <div class="px-6 py-6">
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded mb-4">
                            <div class="flex">
                                <svg class="h-5 w-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div class="ml-3">
                                    <p class="text-sm font-bold text-red-800">Atenção!</p>
                                    <p class="text-sm text-red-700 mt-1">Esta ação será registrada no histórico do processo.</p>
                                </div>
                            </div>
                        </div>

                        <p class="text-sm text-gray-600 mb-4">
                            Você está prestes a excluir: <strong class="text-red-600" x-text="exclusaoNome"></strong>
                        </p>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Senha de Assinatura Digital <span class="text-red-500">*</span>
                            </label>
                            <input type="password" 
                                   x-model="senhaExclusao"
                                   @keyup.enter="executarExclusao()"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                   :class="{ 'border-red-500': exclusaoErro }"
                                   placeholder="Digite sua senha de assinatura"
                                   autofocus>
                            <p class="mt-1 text-xs text-gray-500">
                                Use a mesma senha configurada em <a href="{{ route('admin.assinatura.configurar-senha') }}" class="text-blue-600 hover:underline" target="_blank">Configurar Senha de Assinatura</a>
                            </p>
                            <p x-show="exclusaoErro" x-text="exclusaoErro" class="mt-1 text-sm text-red-600"></p>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-3">
                        <button type="button" 
                                @click="modalExcluirComSenha = false" 
                                class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                :disabled="exclusaoCarregando">
                            Cancelar
                        </button>
                        <button type="button" 
                                @click="executarExclusao()"
                                class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-2"
                                :disabled="exclusaoCarregando || !senhaExclusao">
                            <svg x-show="exclusaoCarregando" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="exclusaoCarregando ? 'Excluindo...' : 'Confirmar Exclusão'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Rejeitar Documento --}}
    <template x-if="modalRejeitar">
        <div class="fixed inset-0 z-50 overflow-y-auto" x-show="modalRejeitar" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="modalRejeitar = false"></div>
                <div class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    <div class="px-6 py-4 bg-gradient-to-r from-red-600 to-red-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Rejeitar Documento
                        </h3>
                        <button type="button" @click="modalRejeitar = false" class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <form :action="`{{ url('admin/estabelecimentos/' . $estabelecimento->id . '/processos/' . $processo->id . '/documentos') }}/${documentoRejeitando}/rejeitar`" method="POST">
                        @csrf
                        <div class="px-6 py-4">
                            <p class="text-sm text-gray-600 mb-4">Informe o motivo da rejeição do documento. O usuário externo será notificado.</p>
                            
                            {{-- Dropdown de textos predefinidos --}}
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Texto Predefinido</label>
                                <select @change="if($event.target.value !== 'personalizado') { motivoRejeicao = $event.target.value }"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                    <option value="personalizado">Personalizado (digite abaixo)</option>
                                    <option value="Conforme o art. 4º, inciso II, alínea &quot;d&quot;, da Portaria nº 1153/2025/SES/GASEC, a taxa de licença sanitária é cumulativa para todas as atividades sujeitas ao controle sanitário constantes no CNPJ, independentemente de serem exercidas ou não.

Verificou-se pagamento de DARE referente a apenas uma atividade. É obrigatória a emissão e o pagamento de DARE para todas as atividades de interesse à saúde constantes no CNPJ.

Todos os boletos do DARE deverão ser enviados em um único arquivo.">Boleto DARE</option>
                                    <option value="Conforme o art. 4º, inciso II, alínea &quot;d&quot;, da Portaria nº 1153/2025/SES/GASEC, a taxa de licença sanitária é cumulativa para todas as atividades sujeitas ao controle sanitário constantes no CNPJ, independentemente de serem exercidas ou não.

Verificou-se comprovante de pagamento referente a apenas uma atividade. É obrigatório o pagamento do DARE para todas as atividades de interesse à saúde constantes no CNPJ.

Os comprovantes de pagamento dos DAREs devem ser juntados em um único arquivo.">Comprovante de Pagamento DARE</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Motivo da Rejeição *</label>
                                <textarea name="motivo_rejeicao" x-model="motivoRejeicao" rows="4" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                          placeholder="Ex: Documento ilegível, formato incorreto, informações incompletas..."></textarea>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-3">
                            <button type="button" @click="modalRejeitar = false; motivoRejeicao = ''" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                                Rejeitar Documento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Histórico do Processo --}}
    <template x-if="modalHistorico">
        <div class="fixed inset-0 z-50 overflow-y-auto" x-show="modalHistorico" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="modalHistorico = false"></div>

                {{-- Modal --}}
                <div class="inline-block w-full max-w-3xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    {{-- Header --}}
                    <div class="px-6 py-4 bg-gradient-to-r from-green-600 to-green-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Histórico do Processo
                        </h3>
                        <button @click="modalHistorico = false" class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Conteúdo --}}
                    <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                        {{-- Buscar eventos do histórico --}}
                        @php
                            try {
                                $eventos = $processo->eventos()->with('usuario')->get();
                            } catch (\Exception $e) {
                                // Tabela ainda não existe - migration não foi executada
                                $eventos = collect();
                            }
                        @endphp

                        {{-- Linha do Tempo --}}
                        <div class="relative">
                            @forelse($eventos as $evento)
                            <div class="flex gap-4 pb-8 {{ $loop->last ? '' : 'border-l-2 border-gray-200' }} ml-4">
                                {{-- Ícone do Evento --}}
                                <div class="absolute left-0 flex items-center justify-center w-8 h-8 rounded-full border-2 border-white
                                    @if($evento->cor === 'blue') bg-blue-100
                                    @elseif($evento->cor === 'purple') bg-purple-100
                                    @elseif($evento->cor === 'green') bg-green-100
                                    @elseif($evento->cor === 'red') bg-red-100
                                    @elseif($evento->cor === 'yellow') bg-yellow-100
                                    @elseif($evento->cor === 'cyan') bg-cyan-100
                                    @elseif($evento->cor === 'indigo') bg-indigo-100
                                    @else bg-gray-100
                                    @endif">
                                    @if($evento->icone === 'plus')
                                    <svg class="w-4 h-4 @if($evento->cor === 'blue') text-blue-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    @elseif($evento->icone === 'upload')
                                    <svg class="w-4 h-4 @if($evento->cor === 'purple') text-purple-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    @elseif($evento->icone === 'document')
                                    <svg class="w-4 h-4 @if($evento->cor === 'green') text-green-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    @elseif($evento->icone === 'trash')
                                    <svg class="w-4 h-4 @if($evento->cor === 'red') text-red-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    @elseif($evento->icone === 'refresh')
                                    <svg class="w-4 h-4 @if($evento->cor === 'yellow') text-yellow-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    @elseif($evento->icone === 'archive')
                                    <svg class="w-4 h-4 @if($evento->cor === 'orange') text-orange-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                    </svg>
                                    @elseif($evento->icone === 'check')
                                    <svg class="w-4 h-4 @if($evento->cor === 'green') text-green-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @elseif($evento->icone === 'pause')
                                    <svg class="w-4 h-4 @if($evento->cor === 'red') text-red-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @elseif($evento->icone === 'play')
                                    <svg class="w-4 h-4 @if($evento->cor === 'green') text-green-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @elseif($evento->icone === 'x')
                                    <svg class="w-4 h-4 @if($evento->cor === 'red') text-red-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    @elseif($evento->icone === 'arrow-right')
                                    <svg class="w-4 h-4 @if($evento->cor === 'cyan') text-cyan-600 @elseif($evento->cor === 'indigo') text-indigo-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                    @endif
                                </div>

                                {{-- Conteúdo do Evento --}}
                                <div class="flex-1 ml-12">
                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-sm font-semibold text-gray-900">{{ $evento->titulo }}</h4>
                                                <p class="text-xs text-gray-600 mt-0.5">{{ $evento->descricao }}</p>
                                                
                                                {{-- Detalhes adicionais baseados no tipo de evento --}}
                                                @if($evento->dados_adicionais)
                                                    @if(in_array($evento->tipo_evento, ['documento_excluido', 'documento_digital_excluido']) && isset($evento->dados_adicionais['nome_arquivo']))
                                                    <p class="text-xs text-red-600 mt-1 flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                        Arquivo: {{ $evento->dados_adicionais['nome_arquivo'] }}
                                                    </p>
                                                    @endif
                                                    
                                                    @if($evento->tipo_evento === 'resposta_aprovada')
                                                    <div class="mt-1.5 p-2 bg-green-50 rounded border border-green-200">
                                                        <p class="text-xs text-green-700 flex items-center gap-1">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            Arquivo aprovado: <strong>{{ $evento->dados_adicionais['nome_arquivo'] ?? 'N/D' }}</strong>
                                                        </p>
                                                        @if(isset($evento->dados_adicionais['usuario_externo']))
                                                        <p class="text-[10px] text-green-600 mt-0.5">Enviado por: {{ $evento->dados_adicionais['usuario_externo'] }}</p>
                                                        @endif
                                                    </div>
                                                    @endif
                                                    
                                                    @if($evento->tipo_evento === 'resposta_rejeitada')
                                                    <div class="mt-1.5 p-2 bg-red-50 rounded border border-red-200">
                                                        <p class="text-xs text-red-700 flex items-center gap-1">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                            Arquivo rejeitado: <strong>{{ $evento->dados_adicionais['nome_arquivo'] ?? 'N/D' }}</strong>
                                                        </p>
                                                        @if(isset($evento->dados_adicionais['motivo_rejeicao']))
                                                        <p class="text-[10px] text-red-600 mt-0.5">Motivo: {{ $evento->dados_adicionais['motivo_rejeicao'] }}</p>
                                                        @endif
                                                    </div>
                                                    @endif
                                                    
                                                    @if($evento->tipo_evento === 'processo_atribuido')
                                                    <div class="mt-1.5 p-2 bg-cyan-50 rounded border border-cyan-200">
                                                        <div class="flex items-center gap-2 text-xs text-cyan-700">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                                            </svg>
                                                            <span>
                                                                @if(isset($evento->dados_adicionais['setor_anterior_nome']) || isset($evento->dados_adicionais['responsavel_anterior']))
                                                                    <strong>De:</strong> 
                                                                    {{ $evento->dados_adicionais['setor_anterior_nome'] ?? 'Sem setor' }}
                                                                    {{ isset($evento->dados_adicionais['responsavel_anterior']) ? ' - ' . $evento->dados_adicionais['responsavel_anterior'] : '' }}
                                                                    →
                                                                @endif
                                                                <strong>Para:</strong> 
                                                                {{ $evento->dados_adicionais['setor_novo_nome'] ?? 'Sem setor' }}
                                                                {{ isset($evento->dados_adicionais['responsavel_novo']) ? ' - ' . $evento->dados_adicionais['responsavel_novo'] : '' }}
                                                            </span>
                                                        </div>
                                                        @if(isset($evento->dados_adicionais['prazo']) && $evento->dados_adicionais['prazo'])
                                                        <div class="mt-1 flex items-center gap-1 text-[10px] text-cyan-600">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                            </svg>
                                                            <strong>Prazo:</strong> {{ \Carbon\Carbon::parse($evento->dados_adicionais['prazo'])->format('d/m/Y') }}
                                                        </div>
                                                        @endif
                                                        @if(isset($evento->dados_adicionais['motivo']) && $evento->dados_adicionais['motivo'])
                                                        <div class="mt-1.5 pt-1.5 border-t border-cyan-200">
                                                            <p class="text-[10px] text-cyan-600"><strong>Motivo:</strong> {{ $evento->dados_adicionais['motivo'] }}</p>
                                                        </div>
                                                        @endif
                                                        @if(isset($evento->dados_adicionais['ciente_em']) && $evento->dados_adicionais['ciente_em'])
                                                        <div class="mt-1.5 pt-1.5 border-t border-cyan-200 flex items-center gap-1.5">
                                                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            <span class="text-[10px] text-green-600">
                                                                <strong>Ciente:</strong> {{ $evento->dados_adicionais['ciente_por_nome'] ?? 'Responsável' }} em {{ \Carbon\Carbon::parse($evento->dados_adicionais['ciente_em'])->format('d/m/Y H:i') }}
                                                            </span>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    @endif
                                                    
                                                    @if(in_array($evento->tipo_evento, ['documento_anexado', 'documento_digital_criado']) && isset($evento->dados_adicionais['nome_arquivo']))
                                                    <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                        {{ $evento->dados_adicionais['nome_arquivo'] }}
                                                    </p>
                                                    @endif
                                                @endif
                                                
                                                <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                        </svg>
                                                        {{ $evento->usuario->nome ?? 'Sistema' }}
                                                    </span>
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                        {{ $evento->created_at->format('d/m/Y H:i') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">Nenhum evento registrado</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <button @click="modalHistorico = false" class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Histórico de Atribuições --}}
    <template x-if="modalHistoricoAtribuicoes">
        <div class="fixed inset-0 z-50 overflow-y-auto" x-show="modalHistoricoAtribuicoes" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="modalHistoricoAtribuicoes = false"></div>

                {{-- Modal --}}
                <div class="inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    {{-- Header --}}
                    <div class="px-6 py-4 bg-gradient-to-r from-cyan-600 to-cyan-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            Histórico de Atribuições
                        </h3>
                        <button @click="modalHistoricoAtribuicoes = false" class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Conteúdo --}}
                    <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                        @php
                            try {
                                $eventosAtribuicao = $processo->eventos()
                                    ->where('tipo_evento', 'processo_atribuido')
                                    ->with('usuario')
                                    ->orderBy('created_at', 'desc')
                                    ->get();
                            } catch (\Exception $e) {
                                $eventosAtribuicao = collect();
                            }
                        @endphp

                        @forelse($eventosAtribuicao as $evento)
                        <div class="mb-4 last:mb-0">
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                {{-- Header do evento --}}
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-cyan-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $evento->titulo }}</p>
                                            <p class="text-xs text-gray-500">
                                                por {{ $evento->usuario->nome ?? 'Sistema' }} em {{ $evento->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Detalhes da atribuição --}}
                                <div class="bg-white rounded-lg p-3 border border-cyan-200">
                                    <div class="grid grid-cols-2 gap-4">
                                        {{-- De --}}
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase mb-1">De</p>
                                            @if(isset($evento->dados_adicionais['setor_anterior_nome']) || isset($evento->dados_adicionais['responsavel_anterior']))
                                                <p class="text-sm text-gray-700">
                                                    {{ $evento->dados_adicionais['setor_anterior_nome'] ?? 'Sem setor' }}
                                                </p>
                                                @if(isset($evento->dados_adicionais['responsavel_anterior']))
                                                <p class="text-xs text-gray-500">{{ $evento->dados_adicionais['responsavel_anterior'] }}</p>
                                                @endif
                                            @else
                                                <p class="text-sm text-gray-400 italic">Não atribuído</p>
                                            @endif
                                        </div>
                                        
                                        {{-- Para --}}
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase mb-1">Para</p>
                                            @if(isset($evento->dados_adicionais['setor_novo_nome']) || isset($evento->dados_adicionais['responsavel_novo']))
                                                <p class="text-sm text-cyan-700 font-medium">
                                                    {{ $evento->dados_adicionais['setor_novo_nome'] ?? 'Sem setor' }}
                                                </p>
                                                @if(isset($evento->dados_adicionais['responsavel_novo']))
                                                <p class="text-xs text-cyan-600">{{ $evento->dados_adicionais['responsavel_novo'] }}</p>
                                                @endif
                                            @else
                                                <p class="text-sm text-gray-400 italic">Atribuição removida</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    {{-- Motivo --}}
                                    @if(isset($evento->dados_adicionais['motivo']) && $evento->dados_adicionais['motivo'])
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <p class="text-xs font-medium text-gray-500 uppercase mb-1">Motivo da Atribuição</p>
                                        <p class="text-sm text-gray-700 bg-cyan-50 rounded p-2 border border-cyan-100">
                                            {{ $evento->dados_adicionais['motivo'] }}
                                        </p>
                                    </div>
                                    @endif
                                    
                                    {{-- Prazo --}}
                                    @if(isset($evento->dados_adicionais['prazo']) && $evento->dados_adicionais['prazo'])
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <p class="text-xs font-medium text-gray-500 uppercase mb-1">Prazo para Resolução</p>
                                        <p class="text-sm text-gray-700 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ \Carbon\Carbon::parse($evento->dados_adicionais['prazo'])->format('d/m/Y') }}
                                        </p>
                                    </div>
                                    @endif
                                    
                                    {{-- Ciência --}}
                                    @if(isset($evento->dados_adicionais['ciente_em']) && $evento->dados_adicionais['ciente_em'])
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <div class="flex items-center gap-2 bg-green-50 rounded-lg p-2 border border-green-200">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div>
                                                <p class="text-xs font-medium text-green-700">Ciente</p>
                                                <p class="text-xs text-green-600">
                                                    {{ $evento->dados_adicionais['ciente_por_nome'] ?? 'Responsável' }} 
                                                    em {{ \Carbon\Carbon::parse($evento->dados_adicionais['ciente_em'])->format('d/m/Y \à\s H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Nenhuma atribuição registrada</p>
                            <p class="text-xs text-gray-400 mt-1">O histórico de atribuições aparecerá aqui quando o processo for tramitado.</p>
                        </div>
                        @endforelse
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <button @click="modalHistoricoAtribuicoes = false" class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Scripts Alpine.js --}}
    <script>
        function processoData() {
            return {
                // Modais
                modalUpload: false,
                modalVisualizador: false,
                modalVisualizadorAnotacoes: false,
                modalEditarNome: false,
                modalDocumentoDigital: false,
                modalPastas: false,
                modalHistorico: false,
                modalHistoricoAtribuicoes: false,
                modalArquivar: false,
                modalParar: false,
                modalOrdemServico: false,
                modalAlertas: false,
                modalRejeitar: false,
                modalExcluirProcesso: false,
                modalExcluirComSenha: false,
                modalAtribuir: false,
                modalRespostas: false,
                modalAssinar: false,
                
                // Modal de Assinatura
                assinarDocumentoId: null,
                assinarDocumentoNome: '',
                assinarDocumentoNumero: '',
                assinarOrdem: '',
                assinarAssinaturas: [],
                assinarSenha: '',
                assinarErro: '',
                assinarCarregando: false,
                
                // Modal de Respostas
                respostasDocumentoId: null,
                respostasDocumentoNome: '',
                respostasDocumentoNumero: '',
                respostasDocumentoPdfUrl: '',
                
                // Exclusão com senha
                exclusaoTipo: '', // 'resposta', 'documento', 'documento_digital'
                exclusaoId: null,
                exclusaoNome: '',
                exclusaoUrl: '',
                senhaExclusao: '',
                exclusaoErro: '',
                exclusaoCarregando: false,
                
                // Atribuir processo
                setorAtribuir: '{{ $processo->setor_atual ?? '' }}',
                responsavelAtribuir: '{{ $processo->responsavel_atual_id ?? '' }}',
                usuariosParaAtribuir: [],
                
                // Rejeição de documento
                documentoRejeitando: null,
                motivoRejeicao: '',
                
                // Dados gerais
                pdfUrl: '',
                pdfUrlAnotacoes: '',
                documentoIdAnotacoes: null,
                documentoPendente: false, // Se o documento é externo e pendente de aprovação
                documentoEditando: null,
                nomeEditando: '',
                selecionarMultiplos: false, // Para seleção múltipla de documentos
                
                // Pastas
                pastas: [],
                pastaAtiva: null, // null = Todos, ou ID da pasta
                pastaEditando: null,
                nomePasta: '',
                descricaoPasta: '',
                corPasta: '#3B82F6',
                
                // Designação
                setores: [],
                usuariosPorSetor: [],
                usuariosDesignados: [],
                descricaoTarefa: '',
                dataLimite: '',
                isCompetenciaEstadual: false,
                
                // Documentos (para contagem) - incluindo documentos digitais e arquivos
                documentos: [
                    @foreach($documentosDigitais as $docDigital)
                        { id: {{ $docDigital->id }}, pasta_id: {{ $docDigital->pasta_id ?? 'null' }}, tipo: 'digital' },
                    @endforeach
                    @foreach($processo->documentos->where('tipo_documento', '!=', 'documento_digital') as $documento)
                        { id: {{ $documento->id }}, pasta_id: {{ $documento->pasta_id ?? 'null' }}, tipo: 'arquivo' },
                    @endforeach
                ],

                // Inicialização
                init() {
                    this.carregarPastas();
                },

                // Função para mostrar notificações
                mostrarNotificacao(mensagem, tipo = 'success') {
                    const container = document.createElement('div');
                    container.className = `fixed top-4 right-4 z-50 max-w-sm w-full bg-white rounded-lg shadow-lg border-l-4 ${tipo === 'success' ? 'border-green-500' : 'border-red-500'} p-4 animate-slide-in`;
                    container.style.animation = 'slideIn 0.3s ease-out';
                    
                    container.innerHTML = `
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ${tipo === 'success' ? 'text-green-500' : 'text-red-500'} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                ${tipo === 'success' 
                                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>'
                                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>'}
                            </svg>
                            <p class="text-sm font-medium ${tipo === 'success' ? 'text-green-800' : 'text-red-800'}">${mensagem}</p>
                        </div>
                    `;
                    
                    document.body.appendChild(container);
                    
                    setTimeout(() => {
                        container.style.animation = 'slideOut 0.3s ease-in';
                        setTimeout(() => container.remove(), 300);
                    }, 3000);
                },

                // Métodos de Pastas
                carregarPastas() {
                    fetch('{{ route('admin.estabelecimentos.processos.pastas.index', [$estabelecimento->id, $processo->id]) }}')
                        .then(response => response.json())
                        .then(data => {
                            this.pastas = data;
                        })
                        .catch(error => console.error('Erro ao carregar pastas:', error));
                },

                salvarPasta() {
                    const url = this.pastaEditando 
                        ? '{{ route('admin.estabelecimentos.processos.pastas.update', [$estabelecimento->id, $processo->id, ':id']) }}'.replace(':id', this.pastaEditando.id)
                        : '{{ route('admin.estabelecimentos.processos.pastas.store', [$estabelecimento->id, $processo->id]) }}';
                    
                    const method = this.pastaEditando ? 'PUT' : 'POST';

                    fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            nome: this.nomePasta,
                            descricao: this.descricaoPasta,
                            cor: this.corPasta
                        })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            this.cancelarEdicao();
                            this.carregarPastas();
                            // Pequeno delay para garantir que as pastas foram carregadas antes de fechar
                            setTimeout(() => {
                                alert(result.message);
                            }, 100);
                        }
                    })
                    .catch(error => console.error('Erro ao salvar pasta:', error));
                },

                editarPasta(pasta) {
                    this.pastaEditando = pasta;
                    this.nomePasta = pasta.nome;
                    this.descricaoPasta = pasta.descricao || '';
                    this.corPasta = pasta.cor;
                },

                cancelarEdicao() {
                    this.pastaEditando = null;
                    this.nomePasta = '';
                    this.descricaoPasta = '';
                    this.corPasta = '#3B82F6';
                },

                excluirPasta(pastaId) {
                    if (!confirm('Tem certeza que deseja excluir esta pasta? Os documentos e arquivos serão movidos para "Todos".')) {
                        return;
                    }

                    fetch('{{ route('admin.estabelecimentos.processos.pastas.destroy', [$estabelecimento->id, $processo->id, ':id']) }}'.replace(':id', pastaId), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            this.carregarPastas();
                            alert(result.message);
                        }
                    })
                    .catch(error => console.error('Erro ao excluir pasta:', error));
                },

                moverParaPasta(itemId, tipo, pastaId, element) {
                    fetch('{{ route('admin.estabelecimentos.processos.pastas.mover', [$estabelecimento->id, $processo->id]) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            tipo: tipo,
                            item_id: itemId,
                            pasta_id: pastaId
                        })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            // Encontrar o elemento pai com x-data
                            const docElement = element.closest('[x-data]');
                            if (docElement && docElement.__x) {
                                // Atualizar a variável pastaDocumento do Alpine.js
                                docElement.__x.$data.pastaDocumento = pastaId;
                            }
                            
                            // Atualizar o array de documentos
                            const docIndex = this.documentos.findIndex(doc => doc.id === itemId && doc.tipo === tipo);
                            if (docIndex !== -1) {
                                this.documentos[docIndex].pasta_id = pastaId;
                            }
                            
                            // Mostrar mensagem de sucesso
                            this.mostrarNotificacao(result.message, 'success');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao mover item:', error);
                        this.mostrarNotificacao('Erro ao mover o item. Tente novamente.', 'error');
                    });
                },

                contarDocumentosPorPasta(pastaId) {
                    return this.documentos.filter(doc => doc.pasta_id === pastaId).length;
                },

                // Métodos para Documentos Digitais
                moverDocumentoDigitalParaPasta(documentoId, pastaId, element) {
                    fetch(`${window.APP_URL}/admin/documentos/${documentoId}/mover-pasta`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ pasta_id: pastaId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Encontrar o elemento pai com x-data
                            const docElement = element.closest('[x-data]');
                            if (docElement && docElement.__x) {
                                // Atualizar a variável pastaDocumento do Alpine.js
                                docElement.__x.$data.pastaDocumento = pastaId;
                            }
                            
                            // Atualizar o array de documentos
                            const docIndex = this.documentos.findIndex(doc => doc.id === documentoId && doc.tipo === 'digital');
                            if (docIndex !== -1) {
                                this.documentos[docIndex].pasta_id = pastaId;
                            }
                            
                            // Mostrar mensagem de sucesso
                            this.mostrarNotificacao(data.message || 'Documento movido com sucesso!', 'success');
                        } else {
                            this.mostrarNotificacao(data.message || 'Erro ao mover documento', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        this.mostrarNotificacao('Erro ao mover documento', 'error');
                    });
                },

                renomearDocumentoDigital(documentoId, novoNome) {
                    fetch(`${window.APP_URL}/admin/documentos/${documentoId}/renomear`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ nome: novoNome })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Erro ao renomear documento');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao renomear documento');
                    });
                },

                // Abre modal de exclusão com senha
                abrirModalExclusao(tipo, id, nome, url) {
                    this.exclusaoTipo = tipo;
                    this.exclusaoId = id;
                    this.exclusaoNome = nome;
                    this.exclusaoUrl = url;
                    this.senhaExclusao = '';
                    this.exclusaoErro = '';
                    this.exclusaoCarregando = false;
                    this.modalExcluirComSenha = true;
                },

                // Executa exclusão com validação de senha
                async executarExclusao() {
                    if (!this.senhaExclusao) {
                        this.exclusaoErro = 'Digite sua senha de assinatura';
                        return;
                    }

                    this.exclusaoCarregando = true;
                    this.exclusaoErro = '';

                    try {
                        // Usa POST com _method=DELETE para compatibilidade com servidores que não aceitam DELETE
                        const response = await fetch(this.exclusaoUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                _method: 'DELETE',
                                senha_assinatura: this.senhaExclusao
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.modalExcluirComSenha = false;
                            window.location.reload();
                        } else {
                            this.exclusaoErro = data.message || 'Erro ao excluir';
                        }
                    } catch (error) {
                        console.error('Erro:', error);
                        this.exclusaoErro = 'Erro ao processar exclusão';
                    } finally {
                        this.exclusaoCarregando = false;
                    }
                },

                excluirDocumentoDigital(documentoId, nomeDocumento) {
                    this.abrirModalExclusao(
                        'documento_digital',
                        documentoId,
                        nomeDocumento || 'Documento Digital',
                        `{{ url('/admin/documentos') }}/${documentoId}`
                    );
                },

                // Abre modal de visualização de documento com respostas
                abrirModalRespostas(documentoId, nomeDocumento, numeroDocumento, pdfUrl) {
                    this.respostasDocumentoId = documentoId;
                    this.respostasDocumentoNome = nomeDocumento;
                    this.respostasDocumentoNumero = numeroDocumento;
                    this.respostasDocumentoPdfUrl = pdfUrl;
                    this.modalRespostas = true;
                },

                // Abre modal de assinatura
                abrirModalAssinar(documentoId, nomeDocumento, numeroDocumento, ordem, assinaturas) {
                    this.assinarDocumentoId = documentoId;
                    this.assinarDocumentoNome = nomeDocumento;
                    this.assinarDocumentoNumero = numeroDocumento;
                    this.assinarOrdem = ordem;
                    this.assinarAssinaturas = assinaturas;
                    this.assinarSenha = '';
                    this.assinarErro = '';
                    this.assinarCarregando = false;
                    this.modalAssinar = true;
                },

                // Processa assinatura via AJAX
                async processarAssinatura() {
                    if (!this.assinarSenha) {
                        this.assinarErro = 'Digite sua senha de assinatura';
                        return;
                    }
                    
                    this.assinarCarregando = true;
                    this.assinarErro = '';
                    
                    try {
                        const response = await fetch(`/admin/assinatura/processar/${this.assinarDocumentoId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                senha_assinatura: this.assinarSenha,
                                acao: 'assinar'
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (response.ok && data.success) {
                            this.modalAssinar = false;
                            // Mostrar notificação de sucesso
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Documento assinado!',
                                    text: data.message || 'Assinatura realizada com sucesso.',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                alert(data.message || 'Documento assinado com sucesso!');
                                window.location.reload();
                            }
                        } else {
                            this.assinarErro = data.message || data.error || 'Erro ao assinar documento';
                        }
                    } catch (error) {
                        console.error('Erro:', error);
                        this.assinarErro = 'Erro de conexão. Tente novamente.';
                    } finally {
                        this.assinarCarregando = false;
                    }
                },

                // Abre o visualizador de PDF com ferramentas de anotação
                async abrirVisualizadorAnotacoes(documentoId, pdfUrl, isPendente = false) {
                    this.documentoIdAnotacoes = documentoId;
                    this.pdfUrlAnotacoes = pdfUrl;
                    this.documentoPendente = isPendente;
                    this.modalVisualizadorAnotacoes = true;
                    
                    // Notificar que o modal PDF foi aberto
                    window.dispatchEvent(new CustomEvent('pdf-modal-aberto'));
                    
                    // Carrega automaticamente o documento na IA
                    await this.carregarDocumentoNaIA();
                },

                // Carrega documento na IA para perguntas
                async carregarDocumentoNaIA() {
                    if (!this.documentoIdAnotacoes) {
                        alert('Nenhum documento selecionado');
                        return;
                    }

                    // Mostra loading
                    const loadingMsg = 'Carregando documento na IA...';
                    console.log(loadingMsg);

                    try {
                        // Chama endpoint para extrair texto do PDF
                        const response = await fetch(`{{ route('admin.assistente-ia.extrair-pdf') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                documento_id: this.documentoIdAnotacoes,
                                estabelecimento_id: {{ $estabelecimento->id }},
                                processo_id: {{ $processo->id }}
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Dispara evento customizado para o componente do chat
                            window.dispatchEvent(new CustomEvent('documento-carregado', {
                                detail: {
                                    documento_id: this.documentoIdAnotacoes,
                                    nome_documento: data.nome_documento,
                                    conteudo: data.conteudo,
                                    total_caracteres: data.total_caracteres,
                                    processo_id: {{ $processo->id }},
                                    estabelecimento_id: {{ $estabelecimento->id }}
                                }
                            }));

                            // NÃO fecha o modal - mantém aberto para visualização
                            // this.modalVisualizadorAnotacoes = false;

                            // Não mostra alert - IA já mostra mensagem no chat
                            // alert('✅ Documento carregado! Agora você pode fazer perguntas sobre ele no chat da IA.');
                        } else {
                            alert('❌ ' + (data.message || 'Erro ao carregar documento'));
                        }
                    } catch (error) {
                        console.error('Erro ao carregar documento:', error);
                        alert('❌ Erro ao carregar documento na IA');
                    }
                },

                // Fecha o modal PDF e dispara evento para fechar assistente de documento
                fecharModalPDF() {
                    this.modalVisualizadorAnotacoes = false;
                    // Limpa as variáveis do documento para forçar recarregamento
                    this.documentoIdAnotacoes = null;
                    this.pdfUrlAnotacoes = '';
                    // Dispara evento para notificar que o modal PDF foi fechado
                    window.dispatchEvent(new CustomEvent('pdf-modal-fechado'));
                },

                // Carrega setores e usuários para designação
                carregarUsuarios() {
                    fetch(`{{ route('admin.estabelecimentos.processos.usuarios.designacao', [$estabelecimento->id, $processo->id]) }}`)
                        .then(response => response.json())
                        .then(data => {
                            this.setores = data.setores || [];
                            this.usuariosPorSetor = data.usuariosPorSetor || [];
                            this.isCompetenciaEstadual = data.isCompetenciaEstadual || false;
                        })
                        .catch(error => {
                            console.error('Erro ao carregar setores e usuários:', error);
                            alert('Erro ao carregar setores e usuários');
                        });
                },
                
                // Carrega usuários para atribuição de processo
                carregarUsuariosAtribuir() {
                    fetch(`{{ route('admin.estabelecimentos.processos.usuarios.designacao', [$estabelecimento->id, $processo->id]) }}`)
                        .then(response => response.json())
                        .then(data => {
                            this.usuariosParaAtribuir = data.usuariosPorSetor || [];
                            this.setores = data.setores || [];
                        })
                        .catch(error => {
                            console.error('Erro ao carregar usuários:', error);
                        });
                }
            }
        }
    </script>

    {{-- Modal Passar Processo (Atribuir Setor/Responsável) --}}
    <div x-show="modalAtribuir" 
         x-cloak
         x-init="$watch('modalAtribuir', value => { if(value) carregarUsuariosAtribuir() })"
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Overlay --}}
            <div x-show="modalAtribuir" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                 @click="modalAtribuir = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel --}}
            <div x-show="modalAtribuir"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <form action="{{ route('admin.estabelecimentos.processos.atribuir', [$estabelecimento->id, $processo->id]) }}" method="POST">
                    @csrf
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-cyan-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Tramitar Processo</h3>
                                <p class="text-sm text-gray-500">Atribua o processo a um setor e/ou responsável</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            {{-- Setor --}}
                            <div>
                                <label for="setor_atual" class="block text-sm font-medium text-gray-700 mb-1">
                                    Setor
                                </label>
                                <select name="setor_atual" id="setor_atual" x-model="setorAtribuir"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 text-sm">
                                    <option value="">Selecione um setor (opcional)</option>
                                    <template x-for="setor in setores" :key="setor.codigo">
                                        <option :value="setor.codigo" x-text="setor.nome"></option>
                                    </template>
                                </select>
                            </div>
                            
                            {{-- Responsável --}}
                            <div>
                                <label for="responsavel_atual_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Responsável
                                </label>
                                <select name="responsavel_atual_id" id="responsavel_atual_id" x-model="responsavelAtribuir"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 text-sm">
                                    <option value="">Selecione um responsável (opcional)</option>
                                    <template x-for="grupo in usuariosParaAtribuir" :key="grupo.setor.codigo">
                                        <template x-if="!setorAtribuir || grupo.setor.codigo === setorAtribuir">
                                            <optgroup :label="grupo.setor.nome">
                                                <template x-for="usuario in grupo.usuarios" :key="usuario.id">
                                                    <option :value="usuario.id" x-text="usuario.nome"></option>
                                                </template>
                                            </optgroup>
                                        </template>
                                    </template>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">
                                    <template x-if="setorAtribuir">
                                        <span>Mostrando usuários do setor selecionado</span>
                                    </template>
                                    <template x-if="!setorAtribuir">
                                        <span>Mostrando todos os usuários</span>
                                    </template>
                                </p>
                            </div>
                            
                            {{-- Info atual --}}
                            @if($processo->setor_atual || $processo->responsavel_atual_id)
                            <div class="p-3 bg-gray-50 rounded-lg text-sm">
                                <p class="text-gray-600">
                                    <strong>Atualmente com:</strong> 
                                    {{ $processo->setor_atual_nome ?? '' }}
                                    {{ $processo->setor_atual && $processo->responsavelAtual ? ' - ' : '' }}
                                    {{ $processo->responsavelAtual->nome ?? '' }}
                                </p>
                            </div>
                            @endif
                            
                            {{-- Motivo/Descrição da Atribuição --}}
                            <div>
                                <label for="motivo_atribuicao" class="block text-sm font-medium text-gray-700 mb-1">
                                    Motivo da Atribuição <span class="text-gray-400 font-normal">(opcional)</span>
                                </label>
                                <textarea name="motivo_atribuicao" id="motivo_atribuicao" rows="3"
                                          placeholder="Descreva o motivo da atribuição para que o responsável saiba o que precisa ser feito..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 text-sm resize-none"></textarea>
                                <p class="text-xs text-gray-500 mt-1">Esta informação ficará visível no histórico de atribuições do processo.</p>
                            </div>
                            
                            {{-- Prazo para Resolução - Apenas para Gestores e Admin --}}
                            @if(in_array(auth('interno')->user()->nivel_acesso->value, ['administrador', 'gestor_estadual', 'gestor_municipal']))
                            <div>
                                <label for="prazo_atribuicao" class="block text-sm font-medium text-gray-700 mb-1">
                                    Prazo para Resolução <span class="text-gray-400 font-normal">(opcional)</span>
                                </label>
                                <input type="date" name="prazo_atribuicao" id="prazo_atribuicao"
                                       min="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 text-sm">
                                <p class="text-xs text-gray-500 mt-1">Defina uma data limite para o responsável resolver a demanda.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-cyan-600 text-base font-medium text-white hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 sm:w-auto sm:text-sm">
                            Atribuir
                        </button>
                        <button type="button" @click="modalAtribuir = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Criar Ordem de Serviço --}}
    <div x-show="modalOrdemServico" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Overlay --}}
            <div x-show="modalOrdemServico" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                 @click="modalOrdemServico = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel --}}
            <div x-show="modalOrdemServico"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                
                <form action="{{ route('admin.ordens-servico.store') }}" method="POST">
                    @csrf
                    
                    {{-- Campos ocultos --}}
                    <input type="hidden" name="tipo_vinculacao" value="com_estabelecimento">
                    <input type="hidden" name="estabelecimento_id" value="{{ $estabelecimento->id }}">
                    <input type="hidden" name="processo_id" value="{{ $processo->id }}">
                    <input type="hidden" name="municipio_id" value="{{ $processo->estabelecimento->municipio_id }}">
                    
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Nova Ordem de Serviço
                            </h3>
                            <button type="button" 
                                    @click="modalOrdemServico = false" 
                                    class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4 space-y-4">
                        {{-- Informações do Processo (Read-only) --}}
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-purple-900 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Vinculado ao Processo
                            </h4>
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div>
                                    <span class="text-gray-600">Estabelecimento:</span>
                                    <p class="font-medium text-gray-900">{{ $estabelecimento->nome_fantasia }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Processo:</span>
                                    <p class="font-medium text-gray-900">{{ $processo->numero_processo }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Período de Execução --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Data Início
                                </label>
                                <input type="date" 
                                       name="data_inicio" 
                                       value="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Data Fim
                                </label>
                                <input type="date" 
                                       name="data_fim" 
                                       value="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm">
                            </div>
                        </div>

                        {{-- Tipos de Ação --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipos de Ação <span class="text-red-500">*</span>
                            </label>
                            <select name="tipos_acao_ids[]" 
                                    id="tipos-acao-select"
                                    class="w-full" 
                                    multiple="multiple" 
                                    required>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Digite para pesquisar tipos de ação</p>
                        </div>

                        {{-- Técnicos --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Técnicos Responsáveis <span class="text-red-500">*</span>
                            </label>
                            <select name="tecnicos_ids[]" 
                                    id="tecnicos-select"
                                    class="w-full" 
                                    multiple="multiple" 
                                    required>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Digite para pesquisar técnicos</p>
                        </div>

                        {{-- Observações --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Observações
                            </label>
                            <textarea name="observacoes" 
                                      rows="3"
                                      placeholder="Observações sobre a ordem de serviço..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm resize-none"></textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3">
                        <button type="button" 
                                @click="modalOrdemServico = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Criar Ordem de Serviço
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Alertas --}}
    <div x-show="modalAlertas" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Overlay --}}
            <div x-show="modalAlertas" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" 
                 @click="modalAlertas = false"></div>

            {{-- Modal --}}
            <div x-show="modalAlertas"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                
                {{-- Header --}}
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            Alertas do Processo
                        </h3>
                        <button type="button" 
                                @click="modalAlertas = false" 
                                class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Body --}}
                <div class="px-6 py-6">
                    {{-- Form Criar Alerta --}}
                    <form method="POST" action="{{ route('admin.estabelecimentos.processos.alertas.criar', [$estabelecimento->id, $processo->id]) }}" class="mb-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
                        @csrf
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Criar Novo Alerta</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Descrição *</label>
                                <input type="text" 
                                       name="descricao" 
                                       required
                                       maxlength="500"
                                       placeholder="Ex: Verificar documentação pendente"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Data do Alerta *</label>
                                <input type="date" 
                                       name="data_alerta" 
                                       required
                                       min="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            </div>
                        </div>
                        
                        <div class="mt-3 flex justify-end">
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Adicionar Alerta
                            </button>
                        </div>
                    </form>

                    {{-- Lista de Alertas --}}
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @forelse($alertas as $alerta)
                        <div class="border rounded-lg p-4 {{ $alerta->isVencido() ? 'bg-red-50 border-red-200' : ($alerta->isProximo() ? 'bg-orange-50 border-orange-200' : 'bg-white border-gray-200') }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        @if($alerta->status === 'pendente')
                                            @if($alerta->isVencido())
                                                <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full">Vencido</span>
                                            @elseif($alerta->isProximo())
                                                <span class="px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full">Próximo</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">Pendente</span>
                                            @endif
                                        @elseif($alerta->status === 'visualizado')
                                            <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">Visualizado</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full">Concluído</span>
                                        @endif
                                        
                                        <span class="text-xs text-gray-500">
                                            <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $alerta->data_alerta->format('d/m/Y') }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-900 mb-1">{{ $alerta->descricao }}</p>
                                    
                                    <p class="text-xs text-gray-500">
                                        Criado por {{ $alerta->usuarioCriador->nome }} • {{ $alerta->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                
                                <div class="flex items-center gap-1">
                                    @if($alerta->status === 'pendente')
                                        <form method="POST" action="{{ route('admin.estabelecimentos.processos.alertas.visualizar', [$estabelecimento->id, $processo->id, $alerta->id]) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    title="Marcar como visualizado"
                                                    class="p-1.5 text-yellow-600 hover:bg-yellow-100 rounded transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($alerta->status !== 'concluido')
                                        <form method="POST" action="{{ route('admin.estabelecimentos.processos.alertas.concluir', [$estabelecimento->id, $processo->id, $alerta->id]) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    title="Marcar como concluído"
                                                    class="p-1.5 text-green-600 hover:bg-green-100 rounded transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <form method="POST" action="{{ route('admin.estabelecimentos.processos.alertas.excluir', [$estabelecimento->id, $processo->id, $alerta->id]) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este alerta?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                title="Excluir alerta"
                                                class="p-1.5 text-red-600 hover:bg-red-100 rounded transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <p class="text-sm">Nenhum alerta cadastrado</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-4 flex justify-end">
                    <button type="button" 
                            @click="modalAlertas = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Select2 CSS --}}
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Container do Select2 */
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        min-height: 42px;
        padding: 4px;
        background-color: #ffffff;
        transition: all 0.2s ease;
    }
    
    /* Estado de foco - borda roxa com sombra suave */
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #9333ea;
        box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.1);
        background-color: #ffffff;
        outline: none;
    }
    
    /* Tags selecionadas - roxo com texto branco */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #9333ea !important;
        border: none !important;
        color: #ffffff !important;
        padding: 6px 10px !important;
        border-radius: 0.375rem !important;
        font-weight: 500 !important;
        text-decoration: none !important; /* Remove qualquer linha cortando */
        line-height: 1.5 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        margin: 2px !important;
    }
    
    /* Garante que o texto dentro do chip não tenha decoração */
    .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
        text-decoration: none !important;
        color: #ffffff !important;
        font-size: 0.875rem !important;
    }
    
    /* Botão de remover tag */
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #ffffff !important;
        background-color: transparent !important;
        border: none !important;
        font-size: 1.25rem !important;
        font-weight: bold !important;
        line-height: 1 !important;
        padding: 0 !important;
        margin: 0 !important;
        margin-right: 4px !important;
        text-decoration: none !important; /* Remove qualquer linha */
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 16px !important;
        height: 16px !important;
        border-radius: 50% !important;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fca5a5 !important;
        background-color: rgba(255, 255, 255, 0.2) !important;
        text-decoration: none !important;
        transform: scale(1.1);
    }
    
    /* Dropdown */
    .select2-dropdown {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    /* Opções no dropdown - estado normal */
    .select2-container--default .select2-results__option {
        padding: 8px 12px;
        transition: all 0.15s ease;
    }
    
    /* Opções destacadas (hover/foco) - CORREÇÃO DE ACESSIBILIDADE */
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #f3e8ff !important; /* Roxo muito claro */
        color: #581c87 !important; /* Roxo escuro para alto contraste */
        font-weight: 500;
    }
    
    /* Opções já selecionadas */
    .select2-container--default .select2-results__option[aria-selected="true"] {
        background-color: #ede9fe;
        color: #6b21a8;
    }
    
    /* Campo de busca dentro do select */
    .select2-container--default .select2-search--inline .select2-search__field {
        color: #1f2937;
        font-size: 0.875rem;
    }
    
    .select2-container--default .select2-search--inline .select2-search__field::placeholder {
        color: #9ca3af;
    }
    
    /* Placeholder quando vazio */
    .select2-container--default .select2-selection--multiple .select2-selection__placeholder {
        color: #9ca3af;
    }
    
    /* Mensagem "Nenhum resultado" */
    .select2-container--default .select2-results__option--no-results {
        color: #6b7280;
        font-style: italic;
    }
</style>
@endpush

{{-- Select2 JS --}}
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa Select2 para Tipos de Ação
    $('#tipos-acao-select').select2({
        ajax: {
            url: '{{ route("admin.ordens-servico.api.search-tipos-acao") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page || 1
                };
            },
            processResults: function (data) {
                return {
                    results: data.results.map(function(item) {
                        return {
                            id: item.id,
                            text: item.text,
                            codigo: item.codigo
                        };
                    }),
                    pagination: data.pagination
                };
            },
            cache: true
        },
        placeholder: 'Digite para pesquisar tipos de ação...',
        minimumInputLength: 0,
        allowClear: true,
        width: '100%',
        language: {
            inputTooShort: function() {
                return 'Digite para pesquisar...';
            },
            searching: function() {
                return 'Buscando...';
            },
            noResults: function() {
                return 'Nenhum resultado encontrado';
            },
            loadingMore: function() {
                return 'Carregando mais resultados...';
            }
        },
        templateResult: function(item) {
            if (item.loading) return item.text;
            
            var $result = $('<div class="py-2">' +
                '<div class="font-medium text-gray-900">' + item.text + '</div>' +
                (item.codigo ? '<div class="text-xs text-gray-500">Código: ' + item.codigo + '</div>' : '') +
                '</div>');
            return $result;
        },
        templateSelection: function(item) {
            return item.text;
        }
    });

    // Inicializa Select2 para Técnicos
    $('#tecnicos-select').select2({
        ajax: {
            url: '{{ route("admin.ordens-servico.api.search-tecnicos") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page || 1
                };
            },
            processResults: function (data) {
                return {
                    results: data.results.map(function(item) {
                        return {
                            id: item.id,
                            text: item.text,
                            email: item.email,
                            nivel: item.nivel
                        };
                    }),
                    pagination: data.pagination
                };
            },
            cache: true
        },
        placeholder: 'Digite para pesquisar técnicos...',
        minimumInputLength: 0,
        allowClear: true,
        width: '100%',
        language: {
            inputTooShort: function() {
                return 'Digite para pesquisar...';
            },
            searching: function() {
                return 'Buscando...';
            },
            noResults: function() {
                return 'Nenhum resultado encontrado';
            },
            loadingMore: function() {
                return 'Carregando mais resultados...';
            }
        },
        templateResult: function(item) {
            if (item.loading) return item.text;
            
            var $result = $('<div class="py-2">' +
                '<div class="font-medium text-gray-900">' + item.text + '</div>' +
                (item.email ? '<div class="text-xs text-gray-500">' + item.email + '</div>' : '') +
                '</div>');
            return $result;
        },
        templateSelection: function(item) {
            return item.text;
        }
    });

    // Carrega dados iniciais quando o modal é aberto
    const modalOrdemServico = document.querySelector('[x-show="modalOrdemServico"]');
    if (modalOrdemServico) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'style') {
                    const isVisible = !modalOrdemServico.style.display || modalOrdemServico.style.display !== 'none';
                    if (isVisible) {
                        // Trigger para carregar dados iniciais
                        $('#tipos-acao-select').select2('open');
                        $('#tipos-acao-select').select2('close');
                        $('#tecnicos-select').select2('open');
                        $('#tecnicos-select').select2('close');
                    }
                }
            });
        });
        observer.observe(modalOrdemServico, { attributes: true });
    }
});
</script>
@endpush

@endsection
