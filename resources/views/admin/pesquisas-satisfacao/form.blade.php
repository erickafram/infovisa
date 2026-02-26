@extends('layouts.admin')

@section('title', isset($pesquisa) ? 'Editar Pesquisa' : 'Nova Pesquisa de Satisfação')
@section('page-title', isset($pesquisa) ? 'Editar Pesquisa' : 'Nova Pesquisa de Satisfação')

@section('content')
@php
    /**
     * Monta os dados iniciais das perguntas para o Alpine.
     * Se for edição, popula a partir do modelo; se for criação, começa vazio.
     */
    $perguntasIniciais = isset($pesquisa)
        ? $pesquisa->perguntas->map(fn($p) => [
            'texto'      => $p->texto,
            'tipo'       => $p->tipo,
            'obrigatoria' => $p->obrigatoria,
            'opcoes'     => $p->opcoes->map(fn($o) => ['texto' => $o->texto])->values()->toArray(),
        ])->values()->toArray()
        : [];

    $setoresIniciais = isset($pesquisa) && $pesquisa->tipo_setores_ids
        ? $pesquisa->tipo_setores_ids
        : [];
@endphp

<div class="max-w-4xl mx-auto"
     x-data="pesquisaForm({{ json_encode($perguntasIniciais) }}, {{ isset($pesquisa) ? "'{$pesquisa->tipo_publico}'" : "'externo'" }}, {{ json_encode($setoresIniciais) }})">

    {{-- Header --}}
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('admin.configuracoes.pesquisas-satisfacao.index') }}"
           class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">
                {{ isset($pesquisa) ? 'Editar Pesquisa' : 'Nova Pesquisa de Satisfação' }}
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Defina o questionário, o público-alvo e as perguntas
            </p>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ isset($pesquisa) ? route('admin.configuracoes.pesquisas-satisfacao.update', $pesquisa) : route('admin.configuracoes.pesquisas-satisfacao.store') }}"
          method="POST" id="form-pesquisa">
        @csrf
        @if(isset($pesquisa)) @method('PUT') @endif

        {{-- Erros de validação --}}
        @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <ul class="list-disc list-inside text-sm text-red-700 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════════════ --}}
        {{-- Bloco 1 — Dados Gerais                                          --}}
        {{-- ═══════════════════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-5">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Dados Gerais
                </h2>
            </div>
            <div class="p-6 space-y-5">

                {{-- Título --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Título <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="titulo"
                           value="{{ old('titulo', $pesquisa->titulo ?? '') }}"
                           required maxlength="255"
                           class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="Ex: Avaliação da inspeção sanitária">
                </div>

                {{-- Descrição --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Descrição</label>
                    <textarea name="descricao" rows="3"
                              class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                              placeholder="Descreva o objetivo da pesquisa (opcional)">{{ old('descricao', $pesquisa->descricao ?? '') }}</textarea>
                </div>

                {{-- Tipo de Público --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Público-alvo <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                        {{-- Externo --}}
                        <label class="relative flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all"
                               :class="tipoPublico === 'externo' ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="tipo_publico" value="externo"
                                   x-model="tipoPublico" class="sr-only">
                            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Usuário Externo (Empresa)</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    As empresas avaliam a visita dos técnicos da vigilância
                                </p>
                            </div>
                            <div class="absolute top-3 right-3 w-4 h-4 rounded-full border-2 transition-all"
                                 :class="tipoPublico === 'externo' ? 'border-amber-500 bg-amber-500' : 'border-gray-300'">
                            </div>
                        </label>

                        {{-- Interno --}}
                        <label class="relative flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all"
                               :class="tipoPublico === 'interno' ? 'border-blue-400 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="tipo_publico" value="interno"
                                   x-model="tipoPublico" class="sr-only">
                            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Usuário Interno (Técnico)</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Os técnicos da vigilância avaliam os estabelecimentos
                                </p>
                            </div>
                            <div class="absolute top-3 right-3 w-4 h-4 rounded-full border-2 transition-all"
                                 :class="tipoPublico === 'interno' ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
                            </div>
                        </label>

                    </div>
                </div>

                {{-- Setores / Gerências vinculados --}}
                <div x-transition>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        <span x-show="tipoPublico === 'interno'">Setores / Gerências que devem responder</span>
                        <span x-show="tipoPublico === 'externo'">Setores / Gerências vinculados à pesquisa</span>
                        <span class="text-xs text-gray-400 font-normal">(deixe vazio para todos)</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($setores as $setor)
                        <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors hover:bg-gray-50"
                               :class="setoresIds.includes({{ $setor->id }}) ? 'border-blue-300 bg-blue-50' : 'border-gray-200'">
                            <input type="checkbox" name="tipo_setores_ids[]"
                                   value="{{ $setor->id }}"
                                   x-model="setoresIds"
                                   :value="{{ $setor->id }}"
                                   class="w-4 h-4 text-blue-600 rounded border-gray-300"
                                   {{ in_array($setor->id, $setoresIniciais) ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">{{ $setor->nome }}</span>
                        </label>
                        @endforeach
                    </div>
                    @if($setores->isEmpty())
                    <p class="text-xs text-gray-400 mt-1">Nenhum setor cadastrado. Cadastre setores em Configurações → Tipos de Setor.</p>
                    @endif
                </div>

                {{-- Ativo --}}
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="ativo" value="0">
                        <input type="checkbox" name="ativo" value="1" id="ativo"
                               {{ old('ativo', ($pesquisa->ativo ?? true)) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                    <label for="ativo" class="text-sm font-medium text-gray-700 cursor-pointer">
                        Pesquisa ativa (link público disponível)
                    </label>
                </div>

            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════ --}}
        {{-- Bloco 2 — Perguntas                                             --}}
        {{-- ═══════════════════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-5">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Perguntas
                    <span class="ml-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-bold"
                          x-text="perguntas.length"></span>
                </h2>
                <button type="button" @click="adicionarPergunta()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Adicionar Pergunta
                </button>
            </div>

            <div class="p-6">
                {{-- Sem perguntas --}}
                <div x-show="perguntas.length === 0" class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-gray-500">Nenhuma pergunta adicionada. Clique em "Adicionar Pergunta" para começar.</p>
                </div>

                {{-- Lista de perguntas --}}
                <div class="space-y-4" x-show="perguntas.length > 0">
                    <template x-for="(pergunta, idx) in perguntas" :key="idx">
                        <div class="border border-gray-200 rounded-xl p-5 bg-gray-50">

                            {{-- Cabeçalho da pergunta --}}
                            <div class="flex items-center justify-between mb-4">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Pergunta <span x-text="idx + 1"></span>
                                </span>
                                <button type="button" @click="removerPergunta(idx)"
                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Remover pergunta">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- hidden inputs --}}
                            <input type="hidden" :name="`perguntas[${idx}][tipo]`" :value="pergunta.tipo">
                            <input type="hidden" :name="`perguntas[${idx}][obrigatoria]`" :value="pergunta.obrigatoria ? 1 : 0">

                            {{-- Texto da pergunta --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    Texto da pergunta <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       :name="`perguntas[${idx}][texto]`"
                                       x-model="pergunta.texto"
                                       required
                                       maxlength="500"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                       placeholder="Ex: Como você avalia o atendimento do técnico?">
                            </div>

                            {{-- Tipo + Obrigatória --}}
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de resposta</label>
                                    <select x-model="pergunta.tipo"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                        <option value="escala_1_5">⭐ Nota de 1 a 5</option>
                                        <option value="multipla_escolha">☑ Múltipla Escolha</option>
                                        <option value="texto_livre">✏️ Texto Livre</option>
                                    </select>
                                </div>
                                <div class="flex items-end pb-0.5">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" x-model="pergunta.obrigatoria"
                                               class="w-4 h-4 text-blue-600 rounded border-gray-300">
                                        <span class="text-sm text-gray-700">Obrigatória</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Preview do tipo --}}
                            <div class="mb-4 p-3 bg-white rounded-lg border border-dashed border-gray-300">
                                {{-- Escala 1-5 --}}
                                <template x-if="pergunta.tipo === 'escala_1_5'">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-2">Preview — o respondente verá:</p>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <template x-for="(item, i) in [{n:1,label:'Muito ruim',cor:'border-red-400 text-red-600'},{n:2,label:'Ruim',cor:'border-orange-400 text-orange-600'},{n:3,label:'Regular',cor:'border-yellow-400 text-yellow-600'},{n:4,label:'Bom',cor:'border-blue-400 text-blue-600'},{n:5,label:'Ótimo',cor:'border-green-500 text-green-700'}]" :key="i">
                                                <div class="flex flex-col items-center gap-1">
                                                    <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-sm font-bold"
                                                         :class="item.cor">
                                                        <span x-text="item.n"></span>
                                                    </div>
                                                    <span class="text-[10px] font-medium text-gray-500 text-center" x-text="item.label"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Texto livre --}}
                                <template x-if="pergunta.tipo === 'texto_livre'">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-2">Preview — o respondente verá:</p>
                                        <div class="w-full h-16 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                                            <span class="text-xs text-gray-400">Campo de texto aberto</span>
                                        </div>
                                    </div>
                                </template>

                                {{-- Múltipla escolha --}}
                                <template x-if="pergunta.tipo === 'multipla_escolha'">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <p class="text-xs text-gray-500">Opções de resposta:</p>
                                            <button type="button"
                                                    @click="adicionarOpcao(idx)"
                                                    class="text-xs text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                Adicionar opção
                                            </button>
                                        </div>
                                        <div class="space-y-2">
                                            <template x-for="(opcao, oi) in pergunta.opcoes" :key="oi">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-4 h-4 rounded-full border-2 border-gray-300 flex-shrink-0"></div>
                                                    <input type="text"
                                                           :name="`perguntas[${idx}][opcoes][${oi}][texto]`"
                                                           x-model="opcao.texto"
                                                           required
                                                           class="flex-1 px-2.5 py-1.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                           placeholder="Texto da opção">
                                                    <button type="button" @click="removerOpcao(idx, oi)"
                                                            class="p-1 text-gray-400 hover:text-red-600 rounded transition-colors flex-shrink-0"
                                                            x-show="pergunta.opcoes.length > 1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                            <p x-show="pergunta.opcoes.length === 0" class="text-xs text-gray-400 italic">
                                                Clique em "Adicionar opção" para inserir as alternativas.
                                            </p>
                                        </div>
                                    </div>
                                </template>
                            </div>

                        </div>
                    </template>

                    {{-- Botão flutuante para adicionar mais --}}
                    <button type="button" @click="adicionarPergunta()"
                            class="w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-sm text-gray-500 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Adicionar mais uma pergunta
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════ --}}
        {{-- Botões de ação                                                  --}}
        {{-- ═══════════════════════════════════════════════════════════════ --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.configuracoes.pesquisas-satisfacao.index') }}"
               class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                Cancelar
            </a>
            <button type="submit"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-all">
                {{ isset($pesquisa) ? 'Salvar Alterações' : 'Criar Pesquisa' }}
            </button>
        </div>

    </form>
</div>

<script>
function pesquisaForm(perguntasIniciais, tipoPublicoInicial, setoresIniciais) {
    return {
        tipoPublico: tipoPublicoInicial,
        setoresIds: setoresIniciais.map(Number),
        perguntas: perguntasIniciais.map(p => ({
            texto: p.texto || '',
            tipo: p.tipo || 'escala_1_5',
            obrigatoria: p.obrigatoria !== undefined ? p.obrigatoria : true,
            opcoes: (p.opcoes || []).map(o => ({ texto: o.texto || '' })),
        })),

        adicionarPergunta() {
            this.perguntas.push({
                texto: '',
                tipo: 'escala_1_5',
                obrigatoria: true,
                opcoes: [],
            });
        },

        removerPergunta(idx) {
            if (confirm('Remover esta pergunta?')) {
                this.perguntas.splice(idx, 1);
            }
        },

        adicionarOpcao(idx) {
            this.perguntas[idx].opcoes.push({ texto: '' });
        },

        removerOpcao(perguntaIdx, opcaoIdx) {
            this.perguntas[perguntaIdx].opcoes.splice(opcaoIdx, 1);
        },
    };
}
</script>
@endsection
