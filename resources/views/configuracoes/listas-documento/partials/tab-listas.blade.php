{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Listas de Documentos</h3>
        <p class="text-sm text-gray-500">Configure quais documentos são exigidos para cada tipo de processo e atividade</p>
    </div>
    <a href="{{ route('admin.configuracoes.listas-documento.create') }}" 
       class="inline-flex items-center gap-2 px-4 py-2 bg-cyan-600 text-white text-sm font-medium rounded-lg hover:bg-cyan-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nova Lista
    </a>
</div>

{{-- Filtros --}}
<div class="bg-gray-50 rounded-lg p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4">
        <input type="hidden" name="tab" value="listas">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="busca" value="{{ request('busca') }}" 
                   placeholder="Buscar por nome..."
                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
        </div>
        <div class="w-40">
            <select name="tipo_processo_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                <option value="">Tipo de Processo</option>
                @foreach($tiposProcesso as $tp)
                <option value="{{ $tp->id }}" {{ request('tipo_processo_id') == $tp->id ? 'selected' : '' }}>{{ $tp->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-36">
            <select name="escopo" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                <option value="">Escopo</option>
                <option value="estadual" {{ request('escopo') === 'estadual' ? 'selected' : '' }}>Estadual</option>
                <option value="municipal" {{ request('escopo') === 'municipal' ? 'selected' : '' }}>Municipal</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-colors">
            Filtrar
        </button>
    </form>
</div>

{{-- Tabela --}}
@if($listas->isEmpty())
<div class="text-center py-8">
    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
    </svg>
    <p class="text-gray-500">Nenhuma lista de documentos cadastrada</p>
    <a href="{{ route('admin.configuracoes.listas-documento.create') }}" class="mt-2 inline-block text-sm text-cyan-600 hover:underline">
        Criar primeira lista
    </a>
</div>
@else
<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-y border-gray-200">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nome</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tipo Processo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Escopo</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Atividades</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Documentos</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($listas as $lista)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $lista->nome }}</span>
                    @if($lista->descricao)
                    <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($lista->descricao, 40) }}</p>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if($lista->tipoProcesso)
                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                        {{ $lista->tipoProcesso->nome }}
                    </span>
                    @else
                    <span class="text-xs text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="flex flex-col gap-1">
                        <span class="px-2 py-1 text-xs font-medium {{ $lista->escopo_cor }} rounded-full inline-block w-fit">
                            {{ $lista->escopo_label }}
                        </span>
                        @if($lista->municipio)
                        <span class="text-xs text-gray-500">{{ $lista->municipio->nome }}</span>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                        {{ $lista->atividades_count }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full">
                        {{ $lista->tipos_documento_obrigatorio_count }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    @if($lista->ativo)
                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Ativo</span>
                    @else
                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Inativo</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('admin.configuracoes.listas-documento.show', $lista) }}" 
                           class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg" title="Ver">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        <a href="{{ route('admin.configuracoes.listas-documento.edit', $lista) }}" 
                           class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form action="{{ route('admin.configuracoes.listas-documento.duplicate', $lista) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg" title="Duplicar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </form>
                        <form action="{{ route('admin.configuracoes.listas-documento.destroy', $lista) }}" method="POST" class="inline"
                              onsubmit="return confirm('Excluir esta lista?')">
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

@if($listas->hasPages())
<div class="mt-4">
    {{ $listas->appends(['tab' => 'listas'])->links() }}
</div>
@endif
@endif
