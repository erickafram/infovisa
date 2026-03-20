@extends('layouts.admin')

@section('title', 'Visualizar Documento')

@push('styles')
<style>
    .documento-conteudo-preservado div,
    .documento-conteudo-preservado li,
    .documento-conteudo-preservado td,
    .documento-conteudo-preservado th,
    .documento-conteudo-preservado h1,
    .documento-conteudo-preservado h2,
    .documento-conteudo-preservado h3,
    .documento-conteudo-preservado h4,
    .documento-conteudo-preservado h5,
    .documento-conteudo-preservado h6 {
        white-space: pre-wrap;
        white-space: break-spaces;
        word-break: break-word;
    }

    .documento-conteudo-preservado p,
    .documento-conteudo-preservado .MsoNormal {
        margin: 0 0 0.85rem;
        line-height: 1.45;
        white-space: pre-wrap;
        white-space: break-spaces;
        word-break: break-word;
    }

    .documento-conteudo-preservado .MsoNormal {
        margin-bottom: 1.15rem;
        line-height: 1.6;
    }

    .documento-conteudo-preservado p:last-child,
    .documento-conteudo-preservado .MsoNormal:last-child {
        margin-bottom: 0;
    }

    .documento-conteudo-preservado ul,
    .documento-conteudo-preservado ol {
        margin: 0 0 0.85rem 1.25rem;
        padding-left: 1.25rem;
    }

    .documento-conteudo-preservado li {
        margin-bottom: 0.25rem;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/40 to-amber-50/30">
    <div class="max-w-8xl mx-auto px-4 py-6">
        @php
            $documentoPodeEditar = $documento->podeEditar();
            $temAssinaturaFeita = $documento->assinaturas->where('status', 'assinado')->count() > 0;
            $totalAssinaturas = $documento->assinaturas->count();
            $totalAssinaturasFeitas = $documento->assinaturas->where('status', 'assinado')->count();
            $totalAssinaturasPendentes = max($totalAssinaturas - $totalAssinaturasFeitas, 0);
            $podeBaixarPdf = $documento->status !== 'rascunho' && $documento->arquivo_pdf;
            $percentualAssinaturas = $totalAssinaturas > 0
                ? (int) round(($totalAssinaturasFeitas / $totalAssinaturas) * 100)
                : 0;
            $nomeDocumento = $documento->nome ?? $documento->tipoDocumento->nome;
            $processo = $documento->processo;
            $nomeEstabelecimento = $processo?->estabelecimento->nome_fantasia
                ?? $processo?->estabelecimento->razao_social
                ?? 'Sem estabelecimento';
            $origemLabel = $documento->isLote()
                ? 'Lote'
                : ($processo ? 'Processo' : ($documento->os_id ? 'OS' : 'Avulso'));
            $statusInfo = match ($documento->status) {
                'rascunho' => [
                    'label' => 'Rascunho',
                    'descricao' => 'Documento em preparação, ainda aberto para ajustes.',
                    'badge' => 'bg-white/12 text-white border-white/15',
                    'hero' => 'from-slate-800 via-slate-700 to-slate-600',
                    'surface' => 'border-slate-200 bg-slate-50 text-slate-700',
                    'accent' => 'bg-slate-500',
                ],
                'aguardando_assinatura' => [
                    'label' => 'Aguardando assinaturas',
                    'descricao' => 'Faltam assinaturas para liberar a versão final.',
                    'badge' => 'bg-white/12 text-white border-white/15',
                    'hero' => 'from-blue-700 via-blue-600 to-cyan-600',
                    'surface' => 'border-blue-200 bg-blue-50 text-blue-700',
                    'accent' => 'bg-blue-500',
                ],
                default => [
                    'label' => 'Finalizado',
                    'descricao' => 'Documento concluído e disponível para consulta.',
                    'badge' => 'bg-white/12 text-white border-white/15',
                    'hero' => 'from-emerald-700 via-teal-600 to-cyan-600',
                    'surface' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                    'accent' => 'bg-emerald-500',
                ],
            };
            $usuarioEhAdmin = auth('interno')->user()?->isAdmin() ?? false;
        @endphp

        <div class="overflow-hidden rounded-2xl bg-gradient-to-r {{ $statusInfo['hero'] }} shadow-sm">
            <div class="px-4 py-4 sm:px-5 sm:py-5">
                <div class="flex items-center gap-1.5 text-[10px] text-white/65">
                    <a href="{{ route('admin.documentos.index') }}" class="hover:text-white transition">Documentos</a>
                    @if($processo)
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}" class="hover:text-white transition">
                            Processo {{ $processo->numero_processo }}
                        </a>
                    @endif
                </div>

                <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl min-w-0">
                        <div class="flex flex-wrap items-center gap-2 text-[10px] font-medium">
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 {{ $statusInfo['badge'] }}">
                                {{ $statusInfo['label'] }}
                            </span>
                            <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-2 py-0.5 text-white/90">
                                {{ $documento->tipoDocumento->nome }}
                            </span>
                        </div>

                        <h1 class="mt-3 text-xl font-semibold leading-tight text-white sm:text-2xl">{{ $nomeDocumento }}</h1>

                        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-white/75">
                            <span>{{ $documento->numero_documento }}</span>
                            @if($processo)
                                <span>{{ $processo->numero_processo }}</span>
                                <span class="truncate max-w-[260px] sm:max-w-none">{{ $nomeEstabelecimento }}</span>
                            @endif
                            @if($documento->os_id)
                                <span>OS #{{ $documento->os_id }}</span>
                            @endif
                        </div>

                        <p class="mt-2 max-w-2xl text-xs leading-5 text-white/80">{{ $statusInfo['descricao'] }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 lg:w-[240px] lg:flex-shrink-0">
                        <div class="rounded-xl border border-white/15 bg-white/10 px-3 py-2.5 backdrop-blur-sm">
                            <p class="text-[10px] uppercase tracking-wide text-white/60">Assinaturas</p>
                            <p class="mt-1 text-sm font-semibold text-white">{{ $totalAssinaturas > 0 ? $totalAssinaturasFeitas . '/' . $totalAssinaturas : 'Sem fluxo' }}</p>
                            <p class="text-[11px] text-white/70">{{ $totalAssinaturas > 0 ? $totalAssinaturasPendentes . ' pend.' : 'Sem assinantes' }}</p>
                        </div>
                        <div class="rounded-xl border border-white/15 bg-white/10 px-3 py-2.5 backdrop-blur-sm">
                            <p class="text-[10px] uppercase tracking-wide text-white/60">Resumo</p>
                            <p class="mt-1 text-sm font-semibold text-white">{{ $documentoPodeEditar ? 'Edição aberta' : 'Somente leitura' }}</p>
                            <p class="text-[11px] text-white/70">{{ $documento->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-1.5 border-t border-white/10 pt-3">
                    <button type="button"
                            onclick="abrirModalVisualizacao()"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-[11px] font-semibold text-gray-800 hover:bg-gray-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Visualizar
                    </button>
                    @if($documentoPodeEditar)
                        <a href="{{ route('admin.documentos.edit', $documento->id) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg border border-white/20 bg-white/10 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-white/15 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Editar
                        </a>
                    @endif
                    @if($podeBaixarPdf)
                        <a href="{{ route('admin.documentos.pdf', $documento->id) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg border border-white/20 bg-white/10 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-white/15 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            PDF
                        </a>
                    @endif
                    @if(!$temAssinaturaFeita && $totalAssinaturas > 0 && $documento->status !== 'assinado')
                        <button onclick="abrirModalGerenciarAssinantes()"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-white/20 bg-white/10 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-white/15 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Assinantes
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.5fr)_340px]">
            <div class="space-y-6">
                <section class="overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-sm">
                    <div class="border-b border-blue-100 bg-gradient-to-r from-blue-50 to-white px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-blue-700">Visão geral</p>
                    </div>
                    <div class="grid gap-3 p-4 md:grid-cols-3">
                        <div class="rounded-xl border {{ $statusInfo['surface'] }} px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide opacity-80">Status</p>
                            <p class="mt-1 text-sm font-semibold">{{ $statusInfo['label'] }}</p>
                            <p class="mt-1 text-xs leading-5 opacity-80">{{ $statusInfo['descricao'] }}</p>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
                            <p class="text-[11px] font-semibold uppercase tracking-wide opacity-80">Assinaturas</p>
                            <p class="mt-1 text-sm font-semibold">
                                @if($totalAssinaturas > 0)
                                    {{ $totalAssinaturasFeitas }} concluída(s), {{ $totalAssinaturasPendentes }} pendente(s)
                                @else
                                    Sem assinaturas configuradas
                                @endif
                            </p>
                            <p class="mt-1 text-xs leading-5 opacity-80">
                                {{ $totalAssinaturas > 0 ? $percentualAssinaturas . '% do fluxo concluído.' : 'O documento segue sem etapa de assinatura.' }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                            <p class="text-[11px] font-semibold uppercase tracking-wide opacity-80">Distribuição</p>
                            <p class="mt-1 text-sm font-semibold">
                                @if($documento->isLote())
                                    {{ count($documento->processos_ids) }} processo(s) no lote
                                @elseif($processo)
                                    Vinculado ao processo
                                @elseif($documento->os_id)
                                    Vinculado à OS
                                @else
                                    Documento avulso
                                @endif
                            </p>
                            <p class="mt-1 text-xs leading-5 opacity-80">
                                @if($documento->isLote())
                                    A distribuição acompanha a conclusão do documento.
                                @elseif($processo)
                                    {{ $nomeEstabelecimento }}
                                @elseif($documento->os_id)
                                    Ordem de serviço de origem vinculada.
                                @else
                                    Sem processo ou OS vinculados.
                                @endif
                            </p>
                        </div>
                    </div>
                </section>

                @if($totalAssinaturas > 0)
                    <section class="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
                        <div class="flex flex-col gap-3 border-b border-amber-100 bg-gradient-to-r from-amber-50 to-white px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700">Fluxo de assinatura</p>
                                <p class="mt-1 text-sm font-medium text-gray-900">{{ $totalAssinaturasFeitas }} de {{ $totalAssinaturas }} assinatura(s) concluídas</p>
                            </div>
                            @if((!$temAssinaturaFeita || $usuarioEhAdmin) && $documento->status !== 'assinado')
                                <button onclick="abrirModalGerenciarAssinantes()"
                                        class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-white px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Gerenciar assinantes
                                </button>
                            @endif
                        </div>

                        <div class="px-4 pt-4">
                            <div class="h-2 w-full overflow-hidden rounded-full bg-amber-100">
                                <div class="h-full rounded-full {{ $statusInfo['accent'] }} transition-all" style="width: {{ $percentualAssinaturas }}%"></div>
                            </div>
                        </div>

                        <div class="space-y-3 p-4">
                            @foreach($documento->assinaturas as $assinatura)
                                <div class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            @if($assinatura->usuarioInterno)
                                                <p class="truncate text-sm font-medium text-gray-900">{{ $assinatura->usuarioInterno->nome }}</p>
                                                <p class="text-xs text-gray-500">{{ $assinatura->usuarioInterno->cargo ?? 'Cargo não informado' }}</p>
                                            @else
                                                <p class="text-sm font-medium text-gray-500">Usuário removido</p>
                                                <p class="text-xs text-gray-400">Usuário não está mais no sistema</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 self-start sm:self-center">
                                        @if($assinatura->status === 'assinado')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-medium text-emerald-700">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Assinado
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-medium text-amber-700">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Pendente
                                            </span>
                                            @if((!$temAssinaturaFeita || $usuarioEhAdmin) && $documento->status !== 'assinado')
                                                <button onclick="removerAssinante({{ $assinatura->id }})"
                                                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-[11px] font-medium text-gray-600 hover:bg-gray-100 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Remover
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($documento->isLote())
                    <section class="overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-sm">
                        <div class="border-b border-emerald-100 bg-gradient-to-r from-emerald-50 to-white px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Distribuição em lote</p>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ count($documento->processos_ids) }} processo(s) vinculados</p>
                        </div>
                        <div class="p-4">
                            <details class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                                <summary class="cursor-pointer list-none text-xs font-semibold text-gray-700">Ver processos envolvidos</summary>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($documento->processosLote() as $procLote)
                                        <a href="{{ route('admin.estabelecimentos.processos.show', [$procLote->estabelecimento_id, $procLote->id]) }}"
                                           target="_blank"
                                           class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-medium text-gray-700 hover:bg-gray-100 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                            {{ $procLote->numero_processo }}
                                        </a>
                                    @endforeach
                                </div>
                            </details>
                        </div>
                    </section>
                @endif
            </div>

            <aside class="space-y-6 xl:sticky xl:top-6">
                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 bg-gradient-to-r from-slate-50 to-white px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-600">Resumo rápido</p>
                    </div>
                    <dl class="divide-y divide-gray-100">
                        <div class="px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Tipo</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $documento->tipoDocumento->nome }}</dd>
                        </div>
                        <div class="px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Criado por</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $documento->usuarioCriador->nome }}</dd>
                            <dd class="mt-1 text-xs text-gray-500">{{ $documento->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        @if($processo)
                            <div class="px-4 py-3">
                                <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Processo</dt>
                                <dd class="mt-1">
                                    <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}"
                                       class="text-sm font-medium text-blue-700 hover:underline">
                                        {{ $processo->numero_processo }}
                                    </a>
                                    <p class="mt-1 text-xs text-gray-500">{{ $nomeEstabelecimento }}</p>
                                </dd>
                            </div>
                        @endif
                        @if($documento->os_id)
                            <div class="px-4 py-3">
                                <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Ordem de serviço</dt>
                                <dd class="mt-1">
                                    <a href="{{ route('admin.ordens-servico.show', $documento->os_id) }}"
                                       class="text-sm font-medium text-blue-700 hover:underline">
                                        Abrir OS #{{ $documento->os_id }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                        <div class="px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Ações</dt>
                            <dd class="mt-3 space-y-2">
                                <a href="{{ route('admin.documentos.index') }}"
                                   class="flex items-center justify-between rounded-xl border border-gray-200 px-3 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                                    <span>Voltar para documentos</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                                @if($processo)
                                    <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}"
                                       class="flex items-center justify-between rounded-xl border border-gray-200 px-3 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                                        <span>Abrir processo vinculado</span>
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </section>
            </aside>
        </div>
    </div>
</div>

{{-- Modal para Visualização do Documento --}}
<div id="modalVisualizacaoDocumento" class="hidden fixed inset-0 bg-black bg-opacity-60 overflow-y-auto h-full w-full z-50">
    <div class="relative mx-auto my-6 w-11/12 max-w-6xl rounded-2xl bg-white shadow-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 bg-gray-50">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $documento->nome ?? $documento->tipoDocumento->nome }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ $documento->numero_documento }}</p>
            </div>
            <button type="button" onclick="fecharModalVisualizacao()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="bg-white max-h-[82vh] overflow-y-auto">
            @if($documento->status === 'rascunho')
                <div class="p-6">
                    <div class="prose prose-sm max-w-none border border-gray-200 p-4 rounded-xl bg-white shadow-sm documento-conteudo-preservado">
                        {!! $documento->conteudo !!}
                    </div>
                </div>
            @else
                <div class="border-t border-gray-100 bg-gray-100">
                    <iframe src="{{ route('admin.documentos.visualizar-pdf', $documento->id) }}"
                            class="w-full h-[82vh]"
                            frameborder="0"></iframe>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal para Gerenciar Assinantes --}}
