@extends('layouts.admin')

@section('title', 'Detalhes do Responsável')
@section('page-title', 'Detalhes do Responsável')

@section('content')
<div class="space-y-4">
    {{-- Header com botão voltar --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.responsaveis.index') }}" 
           class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar
        </a>
        <h1 class="text-xl font-bold text-gray-900">{{ $responsavel->nome }}</h1>
    </div>

    {{-- Informações Básicas em uma linha --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">CPF</label>
                <p class="text-sm text-gray-900 mt-1 font-mono">{{ $responsavel->cpf_formatado }}</p>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Email</label>
                <p class="text-sm text-gray-900 mt-1 truncate" title="{{ $responsavel->email }}">{{ $responsavel->email }}</p>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Telefone</label>
                <p class="text-sm text-gray-900 mt-1">{{ $responsavel->telefone_formatado }}</p>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Tipo(s)</label>
                <div class="flex flex-wrap gap-1 mt-1">
                    @foreach($responsavel->tipos as $tipo)
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $tipo === 'legal' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                            {{ $tipo === 'legal' ? 'Legal' : 'Técnico' }}
                        </span>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Estabelecimentos</label>
                <p class="text-sm text-gray-900 mt-1 font-semibold">{{ $responsavel->estabelecimentos->count() }}</p>
            </div>
        </div>

        {{-- Informações Técnicas/Legais (se houver) --}}
        @php
            $temInfoExtra = false;
            foreach($responsavel->registros as $registro) {
                if(($registro->tipo === 'tecnico' && $registro->conselho) || ($registro->tipo === 'legal' && $registro->tipo_documento)) {
                    $temInfoExtra = true;
                    break;
                }
            }
        @endphp
        
        @if($temInfoExtra)
        <div class="border-t pt-4 mt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                @foreach($responsavel->registros as $registro)
                    @if($registro->tipo === 'tecnico' && $registro->conselho)
                    <div>
                        <h4 class="text-xs font-semibold text-gray-700 uppercase mb-2">Informações Técnicas</h4>
                                
                        <div class="space-y-2">
                            <div>
                                <label class="text-xs font-medium text-gray-500">Conselho</label>
                                <p class="text-sm text-gray-900">{{ $registro->conselho }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">Nº Registro</label>
                                <p class="text-sm text-gray-900">{{ $registro->numero_registro_conselho }}</p>
                            </div>
                            @if($registro->carteirinha_conselho)
                            <div>
                                <a href="{{ asset('storage/' . $registro->carteirinha_conselho) }}" 
                                   target="_blank"
                                   class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <div>
                        <h4 class="text-xs font-semibold text-gray-700 uppercase mb-2">Informações Legais</h4>
                        <div class="space-y-2">
                            <div>
                                <label class="text-xs font-medium text-gray-500">Tipo de Documento</label>
                                <p class="text-sm text-gray-900">{{ strtoupper($registro->tipo_documento) }}</p>
                            </div>
                            @if($registro->documento_identificacao)
                            <div>
                                <a href="{{ asset('storage/' . $registro->documento_identificacao) }}" 
                                   target="_blank"
                                   class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        @endif
    </div>

    {{-- Estabelecimentos Vinculados --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-900">Estabelecimentos Vinculados</h3>
            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded">
                {{ $responsavel->estabelecimentos->count() }}
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
