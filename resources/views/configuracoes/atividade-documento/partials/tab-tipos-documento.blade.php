{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Tipos de Documento</h3>
        <p class="text-sm text-gray-500">Cadastre os tipos de documentos que podem ser exigidos dos estabelecimentos</p>
    </div>
    <a href="{{ route('admin.configuracoes.tipos-documento-obrigatorio.create') }}" 
       class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Novo Tipo de Documento
    </a>
</div>

{{-- Estatísticas --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-purple-50 rounded-lg p-4">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-purple-100 text-purple-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-purple-900">{{ $documentosComuns->count() }}</p>
                <p class="text-sm text-purple-600">Documentos Comuns</p>
            </div>
        </div>
    </div>
    <div class="bg-cyan-50 rounded-lg p-4">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-cyan-100 text-cyan-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-cyan-900">{{ $documentosEspecificos->count() }}</p>
                <p class="text-sm text-cyan-600">Documentos Específicos</p>
            </div>
        </div>
    </div>
    <div class="bg-amber-50 rounded-lg p-4">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-amber-100 text-amber-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-amber-900">{{ $todosDocumentos->count() }}</p>
                <p class="text-sm text-amber-600">Total de Documentos</p>
            </div>
        </div>
    </div>
</div>

{{-- Tabela --}}
@if($todosDocumentos->isEmpty())
<div class="text-center py-8">
    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    <p class="text-gray-500">Nenhum tipo de documento cadastrado</p>
    <a href="{{ route('admin.configuracoes.tipos-documento-obrigatorio.create') }}" class="mt-2 inline-block text-sm text-amber-600 hover:underline">
        Cadastrar primeiro tipo de documento
    </a>
</div>
@else
<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-y border-gray-200">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nome</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nomenclatura</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Tipo</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Escopo</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Setor</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Validade</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($todosDocumentos as $doc)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div>
                        <span class="text-sm font-medium text-gray-900">{{ $doc->nome }}</span>
                        @if($doc->descricao)
                        <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($doc->descricao, 50) }}</p>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="text-sm font-mono text-gray-600">{{ $doc->nomenclatura }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                    @if($doc->documento_comum)
                    <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                        Comum
                    </span>
                    @else
                    <span class="px-2 py-1 text-xs font-medium bg-cyan-100 text-cyan-800 rounded-full">
                        Específico
                    </span>
                    @endif
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        @if($doc->escopo_competencia === 'estadual') bg-blue-100 text-blue-800
                        @elseif($doc->escopo_competencia === 'municipal') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-600 @endif">
                        {{ $doc->escopo_competencia_label }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        @if($doc->tipo_setor === 'publico') bg-indigo-100 text-indigo-800
                        @elseif($doc->tipo_setor === 'privado') bg-orange-100 text-orange-800
                        @else bg-gray-100 text-gray-600 @endif">
                        {{ $doc->tipo_setor_label }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    @if($doc->prazo_validade_dias)
                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                        {{ $doc->prazo_validade_dias }}d
                    </span>
                    @else
                    <span class="text-xs text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-center">
                    @if($doc->ativo)
                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Ativo</span>
                    @else
                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Inativo</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.configuracoes.tipos-documento-obrigatorio.edit', $doc) }}" 
                           class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form action="{{ route('admin.configuracoes.tipos-documento-obrigatorio.destroy', $doc) }}" method="POST" class="inline"
                              onsubmit="return confirm('Excluir este tipo de documento?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Excluir">
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
@endif
