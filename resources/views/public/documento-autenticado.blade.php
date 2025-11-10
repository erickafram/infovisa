@extends('layouts.public')

@section('title', 'Documento Autenticado')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Mensagem de Sucesso --}}
    <div class="mb-6 p-6 bg-green-50 border-l-4 border-green-500 rounded-lg shadow">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h3 class="text-lg font-semibold text-green-800">✅ Documento Autêntico e Válido</h3>
                <p class="mt-1 text-sm text-green-700">
                    Este documento foi verificado e é autêntico. Todas as informações abaixo são oficiais.
                </p>
            </div>
        </div>
    </div>

    {{-- Informações do Documento --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        {{-- Cabeçalho --}}
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <h1 class="text-xl font-bold text-white">{{ $documento->tipoDocumento->nome }}</h1>
            <p class="text-blue-100 text-sm mt-1">{{ $documento->numero_documento }}</p>
        </div>

        {{-- Conteúdo --}}
        <div class="p-6">
            {{-- Processo --}}
            @if($documento->processo)
            <div class="mb-6 pb-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">📋 Processo</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Tipo:</span>
                        <p class="text-sm text-gray-900">{{ $documento->processo->tipoProcesso->nome ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Número:</span>
                        <p class="text-sm text-gray-900">{{ $documento->processo->numero }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Estabelecimento --}}
            @if($documento->processo && $documento->processo->estabelecimento)
            @php $estabelecimento = $documento->processo->estabelecimento; @endphp
            <div class="mb-6 pb-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">🏢 Estabelecimento</h2>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Nome Fantasia:</span>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->nome_fantasia ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Razão Social:</span>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->nome_razao_social }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">{{ $estabelecimento->tipo_pessoa === 'juridica' ? 'CNPJ' : 'CPF' }}:</span>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->documento_formatado }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Endereço:</span>
                        <p class="text-sm text-gray-900">
                            {{ $estabelecimento->logradouro }}, {{ $estabelecimento->numero }}
                            @if($estabelecimento->complemento), {{ $estabelecimento->complemento }}@endif
                            <br>{{ $estabelecimento->bairro }} - {{ $estabelecimento->cidade }}/{{ $estabelecimento->estado }}
                            <br>CEP: {{ $estabelecimento->cep }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Assinaturas --}}
            @if($documento->assinaturas->count() > 0)
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">✍️ Assinaturas Eletrônicas</h2>
                <div class="space-y-3">
                    @foreach($documento->assinaturas as $assinatura)
                    <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
                        <p class="text-sm font-medium text-blue-900">
                            {{ $assinatura->usuarioInterno->nome }}
                        </p>
                        <p class="text-xs text-blue-700 mt-1">
                            Assinado em {{ $assinatura->assinado_em->format('d/m/Y') }} às {{ $assinatura->assinado_em->format('H:i:s') }}
                        </p>
                        <p class="text-xs text-blue-600 mt-1 font-mono">
                            Hash: {{ $assinatura->hash_assinatura }}
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Código de Autenticidade --}}
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h3 class="text-sm font-semibold text-yellow-900 mb-2">🔒 Código de Autenticidade</h3>
                <p class="text-xs text-yellow-800 font-mono break-all">
                    {{ $documento->codigo_autenticidade }}
                </p>
            </div>

            {{-- Botões de Ação --}}
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('documento.autenticado.pdf', $documento->codigo_autenticidade) }}" 
                   target="_blank"
                   class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Visualizar PDF Completo
                </a>
                <a href="{{ route('verificar.autenticidade.form') }}" 
                   class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Verificar Outro Documento
                </a>
            </div>
        </div>
    </div>

    {{-- Informações Adicionais --}}
    <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
        <h3 class="text-sm font-semibold text-gray-900 mb-2">ℹ️ Informações</h3>
        <ul class="text-xs text-gray-700 space-y-1 ml-4 list-disc">
            <li>Este documento foi gerado eletronicamente pelo Sistema InfoVISA</li>
            <li>As assinaturas eletrônicas têm validade jurídica</li>
            <li>Você pode verificar a autenticidade a qualquer momento usando o código acima</li>
            <li>Guarde o código de autenticidade para futuras consultas</li>
        </ul>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('home') }}" class="text-sm text-blue-600 hover:text-blue-800">
            ← Voltar para a página inicial
        </a>
    </div>
</div>
@endsection
