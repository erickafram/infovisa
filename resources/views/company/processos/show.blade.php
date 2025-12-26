@extends('layouts.company')

@section('title', 'Detalhes do Processo')
@section('page-title', 'Detalhes do Processo')

@section('content')
<div class="space-y-6" x-data="{ modalUpload: false, modalAlertas: false, modalVisualizador: false, documentoUrl: '', documentoNome: '', documentoExtensao: '', modalResposta: false, docRespostaId: null, docRespostaNome: '' }">
    {{-- Mensagens --}}
    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <ul class="list-disc list-inside text-sm text-red-700">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Cabeçalho com dados do processo --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between mb-4">
            <div>
                <a href="{{ route('company.processos.index') }}" class="text-sm text-blue-600 hover:text-blue-700 flex items-center mb-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar
                </a>
                <h1 class="text-xl font-bold text-gray-900">Processo {{ $processo->numero_processo }}</h1>
            </div>
            <span class="px-3 py-1.5 text-sm font-medium rounded-full 
                @if($processo->status === 'aprovado') bg-green-100 text-green-800
                @elseif($processo->status === 'em_analise') bg-blue-100 text-blue-800
                @elseif($processo->status === 'arquivado') bg-gray-100 text-gray-800
                @elseif($processo->status === 'parado') bg-red-100 text-red-800
                @else bg-yellow-100 text-yellow-800 @endif">
                {{ $processo->status_nome }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <dt class="text-xs font-medium text-gray-500">Tipo</dt>
                <dd class="text-sm text-gray-900 mt-1">{{ $processo->tipo_nome }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">Estabelecimento</dt>
                <dd class="text-sm text-gray-900 mt-1">
                    <a href="{{ route('company.estabelecimentos.show', $processo->estabelecimento->id) }}" class="text-blue-600 hover:text-blue-700">
                        {{ $processo->estabelecimento->nome_fantasia ?: $processo->estabelecimento->razao_social }}
                    </a>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">Data de Abertura</dt>
                <dd class="text-sm text-gray-900 mt-1">{{ $processo->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">Última Atualização</dt>
                <dd class="text-sm text-gray-900 mt-1">{{ $processo->updated_at->format('d/m/Y H:i') }}</dd>
            </div>
        </div>
        @if($processo->observacoes)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <dt class="text-xs font-medium text-gray-500">Observações</dt>
            <dd class="text-sm text-gray-900 mt-1">{{ $processo->observacoes }}</dd>
        </div>
        @endif
    </div>

    {{-- Layout 2 colunas: Menu (esquerda) + Documentos (direita) --}}
    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Coluna Esquerda: Menu --}}
        <div class="flex-shrink-0 space-y-4" style="width: 320px; min-width: 320px;">
            {{-- Menu de Opções --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900 uppercase mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    Menu
                </h3>
                <div class="space-y-1">
                    @if($processo->status !== 'arquivado' && $processo->status !== 'parado')
                    <button @click="modalUpload = true" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Upload de Arquivos
                    </button>
                    @endif
                    <button @click="modalAlertas = true" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-orange-50 hover:text-orange-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Alertas
                        @if($alertas->where('status', 'pendente')->count() > 0)
                        <span class="ml-auto px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full">
                            {{ $alertas->where('status', 'pendente')->count() }}
                        </span>
                        @endif
                    </button>
                </div>
            </div>

            {{-- Resumo --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900 uppercase mb-3">Resumo</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Documentos</span>
                        <span class="font-medium text-gray-900">{{ $documentosAprovados->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Pendentes</span>
                        <span class="font-medium text-yellow-600">{{ $documentosPendentes->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Alertas</span>
                        <span class="font-medium text-orange-600">{{ $alertas->where('status', 'pendente')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Coluna Direita: Documentos --}}
        <div class="flex-1 space-y-4">
            {{-- Documentos Pendentes --}}
            @if($documentosPendentes->count() > 0)
            <div class="bg-yellow-50 rounded-xl shadow-sm border border-yellow-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-yellow-200 bg-yellow-100">
                    <h2 class="text-sm font-semibold text-yellow-800 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Aguardando Aprovação
                        <span class="px-2 py-0.5 bg-yellow-200 text-yellow-800 text-xs font-bold rounded-full">{{ $documentosPendentes->count() }}</span>
                    </h2>
                </div>
                <div class="divide-y divide-yellow-200">
                    @foreach($documentosPendentes as $documento)
                    <div class="px-4 py-3 flex items-center justify-between hover:bg-yellow-100/50">
                        <button type="button" 
                                @click="documentoUrl = '{{ route('company.processos.documento.visualizar', [$processo->id, $documento->id]) }}'; documentoNome = '{{ $documento->nome_original }}'; documentoExtensao = '{{ $documento->extensao }}'; modalVisualizador = true"
                                class="flex items-center gap-3 text-left flex-1">
                            <span class="text-xl">{{ $documento->icone }}</span>
                            <div>
                                <p class="text-sm font-medium text-gray-900 hover:text-blue-600">{{ $documento->nome_original }}</p>
                                <p class="text-xs text-gray-500">{{ $documento->tamanho_formatado }} • {{ $documento->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </button>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 bg-yellow-200 text-yellow-800 text-xs font-medium rounded">Pendente</span>
                            @if($documento->usuario_externo_id == auth('externo')->id())
                            <form action="{{ route('company.processos.documento.delete', [$processo->id, $documento->id]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este arquivo?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition-colors" title="Excluir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Documentos Rejeitados --}}
            @if($documentosRejeitados->count() > 0)
            <div class="bg-red-50 rounded-xl shadow-sm border border-red-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-red-200 bg-red-100">
                    <h2 class="text-sm font-semibold text-red-800 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Arquivos Rejeitados
                        <span class="px-2 py-0.5 bg-red-200 text-red-800 text-xs font-bold rounded-full">{{ $documentosRejeitados->count() }}</span>
                    </h2>
                </div>
                <div class="divide-y divide-red-200">
                    @foreach($documentosRejeitados as $documento)
                    <div class="px-4 py-3" x-data="{ showReenvio: false }">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-xl">{{ $documento->icone }}</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $documento->nome_original }}</p>
                                    <p class="text-xs text-gray-500">{{ $documento->tamanho_formatado }} • {{ $documento->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 bg-red-200 text-red-800 text-xs font-medium rounded">Rejeitado</span>
                                @if($processo->status !== 'arquivado' && $processo->status !== 'parado')
                                <button @click="showReenvio = !showReenvio" 
                                        class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Reenviar
                                </button>
                                @endif
                            </div>
                        </div>
                        @if($documento->motivo_rejeicao)
                        <div class="mt-2 ml-9 p-2 bg-red-100 rounded text-xs text-red-700">
                            <strong>Motivo:</strong> {{ $documento->motivo_rejeicao }}
                        </div>
                        @endif
                        
                        {{-- Histórico de Rejeições Anteriores --}}
                        @if($documento->historico_rejeicao && count($documento->historico_rejeicao) > 0)
                        <div class="mt-2 ml-9 p-2 bg-gray-100 rounded text-xs text-gray-600" x-data="{ showHistorico: false }">
                            <button type="button" @click="showHistorico = !showHistorico" class="flex items-center gap-1 text-gray-700 hover:text-gray-900">
                                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-90': showHistorico }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                <strong>Histórico de rejeições ({{ count($documento->historico_rejeicao) }})</strong>
                            </button>
                            <div x-show="showHistorico" x-transition class="mt-2 space-y-2" style="display: none;">
                                @foreach($documento->historico_rejeicao as $index => $rejeicao)
                                <div class="p-2 bg-white rounded border border-gray-200">
                                    <p class="text-[10px] text-gray-500">Tentativa {{ $index + 1 }} - {{ \Carbon\Carbon::parse($rejeicao['rejeitado_em'])->format('d/m/Y H:i') }}</p>
                                    <p class="text-xs text-gray-700"><strong>Arquivo:</strong> {{ $rejeicao['arquivo_anterior'] }}</p>
                                    <p class="text-xs text-red-600"><strong>Motivo:</strong> {{ $rejeicao['motivo'] }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        {{-- Formulário de Reenvio --}}
                        <div x-show="showReenvio" x-transition class="mt-3 ml-9 p-3 bg-white border border-gray-200 rounded-lg" style="display: none;">
                            <form action="{{ route('company.processos.reenviar', [$processo->id, $documento->id]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <p class="text-xs text-gray-600 mb-3">
                                    <strong>Enviar arquivo corrigido</strong> para substituir o documento rejeitado.
                                </p>
                                <div class="space-y-3">
                                    <div>
                                        <input type="file" name="arquivo" required
                                               class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
                                               accept=".pdf">
                                        <p class="mt-1 text-xs text-gray-500">Apenas arquivos PDF. Máx: 10MB</p>
                                    </div>
                                    <div>
                                        <textarea name="observacoes" rows="2" 
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                                  placeholder="Observações sobre a correção (opcional)"></textarea>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700">
                                            Enviar Correção
                                        </button>
                                        <button type="button" @click="showReenvio = false" class="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-200">
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Lista de Documentos e Arquivos do Processo --}}
            @php
                $totalDocumentos = isset($todosDocumentos) ? $todosDocumentos->count() : 0;
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Lista de Documentos e Arquivos
                        @if($totalDocumentos > 0)
                        <span class="px-2 py-0.5 bg-gray-200 text-gray-700 text-xs font-bold rounded-full">{{ $totalDocumentos }}</span>
                        @endif
                    </h2>
                    @if($processo->status !== 'arquivado' && $processo->status !== 'parado')
                    <button @click="modalUpload = true" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Enviar Arquivo
                    </button>
                    @endif
                </div>
                
                @if($totalDocumentos > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($todosDocumentos as $item)
                        @if($item['tipo'] === 'vigilancia')
                            @php $docDigital = $item['documento']; @endphp
                            <div class="px-4 py-3 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <a href="{{ route('company.processos.documento-digital.visualizar', [$processo->id, $docDigital->id]) }}" 
                                       target="_blank"
                                       class="flex items-center gap-3 flex-1">
                                        <div class="w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 hover:text-blue-600 truncate">
                                                {{ $docDigital->tipoDocumento->nome ?? 'Documento' }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                Nº {{ $docDigital->numero_documento }} • {{ $docDigital->created_at->format('d/m/Y H:i') }}
                                                <span class="text-blue-600 font-medium">• Vigilância Sanitária</span>
                                            </p>
                                        </div>
                                    </a>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        {{-- Badge de Prazo --}}
                                        @if($docDigital->prazo_dias)
                                            @php
                                                $corBadge = $docDigital->cor_status_prazo;
                                                $textoBadge = $docDigital->texto_status_prazo;
                                                $classesCor = [
                                                    'red' => 'bg-red-100 text-red-700 border-red-200',
                                                    'yellow' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                                    'green' => 'bg-green-100 text-green-700 border-green-200',
                                                    'blue' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                    'gray' => 'bg-gray-100 text-gray-700 border-gray-200',
                                                ];
                                                $classeBadge = $classesCor[$corBadge] ?? $classesCor['gray'];
                                            @endphp
                                            <span class="px-2 py-0.5 {{ $classeBadge }} border rounded-full text-xs font-medium whitespace-nowrap flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ $textoBadge }}
                                            </span>
                                        @endif
                                        <a href="{{ route('company.processos.documento-digital.download', [$processo->id, $docDigital->id]) }}" 
                                           class="px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-medium rounded hover:bg-blue-200 transition-colors flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            Download
                                        </a>
                                        @if($docDigital->permiteResposta())
                                        <button type="button"
                                                @click="docRespostaId = {{ $docDigital->id }}; docRespostaNome = '{{ $docDigital->tipoDocumento->nome ?? 'Documento' }}'; modalResposta = true"
                                                class="px-3 py-1.5 bg-green-100 text-green-700 text-xs font-medium rounded hover:bg-green-200 transition-colors flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                            </svg>
                                            Responder
                                        </button>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Respostas vinculadas a este documento --}}
                                @if($docDigital->respostas->count() > 0)
                                <div class="mt-2 ml-13 border-l-2 border-green-200 pl-3">
                                    @foreach($docDigital->respostas as $resposta)
                                    <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                            </svg>
                                            <div>
                                                <p class="text-xs font-medium text-gray-700">Resposta: {{ $resposta->nome_original }}</p>
                                                <p class="text-[10px] text-gray-500">
                                                    Enviado em {{ $resposta->created_at->format('d/m/Y H:i') }} • {{ $resposta->tamanho_formatado }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 text-[10px] font-medium rounded-full
                                                @if($resposta->status === 'pendente') bg-yellow-100 text-yellow-700
                                                @elseif($resposta->status === 'aprovado') bg-green-100 text-green-700
                                                @else bg-red-100 text-red-700
                                                @endif">
                                                {{ ucfirst($resposta->status) }}
                                            </span>
                                            <a href="{{ route('company.processos.documento-digital.resposta.visualizar', [$processo->id, $docDigital->id, $resposta->id]) }}"
                                               target="_blank"
                                               class="p-1 text-blue-600 hover:bg-blue-50 rounded" title="Visualizar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('company.processos.documento-digital.resposta.download', [$processo->id, $docDigital->id, $resposta->id]) }}"
                                               class="p-1 text-gray-600 hover:bg-gray-100 rounded" title="Download">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                            </a>
                                            @if($resposta->status === 'pendente')
                                            <form action="{{ route('company.processos.documento-digital.resposta.excluir', [$processo->id, $docDigital->id, $resposta->id]) }}" 
                                                  method="POST" 
                                                  class="inline"
                                                  onsubmit="return confirm('Tem certeza que deseja excluir esta resposta?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1 text-red-600 hover:bg-red-50 rounded" 
                                                        title="Excluir resposta">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                    @if($resposta->status === 'rejeitado' && $resposta->motivo_rejeicao)
                                    <div class="py-1 px-2 mb-2 bg-red-50 rounded text-xs text-red-700">
                                        <strong>Motivo da rejeição:</strong> {{ $resposta->motivo_rejeicao }}
                                    </div>
                                    @endif
                                    {{-- Histórico de Rejeições Anteriores --}}
                                    @if($resposta->historico_rejeicao && count($resposta->historico_rejeicao) > 0)
                                    <div class="py-1 px-2 mb-2 bg-gray-50 rounded text-xs text-gray-600" x-data="{ showHistorico: false }">
                                        <button type="button" @click="showHistorico = !showHistorico" class="flex items-center gap-1 text-gray-700 hover:text-gray-900">
                                            <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-90': showHistorico }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            <strong>Histórico de rejeições ({{ count($resposta->historico_rejeicao) }})</strong>
                                        </button>
                                        <div x-show="showHistorico" x-transition class="mt-2 space-y-1" style="display: none;">
                                            @foreach($resposta->historico_rejeicao as $index => $rejeicao)
                                            <div class="p-1.5 bg-white rounded border border-gray-200">
                                                <p class="text-[10px] text-gray-500">Tentativa {{ $index + 1 }} - {{ \Carbon\Carbon::parse($rejeicao['rejeitado_em'])->format('d/m/Y H:i') }}</p>
                                                <p class="text-[10px] text-gray-700"><strong>Arquivo:</strong> {{ $rejeicao['arquivo_anterior'] }}</p>
                                                <p class="text-[10px] text-red-600"><strong>Motivo:</strong> {{ $rejeicao['motivo'] }}</p>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        @else
                            @php $documento = $item['documento']; @endphp
                            <div class="px-4 py-3 flex items-center justify-between hover:bg-gray-50">
                                <button type="button" 
                                        @click="documentoUrl = '{{ route('company.processos.documento.visualizar', [$processo->id, $documento->id]) }}'; documentoNome = '{{ $documento->nome_original }}'; documentoExtensao = '{{ $documento->extensao }}'; modalVisualizador = true"
                                        class="flex items-center gap-3 text-left flex-1">
                                    <span class="text-xl">{{ $documento->icone }}</span>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 hover:text-blue-600">{{ $documento->nome_original }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $documento->tamanho_formatado }} • {{ $documento->created_at->format('d/m/Y H:i') }}
                                            @if($documento->tipo_usuario === 'externo')
                                            <span class="text-green-600 font-medium">• Enviado por você</span>
                                            @else
                                            <span class="text-blue-600 font-medium">• Vigilância Sanitária</span>
                                            @endif
                                        </p>
                                    </div>
                                </button>
                                <a href="{{ route('company.processos.download', [$processo->id, $documento->id]) }}" 
                                   class="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded hover:bg-gray-200 transition-colors flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Download
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
                @else
                <div class="px-4 py-8 text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Nenhum documento no processo</p>
                    @if($processo->status !== 'arquivado' && $processo->status !== 'parado')
                    <button @click="modalUpload = true" class="mt-3 text-sm text-blue-600 hover:text-blue-700 font-medium">
                        Enviar primeiro arquivo →
                    </button>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Upload --}}
    <div x-show="modalUpload" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="modalUpload = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg" @click.stop>
                <form action="{{ route('company.processos.upload', $processo->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Enviar Arquivo</h3>
                        <button type="button" @click="modalUpload = false" class="text-gray-400 hover:text-gray-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <p class="text-xs text-blue-700">
                                <strong>Atenção:</strong> Os arquivos enviados ficarão pendentes de aprovação pela Vigilância Sanitária.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Arquivo *</label>
                            <input type="file" name="arquivo" required
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
                                   accept=".pdf">
                            <p class="mt-1 text-xs text-gray-500">Apenas arquivos PDF. Máx: 10MB</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                            <textarea name="observacoes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Descreva o documento (opcional)"></textarea>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex flex-row-reverse gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Enviar</button>
                        <button type="button" @click="modalUpload = false" class="px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Resposta a Documento --}}
    <div x-show="modalResposta" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="modalResposta = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg" @click.stop>
                <form :action="`{{ url('/company/processos/' . $processo->id . '/documentos-vigilancia') }}/${docRespostaId}/resposta`" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-green-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                            Responder Documento
                        </h3>
                        <button type="button" @click="modalResposta = false" class="text-gray-400 hover:text-gray-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <p class="text-sm text-green-800 font-medium">Respondendo a: <span x-text="docRespostaNome"></span></p>
                            <p class="text-xs text-green-700 mt-1">
                                <strong>Atenção:</strong> A resposta ficará vinculada ao documento original e aguardará aprovação da Vigilância Sanitária.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Arquivo PDF *</label>
                            <input type="file" name="arquivo" required
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100 cursor-pointer"
                                   accept=".pdf">
                            <p class="mt-1 text-xs text-gray-500">Apenas arquivos PDF. Máx: 10MB</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                            <textarea name="observacoes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Descreva a resposta ou justificativa (opcional)"></textarea>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex flex-row-reverse gap-2">
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Enviar Resposta
                        </button>
                        <button type="button" @click="modalResposta = false" class="px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Alertas --}}
    <div x-show="modalAlertas" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="modalAlertas = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg" @click.stop>
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Alertas do Processo
                    </h3>
                    <button type="button" @click="modalAlertas = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    @if($alertas->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($alertas as $alerta)
                        <div class="px-6 py-4 {{ $alerta->status === 'pendente' ? ($alerta->isVencido() ? 'bg-red-50' : ($alerta->isProximo() ? 'bg-yellow-50' : '')) : 'bg-gray-50' }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900">{{ $alerta->descricao }}</p>
                                    <div class="flex items-center gap-3 mt-1">
                                        <p class="text-xs text-gray-500 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $alerta->data_alerta->format('d/m/Y') }}
                                        </p>
                                        @if($alerta->usuarioCriador)
                                        <p class="text-xs text-gray-400">
                                            por {{ $alerta->usuarioCriador->nome }}
                                        </p>
                                        @endif
                                    </div>
                                    @if($alerta->status === 'concluido' && $alerta->concluido_em)
                                    <p class="text-xs text-green-600 mt-1">
                                        Resolvido em {{ $alerta->concluido_em->format('d/m/Y H:i') }}
                                    </p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="px-2 py-0.5 text-xs font-medium rounded
                                        @if($alerta->status === 'pendente')
                                            @if($alerta->isVencido()) bg-red-100 text-red-700
                                            @elseif($alerta->isProximo()) bg-yellow-100 text-yellow-700
                                            @else bg-blue-100 text-blue-700
                                            @endif
                                        @elseif($alerta->status === 'concluido') bg-green-100 text-green-700
                                        @else bg-gray-100 text-gray-700
                                        @endif">
                                        @if($alerta->status === 'pendente')
                                            @if($alerta->isVencido()) Vencido
                                            @elseif($alerta->isProximo()) Próximo
                                            @else Pendente
                                            @endif
                                        @elseif($alerta->status === 'concluido') Concluído
                                        @else {{ ucfirst($alerta->status) }}
                                        @endif
                                    </span>
                                    @if($alerta->status !== 'concluido')
                                    <form action="{{ route('company.processos.alertas.concluir', [$processo->id, $alerta->id]) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                            class="p-1.5 text-green-600 hover:text-green-700 hover:bg-green-50 rounded-lg transition-colors"
                                            title="Marcar como resolvido"
                                            onclick="return confirm('Confirma que este alerta foi resolvido?')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="px-6 py-8 text-center">
                        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Nenhum alerta cadastrado</p>
                    </div>
                    @endif
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <button type="button" @click="modalAlertas = false" class="w-full px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Visualizador de Documento --}}
    <div x-show="modalVisualizador" x-cloak class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
        <div class="fixed inset-0 bg-black bg-opacity-75" @click="modalVisualizador = false"></div>
        <div class="fixed inset-4 flex flex-col">
            {{-- Header --}}
            <div class="bg-white rounded-t-lg px-4 py-3 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <span class="text-xl">📄</span>
                    <span class="text-sm font-medium text-gray-900" x-text="documentoNome"></span>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="documentoUrl.replace('/visualizar', '/download')" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download
                    </a>
                    <button type="button" @click="modalVisualizador = false" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            {{-- Content --}}
            <div class="flex-1 bg-gray-100 rounded-b-lg overflow-hidden">
                <template x-if="['pdf'].includes(documentoExtensao.toLowerCase())">
                    <iframe :src="documentoUrl" class="w-full h-full border-0"></iframe>
                </template>
                <template x-if="['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(documentoExtensao.toLowerCase())">
                    <div class="w-full h-full flex items-center justify-center p-4 overflow-auto">
                        <img :src="documentoUrl" :alt="documentoNome" class="max-w-full max-h-full object-contain shadow-lg rounded">
                    </div>
                </template>
                <template x-if="!['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'].includes(documentoExtensao.toLowerCase())">
                    <div class="w-full h-full flex flex-col items-center justify-center p-8">
                        <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-gray-600 text-center mb-4">Este tipo de arquivo não pode ser visualizado no navegador.</p>
                        <a :href="documentoUrl.replace('/visualizar', '/download')" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                            Fazer Download
                        </a>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
