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
    @php
        $tiposEstaduais = $tiposServico->where('escopo', 'estadual');
        $tiposMunicipais = $tiposServico->where('escopo', 'municipal');
        $tiposMunAgrupados = $tiposMunicipais->groupBy('municipio_id');
    @endphp

    {{-- Estaduais --}}
    @if($tiposEstaduais->count() > 0)
    <div class="mb-4">
        <div class="flex items-center gap-2 mb-2">
            <span class="text-base">🏛️</span>
            <span class="text-xs font-bold text-blue-800 uppercase tracking-wide">Estadual</span>
            <span class="text-[10px] px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded-full font-bold">{{ $tiposEstaduais->count() }}</span>
            <div class="flex-1 h-px bg-blue-100"></div>
        </div>
    @foreach($tiposEstaduais as $tipo)
    @include('configuracoes.listas-documento.partials.tipo-servico-item', ['tipo' => $tipo, 'corBorda' => 'border-blue-200'])
    @endforeach
    </div>
    @endif

    {{-- Municipais --}}
    @if($tiposMunicipais->count() > 0)
    <div>
        <div class="flex items-center gap-2 mb-2">
            <span class="text-base">🏘️</span>
            <span class="text-xs font-bold text-green-800 uppercase tracking-wide">Municipal</span>
            <span class="text-[10px] px-1.5 py-0.5 bg-green-100 text-green-700 rounded-full font-bold">{{ $tiposMunicipais->count() }}</span>
            <div class="flex-1 h-px bg-green-100"></div>
        </div>
        @foreach($tiposMunAgrupados as $munId => $tiposDoMun)
        @php $munNome = $tiposDoMun->first()->municipio->nome ?? 'Município'; @endphp
        <div class="mb-3">
            <div class="flex items-center gap-2 mb-1.5 ml-2">
                <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                <span class="text-xs font-semibold text-green-700">{{ $munNome }}</span>
            </div>
            @foreach($tiposDoMun as $tipo)
            @include('configuracoes.listas-documento.partials.tipo-servico-item', ['tipo' => $tipo, 'corBorda' => 'border-green-200'])
            @endforeach
        </div>
        @endforeach
    </div>
    @endif
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
            
            <form action="{{ route('admin.configuracoes.tipos-servico.store') }}" method="POST" x-data="{ escopo: 'estadual' }">
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Escopo *</label>
                        <div class="flex gap-3">
                            <label class="flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer transition"
                                   :class="escopo === 'estadual' ? 'border-blue-400 bg-blue-50' : 'border-gray-200'">
                                <input type="radio" name="escopo" value="estadual" x-model="escopo" class="text-blue-600">
                                <span class="text-sm">🏛️ Estadual</span>
                            </label>
                            <label class="flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer transition"
                                   :class="escopo === 'municipal' ? 'border-green-400 bg-green-50' : 'border-gray-200'">
                                <input type="radio" name="escopo" value="municipal" x-model="escopo" class="text-green-600">
                                <span class="text-sm">🏘️ Municipal</span>
                            </label>
                        </div>
                    </div>
                    <div x-show="escopo === 'municipal'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Município *</label>
                        <select name="municipio_id" :required="escopo === 'municipal'"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                            <option value="">Selecione...</option>
                            @foreach(\App\Models\Municipio::orderBy('nome')->get() as $mun)
                            <option value="{{ $mun->id }}">{{ $mun->nome }}</option>
                            @endforeach
                        </select>
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
