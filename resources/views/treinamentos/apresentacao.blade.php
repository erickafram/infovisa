@extends('layouts.admin')

@section('title', $apresentacao->titulo)
@section('page-title', 'Apresentação do treinamento')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <p class="text-sm font-medium text-blue-600">{{ $apresentacao->evento->titulo }}</p>
            <h2 class="text-2xl font-bold text-gray-900">{{ $apresentacao->titulo }}</h2>
            <p class="mt-2 text-sm text-gray-600">{{ $apresentacao->descricao ?: 'Organize os slides, vincule perguntas e acompanhe as respostas por questão.' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.treinamentos.show', $apresentacao->evento) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Voltar ao evento</a>
            <a href="{{ route('admin.treinamentos.slides.create', $apresentacao) }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Novo slide</a>
            <a href="{{ route('admin.treinamentos.apresentacoes.apresentar', $apresentacao) }}" class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100">Modo apresentador</a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

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
                <textarea name="descricao" rows="3" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('descricao', $apresentacao->descricao) }}</textarea>
            </div>
        </div>
    </form>

    <div class="space-y-5">
        @forelse($apresentacao->slides as $slide)
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-5 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-full bg-blue-600 px-3 text-sm font-bold text-white">{{ $slide->ordem }}</span>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $slide->titulo }}</h3>
                        </div>
                        @if($slide->conteudo)
                            <div class="mt-4 whitespace-pre-line text-sm leading-6 text-gray-600">{{ $slide->conteudo }}</div>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.treinamentos.slides.edit', $slide) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Editar slide</a>
                        <a href="{{ route('admin.treinamentos.perguntas.create', $slide) }}" class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100">Nova pergunta</a>
                        <form method="POST" action="{{ route('admin.treinamentos.slides.destroy', $slide) }}" onsubmit="return confirm('Excluir este slide?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700">Excluir</button>
                        </form>
                    </div>
                </div>

                <div class="space-y-4 px-6 py-5">
                    @forelse($slide->perguntas as $pergunta)
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h4 class="text-base font-semibold text-gray-900">{{ $pergunta->enunciado }}</h4>
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $pergunta->ativa ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">{{ $pergunta->ativa ? 'Ativa' : 'Inativa' }}</span>
                                    </div>
                                    <p class="mt-2 break-all text-sm text-blue-700">{{ $pergunta->public_url }}</p>
                                    <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                        @foreach($pergunta->estatisticas['opcoes'] as $item)
                                            <div class="rounded-xl border border-white bg-white p-4 shadow-sm">
                                                <p class="text-sm font-semibold text-gray-900">{{ $item['texto'] }}</p>
                                                <p class="mt-2 text-2xl font-bold text-blue-600">{{ $item['percentual'] }}%</p>
                                                <p class="text-xs text-gray-500">{{ $item['quantidade'] }} resposta(s)</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="flex flex-col gap-3 lg:w-56">
                                    @if($pergunta->qr_code_base64)
                                        <div class="rounded-2xl border border-gray-200 bg-white p-3">
                                            <img src="data:image/png;base64,{{ $pergunta->qr_code_base64 }}" alt="QR Code da pergunta" class="mx-auto h-44 w-44 object-contain">
                                        </div>
                                    @endif
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('admin.treinamentos.perguntas.edit', $pergunta) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100">Editar</a>
                                        <form method="POST" action="{{ route('admin.treinamentos.perguntas.destroy', $pergunta) }}" onsubmit="return confirm('Excluir esta pergunta?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700">Excluir</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-4 py-8 text-center text-sm text-gray-500">Nenhuma pergunta vinculada a este slide.</div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Nenhum slide cadastrado</h3>
                <p class="mt-2 text-sm text-gray-500">Adicione slides para iniciar a apresentação e inserir perguntas interativas.</p>
                <a href="{{ route('admin.treinamentos.slides.create', $apresentacao) }}" class="mt-5 inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Criar primeiro slide</a>
            </div>
        @endforelse
    </div>
</div>
@endsection