@extends('layouts.admin')

@section('title', 'Treinamentos')
@section('page-title', 'Treinamentos')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Gestão de treinamentos</h2>
            <p class="text-sm text-gray-600 mt-1">Cadastre eventos, organize apresentações interativas e acompanhe relatórios.</p>
        </div>
        <a href="{{ route('admin.treinamentos.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo evento
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($eventos->count() > 0)
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach($eventos as $evento)
                @php
                    $statusClasses = [
                        'planejado' => 'bg-slate-100 text-slate-700',
                        'aberto' => 'bg-emerald-100 text-emerald-700',
                        'encerrado' => 'bg-amber-100 text-amber-700',
                        'cancelado' => 'bg-rose-100 text-rose-700',
                    ];
                @endphp
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $evento->titulo }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $evento->local ?: 'Local não informado' }}</p>
                        </div>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$evento->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($evento->status) }}
                        </span>
                    </div>

                    <dl class="mt-5 grid grid-cols-2 gap-4 text-sm">
                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-gray-500">Inscritos</dt>
                            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $evento->inscricoes_count }}</dd>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-gray-500">Apresentações</dt>
                            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $evento->apresentacoes_count }}</dd>
                        </div>
                    </dl>

                    <div class="mt-5 space-y-2 text-sm text-gray-600">
                        <p><span class="font-medium text-gray-800">Início:</span> {{ $evento->data_inicio?->format('d/m/Y H:i') ?: 'Não definido' }}</p>
                        <p><span class="font-medium text-gray-800">Fim:</span> {{ $evento->data_fim?->format('d/m/Y H:i') ?: 'Não definido' }}</p>
                        <p>
                            <span class="font-medium text-gray-800">Inscrições:</span>
                            {{ $evento->inscricoes_ativas ? 'Ativas' : 'Fechadas' }}
                        </p>
                    </div>

                    @if($evento->descricao)
                        <p class="mt-4 text-sm leading-6 text-gray-600">{{ \Illuminate\Support\Str::limit($evento->descricao, 160) }}</p>
                    @endif

                    <div class="mt-6 flex flex-wrap gap-2">
                        <a href="{{ route('admin.treinamentos.show', $evento) }}" class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100">
                            Abrir evento
                        </a>
                        <a href="{{ route('admin.treinamentos.relatorios.inscritos', $evento) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                            Relatórios
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div>
            {{ $eventos->links() }}
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center shadow-sm">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0112 20.055a12.083 12.083 0 01-6.16-9.477L12 14zm0 0v6"/>
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-gray-900">Nenhum treinamento cadastrado</h3>
            <p class="mt-2 text-sm text-gray-500">Crie o primeiro evento para começar a gerenciar inscrições, apresentações e respostas ao vivo.</p>
            <a href="{{ route('admin.treinamentos.create') }}" class="mt-6 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                Criar primeiro evento
            </a>
        </div>
    @endif
</div>
@endsection