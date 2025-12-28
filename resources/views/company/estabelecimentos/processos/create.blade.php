@extends('layouts.company')

@section('title', 'Abrir Processo')
@section('page-title', 'Abrir Processo')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('company.estabelecimentos.processos.index', $estabelecimento->id) }}" 
           class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
        <h1 class="text-xl font-bold text-gray-900">Abrir Novo Processo</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $estabelecimento->nome_fantasia ?: $estabelecimento->razao_social }}</p>
    </div>

    {{-- Formulário --}}
    <form action="{{ route('company.estabelecimentos.processos.store', $estabelecimento->id) }}" method="POST">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            {{-- Erros --}}
            @if($errors->any())
            <div class="p-4 border-b border-gray-200 bg-red-50">
                <ul class="text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="p-6">
                @if($tiposProcesso->count() > 0)
                    <label class="block text-sm font-medium text-gray-700 mb-4">Selecione o tipo de processo</label>
                    
                    <div class="space-y-2">
                        @foreach($tiposProcesso as $tipo)
                        <label class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg cursor-pointer transition-all hover:border-blue-300 hover:bg-blue-50/50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="tipo_processo_id" value="{{ $tipo->id }}" 
                                   {{ old('tipo_processo_id') == $tipo->id ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" required>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-medium text-gray-900">{{ $tipo->nome }}</span>
                                    @if($tipo->anual)
                                        <span class="px-1.5 py-0.5 text-[10px] font-medium bg-amber-100 text-amber-700 rounded">Anual</span>
                                    @endif
                                    @if($tipo->unico_por_estabelecimento)
                                        <span class="px-1.5 py-0.5 text-[10px] font-medium bg-purple-100 text-purple-700 rounded">Único</span>
                                    @endif
                                    @if(isset($documentosObrigatorios[$tipo->id]) && count($documentosObrigatorios[$tipo->id]) > 0)
                                        <span class="px-1.5 py-0.5 text-[10px] font-medium bg-cyan-100 text-cyan-700 rounded">{{ count($documentosObrigatorios[$tipo->id]) }} doc(s)</span>
                                    @endif
                                </div>
                                @if($tipo->descricao)
                                    <p class="text-xs text-gray-500 mt-1">{{ $tipo->descricao }}</p>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>

                    {{-- Observação --}}
                    <div class="mt-6">
                        <label for="observacao" class="block text-sm font-medium text-gray-700 mb-2">Observação <span class="text-gray-400 font-normal">(opcional)</span></label>
                        <textarea name="observacao" id="observacao" rows="2" 
                                  placeholder="Informações adicionais..."
                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('observacao') }}</textarea>
                    </div>

                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="mt-3 text-sm text-gray-500">Nenhum tipo de processo disponível no momento.</p>
                    </div>
                @endif
            </div>

            {{-- Botões --}}
            @if($tiposProcesso->count() > 0)
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                <a href="{{ route('company.estabelecimentos.processos.index', $estabelecimento->id) }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Abrir Processo
                </button>
            </div>
            @endif
        </div>
    </form>

    {{-- Tipos de processo bloqueados --}}
    @if(isset($tiposBloqueados) && $tiposBloqueados->count() > 0)
    <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
        <h4 class="text-sm font-medium text-gray-700 mb-2">Processos já abertos</h4>
        <div class="space-y-2">
            @foreach($tiposBloqueados as $tipo)
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>{{ $tipo->nome }}</span>
                <span class="text-xs text-gray-400">
                    @if($tipo->unico_por_estabelecimento)
                        (já aberto - único por estabelecimento)
                    @elseif($tipo->anual)
                        (já aberto em {{ date('Y') }})
                    @endif
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    {{-- Info sobre documentos --}}
    @if($tiposProcesso->count() > 0)
    @endif
</div>
@endsection
