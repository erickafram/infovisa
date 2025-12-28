@extends('layouts.admin')

@section('title', 'Meus Documentos Digitais')

@section('content')
<div class="container-fluid px-3 py-4">
    <!-- Header -->
    <div class="mb-4">
        <h1 class="text-xl font-bold text-gray-900">Meus Documentos Digitais</h1>
        <p class="text-xs text-gray-600 mt-0.5">Documentos criados por você ou que aguardam sua assinatura</p>
    </div>

    <!-- Filtros com Badges -->
    <div class="mb-4 bg-white rounded-lg shadow-sm border border-gray-200 p-3">
        <div class="flex flex-wrap gap-2">
            <!-- Todos -->
            <a href="{{ route('admin.documentos.index', ['status' => 'todos']) }}" 
               class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200
                      {{ $filtroStatus === 'todos' ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Todos
            </a>

            <!-- Rascunhos -->
            <a href="{{ route('admin.documentos.index', ['status' => 'rascunho']) }}" 
               class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200
                      {{ $filtroStatus === 'rascunho' ? 'bg-gray-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Rascunhos
                @if($stats['rascunhos'] > 0)
                    <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ $filtroStatus === 'rascunho' ? 'bg-white text-gray-600' : 'bg-gray-600 text-white' }}">
                        {{ $stats['rascunhos'] }}
                    </span>
                @endif
            </a>

            <!-- Aguardando Minha Assinatura -->
            <a href="{{ route('admin.documentos.index', ['status' => 'aguardando_minha_assinatura']) }}" 
               class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200
                      {{ $filtroStatus === 'aguardando_minha_assinatura' ? 'bg-yellow-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Aguardando Minha Assinatura
                @if($stats['aguardando_minha_assinatura'] > 0)
                    <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ $filtroStatus === 'aguardando_minha_assinatura' ? 'bg-white text-yellow-600' : 'bg-yellow-600 text-white' }}">
                        {{ $stats['aguardando_minha_assinatura'] }}
                    </span>
                @endif
            </a>

            <!-- Assinados por Mim -->
            <a href="{{ route('admin.documentos.index', ['status' => 'assinados_por_mim']) }}" 
               class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200
                      {{ $filtroStatus === 'assinados_por_mim' ? 'bg-green-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Assinados por Mim
                @if($stats['assinados_por_mim'] > 0)
                    <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ $filtroStatus === 'assinados_por_mim' ? 'bg-white text-green-600' : 'bg-green-600 text-white' }}">
                        {{ $stats['assinados_por_mim'] }}
                    </span>
                @endif
            </a>

            <!-- Documentos com Prazos -->
            <a href="{{ route('admin.documentos.index', ['status' => 'com_prazos']) }}" 
               class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200
                      {{ $filtroStatus === 'com_prazos' ? 'bg-purple-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Documentos com Prazos
                @if(isset($stats['com_prazos']) && $stats['com_prazos'] > 0)
                    <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ $filtroStatus === 'com_prazos' ? 'bg-white text-purple-600' : 'bg-purple-600 text-white' }}">
                        {{ $stats['com_prazos'] }}
                    </span>
                @endif
            </a>
        </div>

        {{-- Filtro adicional de Tipo de Documento (apenas quando status = com_prazos) --}}
        @if($filtroStatus === 'com_prazos')
        <div class="mt-3 pt-3 border-t border-gray-200">
            <label for="filtro_tipo" class="block text-xs font-medium text-gray-700 mb-1.5">
                Filtrar por Tipo de Documento:
            </label>
            <select id="filtro_tipo" 
                    onchange="window.location.href = '{{ route('admin.documentos.index', ['status' => 'com_prazos']) }}&tipo_documento_id=' + this.value"
                    class="w-full md:w-80 px-2.5 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-xs">
                <option value="">Todos os tipos</option>
                @foreach($tiposDocumento as $tipo)
                    <option value="{{ $tipo->id }}" {{ request('tipo_documento_id') == $tipo->id ? 'selected' : '' }}>
                        {{ $tipo->nome }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    <!-- Lista de Documentos -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        @if($documentos->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                                Número
                            </th>
                            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                                Processo
                            </th>
                            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                                Tipo
                            </th>
                            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                                Criado Por
                            </th>
                            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                                Data
                            </th>
                            @if($filtroStatus === 'com_prazos')
                            <th class="px-4 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                                Prazo/Validade
                            </th>
                            @endif
                            <th class="px-4 py-2 text-right text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($documentos as $documento)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="text-xs font-medium text-gray-900">
                                        {{ $documento->numero_documento }}
                                    </div>
                                    @if($documento->sigiloso)
                                        <span class="text-[10px] bg-red-100 text-red-800 px-1.5 py-0.5 rounded">Sigiloso</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if($documento->processo)
                                        <a href="{{ route('admin.estabelecimentos.processos.show', [$documento->processo->estabelecimento_id, $documento->processo->id]) }}#documentos" 
                                           class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800 font-medium hover:underline transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            {{ $documento->processo->numero_processo }}
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="text-xs text-gray-900">
                                        {{ $documento->tipoDocumento->nome }}
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <span class="px-1.5 py-0.5 inline-flex text-[10px] leading-4 font-semibold rounded-full 
                                        @if($documento->status == 'rascunho') bg-gray-100 text-gray-800
                                        @elseif($documento->status == 'aguardando_assinatura') bg-yellow-100 text-yellow-800
                                        @elseif($documento->status == 'assinado') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $documento->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-xs text-gray-500">
                                    <div>{{ $documento->usuarioCriador->nome }}</div>
                                    @php
                                        $minhaAssinatura = $documento->assinaturas->where('usuario_interno_id', auth('interno')->id())->first();
                                    @endphp
                                    @if($minhaAssinatura)
                                        <div class="mt-0.5 text-[10px]">
                                            @if($minhaAssinatura->status === 'assinado')
                                                <span class="inline-flex items-center text-green-600">
                                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Você assinou
                                                </span>
                                            @elseif($minhaAssinatura->status === 'pendente')
                                                <span class="inline-flex items-center text-yellow-600">
                                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Aguardando
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap text-xs text-gray-500">
                                    {{ $documento->created_at->format('d/m/Y H:i') }}
                                </td>
                                @if($filtroStatus === 'com_prazos')
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if($documento->data_vencimento)
                                        @php
                                            $corBadge = $documento->cor_status_prazo;
                                            $textoBadge = $documento->texto_status_prazo;
                                            
                                            $classesCor = [
                                                'red' => 'bg-red-100 text-red-700 border-red-200',
                                                'yellow' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                                'green' => 'bg-green-100 text-green-700 border-green-200',
                                                'gray' => 'bg-gray-100 text-gray-700 border-gray-200',
                                            ];
                                            
                                            $classeBadge = $classesCor[$corBadge] ?? $classesCor['gray'];
                                        @endphp
                                        <div class="flex flex-col gap-0.5">
                                            <span class="inline-flex items-center px-1.5 py-0.5 {{ $classeBadge }} border rounded-full text-[10px] font-medium whitespace-nowrap">
                                                <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ $textoBadge }}
                                            </span>
                                            <span class="text-[10px] text-gray-500">
                                                {{ $documento->data_vencimento->format('d/m/Y') }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-[10px] text-gray-400">Sem prazo</span>
                                    @endif
                                </td>
                                @endif
                                <td class="px-4 py-2.5 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        @php
                                            $minhaAssinatura = $documento->assinaturas->where('usuario_interno_id', auth('interno')->id())->first();
                                        @endphp
                                        
                                        @if($minhaAssinatura && $minhaAssinatura->status === 'pendente' && $documento->status === 'aguardando_assinatura')
                                            <a href="{{ route('admin.assinatura.assinar', $documento->id) }}" 
                                               class="inline-flex items-center px-2 py-1 text-[10px] font-medium text-white bg-yellow-600 rounded hover:bg-yellow-700 transition">
                                                <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                                Assinar
                                            </a>
                                        @endif
                                        
                                        @if($documento->status === 'rascunho' && $documento->usuario_criador_id === auth('interno')->id())
                                            <a href="{{ route('admin.documentos.edit', $documento->id) }}" 
                                               class="inline-flex items-center px-2 py-1 text-[10px] font-medium text-white bg-gray-600 rounded hover:bg-gray-700 transition">
                                                <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Editar
                                            </a>
                                        @endif
                                        
                                        <a href="{{ route('admin.documentos.show', $documento->id) }}" 
                                           class="inline-flex items-center px-2 py-1 text-[10px] font-medium text-blue-600 bg-blue-50 rounded hover:bg-blue-100 transition">
                                            <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Ver
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="px-3 py-2 border-t border-gray-200 text-xs">
                {{ $documentos->links() }}
            </div>
        @else
            <!-- Sem Documentos -->
            <div class="px-4 py-8 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <h3 class="mt-2 text-xs font-medium text-gray-900">Nenhum documento encontrado</h3>
                <p class="mt-1 text-xs text-gray-500">
                    Os documentos digitais são criados a partir dos processos.
                </p>
                <div class="mt-4">
                    <a href="{{ route('admin.processos.index-geral') }}" 
                       class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Ver Processos
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
