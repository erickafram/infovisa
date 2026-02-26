@extends('layouts.public')

@section('title', $pesquisa->titulo . ' – Pesquisa de Satisfação')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-10 px-4">
    <div class="max-w-2xl mx-auto">

        {{-- Cabeçalho --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-600 shadow-lg mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $pesquisa->titulo }}</h1>
            @if($pesquisa->descricao)
            <p class="text-gray-500 mt-2 text-sm">{{ $pesquisa->descricao }}</p>
            @endif
            <div class="mt-3 inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold
                {{ $pesquisa->tipo_publico === 'interno' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                {{ $pesquisa->tipo_publico_label }}
            </div>
        </div>

        {{-- Erros de validação --}}
        @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
            <p class="text-sm font-semibold text-red-700 mb-1">Por favor, corrija os erros abaixo:</p>
            <ul class="list-disc list-inside text-sm text-red-600 space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Formulário --}}
        <form action="{{ route('pesquisa.responder', $pesquisa->slug) }}" method="POST"
              class="space-y-6">
            @csrf

            {{-- Hidden fields: contexto OS/Estabelecimento/Usuário --}}
            @if(isset($ordemServico) && $ordemServico)
            <input type="hidden" name="ordem_servico_id" value="{{ $ordemServico->id }}">
            @endif
            @if(isset($estabelecimento) && $estabelecimento)
            <input type="hidden" name="estabelecimento_id" value="{{ $estabelecimento->id }}">
            @endif
            @if(isset($usuarioInternoId) && $usuarioInternoId)
            <input type="hidden" name="usuario_interno_id" value="{{ $usuarioInternoId }}">
            @endif
            @if(isset($usuarioExternoId) && $usuarioExternoId)
            <input type="hidden" name="usuario_externo_id" value="{{ $usuarioExternoId }}">
            @endif

            {{-- Contexto da OS/Estabelecimento (se vinculado) --}}
            @if(isset($ordemServico) && $ordemServico)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-900">OS #{{ $ordemServico->numero }}</p>
                        @if(isset($estabelecimento) && $estabelecimento)
                        <p class="text-xs text-gray-500 truncate">{{ $estabelecimento->nome_fantasia ?? $estabelecimento->razao_social }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @php
                $usuarioLogadoPesquisa = !empty($usuarioInternoId) || !empty($usuarioExternoId);
            @endphp

            {{-- Dados do respondente (somente quando não estiver logado) --}}
            @if(!$usuarioLogadoPesquisa)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">
                    Seus dados (opcional)
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nome</label>
                        <input type="text" name="respondente_nome"
                               value="{{ old('respondente_nome', $respondente['nome'] ?? '') }}"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               placeholder="Seu nome (opcional)">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">E-mail</label>
                        <input type="email" name="respondente_email"
                               value="{{ old('respondente_email', $respondente['email'] ?? '') }}"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               placeholder="seu@email.com (opcional)">
                    </div>
                </div>
            </div>
            @endif

            {{-- Perguntas --}}
            @foreach($pesquisa->perguntas as $i => $pergunta)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-start gap-3 mb-5">
                    <span class="flex-shrink-0 w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center">
                        {{ $i + 1 }}
                    </span>
                    <div>
                        <p class="text-base font-semibold text-gray-900">
                            {{ $pergunta->texto }}
                            @if($pergunta->obrigatoria)
                            <span class="text-red-500 ml-0.5">*</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $pergunta->tipo_label }}</p>
                    </div>
                </div>

                {{-- Escala 1-5 --}}
                @if($pergunta->tipo === 'escala_1_5')
                @php
                    $notaLabels = [1 => 'Muito ruim', 2 => 'Ruim', 3 => 'Regular', 4 => 'Bom', 5 => 'Ótimo'];
                    $notaBg     = [1 => '#ef4444', 2 => '#f97316', 3 => '#eab308', 4 => '#3b82f6', 5 => '#22c55e'];
                    $notaBorder = [1 => '#ef4444', 2 => '#f97316', 3 => '#eab308', 4 => '#3b82f6', 5 => '#22c55e'];
                    $notaText   = [1 => '#ef4444', 2 => '#f97316', 3 => '#ca8a04', 4 => '#2563eb', 5 => '#15803d'];
                @endphp
                <div class="flex flex-wrap gap-3 justify-center sm:justify-start"
                     id="escala-{{ $pergunta->id }}"
                     data-old="{{ old("resp_{$pergunta->id}", '') }}">
                    @foreach([1,2,3,4,5] as $nota)
                    <label class="flex flex-col items-center gap-1.5 cursor-pointer"
                           onclick="selecionarNota({{ $pergunta->id }}, {{ $nota }})">
                        <input type="radio"
                               name="resp_{{ $pergunta->id }}"
                               value="{{ $nota }}"
                               {{ old("resp_{$pergunta->id}") == $nota ? 'checked' : '' }}
                               class="sr-only"
                               id="nota-{{ $pergunta->id }}-{{ $nota }}"
                               @if($pergunta->obrigatoria) required @endif>
                        <div id="btn-{{ $pergunta->id }}-{{ $nota }}"
                             class="w-14 h-14 rounded-2xl border-2 flex items-center justify-center text-xl font-bold transition-all duration-150 select-none hover:scale-105"
                             style="border-color: {{ $notaBorder[$nota] }}; color: {{ $notaText[$nota] }}; background: #fff;">
                            {{ $nota }}
                        </div>
                        <span class="text-[11px] font-semibold text-gray-500 text-center w-16 leading-tight">
                            {{ $notaLabels[$nota] }}
                        </span>
                    </label>
                    @endforeach
                </div>

                {{-- Múltipla escolha --}}
                @elseif($pergunta->tipo === 'multipla_escolha')
                <div class="space-y-2.5">
                    @foreach($pergunta->opcoes as $opcao)
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio"
                               name="resp_{{ $pergunta->id }}"
                               value="{{ $opcao->id }}"
                               {{ old("resp_{$pergunta->id}") == $opcao->id ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600"
                               @if($pergunta->obrigatoria) required @endif>
                        <span class="text-sm text-gray-800">{{ $opcao->texto }}</span>
                    </label>
                    @endforeach
                </div>

                {{-- Texto livre --}}
                @elseif($pergunta->tipo === 'texto_livre')
                <textarea name="resp_{{ $pergunta->id }}"
                          rows="4"
                          maxlength="2000"
                          class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                          placeholder="Escreva sua resposta aqui..."
                          @if($pergunta->obrigatoria) required @endif>{{ old("resp_{$pergunta->id}") }}</textarea>
                @endif
            </div>
            @endforeach

            {{-- Botão enviar --}}
            <div class="flex justify-center pb-6">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white text-base font-semibold rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enviar Avaliação
                </button>
            </div>
        </form>

        <p class="text-center text-xs text-gray-400 pb-8">
            Suas respostas são anônimas e utilizadas apenas para melhorar os serviços da Vigilância Sanitária.
        </p>
    </div>
</div>

<script>
var bgMap = { 1:'#ef4444', 2:'#f97316', 3:'#eab308', 4:'#3b82f6', 5:'#22c55e' };

function selecionarNota(perguntaId, nota) {
    // Marcar o radio
    var radio = document.getElementById('nota-' + perguntaId + '-' + nota);
    if (radio) radio.checked = true;

    // Resetar todos os botões desta pergunta (1-5)
    for (var n = 1; n <= 5; n++) {
        var btn = document.getElementById('btn-' + perguntaId + '-' + n);
        if (btn) {
            btn.style.background    = '#fff';
            btn.style.color         = bgMap[n];
            btn.style.transform     = '';
            btn.style.boxShadow     = '';
        }
    }

    // Destacar o botão selecionado
    var sel = document.getElementById('btn-' + perguntaId + '-' + nota);
    if (sel) {
        sel.style.background  = bgMap[nota];
        sel.style.color       = '#fff';
        sel.style.transform   = 'scale(1.12)';
        sel.style.boxShadow   = '0 4px 14px rgba(0,0,0,0.18)';
    }
}

// Restaurar valores antigos (após validação com erro)
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-old]').forEach(function (el) {
        var old = parseInt(el.getAttribute('data-old'));
        var id  = el.id.replace('escala-', '');
        if (old >= 1 && old <= 5) selecionarNota(id, old);
    });
});
</script>
@endsection
