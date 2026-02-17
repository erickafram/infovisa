@extends('layouts.admin')

@section('title', 'Relatório de Documentos Gerados')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.relatorios.index') }}" class="hover:text-gray-700">Relatórios</a>
                <span>/</span>
                <span class="text-gray-900">Documentos Gerados</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Relatório de Documentos Gerados</h1>
            <p class="text-gray-500 mt-1">Clique no número do processo para abrir o processo e no número do documento para visualizar o documento</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Total</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totais['total'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Assinados</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($totais['assinados'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Aguardando Assinatura</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($totais['aguardando_assinatura'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Rascunhos</p>
            <p class="text-2xl font-bold text-gray-700 mt-1">{{ number_format($totais['rascunhos'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-800">Filtros</h2>
            <span class="text-xs text-gray-500">Use busca, status, tipo e período</span>
        </div>
        <form method="GET" action="{{ route('admin.relatorios.documentos-gerados') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Busca</label>
                <input
                    type="text"
                    name="busca"
                    value="{{ request('busca') }}"
                    placeholder="Número, tipo, processo, estabelecimento..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    <option value="rascunho" @selected(request('status') === 'rascunho')>Rascunho</option>
                    <option value="aguardando_assinatura" @selected(request('status') === 'aguardando_assinatura')>Aguardando Assinatura</option>
                    <option value="assinado" @selected(request('status') === 'assinado')>Assinado</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de documento</label>
                <select name="tipo_documento_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach($tiposDocumento as $tipoDocumento)
                        <option value="{{ $tipoDocumento->id }}" @selected((string) request('tipo_documento_id') === (string) $tipoDocumento->id)>
                            {{ $tipoDocumento->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data inicial</label>
                <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data final</label>
                <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="md:col-span-5 flex items-center gap-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Filtrar
                </button>
                <a href="{{ route('admin.relatorios.documentos-gerados') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nº Documento</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Processo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estabelecimento</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Município</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Criado por</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Data</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($documentos as $documento)
                        @php
                            $processo = $documento->processo;
                            $estabelecimento = $processo?->estabelecimento;
                            $municipio = $estabelecimento?->municipio;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                <a href="{{ route('admin.documentos.show', $documento->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline transition">
                                    {{ $documento->numero_documento ?? '-' }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $documento->tipoDocumento->nome ?? $documento->nome ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if($processo && $estabelecimento)
                                    <a href="{{ route('admin.estabelecimentos.processos.show', [$estabelecimento->id, $processo->id]) }}" class="text-blue-600 hover:text-blue-800 hover:underline transition">
                                        {{ $processo->numero_processo }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $estabelecimento?->nome_fantasia ?? $estabelecimento?->razao_social ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $municipio?->nome ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $documento->usuarioCriador->nome ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $status = $documento->status;
                                    $statusClass = match($status) {
                                        'assinado' => 'bg-green-100 text-green-700',
                                        'aguardando_assinatura' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                    {{ str_replace('_', ' ', ucfirst($status ?? 'não informado')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ optional($documento->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">Nenhum documento encontrado para os filtros informados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documentos->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $documentos->links('pagination.tailwind-clean') }}
            </div>
        @endif
    </div>
</div>
@endsection
