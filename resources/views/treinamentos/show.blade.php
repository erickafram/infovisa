@extends('layouts.admin')

@section('title', $evento->titulo)
@section('page-title', 'Detalhes do treinamento')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">{{ $evento->titulo }}</h2>
                <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">{{ ucfirst($evento->status) }}</span>
            </div>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600">{{ $evento->descricao ?: 'Sem descrição cadastrada para este treinamento.' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.treinamentos.edit', $evento) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Editar evento</a>
            <a href="{{ route('admin.treinamentos.relatorios.inscritos', $evento) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Inscritos</a>
            <a href="{{ route('admin.treinamentos.relatorios.respostas', $evento) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Respostas</a>
            <form method="POST" action="{{ route('admin.treinamentos.destroy', $evento) }}" onsubmit="return confirm('Deseja realmente excluir este treinamento?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">Excluir</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Corrija os campos abaixo:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2 space-y-6">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Local</p>
                    <p class="mt-2 text-lg font-semibold text-gray-900">{{ $evento->local ?: 'Não informado' }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Período</p>
                    <p class="mt-2 text-sm font-semibold text-gray-900">{{ $evento->data_inicio?->format('d/m/Y H:i') ?: 'Sem início' }}</p>
                    <p class="text-sm text-gray-600">até {{ $evento->data_fim?->format('d/m/Y H:i') ?: 'sem fim' }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Inscrições</p>
                    <p class="mt-2 text-lg font-semibold {{ $evento->inscricoes_ativas ? 'text-emerald-600' : 'text-rose-600' }}">{{ $evento->inscricoes_ativas ? 'Abertas' : 'Fechadas' }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Apresentações</h3>
                    <p class="mt-1 text-sm text-gray-500">Monte os slides, perguntas interativas e modo de apresentação ao vivo.</p>
                </div>

                <form method="POST" action="{{ route('admin.treinamentos.apresentacoes.store', $evento) }}" class="grid gap-4 border-b border-gray-200 bg-gray-50 px-6 py-5 lg:grid-cols-4">
                    @csrf
                    <div class="lg:col-span-2">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-600">Título</label>
                        <input type="text" name="titulo" value="{{ old('titulo') }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-600">Status</label>
                        <select name="status" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="rascunho">Rascunho</option>
                            <option value="publicada">Publicada</option>
                            <option value="arquivada">Arquivada</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">Nova apresentação</button>
                    </div>
                    <div class="lg:col-span-4">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-600">Descrição</label>
                        <textarea name="descricao" rows="3" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('descricao') }}</textarea>
                    </div>
                </form>

                <div class="divide-y divide-gray-200">
                    @forelse($evento->apresentacoes as $apresentacao)
                        <div class="flex flex-col gap-4 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <h4 class="text-base font-semibold text-gray-900">{{ $apresentacao->titulo }}</h4>
                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ ucfirst($apresentacao->status) }}</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-600">{{ $apresentacao->descricao ?: 'Sem descrição cadastrada.' }}</p>
                                <div class="mt-3 flex flex-wrap gap-4 text-xs font-medium text-gray-500">
                                    <span>{{ $apresentacao->slides_count }} slide(s)</span>
                                    <span>{{ $apresentacao->total_perguntas }} pergunta(s)</span>
                                    <span>{{ $apresentacao->total_respostas }} resposta(s)</span>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.treinamentos.apresentacoes.show', $apresentacao) }}" class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100">Gerenciar</a>
                                <a href="{{ route('admin.treinamentos.apresentacoes.apresentar', $apresentacao) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Apresentar</a>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center text-sm text-gray-500">Nenhuma apresentação cadastrada para este evento.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" x-data="{ copied: false, async copy() { await navigator.clipboard.writeText(@js($linkInscricao)); this.copied = true; setTimeout(() => this.copied = false, 2000); } }">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-lg font-semibold text-gray-900">Link de inscrição</h3>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $evento->inscricoes_ativas ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ $evento->inscricoes_ativas ? 'Ativo' : 'Fechado' }}</span>
                </div>
                <p class="mt-2 text-sm text-gray-500">Compartilhe este link com os participantes do evento.</p>
                <input type="text" readonly value="{{ $linkInscricao }}" class="mt-4 w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                <button type="button" @click="copy()" class="mt-3 w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <span x-show="!copied">Copiar link</span>
                    <span x-show="copied" x-cloak>Link copiado</span>
                </button>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Últimos inscritos</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($evento->inscricoes->take(8) as $inscricao)
                        <div class="px-6 py-4">
                            <p class="text-sm font-semibold text-gray-900">{{ $inscricao->nome }}</p>
                            <p class="text-sm text-gray-600">{{ $inscricao->email }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $inscricao->instituicao ?: 'Instituição não informada' }}{{ $inscricao->cargo ? ' • ' . $inscricao->cargo : '' }}</p>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-sm text-gray-500">Nenhuma inscrição registrada até o momento.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection