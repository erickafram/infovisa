@extends('layouts.admin')

@section('title', 'Relatório de respostas')
@section('page-title', 'Relatório de respostas')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium text-blue-600">{{ $evento->titulo }}</p>
            <h2 class="text-2xl font-bold text-gray-900">Consolidação de respostas</h2>
            <p class="mt-1 text-sm text-gray-600">Acompanhe a participação por apresentação, slide e pergunta.</p>
        </div>
        <a href="{{ route('admin.treinamentos.show', $evento) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Voltar ao evento</a>
    </div>

    @forelse($evento->apresentacoes as $apresentacao)
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ $apresentacao->titulo }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ $apresentacao->descricao ?: 'Sem descrição cadastrada.' }}</p>
            </div>

            <div class="space-y-5 p-6">
                @forelse($apresentacao->slides as $slide)
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-full bg-blue-600 px-3 text-sm font-bold text-white">{{ $slide->ordem }}</span>
                            <h4 class="text-base font-semibold text-gray-900">{{ $slide->titulo }}</h4>
                        </div>

                        <div class="space-y-4">
                            @forelse($slide->perguntas as $pergunta)
                                <div class="rounded-2xl border border-white bg-white p-5 shadow-sm">
                                    <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            <h5 class="text-base font-semibold text-gray-900">{{ $pergunta->enunciado }}</h5>
                                            <p class="mt-1 break-all text-sm text-blue-700">{{ $pergunta->public_url }}</p>
                                            <p class="mt-2 text-sm text-gray-500">Total de respostas: <span class="font-semibold text-gray-800">{{ $pergunta->estatisticas['total_respostas'] }}</span></p>
                                        </div>
                                        <a href="{{ route('admin.treinamentos.perguntas.edit', $pergunta) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Editar pergunta</a>
                                    </div>

                                    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                        @foreach($pergunta->estatisticas['opcoes'] as $item)
                                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                                <p class="text-sm font-semibold text-gray-900">{{ $item['texto'] }}</p>
                                                <div class="mt-3 h-3 overflow-hidden rounded-full bg-gray-200">
                                                    <div class="h-full rounded-full bg-blue-600" style="width: {{ $item['percentual'] }}%"></div>
                                                </div>
                                                <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                                                    <span>{{ $item['quantidade'] }} resposta(s)</span>
                                                    <span class="font-semibold text-blue-700">{{ $item['percentual'] }}%</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-4 py-8 text-center text-sm text-gray-500">Nenhuma pergunta cadastrada neste slide.</div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-sm text-gray-500">Esta apresentação não possui slides.</div>
                @endforelse
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center text-sm text-gray-500 shadow-sm">Nenhuma apresentação encontrada para consolidar respostas.</div>
    @endforelse
</div>
@endsection