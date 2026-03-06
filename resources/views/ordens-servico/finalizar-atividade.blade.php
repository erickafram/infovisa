@extends('layouts.admin')

@section('title', 'Finalizar Atividade - OS #' . $ordemServico->numero)

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header --}}
    <div class="bg-white border-b border-gray-200">
        <div class="container-fluid px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.ordens-servico.show', $ordemServico) }}" 
                       class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">Finalizar Atividade</h1>
                        <p class="text-xs text-gray-500">OS #{{ $ordemServico->numero }} • {{ $atividade['nome_atividade'] ?? 'Atividade' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    {!! $ordemServico->status_badge !!}
                    {!! $ordemServico->competencia_badge !!}
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-6">
        <div class="max-w-8xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- ========================================
                     COLUNA ESQUERDA - Formulário (2/3)
                ======================================== --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Card: Informações da Atividade --}}
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-indigo-50 border-b border-indigo-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-base font-semibold text-gray-900">{{ $atividade['nome_atividade'] ?? 'Atividade' }}</h2>
                                    <div class="flex items-center gap-3 mt-0.5">
                                        @if(!empty($atividade['estabelecimento_id']))
                                            @php $estabAtiv = $estabelecimentosAtividade->first(); @endphp
                                            @if($estabAtiv)
                                            <span class="text-xs text-blue-600 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                {{ $estabAtiv->nome_fantasia ?? $estabAtiv->nome_razao_social }}
                                            </span>
                                            @endif
                                        @else
                                            <span class="text-xs text-gray-500">{{ $estabelecimentosAtividade->count() }} estabelecimentos</span>
                                        @endif
                                        <span class="text-xs text-gray-400">•</span>
                                        <span class="text-xs text-gray-500">{{ $tecnicos->count() }} {{ $tecnicos->count() === 1 ? 'técnico' : 'técnicos' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4">
                            <div class="flex flex-wrap gap-2">
                                @foreach($tecnicos as $tecnico)
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs {{ $tecnico->id == ($atividade['responsavel_id'] ?? null) ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-gray-100 text-gray-700' }}">
                                        <div class="w-5 h-5 rounded-full {{ $tecnico->id == ($atividade['responsavel_id'] ?? null) ? 'bg-indigo-600' : 'bg-gray-500' }} flex items-center justify-center">
                                            <span class="text-white font-bold text-[10px]">{{ strtoupper(substr($tecnico->nome, 0, 1)) }}</span>
                                        </div>
                                        <span class="font-medium">{{ $tecnico->nome }}</span>
                                        @if($tecnico->id == ($atividade['responsavel_id'] ?? null))
                                            <span class="text-[10px] bg-indigo-200 px-1 rounded">Responsável</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Card: Execução --}}
                    <form id="formFinalizarAtividade" method="POST" action="{{ route('admin.ordens-servico.finalizar-atividade', $ordemServico) }}" class="flex flex-col gap-6">
                        @csrf
                        <input type="hidden" name="atividade_index" value="{{ $atividadeIndex }}">
                        <input type="hidden" name="_from_page" value="1">

                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden order-2">
                            <div class="px-6 py-4 border-b border-gray-100">
                                <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Execução da Atividade
                                </h3>
                            </div>
                            <div class="px-6 py-5 space-y-5">

                                @if(!$isMultiEstabelecimento)
                                {{-- ========== ESTABELECIMENTO ÚNICO ========== --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">
                                        Status da execução <span class="text-red-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <label class="relative flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-green-400 hover:bg-green-50/50 transition-all group has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                                            <input type="radio" name="status_execucao" value="concluido" required class="w-4 h-4 text-green-600 focus:ring-green-500">
                                            <div>
                                                <span class="text-sm font-semibold text-gray-900 group-hover:text-green-800">Concluído</span>
                                                <p class="text-[11px] text-gray-500">com sucesso</p>
                                            </div>
                                        </label>
                                        <label class="relative flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-yellow-400 hover:bg-yellow-50/50 transition-all group has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-50">
                                            <input type="radio" name="status_execucao" value="parcial" required class="w-4 h-4 text-yellow-600 focus:ring-yellow-500">
                                            <div>
                                                <span class="text-sm font-semibold text-gray-900 group-hover:text-yellow-800">Parcial</span>
                                                <p class="text-[11px] text-gray-500">concluído parcialmente</p>
                                            </div>
                                        </label>
                                        <label class="relative flex items-center gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-red-400 hover:bg-red-50/50 transition-all group has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                                            <input type="radio" name="status_execucao" value="nao_concluido" required class="w-4 h-4 text-red-600 focus:ring-red-500">
                                            <div>
                                                <span class="text-sm font-semibold text-gray-900 group-hover:text-red-800">Não concluído</span>
                                                <p class="text-[11px] text-gray-500">não foi possível</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                {{-- Observações --}}
                                <div>
                                    <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-2">
                                        Observações <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <button type="button" onclick="aplicarPreset('Atividade concluida conforme previsto.')"
                                                class="px-3 py-1.5 text-xs text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition border border-gray-200">
                                            Concluída conforme previsto
                                        </button>
                                        <button type="button" onclick="aplicarPreset('Concluida com orientacoes prestadas ao responsavel.')"
                                                class="px-3 py-1.5 text-xs text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition border border-gray-200">
                                            Concluída com orientações
                                        </button>
                                        <button type="button" onclick="aplicarPreset('Atividade parcialmente executada. Pendencias registradas.')"
                                                class="px-3 py-1.5 text-xs text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition border border-gray-200">
                                            Parcial com pendências
                                        </button>
                                        <button type="button" onclick="aplicarPreset('Nao foi possivel concluir. Reagendar necessario.')"
                                                class="px-3 py-1.5 text-xs text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition border border-gray-200">
                                            Não foi possível concluir
                                        </button>
                                    </div>
                                    <textarea 
                                        id="observacoes" 
                                        name="observacoes" 
                                        rows="4" 
                                        required
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none transition-all text-sm"
                                        placeholder="Descreva como foi a execução desta atividade...">{{ old('observacoes') }}</textarea>
                                    <p class="mt-1.5 text-xs text-gray-400">Mínimo de 10 caracteres</p>
                                </div>

                                @else
                                {{-- ========== MÚLTIPLOS ESTABELECIMENTOS ========== --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Execução por estabelecimento <span class="text-red-500">*</span>
                                    </label>
                                    <p class="text-xs text-gray-500 mb-4">
                                        Marque em quais estabelecimentos esta atividade foi executada. Para os não executados, informe justificativa.
                                    </p>
                                    <div class="space-y-3">
                                        @foreach($estabelecimentosAtividade as $estIdx => $estab)
                                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50">
                                            <div class="mb-3">
                                                <p class="text-sm font-semibold text-gray-900">{{ $estab->nome_fantasia ?? $estab->nome_razao_social ?? 'Estabelecimento' }}</p>
                                                @if($estab->cnpj_formatado ?? $estab->cnpj ?? $estab->cpf_cnpj)
                                                <p class="text-xs text-gray-500">{{ $estab->cnpj_formatado ?? $estab->cnpj ?? $estab->cpf_cnpj }}</p>
                                                @endif
                                            </div>
                                            <div class="grid grid-cols-2 gap-3 mb-3">
                                                <label class="flex items-center gap-2 p-3 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-400 has-[:checked]:border-green-500 has-[:checked]:bg-green-50 transition-all">
                                                    <input type="radio" name="execucao_estabelecimentos[{{ $estIdx }}][executada]" value="1" 
                                                           class="w-4 h-4 text-green-600 execucao-radio" data-est-idx="{{ $estIdx }}" onchange="toggleJustificativa({{ $estIdx }}, false)">
                                                    <span class="text-sm font-medium text-green-700">Executada</span>
                                                </label>
                                                <label class="flex items-center gap-2 p-3 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:border-red-400 has-[:checked]:border-red-500 has-[:checked]:bg-red-50 transition-all">
                                                    <input type="radio" name="execucao_estabelecimentos[{{ $estIdx }}][executada]" value="0"
                                                           class="w-4 h-4 text-red-600 execucao-radio" data-est-idx="{{ $estIdx }}" onchange="toggleJustificativa({{ $estIdx }}, true)">
                                                    <span class="text-sm font-medium text-red-700">Não executada</span>
                                                </label>
                                            </div>
                                            <input type="hidden" name="execucao_estabelecimentos[{{ $estIdx }}][estabelecimento_id]" value="{{ $estab->id }}">
                                            <div id="justificativa-wrap-{{ $estIdx }}" class="hidden">
                                                <textarea name="execucao_estabelecimentos[{{ $estIdx }}][justificativa]" rows="2"
                                                          class="w-full px-3 py-2 text-sm border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 resize-none"
                                                          placeholder="Justifique por que não foi executada neste estabelecimento..."></textarea>
                                                <p class="text-xs text-red-600 mt-1">Justificativa obrigatória (mínimo 10 caracteres).</p>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Card: Documentos da OS --}}
                        @if($processosVinculadosOs->isNotEmpty())
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden order-1">
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Documentos da OS
                                    @if($documentosOs->count() > 0)
                                    <span class="px-2 py-0.5 text-[11px] font-semibold bg-green-100 text-green-700 rounded-full">{{ $documentosOs->count() }}</span>
                                    @endif
                                </h3>
                                          <a href="{{ route('admin.documentos.create') }}?{{ $processosVinculadosOs->count() > 1 ? 'processos_ids=' . $processosVinculadosOs->implode(',') : 'processo_id=' . $processosVinculadosOs->first() }}&os_id={{ $ordemServico->id }}&atividade_index={{ $atividadeIndex }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Criar Documento
                                </a>
                            </div>
                            <div class="px-6 py-5">
                                @if($documentosOs->count() > 0)
                                <div class="space-y-2 mb-4">
                                    @foreach($documentosOs as $docOs)
                                    @php
                                        $statusDocOs = $docOs->status;
                                        $assinaturasObrigatoriasDocOs = $docOs->assinaturas->where('obrigatoria', true)->values();
                                        $assinantesConcluidosDocOs = $assinaturasObrigatoriasDocOs->where('status', 'assinado');
                                        $assinantesPendentesDocOs = $assinaturasObrigatoriasDocOs->where('status', '!=', 'assinado');
                                        $docOsPossuiAssinaturaRealizada = $docOs->assinaturas->contains(fn($assinatura) => $assinatura->status === 'assinado');
                                        $docOsAssinaturasTotais = $assinaturasObrigatoriasDocOs->count();
                                        $docOsAssinaturasConcluidas = $assinantesConcluidosDocOs->count();
                                        $docOsAssinaturasCompletas = $docOs->todasAssinaturasCompletas();
                                        $docOsPodeEditar = $statusDocOs === 'rascunho'
                                            || ($statusDocOs === 'aguardando_assinatura' && !$docOsPossuiAssinaturaRealizada);
                                        $statusDocOsBadge = match($statusDocOs) {
                                            'rascunho' => '<span class="px-2 py-0.5 text-[10px] font-semibold bg-gray-100 text-gray-600 rounded">Rascunho</span>',
                                            'aguardando_assinatura' => '<span class="px-2 py-0.5 text-[10px] font-semibold bg-yellow-100 text-yellow-700 rounded">Aguardando assinatura</span>',
                                            'assinado' => '<span class="px-2 py-0.5 text-[10px] font-semibold bg-green-100 text-green-700 rounded">Assinado</span>',
                                            'cancelado' => '<span class="px-2 py-0.5 text-[10px] font-semibold bg-red-100 text-red-700 rounded">Cancelado</span>',
                                            default => '<span class="px-2 py-0.5 text-[10px] font-semibold bg-gray-100 text-gray-600 rounded">' . ucfirst($statusDocOs) . '</span>'
                                        };
                                    @endphp
                                    <a href="{{ route('admin.documentos.show', $docOs->id) }}" target="_blank"
                                       class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-indigo-50 hover:border-indigo-200 transition-all group">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="flex-shrink-0 w-9 h-9 rounded-lg {{ $statusDocOs === 'assinado' ? 'bg-green-100' : ($statusDocOs === 'aguardando_assinatura' ? 'bg-yellow-100' : 'bg-gray-100') }} flex items-center justify-center">
                                                <svg class="w-4 h-4 {{ $statusDocOs === 'assinado' ? 'text-green-600' : ($statusDocOs === 'aguardando_assinatura' ? 'text-yellow-600' : 'text-gray-500') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-gray-900 group-hover:text-indigo-700 truncate">
                                                    {{ $docOs->nome ?? $docOs->tipoDocumento->nome ?? 'Documento' }}
                                                    <span class="text-xs text-gray-400 font-normal ml-1">#{{ $docOs->numero_documento }}</span>
                                                </p>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-xs text-gray-500">{{ $docOs->created_at->format('d/m/Y H:i') }}</span>
                                                    @if($docOs->usuarioCriador)
                                                    <span class="text-xs text-gray-400">por {{ $docOs->usuarioCriador->nome }}</span>
                                                    @endif
                                                </div>
                                                <div class="mt-1">
                                                    @if($docOsAssinaturasCompletas && $statusDocOs === 'assinado')
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-green-700 bg-green-50 border border-green-200 rounded px-2 py-0.5">
                                                        Assinaturas concluídas: {{ $docOsAssinaturasConcluidas }}/{{ $docOsAssinaturasTotais }}
                                                    </span>
                                                    @elseif($docOsPodeEditar)
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-0.5">
                                                        Pendente de assinatura: {{ $docOsAssinaturasConcluidas }}/{{ $docOsAssinaturasTotais }} concluídas
                                                    </span>
                                                    @else
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-0.5">
                                                        Aguardando assinaturas finais: {{ $docOsAssinaturasConcluidas }}/{{ $docOsAssinaturasTotais }}
                                                    </span>
                                                    @endif
                                                </div>
                                                @if($assinaturasObrigatoriasDocOs->isNotEmpty())
                                                <div class="mt-2 space-y-1">
                                                    @if($assinantesConcluidosDocOs->isNotEmpty())
                                                    <p class="text-[11px] text-green-700 leading-relaxed">
                                                        <span class="font-semibold">Assinaram:</span>
                                                        {{ $assinantesConcluidosDocOs->map(fn($assinatura) => $assinatura->usuarioInterno?->nome ?? 'Usuário removido')->implode(', ') }}
                                                    </p>
                                                    @endif
                                                    @if($assinantesPendentesDocOs->isNotEmpty())
                                                    <p class="text-[11px] text-amber-700 leading-relaxed">
                                                        <span class="font-semibold">Faltam assinar:</span>
                                                        {{ $assinantesPendentesDocOs->map(fn($assinatura) => $assinatura->usuarioInterno?->nome ?? 'Usuário removido')->implode(', ') }}
                                                    </p>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                                            {!! $statusDocOsBadge !!}
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </div>
                                    </a>
                                    @endforeach
                                </div>

                                @if($documentosOsAssinadosCompletos->isNotEmpty())
                                <div class="p-3 bg-green-50 border border-green-200 rounded-lg flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-sm font-medium text-green-800">
                                        {{ $documentosOsAssinadosCompletos->count() }} {{ $documentosOsAssinadosCompletos->count() === 1 ? 'documento já está totalmente assinado' : 'documentos já estão totalmente assinados' }}.
                                    </p>
                                </div>
                                @endif

                                @if($documentosOsPendentesAssinatura->isNotEmpty())
                                <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l6.518 11.591c.75 1.334-.213 2.99-1.742 2.99H3.48c-1.53 0-2.492-1.656-1.743-2.99L8.257 3.1zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-6a1 1 0 00-1 1v3a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-sm font-medium text-amber-800">
                                        {{ $documentosOsPendentesAssinatura->count() }} {{ $documentosOsPendentesAssinatura->count() === 1 ? 'documento ainda está pendente de assinatura' : 'documentos ainda estão pendentes de assinatura' }}. A atividade só será finalizada quando todos os assinantes concluírem.
                                    </p>
                                </div>
                                @endif
                                @else
                                {{-- Sem documentos --}}
                                <div class="text-center py-6">
                                    <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900 mb-1">Nenhum documento vinculado</p>
                                    <p class="text-xs text-gray-500 mb-4 max-w-sm mx-auto">
                                        Nenhum documento foi criado para esta OS. Crie um documento ou confirme abaixo que não há documentos a serem criados.
                                    </p>
                                                <a href="{{ route('admin.documentos.create') }}?{{ $processosVinculadosOs->count() > 1 ? 'processos_ids=' . $processosVinculadosOs->implode(',') : 'processo_id=' . $processosVinculadosOs->first() }}&os_id={{ $ordemServico->id }}&atividade_index={{ $atividadeIndex }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-indigo-700 bg-indigo-50 border-2 border-dashed border-indigo-300 rounded-xl hover:bg-indigo-100 transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Criar Documento Digital
                                    </a>
                                </div>

                                <div class="mt-4 border-t border-gray-100 pt-4">
                                    <label class="flex items-start gap-3 p-3 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-100/60 transition-colors" id="labelSemDocumentos">
                                        <input type="checkbox" name="confirmou_sem_documentos" value="1" id="checkSemDocumentos"
                                               class="mt-0.5 h-4 w-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                                        <span class="text-sm text-gray-700 leading-relaxed">
                                            <span class="font-semibold">Confirmo que não existem documentos a serem criados</span> para esta OS.
                                        </span>
                                    </label>
                                    <p id="erroSemDocumentos" class="hidden text-xs text-red-600 font-medium mt-2 ml-1">
                                        Crie um documento ou confirme que não existem documentos antes de finalizar.
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Botões de ação --}}
                        <div class="flex items-center justify-between gap-4 order-3">
                            <a href="{{ route('admin.ordens-servico.show', $ordemServico) }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Voltar para OS
                            </a>
                            <button type="submit" id="btnFinalizar"
                                    class="inline-flex items-center gap-2 px-8 py-2.5 text-sm font-semibold text-white bg-green-600 rounded-xl hover:bg-green-700 shadow-sm hover:shadow transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Finalizar Atividade
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ========================================
                     COLUNA DIREITA - Resumo (1/3)
                ======================================== --}}
                <div class="lg:col-span-1 space-y-5">
                    
                    {{-- Card: Resumo da OS --}}
                    <div class="bg-white rounded-xl border border-gray-200 sticky top-6">
                        <div class="px-5 py-4 border-b border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-900">Resumo da OS</h3>
                        </div>
                        <div class="px-5 py-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Número</span>
                                <span class="text-xs font-semibold text-gray-900">#{{ $ordemServico->numero }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Status</span>
                                {!! $ordemServico->status_badge !!}
                            </div>
                            @if($ordemServico->data_inicio || $ordemServico->data_fim)
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Período</span>
                                <span class="text-xs text-gray-700">
                                    {{ $ordemServico->data_inicio?->format('d/m/Y') ?? '?' }} - {{ $ordemServico->data_fim?->format('d/m/Y') ?? '?' }}
                                </span>
                            </div>
                            @endif
                            
                            {{-- Progresso das atividades --}}
                            @php
                                $totalAtividades = count($ordemServico->atividades_tecnicos ?? []);
                                $finalizadas = collect($ordemServico->atividades_tecnicos ?? [])
                                    ->filter(fn($a) => ($a['status'] ?? 'pendente') === 'finalizada')->count();
                                $percentual = $totalAtividades > 0 ? round(($finalizadas / $totalAtividades) * 100) : 0;
                            @endphp
                            <div class="pt-2 border-t border-gray-100">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs text-gray-500">Atividades</span>
                                    <span class="text-xs font-semibold {{ $finalizadas === $totalAtividades ? 'text-green-600' : 'text-gray-700' }}">
                                        {{ $finalizadas }}/{{ $totalAtividades }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ $percentual }}%"></div>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-1">{{ $percentual }}% concluído</p>
                            </div>
                        </div>

                        {{-- Processos vinculados --}}
                        @if($processosVinculadosOs->isNotEmpty())
                        <div class="px-5 py-4 border-t border-gray-100">
                            <p class="text-xs font-semibold text-gray-700 mb-2">Processos vinculados</p>
                            <div class="space-y-1.5">
                                @foreach($processosInfo as $procInfo)
                                <a href="{{ route('admin.estabelecimentos.processos.show', [$procInfo->estabelecimento_id, $procInfo->id]) }}"
                                   target="_blank"
                                   class="flex items-center gap-2 p-2 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                                    <svg class="w-3.5 h-3.5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    <span class="text-xs font-medium text-blue-700 truncate">{{ $procInfo->numero_processo ?? 'Processo #' . $procInfo->id }}</span>
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Aviso --}}
                        <div class="px-5 py-4 border-t border-gray-100">
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-xs text-blue-800 leading-relaxed">
                                    <strong>Dica:</strong> Ao finalizar, a atividade será marcada como concluída. 
                                    A OS será automaticamente encerrada quando todas as atividades forem concluídas.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@if(isset($pesquisaInterna) && $pesquisaInterna && $pesquisaInterna->perguntas->count() > 0)
<div id="modalPesquisaInterna" class="hidden fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center justify-center w-10 h-10 bg-indigo-50 rounded-full">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $pesquisaInterna->titulo }}</h3>
                    @if($pesquisaInterna->descricao)
                        <p class="text-sm text-gray-500">{{ $pesquisaInterna->descricao }}</p>
                    @endif
                </div>
            </div>
        </div>

        <form id="formPesquisaInterna" class="px-6 py-4 space-y-5">
            <input type="hidden" name="pesquisa_id" value="{{ $pesquisaInterna->id }}">
            <input type="hidden" name="ordem_servico_id" value="{{ $ordemServico->id }}">

            @foreach($pesquisaInterna->perguntas as $pi => $pergunta)
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">
                    {{ $pi + 1 }}. {{ $pergunta->texto }}
                    @if($pergunta->obrigatoria)
                        <span class="text-red-500">*</span>
                    @endif
                </label>

                @if($pergunta->tipo === 'escala_1_5')
                <div class="flex items-center gap-2 flex-wrap">
                    @php
                        $labels = [1 => 'Muito ruim', 2 => 'Ruim', 3 => 'Regular', 4 => 'Bom', 5 => 'Ótimo'];
                        $cores = [1 => '#ef4444', 2 => '#f97316', 3 => '#eab308', 4 => '#3b82f6', 5 => '#22c55e'];
                    @endphp
                    @foreach($labels as $nota => $label)
                    <label class="cursor-pointer text-center" onclick="selecionarNotaInterna(this, {{ $pergunta->id }}, {{ $nota }}, '{{ $cores[$nota] }}')">
                        <input type="radio" name="respostas[{{ $pergunta->id }}]" value="{{ $nota }}" class="hidden pergunta-interna-{{ $pergunta->id }}">
                        <div class="w-10 h-10 rounded-full border-2 border-gray-300 flex items-center justify-center text-sm font-bold text-gray-500 transition-all nota-circulo-interna">
                            {{ $nota }}
                        </div>
                        <span class="text-[9px] text-gray-500 mt-0.5 block">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>

                @elseif($pergunta->tipo === 'multipla_escolha')
                <div class="space-y-1.5">
                    @foreach($pergunta->opcoes as $opcao)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="respostas[{{ $pergunta->id }}]" value="{{ $opcao->texto }}" class="h-4 w-4 text-indigo-600">
                        <span class="text-sm text-gray-700">{{ $opcao->texto }}</span>
                    </label>
                    @endforeach
                </div>

                @elseif($pergunta->tipo === 'texto_livre')
                <textarea name="respostas[{{ $pergunta->id }}]" rows="2"
                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                          placeholder="Digite sua resposta..."></textarea>
                @endif
            </div>
            @endforeach

            <div class="flex items-center justify-between gap-3 pt-3 border-t border-gray-100">
                <button type="button" onclick="fecharModalPesquisaInterna()"
                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">
                    Cancelar
                </button>
                <button type="button" onclick="enviarPesquisaInterna()"
                        id="btnEnviarPesquisaInterna"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enviar Pesquisa
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    let payloadFinalizacaoPendente = null;

    function aplicarPreset(texto) {
        const campo = document.getElementById('observacoes');
        if (!campo) return;
        const valorAtual = campo.value.trim();
        campo.value = valorAtual ? (valorAtual + ' ' + texto) : texto;
        campo.focus();
    }

    function toggleJustificativa(idx, mostrar) {
        const wrap = document.getElementById('justificativa-wrap-' + idx);
        if (wrap) {
            wrap.classList.toggle('hidden', !mostrar);
            if (!mostrar) {
                const textarea = wrap.querySelector('textarea');
                if (textarea) textarea.value = '';
            }
        }
    }

    function resetarBotaoFinalizarAtividade() {
        const btnFinalizar = document.getElementById('btnFinalizar');
        if (!btnFinalizar) return;

        btnFinalizar.disabled = false;
        btnFinalizar.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Finalizar Atividade';
    }

    function abrirModalPesquisaInterna() {
        const modal = document.getElementById('modalPesquisaInterna');
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function fecharModalPesquisaInterna() {
        const modal = document.getElementById('modalPesquisaInterna');
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function selecionarNotaInterna(el, perguntaId, nota, cor) {
        el.closest('.flex').querySelectorAll('.nota-circulo-interna').forEach(circulo => {
            circulo.style.backgroundColor = '';
            circulo.style.borderColor = '#d1d5db';
            circulo.style.color = '#6b7280';
        });

        const circulo = el.querySelector('.nota-circulo-interna');
        circulo.style.backgroundColor = cor;
        circulo.style.borderColor = cor;
        circulo.style.color = 'white';
        el.querySelector('input[type=radio]').checked = true;
    }

    async function enviarPesquisaInterna() {
        const form = document.getElementById('formPesquisaInterna');
        const btn = document.getElementById('btnEnviarPesquisaInterna');

        if (!form || !btn) {
            return;
        }

        const formData = new FormData(form);

        @if(isset($pesquisaInterna) && $pesquisaInterna)
            @foreach($pesquisaInterna->perguntas as $pergunta)
                @if($pergunta->obrigatoria)
                {
                    const val = formData.get('respostas[{{ $pergunta->id }}]');
                    if (!val || val.trim() === '') {
                        alert('Por favor, responda a pergunta: "{{ addslashes($pergunta->texto) }}"');
                        return;
                    }
                }
                @endif
            @endforeach
        @endif

        const respostas = {};
        for (const [key, value] of formData.entries()) {
            const match = key.match(/respostas\[(\d+)\]/);
            if (match) {
                respostas[match[1]] = value;
            }
        }

        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Enviando...';

        try {
            const response = await fetch('{{ route("pesquisa.responder.interno") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    pesquisa_id: formData.get('pesquisa_id'),
                    ordem_servico_id: formData.get('ordem_servico_id'),
                    respostas: respostas,
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Erro ao enviar pesquisa.');
            }

            fecharModalPesquisaInterna();

            if (!payloadFinalizacaoPendente) {
                window.location.reload();
                return;
            }

            const payload = payloadFinalizacaoPendente;
            payloadFinalizacaoPendente = null;

            const responseFinalizar = await fetch('{{ route("admin.ordens-servico.finalizar-atividade", $ordemServico) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            const dataFinalizar = await responseFinalizar.json();

            if (responseFinalizar.ok) {
                if (dataFinalizar.os_finalizada) {
                    alert('A Ordem de Serviço foi encerrada automaticamente pois todas as atividades foram concluídas!');
                } else {
                    alert('Atividade finalizada com sucesso!');
                }
                window.location.href = '{{ route("admin.ordens-servico.show", $ordemServico) }}';
                return;
            }

            throw new Error(dataFinalizar.message || 'Erro ao finalizar atividade após responder a pesquisa.');
        } catch (error) {
            alert(error.message || 'Erro ao enviar pesquisa. Tente novamente.');
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Enviar Pesquisa';
            resetarBotaoFinalizarAtividade();
        }
    }

    document.getElementById('formFinalizarAtividade').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const btnFinalizar = document.getElementById('btnFinalizar');
        const isMulti = {{ $isMultiEstabelecimento ? 'true' : 'false' }};

        // Validações para estabelecimento único
        if (!isMulti) {
            const statusSelecionado = document.querySelector('input[name="status_execucao"]:checked');
            if (!statusSelecionado) {
                alert('Selecione o status da execução.');
                return;
            }
            const observacoes = document.getElementById('observacoes').value.trim();
            if (observacoes.length < 10) {
                alert('Informe as observações (mínimo 10 caracteres).');
                document.getElementById('observacoes').focus();
                return;
            }
        } else {
            // Validações para múltiplos estabelecimentos
            const totalEstabs = {{ $estabelecimentosAtividade->count() }};
            for (let i = 0; i < totalEstabs; i++) {
                const selecionado = document.querySelector(`input[name="execucao_estabelecimentos[${i}][executada]"]:checked`);
                if (!selecionado) {
                    alert('Informe se a atividade foi executada em cada estabelecimento.');
                    return;
                }
                if (selecionado.value === '0') {
                    const justificativa = document.querySelector(`textarea[name="execucao_estabelecimentos[${i}][justificativa]"]`);
                    if (!justificativa || justificativa.value.trim().length < 10) {
                        alert('Informe justificativa (mínimo 10 caracteres) para os estabelecimentos não executados.');
                        if (justificativa) justificativa.focus();
                        return;
                    }
                }
            }
        }

        // Validação de documentos (se tem o checkbox de "sem documentos")
        const checkSemDoc = document.getElementById('checkSemDocumentos');
        if (checkSemDoc && !checkSemDoc.checked) {
            const erroSemDoc = document.getElementById('erroSemDocumentos');
            if (erroSemDoc) erroSemDoc.classList.remove('hidden');
            const label = document.getElementById('labelSemDocumentos');
            if (label) label.classList.add('ring-2', 'ring-red-400');
            checkSemDoc.focus();
            return;
        }

        const documentosPendentesAssinatura = {{ $documentosOsPendentesAssinatura->count() ?? 0 }};
        if (documentosPendentesAssinatura > 0) {
            alert(documentosPendentesAssinatura === 1
                ? 'A atividade só pode ser finalizada quando o documento da OS estiver com todas as assinaturas concluídas.'
                : 'A atividade só pode ser finalizada quando todos os documentos da OS estiverem com todas as assinaturas concluídas.');
            return;
        }

        // Bloqueia botão
        btnFinalizar.disabled = true;
        btnFinalizar.innerHTML = '<svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Finalizando...';

        // Submit via fetch (POST JSON) para reaproveitar a lógica do controller
        const formData = new FormData(form);
        const payload = {};
        
        payload.atividade_index = parseInt(formData.get('atividade_index'));
        payload._from_page = '1';

        if (!isMulti) {
            payload.status_execucao = formData.get('status_execucao');
            payload.observacoes = formData.get('observacoes');
        }

        // Multi-estabelecimento
        if (isMulti) {
            const totalEstabs = {{ $estabelecimentosAtividade->count() }};
            const execucaoEstabelecimentos = [];
            for (let i = 0; i < totalEstabs; i++) {
                const estId = parseInt(formData.get(`execucao_estabelecimentos[${i}][estabelecimento_id]`));
                const executada = formData.get(`execucao_estabelecimentos[${i}][executada]`) === '1';
                const justificativa = formData.get(`execucao_estabelecimentos[${i}][justificativa]`) || null;
                execucaoEstabelecimentos.push({ estabelecimento_id: estId, executada, justificativa });
            }
            payload.execucao_estabelecimentos = execucaoEstabelecimentos;
        }

        payload.confirmou_sem_documentos = checkSemDoc?.checked || false;

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        })
        .then(async (response) => {
            const data = await response.json();
            if (response.ok) {
                if (data.os_finalizada) {
                    alert('A Ordem de Serviço foi encerrada automaticamente pois todas as atividades foram concluídas!');
                } else {
                    alert('Atividade finalizada com sucesso!');
                }
                window.location.href = '{{ route("admin.ordens-servico.show", $ordemServico) }}';
            } else if (data.survey_required) {
                payloadFinalizacaoPendente = payload;
                const modalPesquisa = document.getElementById('modalPesquisaInterna');

                if (modalPesquisa) {
                    abrirModalPesquisaInterna();
                } else {
                    alert('A pesquisa obrigatória não foi carregada na tela. Recarregue a página e tente novamente.');
                }

                resetarBotaoFinalizarAtividade();
            } else {
                alert(data.message || 'Erro ao finalizar atividade.');
                resetarBotaoFinalizarAtividade();
            }
        })
        .catch((error) => {
            console.error('Erro:', error);
            alert('Erro ao finalizar atividade. Tente novamente.');
            resetarBotaoFinalizarAtividade();
        });
    });

    // Limpa erro de checkbox ao marcar
    const checkSemDoc = document.getElementById('checkSemDocumentos');
    if (checkSemDoc) {
        checkSemDoc.addEventListener('change', function () {
            if (this.checked) {
                const erroSemDoc = document.getElementById('erroSemDocumentos');
                if (erroSemDoc) erroSemDoc.classList.add('hidden');
                const label = document.getElementById('labelSemDocumentos');
                if (label) label.classList.remove('ring-2', 'ring-red-400');
            }
        });
    }

    document.getElementById('modalPesquisaInterna')?.addEventListener('click', function (e) {
        if (e.target === this) {
            fecharModalPesquisaInterna();
            resetarBotaoFinalizarAtividade();
        }
    });
</script>
@endpush
