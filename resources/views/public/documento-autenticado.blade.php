@extends('layouts.public')

@section('title', 'Documento Autenticado - ' . $documento->numero_documento)

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    {{-- Banner de Autenticidade --}}
    <div class="mb-8 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="flex-shrink-0 w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-green-800">Documento Autêntico e Válido</h2>
                <p class="text-sm text-green-700 mt-0.5">Este documento foi verificado digitalmente e todas as informações são oficiais.</p>
            </div>
        </div>
    </div>

    {{-- Card Principal --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        {{-- Cabeçalho do Documento --}}
        <div class="bg-gradient-to-r from-blue-700 to-indigo-700 px-8 py-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-blue-200 text-xs font-medium uppercase tracking-wider mb-1">Documento Digital</p>
                    <h1 class="text-2xl font-bold text-white">{{ $documento->tipoDocumento->nome ?? 'Documento' }}</h1>
                    <div class="flex items-center gap-4 mt-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/15 backdrop-blur-sm rounded-full text-sm font-medium text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                            {{ $documento->numero_documento }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-500/30 backdrop-blur-sm rounded-full text-sm font-medium text-green-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Assinado
                        </span>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-blue-200 text-xs font-medium">Data de Emissão</p>
                    <p class="text-white font-semibold text-sm mt-0.5">{{ $documento->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <div class="p-8 space-y-8">
            {{-- Processo --}}
            @if($documento->processo)
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Processo</h3>
                <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Tipo do Processo</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $documento->processo->tipoProcesso->nome ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Número do Processo</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $documento->processo->numero_processo ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Abertura</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $documento->processo->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Estabelecimento --}}
            @if($documento->processo && $documento->processo->estabelecimento)
            @php $estab = $documento->processo->estabelecimento; @endphp
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Estabelecimento</h3>
                <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Nome Fantasia</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $estab->nome_fantasia ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Razão Social</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $estab->nome_razao_social }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">{{ $estab->tipo_pessoa === 'juridica' ? 'CNPJ' : 'CPF' }}</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $estab->documento_formatado }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Município</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $estab->cidade }}/{{ $estab->estado }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-xs text-gray-500 font-medium">Endereço</p>
                            <p class="text-sm text-gray-900 mt-0.5">{{ $estab->endereco_completo }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Assinaturas --}}
            @if($documento->assinaturas->count() > 0)
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">
                    Assinaturas Digitais ({{ $documento->assinaturas->count() }})
                </h3>
                <div class="space-y-3">
                    @foreach($documento->assinaturas as $index => $assinatura)
                    <div class="flex items-start gap-4 p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-sm">{{ $index + 1 }}º</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900">{{ $assinatura->usuarioInterno->nome }}</p>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1">
                                <span class="text-xs text-gray-600 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Assinado em {{ $assinatura->assinado_em->format('d/m/Y \à\s H:i:s') }}
                                </span>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1.5 font-mono break-all">Hash: {{ $assinatura->hash_assinatura }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Código de Autenticidade --}}
            <div class="bg-amber-50 rounded-xl p-5 border border-amber-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-amber-900">Código de Autenticidade</p>
                        <p class="text-sm text-amber-800 font-mono mt-1 break-all select-all">{{ $documento->codigo_autenticidade }}</p>
                        <p class="text-xs text-amber-600 mt-2">Use este código para verificar a autenticidade do documento a qualquer momento.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botões de Ação --}}
        <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row gap-3">
            <a href="{{ route('documento.autenticado.pdf', $documento->codigo_autenticidade) }}" 
               target="_blank"
               class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-all shadow-sm hover:shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Visualizar PDF Completo
            </a>
            <a href="{{ route('verificar.autenticidade.form') }}" 
               class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-gray-700 font-semibold rounded-xl border-2 border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Verificar Outro Documento
            </a>
        </div>
    </div>

    {{-- Informações de Rodapé --}}
    <div class="mt-6 bg-gray-50 rounded-xl border border-gray-200 p-5">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-gray-700 mb-2">Sobre este documento</p>
                <ul class="text-xs text-gray-600 space-y-1">
                    <li>Documento gerado eletronicamente pelo Sistema InfoVISA - Vigilância Sanitária</li>
                    <li>As assinaturas eletrônicas possuem validade jurídica conforme legislação vigente</li>
                    <li>A autenticidade pode ser verificada a qualquer momento pelo código acima</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-blue-600 transition-colors">
            ← Voltar para a página inicial
        </a>
    </div>
</div>
@endsection
