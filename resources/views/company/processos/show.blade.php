@extends('layouts.company')

@section('title', 'Detalhes do Processo')
@section('page-title', 'Detalhes do Processo')

@section('content')
<div class="space-y-6" x-data="{ modalUpload: false, modalAlertas: false, modalVisualizador: false, documentoUrl: '', documentoNome: '', documentoExtensao: '', modalResposta: false, docRespostaId: null, docRespostaNome: '', modalReenvio: false, docReenvioId: null, docReenvioNome: '', docReenvioMotivo: '' }">
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
        
        {{-- Setor Atual (visível para o estabelecimento) --}}
        @if($processo->setor_atual)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center gap-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500">Processo está com</dt>
                    <dd class="text-sm font-semibold text-blue-600 mt-0.5">{{ $processo->setor_atual_nome }}</dd>
                    @if($processo->responsavel_desde)
                    @php
                        $diasNoSetor = (int) $processo->responsavel_desde->startOfDay()->diffInDays(now()->startOfDay());
                    @endphp
                    <dd class="text-xs text-gray-500 mt-0.5">
                        @if($diasNoSetor === 0)
                            desde hoje
                        @elseif($diasNoSetor === 1)
                            há 1 dia
                        @else
                            há {{ $diasNoSetor }} dias
                        @endif
                        <span class="text-gray-400">({{ $processo->responsavel_desde->format('d/m/Y') }})</span>
                    </dd>
                    @endif
                </div>
            </div>
        </div>
        @endif
        
        @if($processo->observacoes)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <dt class="text-xs font-medium text-gray-500">Observações</dt>
            <dd class="text-sm text-gray-900 mt-1">{{ $processo->observacoes }}</dd>
        </div>
        @endif
    </div>

    {{-- Modal Bloqueante de Equipamentos de Imagem Pendentes --}}
    @if(isset($precisaCadastrarEquipamentos) && $precisaCadastrarEquipamentos)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
        {{-- Overlay escuro --}}
        <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
        
        {{-- Container do Modal --}}
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border-t-4 border-red-500">
                {{-- Ícone de alerta --}}
                <div class="flex justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
                
                {{-- Título --}}
                <h2 class="text-xl font-bold text-gray-900 text-center mb-2">
                    Ação Obrigatória
                </h2>
                <h3 class="text-lg font-semibold text-red-600 text-center mb-4">
                    Cadastro de Equipamentos de Imagem
                </h3>
                
                {{-- Conteúdo --}}
                <div class="bg-red-50 rounded-lg p-4 mb-6">
                    <p class="text-sm text-gray-700 mb-3">
                        O estabelecimento <strong class="text-gray-900">{{ $processo->estabelecimento->nome_fantasia ?: $processo->estabelecimento->razao_social }}</strong> 
                        possui atividades que exigem o cadastro de equipamentos de imagem (raio-x, tomografia, ressonância, etc).
                    </p>
                    <p class="text-sm text-gray-700">
                        Para dar continuidade ao processo, é necessário <strong>cadastrar os equipamentos</strong> ou <strong>declarar que o estabelecimento não os possui</strong>.
                    </p>
                </div>
                
                {{-- Botão --}}
                <a href="{{ route('company.estabelecimentos.equipamentos-radiacao.index', $processo->estabelecimento->id) }}" 
                   class="flex items-center justify-center w-full px-6 py-3 bg-red-600 hover:bg-red-700 text-white text-base font-semibold rounded-xl transition-colors shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                    </svg>
                    Cadastrar Equipamentos de Imagem
                </a>
                
                {{-- Aviso --}}
                <p class="text-xs text-gray-500 text-center mt-4">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Este processo está bloqueado até o cadastro ser realizado
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Alerta de Documentos com Prazo --}}
    @php
        $documentosComPrazo = collect();
        if(isset($todosDocumentos)) {
            $documentosComPrazo = $todosDocumentos->filter(function($item) {
                if($item['tipo'] === 'vigilancia') {
                    $doc = $item['documento'];
                    return $doc->prazo_dias && !$doc->isPrazoFinalizado() && $doc->status === 'assinado';
                }
                return false;
            });
        }
    @endphp
    @if($documentosComPrazo->count() > 0)
    <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-orange-800">
                    {{ $documentosComPrazo->count() }} documento(s) com prazo pendente
                </h3>
                <div class="mt-2 space-y-1">
                    @foreach($documentosComPrazo as $item)
                        @php $doc = $item['documento']; @endphp
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-orange-700">
                                • {{ $doc->tipoDocumento->nome ?? 'Documento' }} - 
                                <span class="font-medium">{{ $doc->texto_status_prazo }}</span>
                            </span>
                        </div>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-orange-600">
                    Clique em "Responder" no documento para anexar sua resposta.
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Layout 2 colunas: Menu (esquerda) + Documentos (direita) --}}
    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Coluna Esquerda: Menu --}}
        <div class="flex-shrink-0 space-y-4" style="width: 320px; min-width: 320px;">
            {{-- Documentos de Ajuda --}}
            @if(isset($documentosAjuda) && $documentosAjuda->count() > 0)
            <div class="bg-emerald-50 rounded-lg shadow-sm border border-emerald-200 p-2">
                <h3 class="text-xs font-semibold text-emerald-800 uppercase mb-2 flex items-center gap-1 px-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Ajuda
                </h3>
                <div class="space-y-1">
                    @foreach($documentosAjuda as $docAjuda)
                    <a href="{{ route('company.processos.documento-ajuda', [$processo->id, $docAjuda->id]) }}" 
                       target="_blank"
                       class="flex items-center gap-2 p-1.5 bg-white rounded-md border border-emerald-200 hover:border-emerald-400 hover:bg-emerald-50 transition-colors group">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-900 truncate">{{ $docAjuda->titulo }}</p>
                        </div>
                        <svg class="w-3 h-3 text-gray-400 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Progresso dos Documentos Obrigatórios --}}
            @php
                $totalObrigatorios = isset($documentosObrigatorios) ? $documentosObrigatorios->where('obrigatorio', true)->count() : 0;
                $enviadosOuAprovados = isset($documentosObrigatorios) ? $documentosObrigatorios->where('obrigatorio', true)->whereIn('status_envio', ['pendente', 'aprovado'])->count() : 0;
                $aprovados = isset($documentosObrigatorios) ? $documentosObrigatorios->where('obrigatorio', true)->where('status_envio', 'aprovado')->count() : 0;
                $aguardandoAprovacao = isset($documentosObrigatorios) ? $documentosObrigatorios->where('obrigatorio', true)->where('status_envio', 'pendente')->count() : 0;
                $percentual = $totalObrigatorios > 0 ? round(($enviadosOuAprovados / $totalObrigatorios) * 100) : 0;
                $faltam = $totalObrigatorios - $enviadosOuAprovados;
                $todosAprovados = ($aprovados == $totalObrigatorios && $totalObrigatorios > 0);
                $todosEnviados = ($percentual == 100 && $totalObrigatorios > 0);
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Documentos Obrigatórios
                    </h3>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold {{ $todosAprovados ? 'text-green-600' : ($todosEnviados ? 'text-amber-600' : ($totalObrigatorios == 0 ? 'text-gray-400' : 'text-blue-600')) }}">
                            {{ $enviadosOuAprovados }}/{{ $totalObrigatorios }}
                        </span>
                        @if($totalObrigatorios > 0)
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $todosAprovados ? 'bg-green-100 text-green-700' : ($todosEnviados ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                            {{ $percentual }}%
                        </span>
                        @endif
                    </div>
                </div>
                
                {{-- Barra de Progresso --}}
                <div class="relative">
                    <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                        @if($totalObrigatorios > 0)
                        <div class="h-full rounded-full transition-all duration-500 ease-out flex items-center justify-center {{ $todosAprovados ? 'bg-green-500' : ($todosEnviados ? 'bg-amber-500' : 'bg-blue-500') }}" 
                             style="width: {{ $percentual }}%">
                            @if($percentual >= 20)
                            <span class="text-[10px] font-bold text-white">{{ $percentual }}%</span>
                            @endif
                        </div>
                        @else
                        <div class="h-full rounded-full bg-gray-300" style="width: 100%"></div>
                        @endif
                    </div>
                    @if($todosAprovados)
                    <div class="absolute -top-1 -right-1">
                        <span class="flex h-5 w-5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-5 w-5 bg-green-500 items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                        </span>
                    </div>
                    @elseif($todosEnviados && $aguardandoAprovacao > 0)
                    <div class="absolute -top-1 -right-1">
                        <span class="relative inline-flex rounded-full h-5 w-5 bg-amber-500 items-center justify-center animate-pulse">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                    </div>
                    @endif
                </div>
                
                {{-- Status --}}
                <div class="mt-3">
                    @if($totalObrigatorios == 0)
                    <p class="text-xs text-gray-500 flex items-center gap-1">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Nenhum documento obrigatório configurado
                    </p>
                    @elseif($todosAprovados)
                    <p class="text-xs text-green-600 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Todos os documentos foram aprovados!
                    </p>
                    @elseif($todosEnviados && $aguardandoAprovacao > 0)
                    <p class="text-xs text-amber-600 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Todos enviados! <span class="font-bold">{{ $aguardandoAprovacao }}</span> aguardando aprovação
                    </p>
                    @else
                    <p class="text-xs text-gray-500">
                        <span class="font-medium text-amber-600">{{ $faltam }}</span> documento(s) obrigatório(s) pendente(s)
                    </p>
                    <button @click="modalUpload = true" class="mt-2 text-xs text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Enviar documentos
                    </button>
                    @endif
                </div>
            </div>

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

            {{-- Protocolo do Processo --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <a href="{{ route('company.processos.protocolo', $processo->id) }}" 
                   target="_blank"
                   class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Protocolo do Processo
                </a>
                <p class="text-xs text-gray-500 text-center mt-2">Comprovante de abertura do processo</p>
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
                    <div class="px-4 py-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-xl">{{ $documento->icone }}</span>
                                <div>
                                    <button type="button"
                                            @click="documentoUrl = '{{ route('company.processos.documento.visualizar', [$processo->id, $documento->id]) }}'; documentoNome = '{{ $documento->nome_original }}'; documentoExtensao = '{{ $documento->extensao }}'; modalVisualizador = true"
                                            class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline text-left">
                                        {{ $documento->nome_original }}
                                    </button>
                                    <p class="text-xs text-gray-500">{{ $documento->tamanho_formatado }} • {{ $documento->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 bg-red-200 text-red-800 text-xs font-medium rounded">Rejeitado</span>
                                @if($processo->status !== 'arquivado' && $processo->status !== 'parado')
                                <button @click="docReenvioId = {{ $documento->id }}; docReenvioNome = '{{ addslashes($documento->nome_original) }}'; docReenvioMotivo = '{{ addslashes($documento->motivo_rejeicao ?? '') }}'; modalReenvio = true" 
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
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Lista de Documentos e Arquivos do Processo --}}
            @php
                $totalDocumentos = isset($todosDocumentos) ? $todosDocumentos->count() : 0;
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-200" x-data="{ pastaAtiva: null }">
                <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Lista de Documentos/Arquivos
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
                
                {{-- Abas de Pastas --}}
                @if($pastas->count() > 0)
                <div class="border-b border-gray-200 bg-gray-50">
                    <nav class="flex px-4 overflow-x-auto" aria-label="Tabs">
                        {{-- Aba "Todos" --}}
                        <button @click="pastaAtiva = null" 
                                :class="pastaAtiva === null ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'"
                                class="px-3 py-2.5 text-xs font-medium border-b-2 transition-colors whitespace-nowrap">
                            Todos
                            <span class="ml-1.5 px-1.5 py-0.5 text-[10px] rounded-full"
                                  :class="pastaAtiva === null ? 'bg-blue-100 text-blue-600' : 'bg-gray-200 text-gray-600'">
                                {{ $totalDocumentos }}
                            </span>
                        </button>
                        
                        {{-- Abas das Pastas --}}
                        @foreach($pastas as $pasta)
                        @php
                            $docsNaPasta = $todosDocumentos->where('pasta_id', $pasta->id)->count();
                        @endphp
                        <button @click="pastaAtiva = {{ $pasta->id }}"
                                :class="pastaAtiva === {{ $pasta->id }} ? 'border-b-2' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'"
                                :style="pastaAtiva === {{ $pasta->id }} ? 'color: {{ $pasta->cor }}; border-color: {{ $pasta->cor }}' : ''"
                                class="px-3 py-2.5 text-xs font-medium border-b-2 transition-colors whitespace-nowrap flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="pastaAtiva === {{ $pasta->id }} ? 'color: {{ $pasta->cor }}' : ''">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            {{ $pasta->nome }}
                            @if($docsNaPasta > 0)
                            <span class="px-1.5 py-0.5 text-[10px] rounded-full"
                                  :class="pastaAtiva === {{ $pasta->id }} ? 'bg-opacity-20' : 'bg-gray-200 text-gray-600'"
                                  :style="pastaAtiva === {{ $pasta->id }} ? 'background-color: {{ $pasta->cor }}20; color: {{ $pasta->cor }}' : ''">
                                {{ $docsNaPasta }}
                            </span>
                            @endif
                        </button>
                        @endforeach
                    </nav>
                </div>
                @endif
                
                @if($totalDocumentos > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($todosDocumentos as $item)
                        @if($item['tipo'] === 'vigilancia')
                            @php 
                                $docDigital = $item['documento'];
                                // Determinar cor da borda para documentos da vigilância
                                // Verde: documento assinado e sem prazo pendente
                                // Laranja: documento com prazo pendente ou respostas pendentes
                                // Cinza: outros casos
                                $temRespostaPendente = $docDigital->respostas->where('status', 'pendente')->count() > 0;
                                $temRespostaRejeitada = $docDigital->respostas->where('status', 'rejeitado')->count() > 0;
                                $temPrazoPendente = $docDigital->prazo_dias && !$docDigital->isPrazoFinalizado() && $docDigital->status === 'assinado';
                                
                                if ($temRespostaRejeitada) {
                                    $corBordaDoc = 'border-red-500';
                                } elseif ($temRespostaPendente || $temPrazoPendente) {
                                    $corBordaDoc = 'border-orange-500';
                                } elseif ($docDigital->status === 'assinado') {
                                    $corBordaDoc = 'border-green-500';
                                } else {
                                    $corBordaDoc = 'border-gray-300';
                                }
                            @endphp
                            <div x-show="pastaAtiva === null || pastaAtiva === {{ $item['pasta_id'] ?? 'null' }}"
                                 class="px-4 py-3 hover:bg-gray-50 border-l-4 {{ $corBordaDoc }}">
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
                                @php
                                    $respostasAprovadas = $docDigital->respostas->where('status', 'aprovado');
                                    $respostasPendentes = $docDigital->respostas->where('status', 'pendente');
                                    $respostasRejeitadas = $docDigital->respostas->where('status', 'rejeitado');
                                    $totalRejeicoes = $docDigital->respostas->sum(function($r) {
                                        return $r->historico_rejeicao ? count($r->historico_rejeicao) : 0;
                                    });
                                @endphp
                                <div class="mt-3 ml-13 border-l-2 border-green-200 pl-3">
                                    {{-- Resumo das respostas --}}
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xs font-semibold text-gray-700">Respostas ({{ $docDigital->respostas->count() }})</span>
                                        @if($respostasAprovadas->count() > 0)
                                        <span class="px-1.5 py-0.5 text-[10px] font-medium bg-green-100 text-green-700 rounded">{{ $respostasAprovadas->count() }} aprovado(s)</span>
                                        @endif
                                        @if($respostasPendentes->count() > 0)
                                        <span class="px-1.5 py-0.5 text-[10px] font-medium bg-yellow-100 text-yellow-700 rounded">{{ $respostasPendentes->count() }} pendente(s)</span>
                                        @endif
                                        @if($respostasRejeitadas->count() > 0)
                                        <span class="px-1.5 py-0.5 text-[10px] font-medium bg-red-100 text-red-700 rounded">{{ $respostasRejeitadas->count() }} rejeitado(s)</span>
                                        @endif
                                    </div>
                                    
                                    @foreach($docDigital->respostas as $resposta)
                                    <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 {{ $resposta->status === 'aprovado' ? 'text-green-500' : ($resposta->status === 'rejeitado' ? 'text-red-500' : 'text-yellow-500') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <div>
                                                <p class="text-xs font-medium text-gray-700">{{ $resposta->nome_original }}</p>
                                                <p class="text-[10px] text-gray-500">
                                                    {{ $resposta->tamanho_formatado }}
                                                    <span class="mx-1">•</span>
                                                    {{ $resposta->created_at->format('d/m H:i') }}
                                                    <span class="mx-1">•</span>
                                                    {{ $resposta->usuarioExterno->nome ?? 'Usuário' }}
                                                    @if($resposta->status === 'aprovado' && $resposta->avaliadoPor)
                                                    <span class="mx-1">•</span>
                                                    <span class="text-green-600">Aprovado por {{ $resposta->avaliadoPor->nome }}</span>
                                                    @endif
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
                                    <div class="py-1.5 px-3 mb-2 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700">
                                        <div class="flex items-start gap-2">
                                            <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div>
                                                <strong>Motivo da rejeição:</strong> {{ $resposta->motivo_rejeicao }}
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @endforeach
                                    
                                    {{-- Histórico de Rejeições Consolidado --}}
                                    @if($totalRejeicoes > 0)
                                    <div class="mt-3 p-3 bg-orange-50 border border-orange-200 rounded-lg" x-data="{ showHistorico: false }">
                                        <button type="button" @click="showHistorico = !showHistorico" class="flex items-center gap-2 text-orange-800 hover:text-orange-900 w-full">
                                            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-xs font-semibold">Histórico de rejeições ({{ $totalRejeicoes }})</span>
                                            <svg class="w-3 h-3 ml-auto transition-transform" :class="{ 'rotate-180': showHistorico }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <div x-show="showHistorico" x-transition class="mt-3 space-y-2" style="display: none;">
                                            @foreach($docDigital->respostas as $resposta)
                                                @if($resposta->historico_rejeicao && count($resposta->historico_rejeicao) > 0)
                                                    @foreach($resposta->historico_rejeicao as $index => $rejeicao)
                                                    <div class="p-2.5 bg-white rounded-lg border border-orange-200">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <span class="px-1.5 py-0.5 text-[10px] font-medium bg-orange-100 text-orange-700 rounded">Tentativa {{ $index + 1 }}</span>
                                                            <span class="text-[10px] text-gray-500">{{ \Carbon\Carbon::parse($rejeicao['rejeitado_em'])->format('d/m/Y H:i') }}</span>
                                                        </div>
                                                        <p class="text-xs text-gray-700">
                                                            <strong>Arquivo:</strong> {{ $rejeicao['arquivo_anterior'] }}
                                                        </p>
                                                        <p class="text-xs text-red-600 mt-1">
                                                            <strong>Motivo:</strong> {{ $rejeicao['motivo'] }}
                                                        </p>
                                                    </div>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        @else
                            @php 
                                $documento = $item['documento'];
                                // Determinar cor da borda para arquivos do usuário
                                // Se o tipo é 'aprovado' (vem da collection documentosAprovados), é verde
                                // Caso contrário, verifica o status_aprovacao
                                if ($item['tipo'] === 'aprovado') {
                                    $corBordaArquivo = 'border-green-500';
                                } elseif ($documento->status_aprovacao === 'rejeitado') {
                                    $corBordaArquivo = 'border-red-500';
                                } elseif ($documento->status_aprovacao === 'pendente') {
                                    $corBordaArquivo = 'border-orange-500';
                                } elseif ($documento->status_aprovacao === 'aprovado') {
                                    $corBordaArquivo = 'border-green-500';
                                } else {
                                    $corBordaArquivo = 'border-gray-300';
                                }
                            @endphp
                            <div x-show="pastaAtiva === null || pastaAtiva === {{ $item['pasta_id'] ?? 'null' }}"
                                 class="px-4 py-3 flex items-center justify-between hover:bg-gray-50 border-l-4 {{ $corBordaArquivo }}">
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
    @include('company.processos.partials.modal-upload')

    {{-- Modal Resposta a Documento --}}
    <div x-show="modalResposta" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm" @click="modalResposta = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl" @click.stop
                 x-data="{
                     arquivosResposta: [],
                     maxArquivos: 6,
                     dragover: false,
                     enviando: false,
                     handleFiles(e) {
                         const files = e.target.files || (e.dataTransfer && e.dataTransfer.files);
                         if (files) {
                             for (let i = 0; i < files.length && this.arquivosResposta.length < this.maxArquivos; i++) {
                                 const file = files[i];
                                 if (file.size > 30 * 1024 * 1024) {
                                     alert('O arquivo ' + file.name + ' excede o limite de 30MB.');
                                     continue;
                                 }
                                 const jaExiste = this.arquivosResposta.some(f => f.name === file.name && f.size === file.size);
                                 if (!jaExiste) {
                                     this.arquivosResposta.push({
                                         file: file,
                                         name: file.name,
                                         size: (file.size / 1024 / 1024).toFixed(2) + ' MB'
                                     });
                                 }
                             }
                         }
                         if (e.target && e.target.value) e.target.value = '';
                         this.dragover = false;
                     },
                     removeFile(index) {
                         this.arquivosResposta.splice(index, 1);
                     },
                     async enviarRespostas() {
                         if (this.arquivosResposta.length === 0 || this.enviando) return;
                         
                         this.enviando = true;
                         let sucessos = 0;
                         let erros = 0;
                         const observacoes = document.getElementById('observacoesResposta').value;
                         
                         for (let i = 0; i < this.arquivosResposta.length; i++) {
                             const arquivo = this.arquivosResposta[i];
                             const formData = new FormData();
                             formData.append('arquivo', arquivo.file);
                             formData.append('observacoes', observacoes);
                             formData.append('_token', '{{ csrf_token() }}');
                             
                             try {
                                 const response = await fetch(`{{ url('/company/processos/' . $processo->id . '/documentos-vigilancia') }}/${docRespostaId}/resposta`, {
                                     method: 'POST',
                                     body: formData,
                                     headers: {
                                         'X-Requested-With': 'XMLHttpRequest',
                                         'Accept': 'application/json'
                                     }
                                 });
                                 
                                 if (response.ok) {
                                     sucessos++;
                                 } else {
                                     erros++;
                                 }
                             } catch (error) {
                                 console.error('Erro:', error);
                                 erros++;
                             }
                         }
                         
                         this.enviando = false;
                         this.arquivosResposta = [];
                         
                         if (erros === 0) {
                             alert(`${sucessos} resposta(s) enviada(s) com sucesso!`);
                             modalResposta = false;
                             location.reload();
                         } else {
                             alert(`${sucessos} resposta(s) enviada(s). ${erros} erro(s).`);
                         }
                     }
                 }">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-green-50 to-emerald-50 rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Responder Notificação</h3>
                                <p class="text-xs text-gray-500">Anexe os documentos de resposta</p>
                            </div>
                        </div>
                        <button type="button" @click="modalResposta = false" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="px-6 py-5 space-y-4">
                    {{-- Info do documento --}}
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                        <p class="text-sm text-green-800 font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Respondendo a: <span x-text="docRespostaNome" class="text-green-900"></span>
                        </p>
                        <p class="text-xs text-green-700 mt-2">
                            <strong>Atenção:</strong> A resposta ficará vinculada ao documento original e aguardará aprovação da Vigilância Sanitária.
                        </p>
                    </div>
                    
                    {{-- Área de Upload --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Arquivos de Resposta * 
                            <span class="text-gray-500 font-normal">(<span x-text="arquivosResposta.length"></span>/6 selecionados)</span>
                        </label>
                        
                        {{-- Área de drop --}}
                        <div class="relative mb-3"
                             @dragover.prevent="dragover = true"
                             @dragleave.prevent="dragover = false"
                             @drop.prevent="handleFiles($event)">
                            <input type="file" 
                                   @change="handleFiles($event)"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                   accept=".pdf"
                                   multiple
                                   :disabled="arquivosResposta.length >= maxArquivos">
                            <div class="border-2 border-dashed rounded-xl p-6 text-center transition-all"
                                 :class="dragover ? 'border-green-500 bg-green-50' : (arquivosResposta.length >= maxArquivos ? 'border-gray-200 bg-gray-50' : 'border-gray-300 bg-gray-50 hover:border-green-400 hover:bg-green-50')">
                                <template x-if="arquivosResposta.length < maxArquivos">
                                    <div>
                                        <svg class="w-12 h-12 mx-auto text-green-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <p class="text-sm text-gray-600 mb-1">
                                            <span class="text-green-600 font-semibold">Clique para selecionar</span> ou arraste os arquivos
                                        </p>
                                        <p class="text-xs text-gray-500">Apenas PDF • Máx. 30MB cada • Até 6 arquivos</p>
                                        <p class="text-xs text-amber-600 mt-2 font-medium">💡 Dica: Documentos com muitas folhas devem ter no máximo 5MB</p>
                                    </div>
                                </template>
                                <template x-if="arquivosResposta.length >= maxArquivos">
                                    <div>
                                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="text-sm text-gray-500 font-medium">Limite de 6 arquivos atingido</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                        
                        {{-- Lista de arquivos selecionados --}}
                        <template x-if="arquivosResposta.length > 0">
                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                <template x-for="(arquivo, index) in arquivosResposta" :key="index">
                                    <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate" x-text="arquivo.name"></p>
                                                <p class="text-xs text-gray-500" x-text="arquivo.size"></p>
                                            </div>
                                        </div>
                                        <button type="button" @click="removeFile(index)" 
                                                class="p-1.5 text-red-500 hover:bg-red-100 rounded-lg transition-colors flex-shrink-0" title="Remover">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                    
                    {{-- Observações --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                        <textarea id="observacoesResposta" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Descreva a resposta ou justificativa (opcional)"></textarea>
                    </div>
                </div>
                
                {{-- Footer --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-2xl flex flex-row-reverse gap-3">
                    <button type="button" @click="enviarRespostas()"
                            :disabled="arquivosResposta.length === 0 || enviando"
                            class="px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-xl hover:bg-green-700 transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="!enviando">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </template>
                        <template x-if="enviando">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="enviando ? 'Enviando...' : 'Enviar ' + arquivosResposta.length + ' Resposta(s)'"></span>
                    </button>
                    <button type="button" @click="modalResposta = false" class="px-4 py-2.5 bg-white text-gray-700 text-sm font-medium rounded-xl border border-gray-300 hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                </div>
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

    {{-- Modal Reenvio de Documento Rejeitado --}}
    <div x-show="modalReenvio" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
         x-data="{ 
             fileReenvio: null, 
             fileReenvioSize: '', 
             enviandoReenvio: false,
             dragover: false,
             handleFile(e) {
                 const file = e.target.files[0] || (e.dataTransfer && e.dataTransfer.files[0]);
                 if (file) {
                     this.fileReenvio = file;
                     this.fileReenvioSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                 }
                 this.dragover = false;
             },
             resetFile() {
                 this.fileReenvio = null;
                 this.fileReenvioSize = '';
                 this.$refs.fileReenvioInput.value = '';
             },
             async enviarReenvio() {
                 if (!this.fileReenvio || this.enviandoReenvio) return;
                 
                 this.enviandoReenvio = true;
                 const formData = new FormData();
                 formData.append('arquivo', this.fileReenvio);
                 formData.append('documento_id', docReenvioId);
                 formData.append('_token', '{{ csrf_token() }}');
                 
                 try {
                     const response = await fetch('{{ route('company.processos.upload', $processo->id) }}', {
                         method: 'POST',
                         body: formData,
                         headers: {
                             'X-Requested-With': 'XMLHttpRequest',
                             'Accept': 'application/json'
                         }
                     });
                     
                     if (response.ok) {
                         alert('Documento reenviado com sucesso! Aguarde a aprovação.');
                         window.location.reload();
                     } else {
                         const data = await response.json();
                         alert(data.message || 'Erro ao reenviar documento');
                     }
                 } catch (error) {
                     console.error('Erro:', error);
                     alert('Erro ao reenviar documento. Tente novamente.');
                 } finally {
                     this.enviandoReenvio = false;
                 }
             }
         }">
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm" @click="modalReenvio = false; resetFile()"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg" @click.stop>
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100 bg-red-50 rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Reenviar Documento</h3>
                                <p class="text-xs text-gray-500">Substitua o documento rejeitado</p>
                            </div>
                        </div>
                        <button type="button" @click="modalReenvio = false; resetFile()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                {{-- Content --}}
                <div class="p-6 space-y-4">
                    {{-- Info do documento rejeitado --}}
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl">
                        <p class="text-sm font-medium text-red-800 mb-1">Documento rejeitado:</p>
                        <p class="text-sm text-red-700" x-text="docReenvioNome"></p>
                        <template x-if="docReenvioMotivo">
                            <div class="mt-2 pt-2 border-t border-red-200">
                                <p class="text-xs text-red-600"><strong>Motivo:</strong> <span x-text="docReenvioMotivo"></span></p>
                            </div>
                        </template>
                    </div>
                    
                    {{-- Aviso --}}
                    <div class="flex items-start gap-3 p-3 bg-blue-50 border border-blue-200 rounded-xl">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-blue-800">
                            O novo arquivo substituirá o documento rejeitado. O histórico de rejeições será mantido.
                        </p>
                    </div>
                    
                    {{-- Área de Upload --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Novo Arquivo *</label>
                        <div class="relative"
                             @dragover.prevent="dragover = true"
                             @dragleave.prevent="dragover = false"
                             @drop.prevent="handleFile($event)">
                            <input type="file" x-ref="fileReenvioInput"
                                   @change="handleFile($event)"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                   accept=".pdf">
                            <div class="border-2 border-dashed rounded-xl p-6 text-center transition-all"
                                 :class="dragover ? 'border-blue-500 bg-blue-50' : (fileReenvio ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:border-blue-400 hover:bg-gray-50')">
                                <template x-if="!fileReenvio">
                                    <div>
                                        <svg class="w-10 h-10 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <p class="text-sm text-gray-600 mb-1">
                                            <span class="text-blue-600 font-medium">Clique para selecionar</span> ou arraste o arquivo
                                        </p>
                                        <p class="text-xs text-gray-500">Apenas PDF (máx. 30MB)</p>
                                    </div>
                                </template>
                                <template x-if="fileReenvio">
                                    <div class="flex items-center justify-center gap-3">
                                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div class="text-left">
                                            <p class="text-sm font-medium text-gray-900" x-text="fileReenvio.name"></p>
                                            <p class="text-xs text-gray-500" x-text="fileReenvioSize"></p>
                                        </div>
                                        <button type="button" @click.stop="resetFile()" class="p-1 text-red-500 hover:bg-red-50 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Footer --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-2xl flex justify-end gap-3">
                    <button type="button" @click="modalReenvio = false; resetFile()" 
                            class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="button" @click="enviarReenvio()"
                            :disabled="!fileReenvio || enviandoReenvio"
                            class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="!enviandoReenvio">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                        </template>
                        <template x-if="enviandoReenvio">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="enviandoReenvio ? 'Enviando...' : 'Reenviar Documento'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
