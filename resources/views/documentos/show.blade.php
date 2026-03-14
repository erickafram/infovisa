@extends('layouts.admin')

@section('title', 'Visualizar Documento')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-8xl mx-auto px-4 py-6">
        @php
            $documentoPodeEditar = $documento->podeEditar();
            $temAssinaturaFeita = $documento->assinaturas->where('status', 'assinado')->count() > 0;
            $totalAssinaturas = $documento->assinaturas->count();
            $totalAssinaturasFeitas = $documento->assinaturas->where('status', 'assinado')->count();
            $podeBaixarPdf = $documento->status !== 'rascunho' && $documento->arquivo_pdf;
            $percentualAssinaturas = $totalAssinaturas > 0
                ? (int) round(($totalAssinaturasFeitas / $totalAssinaturas) * 100)
                : 0;
            $statusInfo = match ($documento->status) {
                'rascunho' => [
                    'label' => 'Rascunho',
                    'descricao' => 'Revise o conteúdo antes de enviar para assinatura.',
                    'badge' => 'bg-gray-100 text-gray-700 border-gray-200',
                ],
                'aguardando_assinatura' => [
                    'label' => 'Aguardando assinaturas',
                    'descricao' => 'Acompanhe as assinaturas pendentes para liberar a versão final.',
                    'badge' => 'bg-blue-50 text-blue-700 border-blue-100',
                ],
                default => [
                    'label' => 'Finalizado',
                    'descricao' => 'O documento está concluído e pronto para consulta.',
                    'badge' => 'bg-gray-100 text-gray-700 border-gray-200',
                ],
            };
            $usuarioEhAdmin = auth('interno')->user()?->isAdmin() ?? false;
        @endphp

        {{-- Header --}}
        <div class="mb-4">
            <div class="flex items-center gap-2 text-xs text-gray-600 mb-2">
                @if($documento->processo)
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$documento->processo->estabelecimento_id, $documento->processo->id]) }}" class="hover:text-blue-600 transition">
                        Processo {{ $documento->processo->numero_processo }}
                    </a>
                @else
                    <a href="{{ route('admin.documentos.index') }}" class="hover:text-blue-600 transition">
                        Documentos
                    </a>
                @endif
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-900 font-medium">{{ $documento->nome ?? $documento->tipoDocumento->nome }}</span>
            </div>
        </div>

        {{-- Documento --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-5 border-b border-gray-200 bg-white">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <div class="flex flex-wrap items-center gap-2 mb-3 text-[11px] font-medium">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 {{ $statusInfo['badge'] }}">
                                {{ $statusInfo['label'] }}
                            </span>
                            <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-gray-700">
                                {{ $documento->tipoDocumento->nome }}
                            </span>
                            @if($documento->processo)
                                <a href="{{ route('admin.estabelecimentos.processos.show', [$documento->processo->estabelecimento_id, $documento->processo->id]) }}"
                                   class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-gray-700 hover:bg-gray-100 transition">
                                    Processo {{ $documento->processo->numero_processo }}
                                </a>
                            @endif
                        </div>
                        <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $documento->nome ?? $documento->tipoDocumento->nome }}</h1>
                        <p class="mt-1 text-sm text-gray-500">{{ $documento->numero_documento }}</p>
                        <p class="mt-3 max-w-2xl text-sm text-gray-600">
                            {{ $statusInfo['descricao'] }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:min-w-[280px]">
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                            <p class="text-[11px] uppercase tracking-wide text-gray-500">Assinaturas</p>
                            <p class="mt-1 text-base font-semibold text-gray-900">{{ $totalAssinaturasFeitas }}/{{ $totalAssinaturas }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $totalAssinaturas > 0 ? $percentualAssinaturas . '% concluído' : 'Sem fluxo de assinatura' }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                            <p class="text-[11px] uppercase tracking-wide text-gray-500">Edição</p>
                            <p class="mt-1 text-base font-semibold text-gray-900">{{ $documentoPodeEditar ? 'Liberada' : 'Bloqueada' }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $documentoPodeEditar ? 'Ainda aceita ajustes.' : 'Documento preservado para consulta.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-5 bg-gray-50">
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.6fr)_360px]">
                    <div class="space-y-6">
                        <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="max-w-2xl">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Próximo passo</p>
                                    <h2 class="mt-1.5 text-sm font-semibold text-gray-900">
                                        @if($documento->status === 'rascunho')
                                            Revise e siga para assinatura quando estiver pronto.
                                        @elseif($documento->status === 'aguardando_assinatura')
                                            Acompanhe quem ainda precisa assinar.
                                        @else
                                            Consulte a versão final ou baixe o PDF.
                                        @endif
                                    </h2>
                                    <p class="mt-1.5 text-xs text-gray-600 leading-5">
                                        @if($documento->status === 'rascunho')
                                            A visualização abre o conteúdo imediatamente. Se precisar ajustar, entre em edição antes da primeira assinatura.
                                        @elseif($documento->status === 'aguardando_assinatura')
                                            O documento será concluído quando todas as assinaturas pendentes forem finalizadas.
                                        @else
                                            Use a visualização rápida para conferir o documento ou faça o download para compartilhar.
                                        @endif
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2 lg:max-w-sm lg:justify-end">
                                    <button type="button"
                                            onclick="abrirModalVisualizacao()"
                                            class="inline-flex items-center gap-2 px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Abrir visualização
                                    </button>
                                    @if($documentoPodeEditar)
                                        <a href="{{ route('admin.documentos.edit', $documento->id) }}"
                                           class="inline-flex items-center gap-2 px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Editar
                                        </a>
                                    @endif
                                    @if($podeBaixarPdf)
                                        <a href="{{ route('admin.documentos.pdf', $documento->id) }}"
                                           class="inline-flex items-center gap-2 px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Baixar PDF
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </section>

                        @if($documento->assinaturas->count() > 0)
                            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Fluxo de assinatura</p>
                                        <h2 class="mt-1 text-sm font-semibold text-gray-900">Acompanhe quem já assinou e o que ainda falta</h2>
                                        <p class="mt-1 text-xs text-gray-600">
                                            {{ $totalAssinaturas > 0 ? $totalAssinaturasFeitas . ' de ' . $totalAssinaturas . ' assinaturas concluídas.' : 'Nenhuma assinatura configurada.' }}
                                        </p>
                                    </div>
                                    @if(!$temAssinaturaFeita && $documento->status !== 'assinado')
                                        <button onclick="abrirModalGerenciarAssinantes()"
                                                class="inline-flex items-center gap-2 px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Gerenciar assinantes
                                        </button>
                                    @endif
                                </div>

                                <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full bg-gray-400 transition-all" style="width: {{ $percentualAssinaturas }}%"></div>
                                </div>

                                <div class="mt-4 space-y-3">
                                    @foreach($documento->assinaturas as $assinatura)
                                        <div class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-gray-200 text-gray-600">
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
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-200 px-2.5 py-1 text-[11px] font-medium text-gray-700">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        Assinado
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-600">
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

                                @if(!$temAssinaturaFeita && $documento->status !== 'assinado')
                                    <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-3 py-3 text-xs text-gray-600">
                                        Você ainda pode ajustar a lista de assinantes porque nenhuma assinatura foi registrada.
                                    </div>
                                @elseif($usuarioEhAdmin && $documento->status !== 'assinado')
                                    <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-3 py-3 text-xs text-gray-600">
                                        Como administrador, você ainda pode remover assinaturas pendentes mesmo após já existir assinatura concluída.
                                    </div>
                                @endif
                            </section>
                        @endif

                        @if($documento->isLote())
                            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Distribuição em lote</p>
                                        <h2 class="mt-1 text-sm font-semibold text-gray-900">{{ count($documento->processos_ids) }} processos vinculados a este documento</h2>
                                        <p class="mt-1.5 text-xs leading-5 text-gray-600">
                                            @if($documento->status === 'rascunho')
                                                O envio para os processos acontecerá após a finalização e assinatura.
                                            @elseif($documento->status === 'aguardando_assinatura')
                                                Assim que as assinaturas forem concluídas, a distribuição será feita automaticamente.
                                            @else
                                                A distribuição já foi concluída para os processos listados abaixo.
                                            @endif
                                        </p>

                                        <details class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                            <summary class="cursor-pointer list-none text-xs font-medium text-gray-700">
                                                Ver processos envolvidos
                                            </summary>
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

                                        @if($documento->os_id)
                                            <div class="mt-4">
                                                <a href="{{ route('admin.ordens-servico.show', $documento->os_id) }}"
                                                   class="inline-flex items-center gap-2 text-xs font-medium text-gray-700 hover:text-gray-900 hover:underline">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                    </svg>
                                                    Abrir Ordem de Serviço de origem
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </section>
                        @endif
                    </div>

                    <aside class="space-y-6">
                        <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Resumo rápido</p>
                            <dl class="mt-4 space-y-4">
                                @if($documento->processo)
                                    <div>
                                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Processo</dt>
                                        <dd class="mt-1">
                                            <a href="{{ route('admin.estabelecimentos.processos.show', [$documento->processo->estabelecimento_id, $documento->processo->id]) }}"
                                               class="text-sm font-medium text-gray-900 hover:text-blue-700 hover:underline">
                                                {{ $documento->processo->numero_processo }}
                                            </a>
                                            <p class="mt-1 text-xs text-gray-500 leading-5">
                                                {{ $documento->processo->estabelecimento->nome_fantasia ?? $documento->processo->estabelecimento->razao_social }}
                                            </p>
                                        </dd>
                                    </div>
                                @endif

                                <div>
                                    <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Tipo</dt>
                                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $documento->tipoDocumento->nome }}</dd>
                                </div>

                                <div>
                                    <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Criado por</dt>
                                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $documento->usuarioCriador->nome }}</dd>
                                    <dd class="mt-1 text-xs text-gray-500">{{ $documento->created_at->format('d/m/Y H:i') }}</dd>
                                </div>

                                <div>
                                    <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Situação atual</dt>
                                    <dd class="mt-1 text-xs leading-5 text-gray-600">{{ $statusInfo['descricao'] }}</dd>
                                </div>
                            </dl>
                        </section>

                        <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Atalhos</p>
                            <div class="mt-4 space-y-2">
                                <a href="{{ route('admin.documentos.index') }}"
                                   class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
                                    <span>Voltar para documentos</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>

                                @if($documento->processo)
                                    <a href="{{ route('admin.estabelecimentos.processos.show', [$documento->processo->estabelecimento_id, $documento->processo->id]) }}"
                                       class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
                                        <span>Abrir processo vinculado</span>
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @endif

                                @if($documentoPodeEditar)
                                    <a href="{{ route('admin.documentos.edit', $documento->id) }}"
                                       class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
                                        <span>Editar documento</span>
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @endif

                                <button type="button"
                                        onclick="abrirModalVisualizacao()"
                                        class="flex w-full items-center justify-between rounded-lg border border-gray-200 px-3 py-2.5 text-left text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
                                    <span>Pré-visualizar documento</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>

                                @if($podeBaixarPdf)
                                    <a href="{{ route('admin.documentos.pdf', $documento->id) }}"
                                       class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
                                        <span>Baixar PDF</span>
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
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
                    <div class="prose prose-sm max-w-none border border-gray-200 p-4 rounded-xl bg-white shadow-sm">
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
