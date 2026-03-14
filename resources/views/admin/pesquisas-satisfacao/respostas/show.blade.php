@extends('layouts.admin')

@section('title', 'Detalhes da Resposta')
@section('page-title', 'Pesquisas de Satisfação')

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('admin.relatorios.pesquisa-satisfacao', ['aba' => 'pesquisas']) }}"
           class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Detalhes da Resposta</h1>
            <p class="text-sm text-gray-600 mt-0.5">{{ $resposta->pesquisa->titulo ?? 'Pesquisa' }}</p>
        </div>
    </div>

    {{-- Info do Respondente --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Informações do Respondente</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="text-xs text-gray-500">Nome</label>
                <p class="text-sm font-medium text-gray-900">{{ $resposta->respondente_nome ?? 'Anônimo' }}</p>
            </div>
            <div>
                <label class="text-xs text-gray-500">E-mail</label>
                <p class="text-sm font-medium text-gray-900">{{ $resposta->respondente_email ?? '-' }}</p>
            </div>
            <div>
                <label class="text-xs text-gray-500">Tipo</label>
                <p class="mt-0.5">
                    @if($resposta->tipo_respondente === 'interno')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Usuário Interno</span>
                    @elseif($resposta->tipo_respondente === 'externo')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Usuário Externo</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Não identificado</span>
                    @endif
                </p>
            </div>
            <div>
                <label class="text-xs text-gray-500">Data de Envio</label>
                <p class="text-sm font-medium text-gray-900">{{ $resposta->created_at->format('d/m/Y H:i:s') }}</p>
            </div>
            <div>
                <label class="text-xs text-gray-500">IP</label>
                <p class="text-sm font-medium text-gray-900">{{ $resposta->ip_address ?? '-' }}</p>
            </div>
            @if($resposta->ordemServico)
            <div>
                <label class="text-xs text-gray-500">Ordem de Serviço</label>
                <p class="text-sm">
                    <a href="{{ route('admin.ordens-servico.show', $resposta->ordemServico) }}" 
                       class="font-medium text-blue-600 hover:text-blue-800 hover:underline">
                        OS #{{ $resposta->ordemServico->numero }}
                    </a>
                </p>
            </div>
            @endif
            @if($resposta->estabelecimento)
            <div class="sm:col-span-2">
                <label class="text-xs text-gray-500">Estabelecimento</label>
                <p class="text-sm font-medium text-gray-900">
                    {{ $resposta->estabelecimento->nome_fantasia ?? $resposta->estabelecimento->razao_social ?? '-' }}
                </p>
            </div>
            @endif
        </div>
    </div>

    {{-- Respostas --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Respostas</h3>
        
        @php
            $respostasArray = is_array($resposta->respostas) ? $resposta->respostas : json_decode($resposta->respostas, true);
        @endphp

        @if(!empty($respostasArray))
        <div class="space-y-4">
            @foreach($respostasArray as $i => $resp)
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-700">
                            <span class="inline-flex items-center justify-center w-5 h-5 bg-blue-100 text-blue-700 text-xs font-bold rounded-full mr-1.5">
                                {{ $i + 1 }}
                            </span>
                            {{ $resp['pergunta_texto'] ?? 'Pergunta' }}
                        </p>
                        <div class="mt-2 ml-7">
                            @if(($resp['tipo'] ?? '') === 'escala_1_5')
                                @php
                                    $nota = (int) ($resp['valor'] ?? 0);
                                    $labels = [1 => 'Muito ruim', 2 => 'Ruim', 3 => 'Regular', 4 => 'Bom', 5 => 'Ótimo'];
                                    $cores = [1 => 'bg-red-500', 2 => 'bg-orange-500', 3 => 'bg-yellow-500', 4 => 'bg-blue-500', 5 => 'bg-green-500'];
                                    $coresText = [1 => 'text-red-700', 2 => 'text-orange-700', 3 => 'text-yellow-700', 4 => 'text-blue-700', 5 => 'text-green-700'];
                                @endphp
                                <div class="flex items-center gap-2">
                                    @for($n = 1; $n <= 5; $n++)
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold 
                                        {{ $n === $nota ? $cores[$n] . ' text-white' : 'bg-gray-200 text-gray-400' }}">
                                        {{ $n }}
                                    </div>
                                    @endfor
                                    <span class="ml-2 text-sm font-medium {{ $coresText[$nota] ?? 'text-gray-600' }}">
                                        {{ $labels[$nota] ?? '' }}
                                    </span>
                                </div>
                            @else
                                <p class="text-sm text-gray-900 bg-white px-3 py-2 rounded border border-gray-200">
                                    {{ $resp['valor'] ?? '-' }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-200 text-gray-600 uppercase">
                        @if(($resp['tipo'] ?? '') === 'escala_1_5')
                            Escala 1-5
                        @elseif(($resp['tipo'] ?? '') === 'multipla_escolha')
                            Múltipla Escolha
                        @else
                            Texto Livre
                        @endif
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-500 text-center py-6">Nenhuma resposta registrada.</p>
        @endif
    </div>

    {{-- Ações --}}
    <div class="mt-6 flex items-center justify-between">
        <a href="{{ route('admin.relatorios.pesquisa-satisfacao', ['aba' => 'pesquisas']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar
        </a>
        <form method="POST" action="{{ route('admin.pesquisas-satisfacao.respostas.destroy', $resposta) }}"
              onsubmit="return confirm('Tem certeza que deseja excluir esta resposta?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Excluir Resposta
            </button>
        </form>
    </div>
</div>
@endsection
