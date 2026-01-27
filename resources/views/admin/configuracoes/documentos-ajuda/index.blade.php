@extends('layouts.admin')

@section('title', 'Documentos de Ajuda')
@section('page-title', 'Documentos de Ajuda')

@section('content')
<div class="space-y-6">
    {{-- Mensagens --}}
    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    {{-- Cabeçalho --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <a href="{{ route('admin.configuracoes.index') }}" class="text-sm text-blue-600 hover:text-blue-700 flex items-center mb-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar para Configurações
            </a>
            <p class="text-gray-600 text-sm">Gerencie documentos de ajuda que serão exibidos nos processos dos estabelecimentos</p>
        </div>
        <a href="{{ route('admin.configuracoes.documentos-ajuda.create') }}" 
           class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Documento
        </a>
    </div>

    {{-- Lista de Documentos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($documentos->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ordem</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipos de Processo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tamanho</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($documentos as $documento)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-sm text-gray-500">{{ $documento->ordem }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $documento->titulo }}</p>
                                    @if($documento->descricao)
                                    <p class="text-xs text-gray-500 truncate max-w-xs">{{ $documento->descricao }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($documento->tiposProcesso as $tipo)
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full">
                                    {{ $tipo->nome }}
                                </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-sm text-gray-500">{{ $documento->tamanho_formatado }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($documento->ativo)
                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">Ativo</span>
                            @else
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">Inativo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.configuracoes.documentos-ajuda.visualizar', $documento->id) }}" 
                                   target="_blank"
                                   class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors"
                                   title="Visualizar PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.configuracoes.documentos-ajuda.edit', $documento->id) }}" 
                                   class="p-1.5 text-gray-500 hover:text-yellow-600 hover:bg-yellow-50 rounded transition-colors"
                                   title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.configuracoes.documentos-ajuda.destroy', $documento->id) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Tem certeza que deseja excluir este documento?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded transition-colors"
                                            title="Excluir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        @if($documentos->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $documentos->links() }}
        </div>
        @endif
        @else
        <div class="p-8 text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">Nenhum documento de ajuda</h3>
            <p class="text-sm text-gray-500 mb-4">Comece adicionando documentos de ajuda para os estabelecimentos.</p>
            <a href="{{ route('admin.configuracoes.documentos-ajuda.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Adicionar Documento
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
