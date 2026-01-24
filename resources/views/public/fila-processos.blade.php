@extends('layouts.public')

@section('title', 'Processos - InfoVISA')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="{
    abaAtiva: 'fila',
    cnpj: '',
    statusSelecionados: ['aberto', 'em_analise'],
    formatCnpj() {
        let value = this.cnpj.replace(/\D/g, '');
        if (value.length <= 14) {
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
            this.cnpj = value;
        }
    },
    toggleStatus(status) {
        if (this.statusSelecionados.includes(status)) {
            this.statusSelecionados = this.statusSelecionados.filter(s => s !== status);
        } else {
            this.statusSelecionados.push(status);
        }
    },
    mostrarProcesso(status) {
        return this.statusSelecionados.includes(status);
    }
}">
    <!-- Header -->
    <div class="relative bg-gradient-to-br from-purple-600 via-indigo-600 to-purple-700 py-16 overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-400 rounded-full opacity-20 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-400 rounded-full opacity-20 blur-3xl"></div>
        </div>
        
        <div class="relative max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm text-white rounded-full text-sm font-medium mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Processos Sanitários
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-4 tracking-tight">
                    Consulte & Acompanhe
                </h1>
                <p class="text-base text-purple-100 max-w-2xl mx-auto">
                    Veja a fila de processos em tempo real ou consulte pelo CNPJ
                </p>
            </div>
        </div>
    </div>
    
    <!-- Tabs -->
    <div class="bg-white shadow-sm sticky top-16 z-40">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex gap-8" aria-label="Tabs">
                <button 
                    @click="abaAtiva = 'fila'"
                    :class="abaAtiva === 'fila' ? 'border-purple-600 text-purple-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="relative py-4 px-1 border-b-2 text-sm transition-all">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Fila de Processos
                    </span>
                </button>
                <button 
                    @click="abaAtiva = 'consultar'"
                    :class="abaAtiva === 'consultar' ? 'border-purple-600 text-purple-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="relative py-4 px-1 border-b-2 text-sm transition-all">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Consultar por CNPJ
                    </span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Aba: Consultar Processo -->
        <div x-show="abaAtiva === 'consultar'" x-transition>
            <div class="max-w-xl mx-auto">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl mb-4 shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Consultar Processo</h2>
                    <p class="text-xs text-gray-600">Digite o CNPJ para ver todos os processos do estabelecimento</p>
                </div>
                
                <div class="bg-gradient-to-br from-gray-50 to-purple-50 rounded-2xl shadow-xl border border-gray-200 p-8">
                    <form action="{{ route('consultar.processo') }}" method="POST" class="space-y-5">
                        @csrf
                        <div>
                            <label for="cnpj" class="block text-xs font-semibold text-gray-900 mb-2">
                                CNPJ da Empresa
                            </label>
                            <input 
                                type="text" 
                                id="cnpj"
                                name="cnpj"
                                x-model="cnpj"
                                @input="formatCnpj()"
                                placeholder="00.000.000/0000-00"
                                maxlength="18"
                                required
                                class="w-full px-4 py-3 text-sm font-mono border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white transition-all"
                            >
                            <p class="mt-2 text-xs text-gray-600 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Formato: 00.000.000/0000-00
                            </p>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-3 px-4 rounded-xl text-sm font-semibold hover:from-purple-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Buscar Processos
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Aba: Fila de Processos -->
        <div x-show="abaAtiva === 'fila'" x-transition>
        @if(count($filaProcessos) > 0)
            <!-- Filtros de Status -->
            <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filtrar por Status
                    </h4>
                    <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full" x-text="statusSelecionados.length + ' selecionado(s)'"></span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <!-- Aberto -->
                    <button 
                        @click="toggleStatus('aberto')"
                        :class="statusSelecionados.includes('aberto') ? 'bg-blue-100 text-blue-800 border-blue-300' : 'bg-gray-100 text-gray-600 border-gray-300'"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium border-2 transition-all hover:shadow-sm">
                        <span class="w-2 h-2 rounded-full" :class="statusSelecionados.includes('aberto') ? 'bg-blue-600' : 'bg-gray-400'"></span>
                        Aberto
                    </button>
                    
                    <!-- Em Análise -->
                    <button 
                        @click="toggleStatus('em_analise')"
                        :class="statusSelecionados.includes('em_analise') ? 'bg-yellow-100 text-yellow-800 border-yellow-300' : 'bg-gray-100 text-gray-600 border-gray-300'"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium border-2 transition-all hover:shadow-sm">
                        <span class="w-2 h-2 rounded-full" :class="statusSelecionados.includes('em_analise') ? 'bg-yellow-600' : 'bg-gray-400'"></span>
                        Em Análise
                    </button>
                    
                    <!-- Pendente -->
                    <button 
                        @click="toggleStatus('pendente')"
                        :class="statusSelecionados.includes('pendente') ? 'bg-orange-100 text-orange-800 border-orange-300' : 'bg-gray-100 text-gray-600 border-gray-300'"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium border-2 transition-all hover:shadow-sm">
                        <span class="w-2 h-2 rounded-full" :class="statusSelecionados.includes('pendente') ? 'bg-orange-600' : 'bg-gray-400'"></span>
                        Pendente
                    </button>
                    
                    <!-- Parado -->
                    <button 
                        @click="toggleStatus('parado')"
                        :class="statusSelecionados.includes('parado') ? 'bg-red-100 text-red-800 border-red-300' : 'bg-gray-100 text-gray-600 border-gray-300'"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium border-2 transition-all hover:shadow-sm">
                        <span class="w-2 h-2 rounded-full" :class="statusSelecionados.includes('parado') ? 'bg-red-600' : 'bg-gray-400'"></span>
                        Parado
                    </button>
                    
                    <!-- Arquivado -->
                    <button 
                        @click="toggleStatus('arquivado')"
                        :class="statusSelecionados.includes('arquivado') ? 'bg-gray-600 text-white border-gray-700' : 'bg-gray-100 text-gray-600 border-gray-300'"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium border-2 transition-all hover:shadow-sm">
                        <span class="w-2 h-2 rounded-full" :class="statusSelecionados.includes('arquivado') ? 'bg-gray-200' : 'bg-gray-400'"></span>
                        Arquivado
                    </button>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-5">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 mb-1">Como funciona a fila?</h4>
                        <p class="text-xs text-gray-700 leading-relaxed">
                            Os processos são ordenados por <strong>data de criação</strong> (mais antigo primeiro). 
                            Use os filtros acima para visualizar apenas os status desejados.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filas por Tipo de Processo -->
            <div class="space-y-8">
                @foreach($filaProcessos as $fila)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Header da Fila -->
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-bold text-white">
                                    {{ $fila['tipo'] }}
                                </h3>
                                <p class="text-xs text-purple-100 mt-1">
                                    {{ count($fila['processos']) }} processo(s) em fila
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-purple-200">Atualizado em</div>
                                <div class="text-sm text-white font-medium">{{ now()->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Processos -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Posição
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Nº Processo
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Estabelecimento
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        <div class="flex items-center gap-1" title="Ordenado do mais antigo para o mais recente">
                                            Data Abertura
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                                            </svg>
                                        </div>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Tempo na Fila
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($fila['processos'] as $processo)
                                <tr class="hover:bg-gray-50 transition-colors" 
                                    x-show="mostrarProcesso('{{ $processo['status'] }}')"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 transform scale-95"
                                    x-transition:enter-end="opacity-100 transform scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 transform scale-100"
                                    x-transition:leave-end="opacity-0 transform scale-95">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-purple-100 text-purple-700 font-bold text-sm">
                                            {{ $processo['posicao'] }}º
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-xs font-medium text-gray-900">{{ $processo['numero_processo'] }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs text-gray-700">{{ $processo['estabelecimento'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'aberto' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                'em_analise' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                'pendente' => 'bg-orange-100 text-orange-800 border-orange-200',
                                                'parado' => 'bg-red-100 text-red-800 border-red-200',
                                                'arquivado' => 'bg-gray-100 text-gray-800 border-gray-200',
                                            ];
                                            $statusLabels = [
                                                'aberto' => 'Aberto',
                                                'em_analise' => 'Em Análise',
                                                'pendente' => 'Pendente',
                                                'parado' => 'Parado',
                                                'arquivado' => 'Arquivado',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusColors[$processo['status']] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                                            {{ $statusLabels[$processo['status']] ?? ucfirst($processo['status']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                                        {{ $processo['data_abertura'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-xs font-medium text-gray-900">
                                            {{ $processo['tempo_formatado'] }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer com estatísticas -->
                    <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                        <div class="flex items-center justify-between text-xs text-gray-600">
                            <span>
                                <strong>Total:</strong> {{ count($fila['processos']) }} processos
                            </span>
                            <span class="text-gray-500">
                                Ordenado por data de abertura (mais antigo primeiro)
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Legenda de Status -->
            <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-xs font-semibold text-gray-900 mb-4">Legenda de Status</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border bg-blue-100 text-blue-800 border-blue-200">
                            Aberto
                        </span>
                        <span class="text-xs text-gray-600">Recém criado</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border bg-yellow-100 text-yellow-800 border-yellow-200">
                            Em Análise
                        </span>
                        <span class="text-xs text-gray-600">Sendo analisado</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border bg-orange-100 text-orange-800 border-orange-200">
                            Pendente
                        </span>
                        <span class="text-xs text-gray-600">Aguardando docs</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border bg-red-100 text-red-800 border-red-200">
                            Parado
                        </span>
                        <span class="text-xs text-gray-600">Suspenso</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border bg-gray-100 text-gray-800 border-gray-200">
                            Arquivado
                        </span>
                        <span class="text-xs text-gray-600">Arquivado</span>
                    </div>
                </div>
            </div>

        @else
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <h3 class="text-base font-semibold text-gray-900 mb-2">Nenhum processo na fila</h3>
                <p class="text-xs text-gray-600 max-w-md mx-auto mb-6">
                    Não há processos em andamento no momento. Processos aparecerão aqui quando forem criados e estiverem com status de aberto, em análise, pendente ou parado.
                </p>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                    Voltar para Home
                </a>
            </div>
        @endif
        </div>
        <!-- Fim Aba: Fila de Processos -->
        
    </div>
</div>
@endsection
