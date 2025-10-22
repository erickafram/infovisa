@extends('layouts.admin')

@section('title', 'Gerenciar Atividades')
@section('page-title', 'Gerenciar Atividades')

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gerenciar Atividades Econômicas</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $estabelecimento->nome_fantasia }} - {{ $estabelecimento->documento_formatado }}</p>
            </div>
        </div>
    </div>

    {{-- Mensagem de sucesso --}}
    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    {{-- Formulário --}}
    <form method="POST" 
          action="{{ route('admin.estabelecimentos.atividades.update', $estabelecimento->id) }}"
          x-data="atividadesForm()"
          x-init="init()"
          class="space-y-6">
        @csrf

        {{-- Card de Instruções --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-blue-900 mb-1">Como funciona?</h3>
                    <p class="text-sm text-blue-800">
                        Abaixo estão listadas <strong>todas as atividades econômicas</strong> registradas na Receita Federal para este CNPJ. 
                        Marque apenas as atividades que o estabelecimento <strong>realmente exerce</strong> no dia a dia.
                    </p>
                </div>
            </div>
        </div>

        {{-- Lista de Atividades --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Atividades Disponíveis</h2>
                    <p class="text-sm text-gray-500 mt-1">Selecione as atividades que o estabelecimento pratica</p>
                </div>
                <div class="flex items-center gap-2 bg-blue-50 px-4 py-2 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-semibold text-blue-900">
                        <span x-text="atividadesSelecionadas.length"></span> selecionada(s)
                    </span>
                </div>
            </div>

            @if(count($atividadesApi) > 0)
            <div class="space-y-3">
                <template x-for="(atividade, index) in atividadesDisponiveis" :key="index">
                    <div class="flex items-start gap-4 p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-all"
                         :class="{'bg-blue-50 border-blue-300': isAtividadeSelecionada(atividade.codigo)}">
                        <div class="flex-shrink-0 pt-1">
                            <input type="checkbox" 
                                   :id="'atividade_' + index"
                                   :value="atividade.codigo"
                                   @change="toggleAtividade(atividade)"
                                   :checked="isAtividadeSelecionada(atividade.codigo)"
                                   class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                        </div>
                        <div class="flex-1">
                            <label :for="'atividade_' + index" class="cursor-pointer">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                          :class="atividade.tipo === 'principal' ? 'bg-blue-500 text-white' : 'bg-gray-400 text-white'">
                                        <span x-show="atividade.tipo === 'principal'">⭐ Principal</span>
                                        <span x-show="atividade.tipo === 'secundaria'">Secundária</span>
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-mono font-semibold bg-gray-100 text-gray-800" 
                                          x-text="atividade.codigo"></span>
                                </div>
                                <p class="text-sm text-gray-900 font-medium" x-text="atividade.descricao"></p>
                            </label>
                        </div>
                    </div>
                </template>
            </div>
            @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="mt-2 text-sm text-gray-500">Nenhuma atividade encontrada na API da Receita Federal.</p>
                <p class="mt-1 text-xs text-gray-400">Verifique se o CNPJ está correto ou tente novamente mais tarde.</p>
            </div>
            @endif
        </div>

        {{-- Campo hidden com JSON das atividades --}}
        <input type="hidden" name="atividades_exercidas" :value="getAtividadesJSON()">

        {{-- Botões de ação --}}
        <div class="flex items-center justify-between gap-4 pt-4">
            <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}"
               class="px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Salvar Atividades
            </button>
        </div>
    </form>
</div>

<script>
function atividadesForm() {
    return {
        atividadesDisponiveis: @json($atividadesApi),
        atividadesSelecionadas: [],

        init() {
            // Inicializa com as atividades já salvas
            const atividadesSalvas = @json($estabelecimento->atividades_exercidas ?? []);
            
            console.log('Atividades disponíveis:', this.atividadesDisponiveis);
            console.log('Atividades salvas:', atividadesSalvas);
            
            if (atividadesSalvas && Array.isArray(atividadesSalvas)) {
                this.atividadesSelecionadas = atividadesSalvas.map(a => ({
                    codigo: a.codigo,
                    descricao: a.descricao,
                    principal: a.principal || false
                }));
            }
            
            console.log('Atividades selecionadas após init:', this.atividadesSelecionadas);
        },

        isAtividadeSelecionada(codigo) {
            // Normaliza os códigos removendo caracteres especiais para comparação
            const codigoNormalizado = String(codigo).replace(/[^0-9]/g, '');
            const resultado = this.atividadesSelecionadas.some(a => {
                const codigoAtividadeNormalizado = String(a.codigo).replace(/[^0-9]/g, '');
                return codigoAtividadeNormalizado === codigoNormalizado;
            });
            console.log(`Verificando se ${codigo} está selecionado:`, resultado);
            return resultado;
        },

        toggleAtividade(atividade) {
            // Normaliza códigos para comparação
            const codigoNormalizado = String(atividade.codigo).replace(/[^0-9]/g, '');
            const index = this.atividadesSelecionadas.findIndex(a => {
                const codigoAtividadeNormalizado = String(a.codigo).replace(/[^0-9]/g, '');
                return codigoAtividadeNormalizado === codigoNormalizado;
            });
            
            if (index >= 0) {
                // Remove se já estiver selecionada
                this.atividadesSelecionadas.splice(index, 1);
            } else {
                // Adiciona se não estiver selecionada
                this.atividadesSelecionadas.push({
                    codigo: atividade.codigo,
                    descricao: atividade.descricao,
                    principal: atividade.tipo === 'principal'
                });
            }
            
            console.log('Atividades selecionadas após toggle:', this.atividadesSelecionadas);
        },

        getAtividadesJSON() {
            return JSON.stringify(this.atividadesSelecionadas);
        }
    }
}
</script>
@endsection
