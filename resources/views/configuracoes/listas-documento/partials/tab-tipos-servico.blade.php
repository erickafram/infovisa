{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Tipos de Serviço & Atividades</h3>
        <p class="text-sm text-gray-500">Categorias de serviço e suas atividades (CNAEs)</p>
    </div>
    <button @click="$dispatch('open-modal-tipo-servico')" 
            class="inline-flex items-center gap-2 px-4 py-2 bg-violet-600 text-white text-sm font-medium rounded-lg hover:bg-violet-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Novo Tipo de Serviço
    </button>
</div>

{{-- Lista de Tipos de Serviço com Atividades --}}
@if($tiposServico->isEmpty())
<div class="text-center py-8">
    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
    </svg>
    <p class="text-gray-500">Nenhum tipo de serviço cadastrado</p>
</div>
@else
<div class="space-y-3" x-data="{ expandedTipo: null }">
    @foreach($tiposServico as $tipo)
    <div class="border border-gray-200 rounded-lg overflow-hidden">
        {{-- Header do Tipo de Serviço --}}
        <div class="bg-gray-50 px-4 py-3 flex items-center justify-between cursor-pointer hover:bg-gray-100 transition-colors"
             @click="expandedTipo = expandedTipo === {{ $tipo->id }} ? null : {{ $tipo->id }}">
            <div class="flex items-center gap-4 flex-1">
                <button type="button" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5 transition-transform" :class="expandedTipo === {{ $tipo->id }} ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <h4 class="text-sm font-semibold text-gray-900">{{ $tipo->nome }}</h4>
                        <span class="px-2 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-800 rounded-full">
                            {{ $tipo->atividades_count ?? $tipo->atividades->count() }} atividades
                        </span>
                        @if($tipo->ativo)
                        <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 rounded-full">Ativo</span>
                        @else
                        <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Inativo</span>
                        @endif
                    </div>
                    @if($tipo->descricao)
                    <p class="text-xs text-gray-500 mt-0.5">{{ $tipo->descricao }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2" @click.stop>
                <a href="{{ route('admin.configuracoes.tipos-servico.edit', $tipo) }}" 
                   class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Editar tipo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </a>
                <form action="{{ route('admin.configuracoes.tipos-servico.destroy', $tipo) }}" method="POST" class="inline"
                      onsubmit="return confirm('Excluir este tipo de serviço e todas as suas atividades?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Excluir tipo">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Atividades do Tipo de Serviço --}}
        <div x-show="expandedTipo === {{ $tipo->id }}" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="bg-white p-4 border-t border-gray-200">
            
            {{-- Botão Adicionar Atividades --}}
            <div class="mb-4 flex justify-end">
                <button @click="$dispatch('open-modal-atividade-{{ $tipo->id }}')" 
                        class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Adicionar Atividades (CNAEs)
                </button>
            </div>

            {{-- Lista de Atividades --}}
            @if($tipo->atividades->isEmpty())
            <div class="text-center py-6 bg-gray-50 rounded-lg">
                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-xs text-gray-500">Nenhuma atividade cadastrada</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach($tipo->atividades as $atividade)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex items-center gap-3 flex-1">
                        <span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-xs font-mono font-bold rounded">
                            {{ $atividade->codigo_cnae ?: '-' }}
                        </span>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $atividade->nome }}</p>
                            @if($atividade->descricao)
                            <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($atividade->descricao, 80) }}</p>
                            @endif
                        </div>
                        @if($atividade->ativo)
                        <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 rounded-full">Ativo</span>
                        @else
                        <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Inativo</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-1 ml-3">
                        <a href="{{ route('admin.configuracoes.atividades.edit', $atividade) }}" 
                           class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Editar">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form action="{{ route('admin.configuracoes.atividades.destroy', $atividade) }}" method="POST" class="inline"
                              onsubmit="return confirm('Excluir esta atividade?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Excluir">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Modal Adicionar Atividades para este Tipo --}}
            @include('configuracoes.listas-documento.partials.modal-atividades', ['tipoServico' => $tipo])
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Modal Novo Tipo de Serviço --}}
<div x-data="{ open: false }" 
     @open-modal-tipo-servico.window="open = true"
     x-show="open" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             class="fixed inset-0 bg-black/50" @click="open = false"></div>
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Novo Tipo de Serviço</h3>
            
            <form action="{{ route('admin.configuracoes.tipos-servico.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                        <input type="text" name="nome" required placeholder="Ex: Serviço de Alimentação"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea name="descricao" rows="2" placeholder="Descrição do tipo de serviço..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500"></textarea>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                            <input type="number" name="ordem" value="0" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input type="checkbox" name="ativo" id="ativo_servico" value="1" checked
                                   class="w-4 h-4 text-violet-600 border-gray-300 rounded focus:ring-violet-500">
                            <label for="ativo_servico" class="text-sm text-gray-700">Ativo</label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-violet-600 rounded-lg hover:bg-violet-700">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
