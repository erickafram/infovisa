@extends('layouts.admin')

@section('title', 'Relatórios e Análises')

@section('content')
<div class="container-fluid px-4 py-6">
    {{-- Cabeçalho --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Relatórios</h1>
        <p class="text-gray-600 mt-2">Gere relatórios personalizados do sistema</p>
    </div>

    {{-- Filtros e Geração de PDF --}}
    <div class="max-w-8xl mx-auto">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">

                <div class="p-8" x-data="filtrosRelatorio()">
                    {{-- Filtros --}}
                    <div class="space-y-6">
                        {{-- Tipo de Relatório --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de Relatório</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label class="relative flex items-center p-4 border rounded-lg cursor-pointer transition-all"
                                       :class="tipoRelatorio === 'estabelecimentos' ? 'border-gray-900 bg-gray-50' : 'border-gray-300 hover:border-gray-400'">
                                    <input type="radio" x-model="tipoRelatorio" value="estabelecimentos" class="sr-only">
                                    <div class="flex items-center gap-3 w-full">
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">Estabelecimentos</div>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-4 border rounded-lg cursor-pointer transition-all"
                                       :class="tipoRelatorio === 'processos' ? 'border-gray-900 bg-gray-50' : 'border-gray-300 hover:border-gray-400'">
                                    <input type="radio" x-model="tipoRelatorio" value="processos" class="sr-only">
                                    <div class="flex items-center gap-3 w-full">
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">Processos</div>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-4 border rounded-lg cursor-pointer transition-all"
                                       :class="tipoRelatorio === 'ordens_servico' ? 'border-gray-900 bg-gray-50' : 'border-gray-300 hover:border-gray-400'">
                                    <input type="radio" x-model="tipoRelatorio" value="ordens_servico" class="sr-only">
                                    <div class="flex items-center gap-3 w-full">
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">Ordens de Serviço</div>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-4 border rounded-lg cursor-pointer transition-all"
                                       :class="tipoRelatorio === 'estatisticas' ? 'border-gray-900 bg-gray-50' : 'border-gray-300 hover:border-gray-400'">
                                    <input type="radio" x-model="tipoRelatorio" value="estatisticas" class="sr-only">
                                    <div class="flex items-center gap-3 w-full">
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">Estatísticas Gerais</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Período --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Período</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Data Inicial</label>
                                    <input type="date" x-model="dataInicial" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-900 focus:border-gray-900">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Data Final</label>
                                    <input type="date" x-model="dataFinal" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-900 focus:border-gray-900">
                                </div>
                            </div>
                        </div>

                        {{-- Filtros Específicos por Tipo --}}
                        <div x-show="tipoRelatorio === 'processos'" x-collapse>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Status do Processo</label>
                            <select x-model="statusProcesso" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-900 focus:border-gray-900">
                                <option value="">Todos os status</option>
                                <option value="aberto">Aberto</option>
                                <option value="em_analise">Em Análise</option>
                                <option value="concluido">Concluído</option>
                                <option value="arquivado">Arquivado</option>
                            </select>
                        </div>

                        <div x-show="tipoRelatorio === 'ordens_servico'" x-collapse>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Status da Ordem</label>
                            <select x-model="statusOrdem" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-900 focus:border-gray-900">
                                <option value="">Todos os status</option>
                                <option value="pendente">Pendente</option>
                                <option value="em_andamento">Em Andamento</option>
                                <option value="concluida">Concluída</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                    </div>

                    {{-- Botão Gerar PDF --}}
                    <div class="border-t border-gray-200 pt-6 mt-6">
                        <button @click="gerarPDF()" 
                                :disabled="!tipoRelatorio || gerando"
                                class="w-full bg-gray-900 hover:bg-gray-800 disabled:bg-gray-300 disabled:cursor-not-allowed text-white px-6 py-3 rounded-lg transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="font-medium" x-text="gerando ? 'Gerando PDF...' : 'Gerar Relatório em PDF'"></span>
                        </button>
                    </div>
                </div>
            </div>
    </div>
</div>

<script>
function filtrosRelatorio() {
    return {
        tipoRelatorio: '',
        dataInicial: '',
        dataFinal: '',
        statusProcesso: '',
        statusOrdem: '',
        gerando: false,

        async gerarPDF() {
            if (!this.tipoRelatorio) {
                alert('Por favor, selecione um tipo de relatório');
                return;
            }

            this.gerando = true;

            try {
                const params = new URLSearchParams({
                    tipo: this.tipoRelatorio,
                    data_inicial: this.dataInicial || '',
                    data_final: this.dataFinal || '',
                    status_processo: this.statusProcesso || '',
                    status_ordem: this.statusOrdem || ''
                });

                // Abre o PDF em nova aba
                window.open(`/admin/relatorios/gerar-pdf?${params.toString()}`, '_blank');
                
                // Feedback visual
                setTimeout(() => {
                    alert('Relatório gerado com sucesso! Verifique a nova aba.');
                }, 500);
            } catch (error) {
                console.error('Erro ao gerar PDF:', error);
                alert('Erro ao gerar relatório. Tente novamente.');
            } finally {
                this.gerando = false;
            }
        }
    }
}
</script>
@endsection
