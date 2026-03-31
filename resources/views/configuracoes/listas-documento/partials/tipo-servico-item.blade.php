<div class="border {{ $corBorda ?? 'border-gray-200' }} rounded-lg overflow-hidden mb-2">
    {{-- Header --}}
    <div class="bg-gray-50 px-4 py-3 flex items-center justify-between cursor-pointer hover:bg-gray-100 transition-colors"
         @click="expandedTipo = expandedTipo === {{ $tipo->id }} ? null : {{ $tipo->id }}">
        <div class="flex items-center gap-4 flex-1">
            <button type="button" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5 transition-transform" :class="expandedTipo === {{ $tipo->id }} ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h4 class="text-sm font-semibold text-gray-900">{{ $tipo->nome }}</h4>
                    <span class="px-2 py-0.5 text-[10px] font-medium bg-emerald-100 text-emerald-800 rounded-full">
                        {{ $tipo->atividades_count ?? $tipo->atividades->count() }} atividades
                    </span>
                    @if($tipo->municipio)
                    <span class="px-2 py-0.5 text-[10px] font-medium bg-green-100 text-green-700 rounded-full">{{ $tipo->municipio->nome }}</span>
                    @endif
                    @if(!$tipo->ativo)
                    <span class="px-2 py-0.5 text-[10px] font-medium bg-gray-100 text-gray-600 rounded-full">Inativo</span>
                    @endif
                </div>
                @if($tipo->descricao)
                <p class="text-xs text-gray-500 mt-0.5">{{ $tipo->descricao }}</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-1" @click.stop>
            <a href="{{ route('admin.configuracoes.tipos-servico.edit', $tipo) }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </a>
            <form action="{{ route('admin.configuracoes.tipos-servico.destroy', $tipo) }}" method="POST" class="inline" onsubmit="return confirm('Excluir este tipo de serviço e todas as suas atividades?')">
                @csrf @method('DELETE')
                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Excluir">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </form>
        </div>
    </div>

    {{-- Atividades (expandível) --}}
    <div x-show="expandedTipo === {{ $tipo->id }}" x-transition class="bg-white p-4 border-t border-gray-200">
        <div class="mb-4 flex justify-end">
            <button @click="$dispatch('open-modal-atividade-{{ $tipo->id }}')" class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Adicionar Atividades (CNAEs)
            </button>
        </div>

        @if($tipo->atividades->isEmpty())
        <div class="text-center py-6 bg-gray-50 rounded-lg">
            <p class="text-xs text-gray-500">Nenhuma atividade cadastrada</p>
        </div>
        @else
        <div class="space-y-2">
            @foreach($tipo->atividades as $atividade)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <div class="flex items-center gap-3 flex-1">
                    <span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-xs font-mono font-bold rounded">{{ $atividade->codigo_cnae ?: '-' }}</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $atividade->nome }}</p>
                        @if($atividade->descricao)
                        <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($atividade->descricao, 80) }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-1 ml-3">
                    <a href="{{ route('admin.configuracoes.atividades.edit', $atividade) }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form action="{{ route('admin.configuracoes.atividades.destroy', $atividade) }}" method="POST" class="inline" onsubmit="return confirm('Excluir esta atividade?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Excluir">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @include('configuracoes.listas-documento.partials.modal-atividades', ['tipoServico' => $tipo])
    </div>
</div>
