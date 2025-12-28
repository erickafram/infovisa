@extends('layouts.admin')

@section('title', 'Editar Atividade')
@section('page-title', 'Editar Atividade')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.configuracoes.atividades.index') }}" 
           class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-data="editarAtividade()">
        <form action="{{ route('admin.configuracoes.atividades.update', $atividade) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <div>
                    <label for="tipo_servico_id" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Serviço *</label>
                    <select name="tipo_servico_id" id="tipo_servico_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tipo_servico_id') border-red-500 @enderror">
                        <option value="">Selecione o tipo de serviço...</option>
                        @foreach($tiposServico as $tipo)
                        <option value="{{ $tipo->id }}" {{ old('tipo_servico_id', $atividade->tipo_servico_id) == $tipo->id ? 'selected' : '' }}>
                            {{ $tipo->nome }}
                        </option>
                        @endforeach
                    </select>
                    @error('tipo_servico_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Campo CNAE com busca automática --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Código CNAE</label>
                    <div class="flex gap-2">
                        <input type="text" 
                               x-model="inputCnae"
                               @keydown.enter.prevent="buscarCnae()"
                               placeholder="Ex: 5611201 ou 56.11-2-01"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <button type="button" 
                                @click="buscarCnae()"
                                :disabled="!inputCnae || buscando"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg x-show="buscando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="buscando ? 'Buscando...' : 'Buscar'"></span>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Digite o código CNAE e pressione Enter ou clique em Buscar. O sistema buscará automaticamente a descrição.
                    </p>
                    <input type="hidden" name="codigo_cnae" :value="codigoCnae">
                </div>

                {{-- Erro da busca --}}
                <div x-show="erro" x-transition class="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-700" x-text="erro"></p>
                </div>

                {{-- CNAE encontrado --}}
                <div x-show="codigoCnae" x-transition class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-xs font-mono font-bold rounded" x-text="codigoCnae"></span>
                            <span class="text-sm text-gray-700" x-text="descricaoCnae"></span>
                        </div>
                        <button type="button" 
                                @click="limparCnae()"
                                class="p-1 text-red-500 hover:bg-red-50 rounded" title="Remover CNAE">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="nome" id="nome" x-model="nome" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nome') border-red-500 @enderror">
                    @error('nome')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="descricao" id="descricao" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('descricao') border-red-500 @enderror">{{ old('descricao', $atividade->descricao) }}</textarea>
                    @error('descricao')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ordem" class="block text-sm font-medium text-gray-700 mb-1">Ordem de Exibição</label>
                    <input type="number" name="ordem" id="ordem" value="{{ old('ordem', $atividade->ordem) }}" min="0"
                           class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Menor número aparece primeiro</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="ativo" id="ativo" value="1" {{ old('ativo', $atividade->ativo) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="ativo" class="text-sm text-gray-700">Ativo</label>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200 flex items-center justify-end gap-3">
                <a href="{{ route('admin.configuracoes.atividades.index') }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editarAtividade() {
    return {
        inputCnae: '{{ old('codigo_cnae', $atividade->codigo_cnae) }}',
        codigoCnae: '{{ old('codigo_cnae', $atividade->codigo_cnae) }}',
        descricaoCnae: '',
        nome: '{{ old('nome', $atividade->nome) }}',
        buscando: false,
        erro: '',
        
        init() {
            // Se já tem CNAE, busca a descrição
            if (this.codigoCnae) {
                this.buscarDescricaoInicial();
            }
        },
        
        normalizarCnae(codigo) {
            return String(codigo).replace(/[^0-9]/g, '');
        },
        
        async buscarDescricaoInicial() {
            const resultado = await this.buscarDescricaoCnae(this.codigoCnae);
            if (resultado) {
                this.descricaoCnae = resultado.descricao;
            }
        },
        
        async buscarDescricaoCnae(codigo) {
            const codigoNormalizado = this.normalizarCnae(codigo);
            
            if (codigoNormalizado.length < 5) {
                return null;
            }
            
            try {
                const response = await fetch(`https://servicodados.ibge.gov.br/api/v2/cnae/subclasses/${codigoNormalizado}`);
                
                if (response.ok) {
                    const data = await response.json();
                    if (data && data.id) {
                        return {
                            codigo: codigoNormalizado,
                            descricao: data.descricao
                        };
                    }
                }
                
                const responseClasse = await fetch(`https://servicodados.ibge.gov.br/api/v2/cnae/classes/${codigoNormalizado.slice(0,5)}`);
                if (responseClasse.ok) {
                    const dataClasse = await responseClasse.json();
                    if (dataClasse && dataClasse.id) {
                        return {
                            codigo: codigoNormalizado,
                            descricao: dataClasse.descricao + ' (classe)'
                        };
                    }
                }
                
                return null;
            } catch (error) {
                console.error('Erro ao buscar CNAE:', error);
                return null;
            }
        },
        
        async buscarCnae() {
            if (!this.inputCnae || this.buscando) return;
            
            this.erro = '';
            this.buscando = true;
            
            const codigoNormalizado = this.normalizarCnae(this.inputCnae);
            
            const resultado = await this.buscarDescricaoCnae(codigoNormalizado);
            
            if (resultado) {
                this.codigoCnae = resultado.codigo;
                this.descricaoCnae = resultado.descricao;
                this.nome = resultado.descricao;
                this.inputCnae = resultado.codigo;
            } else {
                this.erro = `CNAE ${codigoNormalizado} não encontrado. Verifique se o código está correto.`;
            }
            
            this.buscando = false;
        },
        
        limparCnae() {
            this.codigoCnae = '';
            this.descricaoCnae = '';
            this.inputCnae = '';
        }
    }
}
</script>
@endsection
