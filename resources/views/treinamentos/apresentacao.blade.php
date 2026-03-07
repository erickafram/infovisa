@extends('layouts.admin')

@section('title', $apresentacao->titulo)
@section('page-title', 'Apresentação do treinamento')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <p class="text-sm font-medium text-blue-600">{{ $apresentacao->evento->titulo }}</p>
            <h2 class="text-2xl font-bold text-gray-900">{{ $apresentacao->titulo }}</h2>
            <p class="mt-2 text-sm text-gray-600">{{ $apresentacao->descricao ?: 'Organize os slides, vincule perguntas e acompanhe as respostas.' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.treinamentos.show', $apresentacao->evento) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Voltar ao evento</a>
            <a href="{{ route('admin.treinamentos.slides.create', $apresentacao) }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Novo slide
            </a>
            @if($apresentacao->slides->count())
            <a href="{{ route('admin.treinamentos.apresentacoes.apresentar', $apresentacao) }}" class="inline-flex items-center rounded-lg bg-gradient-to-r from-indigo-600 to-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-700 hover:to-blue-700" target="_blank">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Apresentar (tela cheia)
            </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Importar PowerPoint --}}
    <div x-data="{ showUpload: false }" class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <button type="button" @click="showUpload = !showUpload" class="flex w-full items-center justify-between px-6 py-4 text-left transition hover:bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Importar do PowerPoint</p>
                    <p class="text-xs text-gray-500">Envie um arquivo .pptx para criar slides automaticamente</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="showUpload && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div x-show="showUpload" x-collapse>
            <form method="POST" action="{{ route('admin.treinamentos.apresentacoes.importar-pptx', $apresentacao) }}" enctype="multipart/form-data" class="border-t border-gray-100 px-6 py-5">
                @csrf
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Arquivo PowerPoint (.pptx)</label>
                        <input type="file" name="arquivo_pptx" accept=".pptx,.ppt" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1.5 text-xs text-gray-500">Cada slide do PowerPoint será convertido em um slide do sistema. Textos, formatações e imagens serão preservados. Máximo de 50 MB.</p>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-700 whitespace-nowrap">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Importar slides
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Configurações da apresentação --}}
    <form method="POST" action="{{ route('admin.treinamentos.apresentacoes.update', $apresentacao) }}" class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        @csrf
        @method('PUT')
        <div class="grid gap-4 p-6 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-gray-700">Título</label>
                <input type="text" name="titulo" value="{{ old('titulo', $apresentacao->titulo) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">Status</label>
                <select name="status" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['rascunho' => 'Rascunho', 'publicada' => 'Publicada', 'arquivada' => 'Arquivada'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $apresentacao->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Salvar apresentação</button>
            </div>
            <div class="lg:col-span-4">
                <label class="mb-2 block text-sm font-semibold text-gray-700">Descrição</label>
                <textarea name="descricao" rows="2" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('descricao', $apresentacao->descricao) }}</textarea>
            </div>
        </div>
    </form>

    {{-- Deck de Slides --}}
    <div class="space-y-5">
        @forelse($apresentacao->slides as $slide)
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                {{-- Cabeçalho do Slide --}}
                <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-4 lg:flex-row lg:items-center lg:justify-between bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl bg-blue-600 px-3 text-sm font-bold text-white shadow-sm">{{ $slide->ordem }}</span>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $slide->titulo }}</h3>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-gray-500">Slide de conteúdo</span>
                                @if($slide->perguntas->count())
                                    <span class="text-xs text-blue-600 font-medium">+ {{ $slide->perguntas->count() }} pergunta(s)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.treinamentos.slides.edit', $slide) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-50">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Editar slide
                        </a>
                        <a href="{{ route('admin.treinamentos.perguntas.create', $slide) }}" class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-medium text-blue-700 transition hover:bg-blue-100">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Nova pergunta
                        </a>
                        <form method="POST" action="{{ route('admin.treinamentos.slides.destroy', $slide) }}" onsubmit="return confirm('Excluir este slide e todas as suas perguntas?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center rounded-lg bg-red-50 border border-red-200 px-3 py-2 text-xs font-medium text-red-700 transition hover:bg-red-100">Excluir</button>
                        </form>
                    </div>
                </div>

                {{-- Preview mini do slide --}}
                @if($slide->conteudo)
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="relative rounded-xl bg-gradient-to-br from-slate-900 via-slate-800 to-blue-900 p-6 text-white shadow-inner overflow-hidden" style="min-height: 120px;">
                        <div class="relative z-10">
                            <p class="text-xs font-medium text-blue-300 uppercase tracking-widest mb-2">Prévia do conteúdo</p>
                            <div class="text-sm leading-6 text-slate-100 line-clamp-4 slide-html-content">{!! $slide->conteudo !!}</div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Perguntas do slide --}}
                @if($slide->perguntas->count())
                <div class="p-5 space-y-4">
                    @foreach($slide->perguntas as $pergunta)
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-3 mb-3">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700">❓ Pergunta</span>
                                        <h4 class="text-base font-semibold text-gray-900">{{ $pergunta->enunciado }}</h4>
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $pergunta->ativa ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">{{ $pergunta->ativa ? 'Ativa' : 'Inativa' }}</span>
                                    </div>
                                    <p class="break-all text-xs text-blue-600 mb-3">{{ $pergunta->public_url }}</p>

                                    {{-- Barras de resultado compactas --}}
                                    <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-4">
                                        @foreach($pergunta->estatisticas['opcoes'] as $item)
                                            <div class="rounded-lg border border-gray-200 bg-white p-3">
                                                <div class="flex items-center justify-between gap-2 mb-1">
                                                    <p class="text-xs font-semibold text-gray-900 truncate">{{ $item['texto'] }}</p>
                                                    <span class="text-xs font-bold text-blue-600">{{ $item['percentual'] }}%</span>
                                                </div>
                                                <div class="h-1.5 rounded-full bg-gray-200 overflow-hidden">
                                                    <div class="h-full rounded-full bg-blue-500" style="width: {{ $item['percentual'] }}%"></div>
                                                </div>
                                                <p class="mt-1 text-[10px] text-gray-500">{{ $item['quantidade'] }} resp.</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex flex-col gap-3 lg:w-48">
                                    @if($pergunta->qr_code_base64)
                                        <div class="rounded-xl border border-gray-200 bg-white p-2">
                                            <img src="data:image/png;base64,{{ $pergunta->qr_code_base64 }}" alt="QR Code" class="mx-auto h-36 w-36 object-contain">
                                        </div>
                                    @endif
                                    <div class="flex flex-wrap gap-1.5">
                                        <a href="{{ route('admin.treinamentos.perguntas.edit', $pergunta) }}" class="flex-1 inline-flex items-center justify-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-100">Editar</a>
                                        <form method="POST" action="{{ route('admin.treinamentos.perguntas.destroy', $pergunta) }}" onsubmit="return confirm('Excluir esta pergunta?');" class="flex-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full inline-flex items-center justify-center rounded-lg bg-red-50 border border-red-200 px-3 py-1.5 text-xs font-medium text-red-700 transition hover:bg-red-100">Excluir</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @else
                <div class="px-6 py-6 text-center">
                    <p class="text-xs text-gray-400">Nenhuma pergunta neste slide &mdash; <a href="{{ route('admin.treinamentos.perguntas.create', $slide) }}" class="text-blue-600 font-medium hover:underline">adicionar pergunta</a></p>
                </div>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center shadow-sm">
                <svg class="mx-auto w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <h3 class="text-lg font-semibold text-gray-900">Nenhum slide cadastrado</h3>
                <p class="mt-2 text-sm text-gray-500">Adicione slides para iniciar a apresentação e inserir perguntas interativas.</p>
                <a href="{{ route('admin.treinamentos.slides.create', $apresentacao) }}" class="mt-5 inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Criar primeiro slide
                </a>
            </div>
        @endforelse
    </div>

    {{-- Dica sobre o Modo Apresentação --}}
    @if($apresentacao->slides->count())
    <div class="rounded-xl border border-blue-200 bg-blue-50 px-5 py-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="text-sm text-blue-800">
                <p class="font-semibold">Dica: Modo Apresentação (PowerPoint)</p>
                <p class="mt-1">Clique em <strong>"Apresentar (tela cheia)"</strong> para projetar os slides em tela inteira. Use as <strong>setas do teclado</strong> ou clique nos lados da tela para navegar. Slides de pergunta mostram o QR Code e resultados em tempo real. Pressione <strong>F</strong> para tela cheia e <strong>S</strong> para abrir o painel de slides.</p>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    .slide-html-content img { max-width: 100%; height: auto; border-radius: 0.375rem; }
    .slide-html-content h1, .slide-html-content h2, .slide-html-content h3 { margin-bottom: 0.25rem; }
    .slide-html-content p { margin-bottom: 0.25rem; }
    .slide-html-content ul, .slide-html-content ol { padding-left: 1.25rem; }
</style>
@endsection