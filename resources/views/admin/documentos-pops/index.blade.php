@extends('layouts.admin')

@section('title', 'Documentos POPS/IA')
@section('page-title', 'Documentos POPS/IA')

@section('content')
<div class="space-y-6">
    {{-- Header com Botão Adicionar --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Documentos IA</h2>
            <p class="mt-1 text-sm text-gray-600">Gerencie documentos de procedimentos operacionais padrão e integração com Assistente IA</p>
        </div>
        <a href="{{ route('admin.configuracoes.documentos-pops.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Documento
        </a>
    </div>

    {{-- Mensagens --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabela --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($documentos->isEmpty())
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum documento cadastrado</h3>
                <p class="mt-1 text-sm text-gray-500">Comece fazendo upload de um novo documento POP.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.configuracoes.documentos-pops.create') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Novo Documento
                    </a>
                </div>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arquivo</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Assistente IA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado por</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($documentos as $documento)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $documento->titulo }}</div>
                            @if($documento->descricao)
                                <div class="text-sm text-gray-500 mt-1">{{ Str::limit($documento->descricao, 60) }}</div>
                            @endif
                            @if($documento->categorias->isNotEmpty())
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach($documento->categorias as $categoria)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded" 
                                              style="background-color: {{ $categoria->cor }}20; color: {{ $categoria->cor }}">
                                            <span class="w-2 h-2 rounded-full" style="background-color: {{ $categoria->cor }}"></span>
                                            {{ $categoria->nome }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                @if($documento->extensao === 'pdf')
                                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                                <div>
                                    <div class="text-sm text-gray-900">{{ $documento->arquivo_nome }}</div>
                                    <div class="text-xs text-gray-500">{{ $documento->tamanho_formatado }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($documento->disponivel_ia)
                                <div class="flex flex-col items-center gap-1">
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-purple-100 text-purple-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Sim
                                    </span>
                                    @if($documento->isIndexado())
                                        <span class="text-xs text-green-600">✓ Indexado</span>
                                    @else
                                        <span class="text-xs text-yellow-600">⚠ Não indexado</span>
                                    @endif
                                </div>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-600">
                                    Não
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $documento->criador->nome }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $documento->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $documento->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.configuracoes.documentos-pops.visualizar', $documento) }}" 
                                   target="_blank"
                                   class="text-green-600 hover:text-green-900 transition-colors"
                                   title="Visualizar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.configuracoes.documentos-pops.download', $documento) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                   title="Download">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </a>
                                @if($documento->disponivel_ia && !$documento->isIndexado())
                                    <form action="{{ route('admin.configuracoes.documentos-pops.reindexar', $documento) }}" 
                                          method="POST" 
                                          class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="text-yellow-600 hover:text-yellow-900 transition-colors"
                                                title="Reindexar para IA">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.configuracoes.documentos-pops.edit', $documento) }}" 
                                   class="text-blue-600 hover:text-blue-900 transition-colors">
                                    Editar
                                </a>
                                <form action="{{ route('admin.configuracoes.documentos-pops.destroy', $documento) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Tem certeza que deseja excluir este documento?');"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 transition-colors">
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Paginação --}}
            @if($documentos->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $documentos->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
@endsection
