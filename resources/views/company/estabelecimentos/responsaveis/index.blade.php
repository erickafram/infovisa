@extends('layouts.company')

@section('title', 'Gerenciar Responsáveis')
@section('page-title', 'Gerenciar Responsáveis')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('company.estabelecimentos.show', $estabelecimento->id) }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Responsáveis do Estabelecimento</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $estabelecimento->nome_fantasia }} - {{ $estabelecimento->documento_formatado }}</p>
            </div>
        </div>
    </div>

    {{-- Mensagem de sucesso --}}
    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    {{-- Mensagem de erro --}}
    @if(session('error'))
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Responsáveis Legais --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Responsáveis Legais</h2>
                    <p class="text-sm text-gray-500 mt-1">Obrigatório ter pelo menos um</p>
                </div>
                <a href="{{ route('company.estabelecimentos.responsaveis.create', [$estabelecimento->id, 'legal']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Adicionar
                </a>
            </div>

            @if($estabelecimento->responsaveisLegais && $estabelecimento->responsaveisLegais->count() > 0)
                <div class="space-y-3">
                    @foreach($estabelecimento->responsaveisLegais as $responsavel)
                    <div class="p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">{{ $responsavel->nome }}</h3>
                                <p class="text-sm text-gray-600 mt-1">CPF: {{ $responsavel->cpf_formatado ?? $responsavel->cpf }}</p>
                                @if($responsavel->email)
                                <p class="text-sm text-gray-600">Email: {{ $responsavel->email }}</p>
                                @endif
                                @if($responsavel->telefone)
                                <p class="text-sm text-gray-600">Telefone: {{ $responsavel->telefone_formatado ?? $responsavel->telefone }}</p>
                                @endif
                                @if($responsavel->documento_identificacao)
                                    <a href="{{ asset('storage/' . $responsavel->documento_identificacao) }}" 
                                       target="_blank"
                                       class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 mt-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Ver Documento
                                    </a>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('company.estabelecimentos.responsaveis.destroy', [$estabelecimento->id, $responsavel->id]) }}" 
                                  onsubmit="return confirm('Tem certeza que deseja remover este responsável?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="mt-2 text-sm">Nenhum responsável legal cadastrado</p>
                </div>
            @endif
        </div>

        {{-- Responsáveis Técnicos --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Responsáveis Técnicos</h2>
                    <p class="text-sm text-gray-500 mt-1">Opcional</p>
                </div>
                <a href="{{ route('company.estabelecimentos.responsaveis.create', [$estabelecimento->id, 'tecnico']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Adicionar
                </a>
            </div>

            @if($estabelecimento->responsaveisTecnicos && $estabelecimento->responsaveisTecnicos->count() > 0)
                <div class="space-y-3">
                    @foreach($estabelecimento->responsaveisTecnicos as $responsavel)
                    <div class="p-4 border border-gray-200 rounded-lg hover:border-green-300 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">{{ $responsavel->nome }}</h3>
                                <p class="text-sm text-gray-600 mt-1">CPF: {{ $responsavel->cpf_formatado ?? $responsavel->cpf }}</p>
                                @if($responsavel->email)
                                <p class="text-sm text-gray-600">Email: {{ $responsavel->email }}</p>
                                @endif
                                @if($responsavel->telefone)
                                <p class="text-sm text-gray-600">Telefone: {{ $responsavel->telefone_formatado ?? $responsavel->telefone }}</p>
                                @endif
                                @if($responsavel->conselho)
                                <p class="text-sm text-gray-600">Conselho: {{ $responsavel->conselho }} - {{ $responsavel->numero_registro_conselho ?? $responsavel->numero_registro }}</p>
                                @endif
                                @if($responsavel->carteirinha_conselho)
                                    <a href="{{ asset('storage/' . $responsavel->carteirinha_conselho) }}" 
                                       target="_blank"
                                       class="inline-flex items-center gap-1 text-sm text-green-600 hover:text-green-800 mt-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Ver Carteirinha
                                    </a>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('company.estabelecimentos.responsaveis.destroy', [$estabelecimento->id, $responsavel->id]) }}" 
                                  onsubmit="return confirm('Tem certeza que deseja remover este responsável?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="mt-2 text-sm">Nenhum responsável técnico cadastrado</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
