{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Documentos por Atividade</h3>
        <p class="text-sm text-gray-500">Configure quais documentos específicos são exigidos para cada atividade (CNAE)</p>
    </div>
    <button @click="showModalLote = true" 
            class="inline-flex items-center gap-2 px-4 py-2 bg-cyan-600 text-white text-sm font-medium rounded-lg hover:bg-cyan-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        Aplicar em Lote
    </button>
</div>

{{-- Filtros --}}
<div class="bg-gray-50 rounded-lg p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4">
        <input type="hidden" name="tab" value="atividades">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="busca" value="{{ request('busca') }}" 
                   placeholder="Buscar por nome ou código CNAE..."
                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
        </div>
        <div class="w-48">
            <select name="tipo_servico_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                <option value="">Todos os Tipos de Serviço</option>
                @foreach($tiposServico as $ts)
                <option value="{{ $ts->id }}" {{ request('tipo_servico_id') == $ts->id ? 'selected' : '' }}>
                    {{ $ts->nome }} ({{ $ts->atividadesAtivas->count() }})
                </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-colors">
            Filtrar
        </button>
        @if(request()->hasAny(['busca', 'tipo_servico_id']))
        <a href="{{ route('admin.configuracoes.atividade-documento.index', ['tab' => 'atividades']) }}" 
           class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
            Limpar
        </a>
        @endif
    </form>
</div>

{{-- Lista de Atividades --}}
@if($atividades->isEmpty())
<div class="text-center py-8">
    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <p class="text-gray-500">Nenhuma atividade encontrada</p>
</div>
@else
<div class="space-y-4">
    @foreach($atividades as $atividade)
    <div class="border border-gray-200 rounded-lg overflow-hidden" x-data="{ expanded: false }">
        {{-- Header da Atividade --}}
        <div class="flex items-center justify-between p-4 bg-gray-50 cursor-pointer hover:bg-gray-100" @click="expanded = !expanded">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-cyan-100 text-cyan-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-900">{{ $atividade->nome }}</h4>
                    <div class="flex items-center gap-2 mt-1">
                        @if($atividade->codigo_cnae)
                        <span class="text-xs font-mono text-gray-500">CNAE: {{ $atividade->codigo_cnae_formatado }}</span>
                        @endif
                        @if($atividade->tipoServico)
                        <span class="px-2 py-0.5 text-xs bg-violet-100 text-violet-700 rounded-full">{{ $atividade->tipoServico->nome }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="px-3 py-1 text-sm font-medium rounded-full {{ $atividade->documentos_obrigatorios_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                    {{ $atividade->documentos_obrigatorios_count }} documento(s)
                </span>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>

        {{-- Conteúdo Expandido --}}
        <div x-show="expanded" x-collapse>
            <div class="p-4 border-t border-gray-200">
                @if($atividade->documentosObrigatorios->isEmpty())
                <div class="text-center py-4">
                    <p class="text-sm text-gray-500">Nenhum documento específico vinculado a esta atividade.</p>
                    <a href="{{ route('admin.configuracoes.atividade-documento.show', $atividade) }}" 
                       class="mt-2 inline-block text-sm text-cyan-600 hover:underline">
                        Adicionar documentos
                    </a>
                </div>
                @else
                <div class="space-y-2">
                    @foreach($atividade->documentosObrigatorios as $doc)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full {{ $doc->pivot->obrigatorio ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600' }}">
                                @if($doc->pivot->obrigatorio)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                @endif
                            </span>
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ $doc->nome }}</span>
                                <span class="text-xs text-gray-500 ml-2">({{ $doc->nomenclatura }})</span>
                                @if($doc->pivot->observacao)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $doc->pivot->observacao }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $doc->pivot->obrigatorio ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $doc->pivot->obrigatorio ? 'Obrigatório' : 'Opcional' }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <div class="mt-4 pt-4 border-t border-gray-200 flex justify-end">
                    <a href="{{ route('admin.configuracoes.atividade-documento.show', $atividade) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-cyan-600 hover:text-cyan-700 hover:bg-cyan-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Gerenciar Documentos
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Paginação --}}
@if($atividades->hasPages())
<div class="mt-6">
    {{ $atividades->links() }}
</div>
@endif
@endif
