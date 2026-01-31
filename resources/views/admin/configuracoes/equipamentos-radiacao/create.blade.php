@extends('layouts.admin')

@section('title', 'Nova Atividade - Equipamentos de Imagem')
@section('page-title', 'Nova Atividade de Radiação')

@section('content')
<div class="max-w-8xl mx-auto space-y-6" x-data="formEquipamentoRadiacao()">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.configuracoes.equipamentos-radiacao.index') }}" class="hover:text-gray-700">
            Equipamentos de Imagem
        </a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-900 font-medium">Nova Atividade</span>
    </div>

    {{-- Header --}}
    <div>
        <h2 class="text-xl font-bold text-gray-900">Cadastrar Atividade</h2>
        <p class="text-sm text-gray-500 mt-1">
            Adicione uma atividade econômica (CNAE) que exige cadastro de equipamentos de imagem.
        </p>
    </div>

    {{-- Formulário --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <form action="{{ route('admin.configuracoes.equipamentos-radiacao.store') }}" method="POST">
            @csrf

            <div class="p-6 space-y-6">
                {{-- Código da Atividade (CNAE) com autocomplete --}}
                <div>
                    <label for="codigo_atividade" class="block text-sm font-semibold text-gray-700 mb-2">
                        Código da Atividade (CNAE) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               name="codigo_atividade" 
                               id="codigo_atividade"
                               x-model="cnaeInput"
                               @input="buscarCnaeAutocomplete()"
                               @keydown.down.prevent="navegarSugestoes(1)"
                               @keydown.up.prevent="navegarSugestoes(-1)"
                               @keydown.enter.prevent="selecionarComEnter()"
                               @blur="setTimeout(() => sugestoesCnae = [], 200)"
                               placeholder="Digite o CNAE (ex: 4711-3/02 ou 4711302)"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('codigo_atividade') border-red-500 @enderror"
                               required>
                        
                        {{-- Dropdown de sugestões --}}
                        <div x-show="sugestoesCnae.length > 0" 
                             x-cloak
                             class="absolute z-30 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto mt-1">
                            <template x-for="(sugestao, idx) in sugestoesCnae" :key="idx">
                                <div class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-100 last:border-0"
                                     :class="{ 'bg-blue-50': idx === indiceSugestaoSelecionada }"
                                     @click="selecionarSugestao(sugestao)">
                                    <div class="font-mono font-semibold text-gray-900" x-text="sugestao.codigo"></div>
                                    <div class="text-xs text-gray-600 mt-0.5 line-clamp-2" x-text="sugestao.descricao"></div>
                                </div>
                            </template>
                        </div>
                        
                        {{-- Indicador de busca --}}
                        <div x-show="buscando" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    @error('codigo_atividade')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        💡 Digite o CNAE com ou sem formatação (4711-3/02 ou 4711302). O sistema busca automaticamente a descrição.
                    </p>
                </div>

                {{-- Descrição da Atividade --}}
                <div>
                    <label for="descricao_atividade" class="block text-sm font-semibold text-gray-700 mb-2">
                        Descrição da Atividade <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="descricao_atividade" 
                           id="descricao_atividade"
                           x-model="descricaoAtividade"
                           placeholder="Ex: Atividades de serviços de diagnóstico por imagem"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('descricao_atividade') border-red-500 @enderror"
                           required>
                    @error('descricao_atividade')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p x-show="descricaoEncontrada" class="mt-1 text-xs text-green-600">
                        ✓ Descrição preenchida automaticamente a partir da API
                    </p>
                </div>

                {{-- Observações --}}
                <div>
                    <label for="observacoes" class="block text-sm font-semibold text-gray-700 mb-2">
                        Observações
                    </label>
                    <textarea name="observacoes" 
                              id="observacoes"
                              rows="3"
                              placeholder="Informações adicionais sobre esta atividade..."
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm resize-none @error('observacoes') border-red-500 @enderror">{{ old('observacoes') }}</textarea>
                    @error('observacoes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status Ativo --}}
                <div class="flex items-center gap-3">
                    <input type="hidden" name="ativo" value="0">
                    <input type="checkbox" 
                           name="ativo" 
                           id="ativo"
                           value="1"
                           class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                           {{ old('ativo', '1') == '1' ? 'checked' : '' }}>
                    <label for="ativo" class="text-sm font-medium text-gray-700">
                        Ativo
                        <span class="font-normal text-gray-500">- Estabelecimentos com esta atividade serão obrigados a cadastrar equipamentos</span>
                    </label>
                </div>

                {{-- Obrigatório para abertura de processo --}}
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-start gap-3">
                        <input type="hidden" name="obrigatorio_processo" value="0">
                        <input type="checkbox" 
                               name="obrigatorio_processo" 
                               id="obrigatorio_processo"
                               value="1"
                               x-model="obrigatorioProcesso"
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5"
                               {{ old('obrigatorio_processo') ? 'checked' : '' }}>
                        <div>
                            <label for="obrigatorio_processo" class="text-sm font-medium text-gray-700">
                                Obrigatório cadastrar equipamento para abrir processo
                            </label>
                            <p class="text-xs text-gray-500 mt-1">
                                Se marcado, o estabelecimento será obrigado a cadastrar no mínimo um equipamento de radiação antes de abrir o processo selecionado.
                            </p>
                        </div>
                    </div>

                    {{-- Tipos de Processo (aparecem quando obrigatório está marcado) --}}
                    <div x-show="obrigatorioProcesso" x-cloak class="mt-4 ml-8">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Tipos de Processo <span class="text-red-500">*</span>
                        </label>
                        <p class="text-xs text-gray-500 mb-3">
                            Selecione em quais tipos de processo será obrigatório o cadastro de equipamentos.
                        </p>
                        <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50">
                            @forelse($tiposProcesso as $tipo)
                            <label class="flex items-center gap-3 cursor-pointer hover:bg-white p-2 rounded-lg transition-colors">
                                <input type="checkbox" 
                                       name="tipos_processo[]" 
                                       value="{{ $tipo->id }}"
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                       {{ in_array($tipo->id, old('tipos_processo', [])) ? 'checked' : '' }}>
                                <div>
                                    <span class="text-sm font-medium text-gray-900">{{ $tipo->nome }}</span>
                                    @if($tipo->codigo)
                                    <span class="text-xs text-gray-500 ml-1">({{ $tipo->codigo }})</span>
                                    @endif
                                </div>
                            </label>
                            @empty
                            <p class="text-sm text-gray-500 text-center py-2">Nenhum tipo de processo estadual cadastrado.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ações --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl flex items-center justify-end gap-3">
                <a href="{{ route('admin.configuracoes.equipamentos-radiacao.index') }}" 
                   class="px-4 py-2.5 text-gray-700 text-sm font-medium bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                    Cadastrar Atividade
                </button>
            </div>
        </form>
    </div>

    {{-- Informação adicional --}}
    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-lg">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-amber-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <h4 class="text-sm font-semibold text-amber-800">Importante</h4>
                <p class="text-sm text-amber-700 mt-1">
                    Após cadastrar a atividade, todos os estabelecimentos que possuem essa atividade em seu CNAE serão obrigados a cadastrar seus equipamentos de imagem.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function formEquipamentoRadiacao() {
    return {
        cnaeInput: '{{ old('codigo_atividade') }}',
        descricaoAtividade: '{{ old('descricao_atividade') }}',
        sugestoesCnae: [],
        indiceSugestaoSelecionada: -1,
        timeoutAutocomplete: null,
        buscando: false,
        descricaoEncontrada: false,
        obrigatorioProcesso: {{ old('obrigatorio_processo') ? 'true' : 'false' }},
        
        // Normaliza CNAE removendo pontos, hífens, barras e espaços
        normalizarCnae(cnae) {
            return cnae.replace(/[.\-\s\/]/g, '');
        },
        
        // Busca sugestões de CNAE enquanto digita (autocomplete)
        async buscarCnaeAutocomplete() {
            clearTimeout(this.timeoutAutocomplete);
            this.descricaoEncontrada = false;
            
            const termo = this.cnaeInput.trim();
            if (termo.length < 4) {
                this.sugestoesCnae = [];
                return;
            }
            
            this.timeoutAutocomplete = setTimeout(async () => {
                this.buscando = true;
                try {
                    const cnaeNormalizado = this.normalizarCnae(termo);
                    const url = `{{ route('admin.configuracoes.pactuacao.buscar-cnaes') }}?termo=${encodeURIComponent(cnaeNormalizado)}`;
                    
                    const response = await fetch(url);
                    const data = await response.json();
                    
                    this.sugestoesCnae = data.slice(0, 5); // Limita a 5 sugestões
                    this.indiceSugestaoSelecionada = -1;
                } catch (error) {
                    console.error('Erro ao buscar sugestões:', error);
                    this.sugestoesCnae = [];
                } finally {
                    this.buscando = false;
                }
            }, 300);
        },
        
        // Navega pelas sugestões com teclado (setas)
        navegarSugestoes(direcao) {
            if (this.sugestoesCnae.length === 0) return;
            
            this.indiceSugestaoSelecionada += direcao;
            
            if (this.indiceSugestaoSelecionada < 0) {
                this.indiceSugestaoSelecionada = this.sugestoesCnae.length - 1;
            } else if (this.indiceSugestaoSelecionada >= this.sugestoesCnae.length) {
                this.indiceSugestaoSelecionada = 0;
            }
        },
        
        // Seleciona com Enter
        selecionarComEnter() {
            if (this.indiceSugestaoSelecionada >= 0 && this.sugestoesCnae[this.indiceSugestaoSelecionada]) {
                this.selecionarSugestao(this.sugestoesCnae[this.indiceSugestaoSelecionada]);
            }
        },
        
        // Seleciona uma sugestão do autocomplete
        selecionarSugestao(sugestao) {
            this.cnaeInput = this.normalizarCnae(sugestao.codigo);
            this.descricaoAtividade = sugestao.descricao;
            this.descricaoEncontrada = true;
            this.sugestoesCnae = [];
            this.indiceSugestaoSelecionada = -1;
        }
    };
}
</script>
@endsection