<div id="modalGerenciarAssinantes" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4 pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">👥 Gerenciar Assinantes</h3>
            <button onclick="fecharModalGerenciarAssinantes()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form action="{{ route('admin.documentos.gerenciar-assinantes', $documento->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Selecione os usuários que devem assinar este documento
                </label>
                <div class="max-h-64 overflow-y-auto border border-gray-300 rounded-lg p-3 space-y-2">
                    @php
                        $usuarioLogado = auth('interno')->user();
                        $usuariosInternosQuery = \App\Models\UsuarioInterno::where('ativo', true);
                        
                        // Filtra por município do usuário logado
                        if ($usuarioLogado->municipio_id) {
                            $usuariosInternosQuery->where('municipio_id', $usuarioLogado->municipio_id);
                        }
                        
                        // Exclui administradores do sistema (sem município)
                        $usuariosInternosQuery->whereNotNull('municipio_id');
                        
                        $usuariosInternos = $usuariosInternosQuery->orderBy('nome')->get();
                        $assinantesAtuais = $documento->assinaturas->pluck('usuario_interno_id')->toArray();
                    @endphp
                    @foreach($usuariosInternos as $usuario)
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                            <input type="checkbox" 
                                   name="assinantes[]" 
                                   value="{{ $usuario->id }}"
                                   {{ in_array($usuario->id, $assinantesAtuais) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">{{ $usuario->nome }}</p>
                                <p class="text-xs text-gray-500">{{ $usuario->cargo ?? 'Cargo não informado' }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    Selecione os usuários que devem assinar o documento. A ordem será definida automaticamente.
                </p>
            </div>
            
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="fecharModalGerenciarAssinantes()" 
                        class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-lg hover:bg-gray-300">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalVisualizacao() {
    document.getElementById('modalVisualizacaoDocumento').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function fecharModalVisualizacao() {
    document.getElementById('modalVisualizacaoDocumento').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function abrirModalGerenciarAssinantes() {
    document.getElementById('modalGerenciarAssinantes').classList.remove('hidden');
}

function fecharModalGerenciarAssinantes() {
    document.getElementById('modalGerenciarAssinantes').classList.add('hidden');
}

function removerAssinante(assinaturaId) {
    if (confirm('Tem certeza que deseja remover este assinante?')) {
        const endpointTemplate = @json(route('admin.documentos.remover-assinante-post', ['id' => '__ASSINATURA_ID__']));
        const endpoint = endpointTemplate.replace('__ASSINATURA_ID__', assinaturaId);

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const contentType = response.headers.get('content-type') || '';
            let data = null;

            if (contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                data = {
                    success: false,
                    message: response.status === 404
                        ? 'Rota de remoção não encontrada (404). Verifique se as rotas foram atualizadas em produção.'
                        : `Resposta inesperada do servidor (${response.status}).`,
                    raw: text
                };
            }

            if (!response.ok && (!data || data.success !== false)) {
                data = {
                    success: false,
                    message: `Erro ao remover assinante (${response.status}).`
                };
            }

            return data;
        })
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Erro ao remover assinante');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao remover assinante');
        });
    }
}

// Fechar modal ao clicar fora
document.getElementById('modalGerenciarAssinantes')?.addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModalGerenciarAssinantes();
    }
});

document.getElementById('modalVisualizacaoDocumento')?.addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModalVisualizacao();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        fecharModalVisualizacao();
        fecharModalGerenciarAssinantes();
    }
});
</script>
@endsection
