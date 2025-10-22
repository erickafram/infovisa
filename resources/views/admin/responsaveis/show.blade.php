@extends('layouts.admin')

@section('title', 'Detalhes do Responsável')
@section('page-title', 'Detalhes do Responsável')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    {{-- Header com botão voltar --}}
    <div>
        <a href="{{ route('admin.responsaveis.index') }}" 
           class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar para lista
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Coluna Esquerda - Dados do Responsável --}}
        <div class="lg:col-span-1">
            {{-- Card Principal --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 h-full">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-white">{{ strtoupper(substr($responsavel->nome, 0, 2)) }}</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $responsavel->nome }}</h2>
                    <p class="text-sm text-gray-500 mt-1">CPF: {{ $responsavel->cpf_formatado }}</p>
                </div>

                <div class="space-y-4 border-t pt-4">
                    {{-- Tipos --}}
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Tipos</label>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach($responsavel->tipos as $tipo)
                                @if($tipo === 'legal')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                        Responsável Legal
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Responsável Técnico
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Email</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $responsavel->email }}</p>
                    </div>

                    {{-- Telefone --}}
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Telefone</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $responsavel->telefone_formatado }}</p>
                    </div>

                    {{-- Informações Adicionais por Tipo --}}
                    @foreach($responsavel->registros as $registro)
                        @if($registro->tipo === 'tecnico' && $registro->conselho)
                            <div class="border-t pt-4 mt-4">
                                <h4 class="text-xs font-semibold text-gray-700 uppercase mb-3">Informações Técnicas</h4>
                                
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase">Conselho</label>
                                        <p class="text-sm text-gray-900 mt-1">{{ $registro->conselho }}</p>
                                    </div>

                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase">Nº Registro</label>
                                        <p class="text-sm text-gray-900 mt-1">{{ $registro->numero_registro_conselho }}</p>
                                    </div>

                                    @if($registro->carteirinha_conselho)
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase">Carteirinha</label>
                                        <a href="{{ asset('storage/' . $registro->carteirinha_conselho) }}" 
                                           target="_blank"
                                           class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 mt-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Ver Carteirinha
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($registro->tipo === 'legal' && $registro->tipo_documento)
                            <div class="border-t pt-4 mt-4">
                                <h4 class="text-xs font-semibold text-gray-700 uppercase mb-3">Informações Legais</h4>
                                
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase">Tipo de Documento</label>
                                        <p class="text-sm text-gray-900 mt-1">{{ strtoupper($registro->tipo_documento) }}</p>
                                    </div>

                                    @if($registro->documento_identificacao)
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase">Documento</label>
                                        <a href="{{ asset('storage/' . $registro->documento_identificacao) }}" 
                                           target="_blank"
                                           class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 mt-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Ver Documento
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Coluna Direita - Estabelecimentos Vinculados --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 h-full">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Estabelecimentos Vinculados</h3>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                        {{ $responsavel->estabelecimentos->count() }} estabelecimento(s)
                    </span>
                </div>

                @if($responsavel->estabelecimentos->count() > 0)
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($responsavel->estabelecimentos as $estabelecimento)
                        <div class="border border-gray-200 rounded-lg p-5 hover:border-blue-300 hover:shadow-md transition-all bg-gray-50">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    {{-- Header com Nome e Badges --}}
                                    <div class="flex items-start gap-3 mb-3">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-semibold text-gray-900 text-base mb-1 truncate">{{ $estabelecimento->nome_fantasia }}</h4>
                                            <p class="text-sm text-gray-600 mb-1 truncate">{{ $estabelecimento->razao_social }}</p>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5 flex-shrink-0">
                                            @if($estabelecimento->pivot->tipo_vinculo === 'legal')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 whitespace-nowrap">
                                                    Legal
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 whitespace-nowrap">
                                                    Técnico
                                                </span>
                                            @endif
                                            @if($estabelecimento->pivot->ativo)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 whitespace-nowrap">
                                                    Ativo
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 whitespace-nowrap">
                                                    Inativo
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    {{-- Informações --}}
                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span class="font-medium">CNPJ:</span>
                                            <span>{{ $estabelecimento->cnpj_formatado }}</span>
                                        </div>
                                        @if($estabelecimento->endereco_completo)
                                            <div class="flex items-start gap-2 text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                <span class="flex-1">{{ $estabelecimento->endereco_completo }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Botão Ver Detalhes --}}
                                <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}" 
                                   class="flex-shrink-0 px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors inline-flex items-center gap-2">
                                    <span>Ver detalhes</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum estabelecimento vinculado</h3>
                        <p class="mt-1 text-sm text-gray-500">Este responsável ainda não está vinculado a nenhum estabelecimento.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
