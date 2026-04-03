@extends('layouts.admin')

@section('title', 'Relatório - Pesquisa de Satisfação')

@section('content')
<div class="max-w-8xl mx-auto space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">📊 Relatório de Satisfação</h1>
            <p class="text-xs text-gray-400 mt-0.5">Selecione uma ou mais pesquisas para gerar o relatório</p>
        </div>
        <div class="flex items-center gap-3">
            @if($pesquisasSelecionadas->count() > 0 && $dados)
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir
            </button>
            @endif
            <a href="{{ route('admin.relatorios.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Voltar
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-2">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.relatorios.pesquisa-satisfacao', array_filter(array_merge(request()->except('page', 'aba'), ['aba' => 'relatorio']))) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition {{ $aba === 'relatorio' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Relatório
            </a>
            <a href="{{ route('admin.relatorios.pesquisa-satisfacao', array_filter(array_merge(request()->except('page', 'pesquisa_ids', 'aba'), ['aba' => 'pesquisas']))) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition {{ $aba === 'pesquisas' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Pesquisas
            </a>
        </div>
    </div>

    @if($aba === 'pesquisas')
        @include('admin.relatorios.partials.pesquisa-satisfacao-respostas')
    @else

    {{-- Seletor de pesquisas (multi-select com checkboxes) --}}
    <form method="GET" action="{{ route('admin.relatorios.pesquisa-satisfacao') }}" id="formPesquisa">
        <input type="hidden" name="aba" value="relatorio">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-semibold text-gray-900">Pesquisas Disponíveis</p>
                <span class="text-[11px] text-gray-400" id="countSelecionadas">
                    {{ $pesquisasSelecionadas->count() }} selecionada(s)
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 mb-4">
                @foreach($pesquisas as $p)
                <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all
                    {{ in_array($p->id, $pesquisasSelecionadas->pluck('id')->toArray()) ? 'border-emerald-500 bg-emerald-50' : 'border-gray-100 hover:border-gray-200 bg-gray-50/50' }}">
                    <input type="checkbox" name="pesquisa_ids[]" value="{{ $p->id }}"
                           {{ in_array($p->id, $pesquisasSelecionadas->pluck('id')->toArray()) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $p->titulo }}</p>
                        <p class="text-[11px] text-gray-400">{{ $p->respostas_count }} respostas</p>
                    </div>
                </label>
                @endforeach
            </div>

            <div class="flex flex-wrap items-end gap-3 pt-3 border-t border-gray-100">
                <div>
                    <label class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">De</label>
                    <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="mt-1 text-sm border border-gray-200 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Até</label>
                    <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="mt-1 text-sm border border-gray-200 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-xl text-sm font-medium hover:bg-emerald-700 transition shadow-sm">
                    📊 Gerar Relatório
                </button>
            </div>
        </div>
    </form>

    @if($pesquisasSelecionadas->count() > 0 && $dados)

    {{-- Título do relatório --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900">
                    @if($pesquisasSelecionadas->count() === 1)
                        {{ $pesquisasSelecionadas->first()->titulo }}
                    @else
                        Relatório Combinado — {{ $pesquisasSelecionadas->count() }} pesquisas
                    @endif
                </h2>
                @if($pesquisasSelecionadas->count() > 1)
                <div class="flex flex-wrap gap-1.5 mt-2">
                    @foreach($pesquisasSelecionadas as $ps)
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-medium">{{ $ps->titulo }}</span>
                    @endforeach
                </div>
                @else
                <p class="text-xs text-gray-400 mt-0.5">{{ $pesquisasSelecionadas->first()->descricao ?? '' }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Resumo --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="text-center p-4 bg-white rounded-2xl shadow-sm border border-gray-100">
            <p class="text-3xl font-bold text-emerald-600">{{ $dados['total_respostas'] }}</p>
            <p class="text-xs text-gray-500 font-medium mt-1">Total Respostas</p>
        </div>
        <div class="text-center p-4 bg-white rounded-2xl shadow-sm border border-gray-100">
            <p class="text-3xl font-bold text-blue-600">{{ $dados['por_tipo_respondente']['interno'] }}</p>
            <p class="text-xs text-gray-500 font-medium mt-1">Técnicos (Interno)</p>
        </div>
        <div class="text-center p-4 bg-white rounded-2xl shadow-sm border border-gray-100">
            <p class="text-3xl font-bold text-purple-600">{{ $dados['por_tipo_respondente']['externo'] }}</p>
            <p class="text-xs text-gray-500 font-medium mt-1">Empresas (Externo)</p>
        </div>
        <div class="text-center p-4 bg-white rounded-2xl shadow-sm border border-gray-100">
            <p class="text-3xl font-bold text-gray-500">{{ $dados['por_tipo_respondente']['anonimo'] }}</p>
            <p class="text-xs text-gray-500 font-medium mt-1">Anônimos</p>
        </div>
    </div>

    {{-- Gráficos: Mensal + Respondentes + (se multi) Por Pesquisa --}}
    <div class="grid grid-cols-1 {{ $pesquisasSelecionadas->count() > 1 ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }} gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-1">Respostas por Mês</h3>
            <p class="text-[11px] text-gray-400 mb-3">Últimos 6 meses</p>
            <div style="height: 220px;"><canvas id="chartMensal"></canvas></div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-1">Perfil dos Respondentes</h3>
            <p class="text-[11px] text-gray-400 mb-3">Distribuição por tipo</p>
            <div style="height: 220px;"><canvas id="chartRespondentes"></canvas></div>
        </div>
        @if($pesquisasSelecionadas->count() > 1)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-1">Respostas por Pesquisa</h3>
            <p class="text-[11px] text-gray-400 mb-3">Comparativo entre pesquisas</p>
            <div style="height: 220px;"><canvas id="chartPorPesquisa"></canvas></div>
        </div>
        @endif
    </div>

    {{-- Média geral --}}
    @php
        $escalas = collect($dados['perguntas'])->where('tipo', 'escala_1_5');
        $mediaGeral = $escalas->count() > 0 ? round($escalas->avg('media'), 1) : null;
    @endphp
    @if($mediaGeral)
    <div class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl shadow-sm p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-100">Nota Média Geral</p>
                <p class="text-5xl font-bold mt-1">{{ $mediaGeral }} <span class="text-lg font-normal text-emerald-200">/ 5.0</span></p>
            </div>
            <div class="text-right">
                <div class="flex items-center gap-1">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-6 h-6 {{ $i <= round($mediaGeral) ? 'text-yellow-300' : 'text-emerald-300/40' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="text-xs text-emerald-200 mt-1">{{ $escalas->count() }} pergunta(s) de nota</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Perguntas --}}
    @foreach($dados['perguntas'] as $index => $pergunta)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full
                        @if($pergunta['tipo'] === 'escala_1_5') bg-amber-100 text-amber-700
                        @elseif($pergunta['tipo'] === 'multipla_escolha') bg-blue-100 text-blue-700
                        @else bg-gray-100 text-gray-600
                        @endif">
                        @if($pergunta['tipo'] === 'escala_1_5') Nota 1-5
                        @elseif($pergunta['tipo'] === 'multipla_escolha') Múltipla Escolha
                        @else Texto Livre
                        @endif
                    </span>
                </div>
                <h3 class="text-sm font-semibold text-gray-900">{{ $pergunta['texto'] }}</h3>
            </div>
            @if($pergunta['tipo'] === 'escala_1_5')
            <div class="text-center ml-4 flex-shrink-0 bg-emerald-50 rounded-xl px-4 py-2">
                <p class="text-2xl font-bold text-emerald-600">{{ $pergunta['media'] }}</p>
                <p class="text-[10px] text-emerald-600 font-medium">Média</p>
            </div>
            @endif
        </div>

        @if($pergunta['tipo'] === 'escala_1_5')
            <div style="height: 200px;">
                <canvas id="chartEscala{{ $index }}"></canvas>
            </div>
        @elseif($pergunta['tipo'] === 'multipla_escolha')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div style="height: 250px;">
                    <canvas id="chartMultipla{{ $index }}"></canvas>
                </div>
                <div class="space-y-2">
                    @php $totalMultipla = collect($pergunta['distribuicao'])->sum('count'); @endphp
                    @foreach($pergunta['distribuicao'] as $di => $opcao)
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-0.5">
                                    <span class="text-xs text-gray-700">{{ $opcao['texto'] }}</span>
                                    <span class="text-xs font-semibold text-gray-900">{{ $opcao['count'] }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all" style="width: {{ $totalMultipla > 0 ? round($opcao['count'] / $totalMultipla * 100) : 0 }}%; background-color: {{ ['#10b981','#3b82f6','#a855f7','#f59e0b','#ef4444','#14b8a6','#6366f1','#ec4899'][$di % 8] }};"></div>
                                </div>
                            </div>
                            <span class="text-[11px] text-gray-400 w-10 text-right">{{ $totalMultipla > 0 ? round($opcao['count'] / $totalMultipla * 100) : 0 }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($pergunta['tipo'] === 'texto_livre')
            @if(count($pergunta['textos_livres']) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-72 overflow-y-auto">
                    @foreach($pergunta['textos_livres'] as $tl)
                        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                            <p class="text-xs text-gray-700 leading-relaxed">"{{ $tl['texto'] }}"</p>
                            <p class="text-[10px] text-gray-400 mt-2">{{ $tl['respondente'] }} · {{ $tl['data'] }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="text-[11px] text-gray-400 mt-2">{{ count($pergunta['textos_livres']) }} resposta(s)</p>
            @else
                <p class="text-xs text-gray-400 italic">Nenhuma resposta de texto.</p>
            @endif
        @endif
    </div>
    @endforeach

    {{-- Análise de IA --}}
    @if($iaPesquisaSatisfacaoAtiva)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" id="secaoAnaliseIA">
        <div class="p-6">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Análise Inteligente (IA)</h3>
                        <p class="text-[11px] text-gray-400">Insights e recomendações para tomada de decisão</p>
                    </div>
                </div>
                <button type="button" id="btnGerarAnaliseIA"
                    onclick="gerarAnaliseIA()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-xl text-sm font-medium hover:from-violet-700 hover:to-purple-700 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span id="btnTextoAnaliseIA">Gerar Análise com IA</span>
                </button>
            </div>
        </div>

        {{-- Loading --}}
        <div id="analiseIALoading" class="hidden px-6 pb-6">
            <div class="flex items-center gap-3 p-4 bg-violet-50 rounded-xl border border-violet-100">
                <svg class="w-5 h-5 text-violet-600 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <p class="text-sm font-medium text-violet-800">Analisando dados com Inteligência Artificial...</p>
                    <p class="text-[11px] text-violet-500 mt-0.5">Isso pode levar alguns segundos</p>
                </div>
            </div>
        </div>

        {{-- Erro --}}
        <div id="analiseIAErro" class="hidden px-6 pb-6">
            <div class="flex items-center gap-3 p-4 bg-red-50 rounded-xl border border-red-100">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm text-red-700" id="analiseIAErroTexto"></p>
            </div>
        </div>

        {{-- Resultado --}}
        <div id="analiseIAResultado" class="hidden">
            <div class="border-t border-gray-100 px-6 py-5">
                <div class="prose prose-sm max-w-none
                    prose-headings:text-gray-900 prose-headings:font-bold prose-headings:mt-5 prose-headings:mb-2
                    prose-h2:text-base prose-h2:border-b prose-h2:border-gray-100 prose-h2:pb-2
                    prose-p:text-gray-600 prose-p:leading-relaxed
                    prose-li:text-gray-600
                    prose-strong:text-gray-800
                    prose-ul:my-2 prose-ol:my-2" id="analiseIAConteudo">
                </div>
                <div class="flex items-center justify-between mt-5 pt-4 border-t border-gray-100">
                    <p class="text-[10px] text-gray-400">
                        <svg class="w-3 h-3 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Análise gerada por IA — revise antes de tomar decisões
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="copiarAnaliseIA()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-500 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                            Copiar
                        </button>
                        <button type="button" onclick="gerarAnaliseIA()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-violet-600 bg-violet-50 border border-violet-200 rounded-lg hover:bg-violet-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Gerar Novamente
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @elseif($pesquisasSelecionadas->count() === 0)
    {{-- Estado vazio --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-20 text-center">
        <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center mx-auto mb-4">
            <span class="text-3xl">📊</span>
        </div>
        <h3 class="text-base font-semibold text-gray-900">Selecione as pesquisas acima</h3>
        <p class="text-sm text-gray-400 mt-1 max-w-md mx-auto">Marque uma ou mais pesquisas e clique em "Gerar Relatório"</p>
    </div>
    @endif
    @endif
</div>
@endsection

@push('scripts')
@if($aba === 'relatorio' && $pesquisasSelecionadas->count() > 0 && $dados)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cores = ['rgba(16,185,129,0.8)','rgba(59,130,246,0.8)','rgba(168,85,247,0.8)','rgba(245,158,11,0.8)','rgba(239,68,68,0.8)','rgba(20,184,166,0.8)','rgba(99,102,241,0.8)','rgba(236,72,153,0.8)'];

    // Respostas por Mês
    const dadosMes = @json($dados['por_mes']);
    new Chart(document.getElementById('chartMensal'), {
        type: 'bar',
        data: {
            labels: dadosMes.map(d => d.label),
            datasets: [{ data: dadosMes.map(d => d.count), backgroundColor: 'rgba(16,185,129,0.8)', borderColor: '#10b981', borderWidth: 1, borderRadius: 8 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: '#f3f4f6' } },
                x: { ticks: { font: { size: 11 } }, grid: { display: false } }
            }
        }
    });

    // Perfil dos respondentes
    const resp = @json($dados['por_tipo_respondente']);
    new Chart(document.getElementById('chartRespondentes'), {
        type: 'doughnut',
        data: {
            labels: ['Técnicos (Interno)', 'Empresas (Externo)', 'Anônimos'],
            datasets: [{ data: [resp.interno, resp.externo, resp.anonimo], backgroundColor: ['rgba(59,130,246,0.8)','rgba(168,85,247,0.8)','rgba(156,163,175,0.6)'], borderColor: '#fff', borderWidth: 3 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '60%',
            plugins: { legend: { position: 'bottom', labels: { font: { size: 12 }, padding: 16, usePointStyle: true, pointStyle: 'circle' } } }
        }
    });

    // Por Pesquisa (se multi)
    const porPesquisaEl = document.getElementById('chartPorPesquisa');
    if (porPesquisaEl) {
        const porPesquisa = @json($dados['por_pesquisa']);
        new Chart(porPesquisaEl, {
            type: 'bar',
            data: {
                labels: porPesquisa.map(d => d.titulo),
                datasets: [{ data: porPesquisa.map(d => d.count), backgroundColor: cores.slice(0, porPesquisa.length), borderRadius: 8 }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: '#f3f4f6' } },
                    y: { ticks: { font: { size: 11 } }, grid: { display: false } }
                }
            }
        });
    }

    // Gráficos por pergunta
    const perguntas = @json($dados['perguntas']);
    perguntas.forEach(function(p, idx) {
        if (p.tipo === 'escala_1_5') {
            const el = document.getElementById('chartEscala' + idx);
            if (!el) return;
            const dist = p.distribuicao;
            const total = (dist['1']||0)+(dist['2']||0)+(dist['3']||0)+(dist['4']||0)+(dist['5']||0);
            new Chart(el, {
                type: 'bar',
                data: {
                    labels: ['⭐ 1 - Péssimo','⭐ 2 - Ruim','⭐ 3 - Regular','⭐ 4 - Bom','⭐ 5 - Ótimo'],
                    datasets: [{ data: [dist['1']||0,dist['2']||0,dist['3']||0,dist['4']||0,dist['5']||0], backgroundColor: ['#ef4444','#f97316','#eab308','#22c55e','#10b981'], borderRadius: 8 }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(ctx) { const pct = total > 0 ? Math.round(ctx.raw/total*100) : 0; return ctx.raw + ' (' + pct + '%)'; } } } },
                    scales: {
                        x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: '#f3f4f6' } },
                        y: { ticks: { font: { size: 12 } }, grid: { display: false } }
                    }
                }
            });
        }
        if (p.tipo === 'multipla_escolha') {
            const el = document.getElementById('chartMultipla' + idx);
            if (!el) return;
            const dist = p.distribuicao;
            new Chart(el, {
                type: 'doughnut',
                data: {
                    labels: dist.map(d => d.texto),
                    datasets: [{ data: dist.map(d => d.count), backgroundColor: cores.slice(0, dist.length), borderColor: '#fff', borderWidth: 3 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '55%', plugins: { legend: { display: false } } }
            });
        }
    });
});

// --- Análise IA ---
let analiseIARaw = '';

function montarDadosRelatorio() {
    const dados = @json($dados);
    let texto = '';

    texto += `RESUMO GERAL:\n`;
    texto += `- Total de respostas: ${dados.total_respostas}\n`;
    texto += `- Respondentes internos (técnicos): ${dados.por_tipo_respondente.interno}\n`;
    texto += `- Respondentes externos (empresas): ${dados.por_tipo_respondente.externo}\n`;
    texto += `- Respondentes anônimos: ${dados.por_tipo_respondente.anonimo}\n\n`;

    texto += `RESPOSTAS POR MÊS (últimos 6 meses):\n`;
    dados.por_mes.forEach(m => {
        texto += `- ${m.label}: ${m.count} respostas\n`;
    });
    texto += '\n';

    if (dados.por_pesquisa && dados.por_pesquisa.length > 1) {
        texto += `RESPOSTAS POR PESQUISA:\n`;
        dados.por_pesquisa.forEach(p => {
            texto += `- ${p.titulo}: ${p.count} respostas\n`;
        });
        texto += '\n';
    }

    texto += `ANÁLISE POR PERGUNTA:\n\n`;
    dados.perguntas.forEach((p, i) => {
        texto += `Pergunta ${i + 1}: "${p.texto}" (Tipo: ${p.tipo})\n`;
        if (p.tipo === 'escala_1_5') {
            texto += `  Média: ${p.media}/5.0\n`;
            texto += `  Distribuição: 1-Péssimo: ${p.distribuicao['1']||0}, 2-Ruim: ${p.distribuicao['2']||0}, 3-Regular: ${p.distribuicao['3']||0}, 4-Bom: ${p.distribuicao['4']||0}, 5-Ótimo: ${p.distribuicao['5']||0}\n`;
            texto += `  Total de respostas: ${p.total||0}\n`;
        } else if (p.tipo === 'multipla_escolha') {
            texto += `  Opções:\n`;
            p.distribuicao.forEach(o => {
                texto += `    - ${o.texto}: ${o.count} respostas\n`;
            });
        } else if (p.tipo === 'texto_livre') {
            texto += `  Respostas de texto livre (${p.textos_livres.length}):\n`;
            p.textos_livres.slice(0, 30).forEach(t => {
                texto += `    - "${t.texto}" (${t.respondente}, ${t.data})\n`;
            });
            if (p.textos_livres.length > 30) {
                texto += `    ... e mais ${p.textos_livres.length - 30} respostas\n`;
            }
        }
        texto += '\n';
    });

    return texto;
}

function gerarAnaliseIA() {
    const btn = document.getElementById('btnGerarAnaliseIA');
    const btnTexto = document.getElementById('btnTextoAnaliseIA');
    const loading = document.getElementById('analiseIALoading');
    const erro = document.getElementById('analiseIAErro');
    const resultado = document.getElementById('analiseIAResultado');

    btn.disabled = true;
    btnTexto.textContent = 'Analisando...';
    loading.classList.remove('hidden');
    erro.classList.add('hidden');
    resultado.classList.add('hidden');

    const dadosRelatorio = montarDadosRelatorio();

    fetch('{{ route("admin.relatorios.pesquisa-satisfacao.analise-ia") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
        body: JSON.stringify({ dados_relatorio: dadosRelatorio }),
    })
    .then(response => response.json())
    .then(data => {
        loading.classList.add('hidden');
        btn.disabled = false;
        btnTexto.textContent = 'Gerar Análise com IA';

        if (data.success) {
            analiseIARaw = data.analise;
            document.getElementById('analiseIAConteudo').innerHTML = markdownToHtml(data.analise);
            resultado.classList.remove('hidden');
            document.getElementById('secaoAnaliseIA').scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            document.getElementById('analiseIAErroTexto').textContent = data.error || 'Erro desconhecido ao gerar análise.';
            erro.classList.remove('hidden');
        }
    })
    .catch(err => {
        loading.classList.add('hidden');
        btn.disabled = false;
        btnTexto.textContent = 'Gerar Análise com IA';
        document.getElementById('analiseIAErroTexto').textContent = 'Erro de conexão. Verifique sua internet e tente novamente.';
        erro.classList.remove('hidden');
    });
}

function copiarAnaliseIA() {
    if (analiseIARaw) {
        navigator.clipboard.writeText(analiseIARaw).then(() => {
            const btn = event.target.closest('button');
            const textoOriginal = btn.innerHTML;
            btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copiado!';
            setTimeout(() => { btn.innerHTML = textoOriginal; }, 2000);
        });
    }
}

function markdownToHtml(md) {
    let html = md
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/^### (.+)$/gm, '<h3>$1</h3>')
        .replace(/^## (.+)$/gm, '<h2>$1</h2>')
        .replace(/^# (.+)$/gm, '<h1>$1</h1>')
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')
        .replace(/^- (.+)$/gm, '<li>$1</li>')
        .replace(/^(\d+)\. (.+)$/gm, '<li>$2</li>')
        .replace(/(<li>.*<\/li>\n?)+/g, function(match) {
            return '<ul>' + match + '</ul>';
        })
        .replace(/\n{2,}/g, '</p><p>')
        .replace(/\n/g, '<br>');
    html = '<p>' + html + '</p>';
    html = html.replace(/<p><h([1-3])>/g, '<h$1>').replace(/<\/h([1-3])><\/p>/g, '</h$1>');
    html = html.replace(/<p><ul>/g, '<ul>').replace(/<\/ul><\/p>/g, '</ul>');
    html = html.replace(/<p><\/p>/g, '');
    return html;
}
</script>
@endif
@endpush
