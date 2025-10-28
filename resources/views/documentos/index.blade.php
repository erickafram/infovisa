@extends('layouts.admin')

@section('title', 'Meus Documentos Digitais')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Meus Documentos Digitais</h1>
        <p class="text-sm text-gray-600 mt-1">Documentos criados por você ou que aguardam sua assinatura</p>
    </div>

    <!-- Filtros com Badges -->
    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex flex-wrap gap-3">
            <!-- Todos -->
            <a href="{{ route('admin.documentos.index', ['status' => 'todos']) }}" 
               class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                      {{ $filtroStatus === 'todos' ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Todos
            </a>

            <!-- Rascunhos -->
            <a href="{{ route('admin.documentos.index', ['status' => 'rascunho']) }}" 
               class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                      {{ $filtroStatus === 'rascunho' ? 'bg-gray-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Rascunhos
                @if($stats['rascunhos'] > 0)
                    <span class="ml-2 px-2 py-0.5 text-xs font-bold rounded-full {{ $filtroStatus === 'rascunho' ? 'bg-white text-gray-600' : 'bg-gray-600 text-white' }}">
                        {{ $stats['rascunhos'] }}
                    </span>
                @endif
            </a>

            <!-- Aguardando Minha Assinatura -->
            <a href="{{ route('admin.documentos.index', ['status' => 'aguardando_minha_assinatura']) }}" 
               class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                      {{ $filtroStatus === 'aguardando_minha_assinatura' ? 'bg-yellow-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Aguardando Minha Assinatura
                @if($stats['aguardando_minha_assinatura'] > 0)
                    <span class="ml-2 px-2 py-0.5 text-xs font-bold rounded-full {{ $filtroStatus === 'aguardando_minha_assinatura' ? 'bg-white text-yellow-600' : 'bg-yellow-600 text-white' }}">
                        {{ $stats['aguardando_minha_assinatura'] }}
                    </span>
                @endif
            </a>

            <!-- Assinados por Mim -->
            <a href="{{ route('admin.documentos.index', ['status' => 'assinados_por_mim']) }}" 
               class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                      {{ $filtroStatus === 'assinados_por_mim' ? 'bg-green-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Assinados por Mim
                @if($stats['assinados_por_mim'] > 0)
                    <span class="ml-2 px-2 py-0.5 text-xs font-bold rounded-full {{ $filtroStatus === 'assinados_por_mim' ? 'bg-white text-green-600' : 'bg-green-600 text-white' }}">
                        {{ $stats['assinados_por_mim'] }}
                    </span>
                @endif
            </a>
        </div>
    </div>

    <!-- Lista de Documentos -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        @if($documentos->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Número
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tipo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Criado Por
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($documentos as $documento)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $documento->numero_documento }}
                                    </div>
                                    @if($documento->sigiloso)
                                        <span class="text-xs bg-red-100 text-red-800 px-2 py-0.5 rounded">Sigiloso</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ $documento->tipoDocumento->nome }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($documento->status == 'rascunho') bg-gray-100 text-gray-800
                                        @elseif($documento->status == 'aguardando_assinatura') bg-yellow-100 text-yellow-800
                                        @elseif($documento->status == 'assinado') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $documento->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div>{{ $documento->usuarioCriador->nome }}</div>
                                    @php
                                        $minhaAssinatura = $documento->assinaturas->where('usuario_interno_id', auth('interno')->id())->first();
                                    @endphp
                                    @if($minhaAssinatura)
                                        <div class="mt-1 text-xs">
                                            @if($minhaAssinatura->status === 'assinado')
                                                <span class="inline-flex items-center text-green-600">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Você assinou
                                                </span>
                                            @elseif($minhaAssinatura->status === 'pendente')
                                                <span class="inline-flex items-center text-yellow-600">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Aguardando sua assinatura
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $documento->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        @php
                                            $minhaAssinatura = $documento->assinaturas->where('usuario_interno_id', auth('interno')->id())->first();
                                        @endphp
                                        
                                        @if($minhaAssinatura && $minhaAssinatura->status === 'pendente' && $documento->status === 'aguardando_assinatura')
                                            <a href="{{ route('admin.assinatura.assinar', $documento->id) }}" 
                                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-yellow-600 rounded-md hover:bg-yellow-700 transition">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                                Assinar
                                            </a>
                                        @endif
                                        
                                        @if($documento->status === 'rascunho' && $documento->usuario_criador_id === auth('interno')->id())
                                            <a href="{{ route('admin.documentos.edit', $documento->id) }}" 
                                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 transition">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Editar
                                            </a>
                                        @endif
                                        
                                        <a href="{{ route('admin.documentos.show', $documento->id) }}" 
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $documentos->links() }}
            </div>
        @else
            <!-- Sem Documentos -->
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum documento encontrado</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Os documentos digitais são criados a partir dos processos.
                </p>
                <div class="mt-6">
                    <a href="{{ route('admin.processos.index-geral') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
