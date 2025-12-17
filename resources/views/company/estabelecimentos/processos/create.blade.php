@extends('layouts.company')

@section('title', 'Abrir Processo')
@section('page-title', 'Abrir Processo')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('company.estabelecimentos.processos.index', $estabelecimento->id) }}" 
           class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-900">Abrir Novo Processo</h2>
            <p class="text-sm text-gray-500">{{ $estabelecimento->nome_fantasia }} - {{ $estabelecimento->documento_formatado }}</p>
        </div>
    </div>

    {{-- Formulário --}}
    <form action="{{ route('company.estabelecimentos.processos.store', $estabelecimento->id) }}" method="POST" 
          class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @csrf

        {{-- Header do Card --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Selecione o Tipo de Processo
            </h3>
        </div>

        <div class="p-6 space-y-6">
            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Tipos de Processo Disponíveis --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de Processo *</label>
                
                @if($tiposProcesso->count() > 0)
                <div class="space-y-3">
                    @foreach($tiposProcesso as $tipo)
                    <label class="flex items-start gap-4 p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 cursor-pointer transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="tipo_processo_id" value="{{ $tipo->id }}" 
                               {{ old('tipo_processo_id') == $tipo->id ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" required>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-sm font-semibold text-gray-900">{{ $tipo->nome }}</p>
                                
                                {{-- Badge Anual --}}
                                @if($tipo->anual)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Anual
                                </span>
                                @endif

                                {{-- Badge Único por Estabelecimento --}}
                                @if($tipo->unico_por_estabelecimento)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    Único
                                </span>
                                @endif
                            </div>
                            
                            @if($tipo->descricao)
                            <p class="text-xs text-gray-500">{{ $tipo->descricao }}</p>
                            @endif

                            {{-- Informação adicional para processos anuais --}}
                            @if($tipo->anual)
                            <p class="text-xs text-amber-700 mt-2 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                Este processo deve ser renovado anualmente
                            </p>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>
                @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h3 class="mt-3 text-sm font-medium text-yellow-800">Nenhum tipo de processo disponível</h3>
                    <p class="mt-1 text-sm text-yellow-700">
                        Não há tipos de processo habilitados para abertura por usuários externos no momento.
                    </p>
                </div>
                @endif
            </div>

            {{-- Observação --}}
            @if($tiposProcesso->count() > 0)
            <div>
                <label for="observacao" class="block text-sm font-medium text-gray-700 mb-2">
                    Observação
                </label>
                <textarea name="observacao" id="observacao" rows="3" 
                          placeholder="Informações adicionais sobre o processo (opcional)"
                          class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">{{ old('observacao') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Máximo de 1000 caracteres</p>
            </div>
            @endif
        </div>

        {{-- Botões --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
            <a href="{{ route('company.estabelecimentos.processos.index', $estabelecimento->id) }}" 
               class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                Cancelar
            </a>
            @if($tiposProcesso->count() > 0)
            <button type="submit" 
                    class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition-all inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Abrir Processo
            </button>
            @endif
        </div>
    </form>

    {{-- Legenda --}}
    @if($tiposProcesso->count() > 0)
    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
        <h4 class="text-sm font-medium text-gray-700 mb-2">Legenda:</h4>
        <div class="flex flex-wrap gap-4 text-xs text-gray-600">
            <div class="flex items-center gap-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Anual
                </span>
                <span>- Processo que deve ser renovado todo ano</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-purple-100 text-purple-800">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Único
                </span>
                <span>- Apenas um processo deste tipo por estabelecimento</span>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
