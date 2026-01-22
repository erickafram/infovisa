{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Tipos de Documento Obrigatório</h3>
        <p class="text-sm text-gray-500">Configure documentos que os estabelecimentos precisam apresentar, incluindo documentos comuns a todos os serviços</p>
    </div>
    <button @click="$dispatch('open-modal-tipo-documento')" 
            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Novo Tipo
    </button>
</div>

{{-- Filtros --}}
<div class="bg-gray-50 rounded-lg p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3">
        <input type="hidden" name="tab" value="tipos-documento">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="busca" value="{{ request('busca') }}" 
                   placeholder="Buscar por nome ou descrição..."
                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
        </div>
        <div class="w-36">
            <select name="documento_comum" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                <option value="">Todos os tipos</option>
                <option value="1" {{ request('documento_comum') === '1' ? 'selected' : '' }}>Documentos Comuns</option>
                <option value="0" {{ request('documento_comum') === '0' ? 'selected' : '' }}>Documentos Específicos</option>
            </select>
        </div>
        <div class="w-32">
            <select name="escopo_competencia" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                <option value="">Todos escopos</option>
                <option value="estadual" {{ request('escopo_competencia') === 'estadual' ? 'selected' : '' }}>Estadual</option>
                <option value="municipal" {{ request('escopo_competencia') === 'municipal' ? 'selected' : '' }}>Municipal</option>
                <option value="todos" {{ request('escopo_competencia') === 'todos' ? 'selected' : '' }}>Todos</option>
            </select>
        </div>
        <div class="w-28">
            <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                <option value="">Status</option>
                <option value="ativo" {{ request('status') === 'ativo' ? 'selected' : '' }}>Ativos</option>
                <option value="inativo" {{ request('status') === 'inativo' ? 'selected' : '' }}>Inativos</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-amber-100 text-amber-700 text-sm font-medium rounded-lg hover:bg-amber-200 transition-colors">
            Filtrar
        </button>
        @if(request()->hasAny(['busca', 'documento_comum', 'escopo_competencia', 'status']))
        <a href="{{ route('admin.configuracoes.listas-documento.index', ['tab' => 'tipos-documento']) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
            Limpar
        </a>
        @endif
    </form>
</div>

{{-- Tabela --}}
@if($tiposDocumento->isEmpty())
<div class="text-center py-8">
    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    <p class="text-gray-500">Nenhum tipo de documento encontrado</p>
</div>
@else
<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-y border-gray-200">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nome</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Descrição</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Tipo</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Escopo</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Setor</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($tiposDocumento as $tipo)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-900">{{ $tipo->nome }}</span>
                        @if($tipo->prazo_validade_dias)
                        <span class="px-1.5 py-0.5 text-xs bg-yellow-100 text-yellow-800 rounded" title="Validade: {{ $tipo->prazo_validade_dias }} dias">
                            {{ $tipo->prazo_validade_dias }}d
                        </span>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="text-sm text-gray-600">{{ Str::limit($tipo->descricao, 40) ?: '-' }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                    @if($tipo->documento_comum)
                    <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full" title="Documento comum a todos os serviços">
                        Comum
                    </span>
                    @else
                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full" title="Documento específico por atividade">
                        Específico
                    </span>
                    @endif
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        @if($tipo->escopo_competencia === 'estadual') bg-blue-100 text-blue-800
                        @elseif($tipo->escopo_competencia === 'municipal') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-600 @endif">
                        {{ $tipo->escopo_competencia_label }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        @if($tipo->tipo_setor === 'publico') bg-indigo-100 text-indigo-800
                        @elseif($tipo->tipo_setor === 'privado') bg-orange-100 text-orange-800
                        @else bg-gray-100 text-gray-600 @endif">
                        {{ $tipo->tipo_setor_label }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    @if($tipo->ativo)
                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Ativo</span>
                    @else
                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Inativo</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.configuracoes.tipos-documento-obrigatorio.edit', $tipo) }}" 
                           class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form action="{{ route('admin.configuracoes.tipos-documento-obrigatorio.destroy', $tipo) }}" method="POST" class="inline"
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

{{-- Paginação --}}
@if($tiposDocumento->hasPages())
<div class="mt-4 flex justify-center">
    {{ $tiposDocumento->appends(request()->query())->links() }}
</div>
@endif
@endif

{{-- Modal Novo Tipo de Documento --}}
<div x-data="{ open: false }" 
     @open-modal-tipo-documento.window="open = true"
     x-show="open" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             class="fixed inset-0 bg-black/50" @click="open = false"></div>
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Novo Tipo de Documento</h3>
            
            <form action="{{ route('admin.configuracoes.tipos-documento-obrigatorio.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                            <input type="text" name="nome" required placeholder="Ex: CNPJ, Contrato Social"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                            <input type="number" name="ordem" value="0" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea name="descricao" rows="2" placeholder="Descrição do documento..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                    </div>

                    {{-- Documento Comum --}}
                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex items-start gap-3 mb-4">
                            <input type="checkbox" name="documento_comum" id="documento_comum_modal" value="1"
                                   class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500 mt-0.5">
                            <div>
                                <label for="documento_comum_modal" class="text-sm font-medium text-gray-700">Documento Comum a Todos os Serviços</label>
                                <p class="text-xs text-gray-500 mt-1">Se marcado, será obrigatório para TODOS os serviços do escopo selecionado</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Escopo de Competência</label>
                                <select name="escopo_competencia"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                    <option value="todos">Todos (Estadual + Municipal)</option>
                                    <option value="estadual" selected>Apenas Estadual</option>
                                    <option value="municipal">Apenas Municipal</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Setor</label>
                                <select name="tipo_setor"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                    <option value="todos">Todos (Público + Privado)</option>
                                    <option value="publico">Apenas Público</option>
                                    <option value="privado">Apenas Privado</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prazo de Validade (dias)</label>
                                <input type="number" name="prazo_validade_dias" min="1" placeholder="Ex: 30"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <p class="text-xs text-gray-500 mt-1">Opcional. Ex: CNPJ válido por 30 dias</p>
                            </div>
                            <div class="flex items-center gap-2 pt-6">
                                <input type="checkbox" name="ativo" id="ativo_modal" value="1" checked
                                       class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                                <label for="ativo_modal" class="text-sm text-gray-700">Ativo</label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observação para Estabelecimentos Públicos</label>
                            <textarea name="observacao_publica" rows="2" placeholder="Ex: Isento para estabelecimentos públicos"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observação para Estabelecimentos Privados</label>
                            <textarea name="observacao_privada" rows="2" placeholder="Ex: Apenas para empresas privadas"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
