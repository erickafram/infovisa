@extends('layouts.admin')

@section('title', 'Pactuação - Competências')
@section('page-title', 'Pactuação de Competências')

@section('content')
<div class="max-w-8xl mx-auto" x-data="pactuacaoManager()">
    
    {{-- Informações --}}
    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-blue-800 mb-1">Como funciona a Pactuação?</h3>
                <p class="text-sm text-blue-700">
                    Configure quais atividades (CNAEs) são de competência <strong>Municipal</strong> ou <strong>Estadual</strong>. 
                    Um estabelecimento será considerado <strong>Estadual</strong> se <strong>pelo menos uma</strong> de suas atividades for estadual.
                    Caso contrário, será <strong>Municipal</strong>.
                </p>
            </div>
        </div>
    </div>

    {{-- Campo de Pesquisa Global --}}
    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" 
                   x-model="termoPesquisa"
                   @input="pesquisarAtividade()"
                   placeholder="Pesquisar atividade por código CNAE ou descrição..."
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <button @click="limparPesquisa()" 
                    x-show="termoPesquisa.length > 0"
                    class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                Limpar
            </button>
        </div>
        
        {{-- Resultados da Pesquisa --}}
        <div x-show="resultadosPesquisa.length > 0" 
             x-cloak
             class="mt-4 border-t border-gray-200 pt-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">
                <span x-text="resultadosPesquisa.length"></span> resultado(s) encontrado(s):
            </h4>
            <div class="space-y-2 max-h-96 overflow-y-auto">
                <template x-for="resultado in resultadosPesquisa" :key="resultado.id">
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer"
                         @click="irParaAba(resultado.tabela)">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-mono text-sm font-semibold text-gray-900" x-text="resultado.cnae_codigo"></span>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full"
                                      :class="{
                                          'bg-blue-100 text-blue-800': resultado.tabela === 'I',
                                          'bg-orange-100 text-orange-800': resultado.tabela === 'II',
                                          'bg-red-100 text-red-800': resultado.tabela === 'III',
                                          'bg-purple-100 text-purple-800': resultado.tabela === 'IV',
                                          'bg-green-100 text-green-800': resultado.tabela === 'V'
                                      }"
                                      x-text="'Tabela ' + resultado.tabela"></span>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full"
                                      :class="resultado.tipo === 'estadual' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800'"
                                      x-text="resultado.tipo === 'estadual' ? 'Estadual' : 'Municipal'"></span>
                            </div>
                            <p class="text-sm text-gray-700" x-text="resultado.cnae_descricao"></p>
                            <p x-show="resultado.observacao" class="text-xs text-gray-500 mt-1" x-text="resultado.observacao"></p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </template>
            </div>
        </div>
        
        {{-- Mensagem quando não encontrar --}}
        <div x-show="termoPesquisa.length > 0 && resultadosPesquisa.length === 0 && !pesquisando" 
             x-cloak
             class="mt-4 text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm">Nenhuma atividade encontrada com "<span x-text="termoPesquisa" class="font-semibold"></span>"</p>
        </div>
        
        {{-- Loading --}}
        <div x-show="pesquisando" x-cloak class="mt-4 text-center py-4">
            <svg class="animate-spin h-6 w-6 text-blue-600 mx-auto" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-4 overflow-x-auto">
                <button @click="abaAtiva = 'tabela-i'" 
                        :class="abaAtiva === 'tabela-i' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <div class="flex items-center gap-2">
                        Tabela I - Municipal
                        <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            {{ $tabelaI->count() }}
                        </span>
                    </div>
                </button>
                
                <button @click="abaAtiva = 'tabela-ii'" 
                        :class="abaAtiva === 'tabela-ii' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <div class="flex items-center gap-2">
                        Tabela II - Estadual Exclusiva
                        <span class="ml-2 bg-orange-100 text-orange-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            {{ $tabelaII->count() }}
                        </span>
                    </div>
                </button>
                
                <button @click="abaAtiva = 'tabela-iii'" 
                        :class="abaAtiva === 'tabela-iii' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <div class="flex items-center gap-2">
                        Tabela III - Alto Risco
                        <span class="ml-2 bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            {{ $tabelaIII->count() }}
                        </span>
                    </div>
                </button>
                
                <button @click="abaAtiva = 'tabela-iv'" 
                        :class="abaAtiva === 'tabela-iv' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <div class="flex items-center gap-2">
                        Tabela IV - Com Questionário
                        <span class="ml-2 bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            {{ $tabelaIV->count() }}
                        </span>
                    </div>
                </button>
                
                <button @click="abaAtiva = 'tabela-v'" 
                        :class="abaAtiva === 'tabela-v' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <div class="flex items-center gap-2">
                        Tabela V - Definir VISA
                        <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            {{ $tabelaV->count() }}
                        </span>
                    </div>
                </button>
            </nav>
        </div>
    </div>

    {{-- Tabela I - Atividades Municipais --}}
    <div x-show="abaAtiva === 'tabela-i'" x-cloak>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Tabela I - Atividades Municipais</h3>
                    <p class="text-sm text-gray-600 mt-1">Atividades de competência dos 139 municípios do Tocantins</p>
                </div>
                <button @click="modalAdicionar = true; tipoModal = 'municipal'; municipioModal = null"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Adicionar Atividade
                </button>
            </div>

            @if($tabelaI->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma atividade cadastrada</h3>
                    <p class="mt-1 text-sm text-gray-500">Adicione as atividades que são de competência estadual</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código CNAE</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risco</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($tabelaI as $pactuacao)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $pactuacao->cnae_codigo }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div>{{ $pactuacao->cnae_descricao }}</div>
                                    @if($pactuacao->observacao)
                                        <div class="mt-1 text-xs text-gray-500 italic">
                                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $pactuacao->observacao }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($pactuacao->classificacao_risco === 'baixo')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Baixo
                                        </span>
                                    @elseif($pactuacao->classificacao_risco === 'medio')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Médio
                                        </span>
                                    @elseif($pactuacao->classificacao_risco === 'alto')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Alto
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $pactuacao->ativo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $pactuacao->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="abrirModalEditarCompleto({{ $pactuacao->id }})" 
                                            class="text-gray-600 hover:text-gray-900 mr-3">
                                        Editar
                                    </button>
                                    <button @click="toggleStatus({{ $pactuacao->id }})" 
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                        {{ $pactuacao->ativo ? 'Desativar' : 'Ativar' }}
                                    </button>
                                    <button @click="remover({{ $pactuacao->id }})" 
                                            class="text-red-600 hover:text-red-900">
                                        Remover
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Tabela II - Atividades Estaduais Exclusivas --}}
    <div x-show="abaAtiva === 'tabela-ii'" x-cloak>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Tabela II - Atividades Estaduais Exclusivas</h3>
                    <p class="text-sm text-gray-600 mt-1">Atividades que são SEMPRE de competência estadual (não descentralizadas)</p>
                </div>
                <button @click="modalAdicionar = true; tipoModal = 'estadual'; tabelaSelecionada = 'II'; municipioModal = null"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Adicionar Atividade
                </button>
            </div>

            @if($tabelaII->isEmpty())
                <div class="text-center py-12">
                    <p class="text-sm text-gray-500">Nenhuma atividade cadastrada</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código CNAE</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Risco</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($tabelaII as $pactuacao)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $pactuacao->cnae_codigo }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $pactuacao->cnae_descricao }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Alto</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="abrirModalEditarCompleto({{ $pactuacao->id }})" 
                                            class="text-gray-600 hover:text-gray-900 mr-3">
                                        Editar
                                    </button>
                                    <button @click="toggleStatus({{ $pactuacao->id }})" 
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                        {{ $pactuacao->ativo ? 'Desativar' : 'Ativar' }}
                                    </button>
                                    <button @click="remover({{ $pactuacao->id }})" 
                                            class="text-red-600 hover:text-red-900">
                                        Remover
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Tabela III - Atividades Alto Risco Pactuadas --}}
    <div x-show="abaAtiva === 'tabela-iii'" x-cloak>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Tabela III - Atividades de Alto Risco Pactuadas</h3>
                    <p class="text-sm text-gray-600 mt-1">Atividades estaduais descentralizadas para municípios específicos</p>
                </div>
                <button @click="modalAdicionar = true; tipoModal = 'estadual'; tabelaSelecionada = 'III'; municipioModal = null"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Adicionar Atividade
                </button>
            </div>

            @if($tabelaIII->isEmpty())
                <div class="text-center py-12">
                    <p class="text-sm text-gray-500">Nenhuma atividade cadastrada</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código CNAE</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Municípios Descentralizados</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($tabelaIII as $pactuacao)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $pactuacao->cnae_codigo }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $pactuacao->cnae_descricao }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @if($pactuacao->municipios_excecao && count($pactuacao->municipios_excecao) > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($pactuacao->municipios_excecao as $mun)
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">{{ $mun }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400">Nenhum</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="abrirModalEditarCompleto({{ $pactuacao->id }})" 
                                            class="text-gray-600 hover:text-gray-900 mr-3">
                                        Editar
                                    </button>
                                    <button @click="toggleStatus({{ $pactuacao->id }})" 
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                        {{ $pactuacao->ativo ? 'Desativar' : 'Ativar' }}
                                    </button>
                                    <button @click="remover({{ $pactuacao->id }})" 
                                            class="text-red-600 hover:text-red-900">
                                        Remover
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Tabela IV - Atividades com Questionário --}}
    <div x-show="abaAtiva === 'tabela-iv'" x-cloak>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Tabela IV - Atividades com Questionário</h3>
                    <p class="text-sm text-gray-600 mt-1">Competência definida por questionário (Estadual ou Municipal)</p>
                </div>
                <button @click="modalAdicionar = true; tipoModal = 'estadual'; tabelaSelecionada = 'IV'; municipioModal = null"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Adicionar Atividade
                </button>
            </div>

            @if($tabelaIV->isEmpty())
                <div class="text-center py-12">
                    <p class="text-sm text-gray-500">Nenhuma atividade cadastrada</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($tabelaIV as $pactuacao)
                    <div class="border border-purple-200 rounded-lg p-4 bg-purple-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-semibold text-gray-900">{{ $pactuacao->cnae_codigo }}</span>
                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-800 text-xs rounded-full">Questionário</span>
                                </div>
                                <p class="text-sm text-gray-700 mb-2">{{ $pactuacao->cnae_descricao }}</p>
                                <div class="bg-white border border-purple-200 rounded p-3 mb-2">
                                    <p class="text-xs font-semibold text-purple-900 mb-1">❓ Pergunta:</p>
                                    <p class="text-sm text-gray-700">{{ $pactuacao->pergunta }}</p>
                                </div>
                                @if($pactuacao->municipios_excecao && count($pactuacao->municipios_excecao) > 0)
                                    <div class="mt-2">
                                        <p class="text-xs text-gray-600 mb-1">Municípios descentralizados (se SIM):</p>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($pactuacao->municipios_excecao as $mun)
                                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded">{{ $mun }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-col gap-2 ml-4">
                                <button @click="abrirModalEditarCompleto({{ $pactuacao->id }})" 
                                        class="text-xs text-gray-600 hover:text-gray-900">
                                    ✏️ Editar
                                </button>
                                <button @click="toggleStatus({{ $pactuacao->id }})" 
                                        class="text-xs text-blue-600 hover:text-blue-900">
                                    {{ $pactuacao->ativo ? '🔒 Desativar' : '✅ Ativar' }}
                                </button>
                                <button @click="remover({{ $pactuacao->id }})" 
                                        class="text-xs text-red-600 hover:text-red-900">
                                    🗑️ Remover
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Tabela V - Definir se é VISA --}}
    <div x-show="abaAtiva === 'tabela-v'" x-cloak>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Tabela V - Definir se é Sujeito à VISA</h3>
                    <p class="text-sm text-gray-600 mt-1">Questionário define se a atividade é sujeita à vigilância sanitária</p>
                </div>
                <button @click="modalAdicionar = true; tipoModal = 'estadual'; tabelaSelecionada = 'V'; municipioModal = null"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Adicionar Atividade
                </button>
            </div>

            @if($tabelaV->isEmpty())
                <div class="text-center py-12">
                    <p class="text-sm text-gray-500">Nenhuma atividade cadastrada</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($tabelaV as $pactuacao)
                    <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-semibold text-gray-900">{{ $pactuacao->cnae_codigo }}</span>
                                    <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded-full">Definir VISA</span>
                                </div>
                                <p class="text-sm text-gray-700 mb-2">{{ $pactuacao->cnae_descricao }}</p>
                                <div class="bg-white border border-green-200 rounded p-3">
                                    <p class="text-xs font-semibold text-green-900 mb-1">❓ Pergunta:</p>
                                    <p class="text-sm text-gray-700">{{ $pactuacao->pergunta }}</p>
                                </div>
                                <div class="mt-2 text-xs text-gray-600">
                                    <p><strong>SIM:</strong> Sujeito à VISA (aplicar regras de competência)</p>
                                    <p><strong>NÃO:</strong> NÃO sujeito à VISA (não precisa licença)</p>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2 ml-4">
                                <button @click="abrirModalEditarCompleto({{ $pactuacao->id }})" 
                                        class="text-xs text-gray-600 hover:text-gray-900">
                                    ✏️ Editar
                                </button>
                                <button @click="toggleStatus({{ $pactuacao->id }})" 
                                        class="text-xs text-blue-600 hover:text-blue-900">
                                    {{ $pactuacao->ativo ? '🔒 Desativar' : '✅ Ativar' }}
                                </button>
                                <button @click="remover({{ $pactuacao->id }})" 
                                        class="text-xs text-red-600 hover:text-red-900">
                                    🗑️ Remover
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Adicionar Atividade --}}
    <div x-show="modalAdicionar" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalAdicionar"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

            <div x-show="modalAdicionar"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block w-full max-w-5xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-white">
                            <span x-text="tipoModal === 'estadual' ? 'Competência Estadual' : municipioModal"></span>
                        </h3>
                        <button @click="fecharModal()" class="text-white hover:text-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <form @submit.prevent="adicionarAtividades" class="p-6">
                    {{-- Layout em duas colunas --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        {{-- Coluna Esquerda: Configurações --}}
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Tabela *
                                </label>
                                <select x-model="tabelaSelecionada" 
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required>
                                    <option value="">Selecione a tabela</option>
                                    <option value="I">Tabela I - Municipal (139 municípios)</option>
                                    <option value="II">Tabela II - Estadual Exclusiva</option>
                                    <option value="III">Tabela III - Alto Risco Pactuado</option>
                                    <option value="IV">Tabela IV - Com Questionário (Estadual/Municipal)</option>
                                    <option value="V">Tabela V - Definir se é VISA</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Classificação de Risco *
                                </label>
                                <select x-model="classificacaoRisco" 
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required>
                                    <option value="">Selecione o risco</option>
                                    <option value="baixo">Baixo</option>
                                    <option value="medio">Médio</option>
                                    <option value="alto">Alto</option>
                                </select>
                            </div>

                            <div x-show="tabelaSelecionada === 'IV' || tabelaSelecionada === 'V'">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Pergunta do Questionário *
                                </label>
                                <textarea 
                                    x-model="perguntaQuestionario" 
                                    rows="3"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Ex: O resultado do exercício da atividade será diferente de produto artesanal?"
                                    :required="tabelaSelecionada === 'IV' || tabelaSelecionada === 'V'"></textarea>
                                <p class="mt-1 text-xs text-gray-500">
                                    <span x-show="tabelaSelecionada === 'IV'">Resposta SIM = Estadual | NÃO = Municipal</span>
                                    <span x-show="tabelaSelecionada === 'V'">Resposta SIM = Sujeito à VISA | NÃO = Não sujeito</span>
                                </p>
                            </div>
                            
                            <div x-show="tabelaSelecionada === 'III' || tabelaSelecionada === 'IV' || tabelaSelecionada === 'V'">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <span x-show="tabelaSelecionada === 'III'">Municípios Descentralizados (Exceções)</span>
                                    <span x-show="tabelaSelecionada === 'IV'">Municípios Descentralizados (se SIM)</span>
                                    <span x-show="tabelaSelecionada === 'V'">Municípios Descentralizados (se SIM e VISA)</span>
                                </label>
                                
                                <div class="relative" @click.away="dropdownAberto = false">
                                    <div class="border border-gray-300 rounded-lg p-2 flex flex-wrap gap-2 cursor-text min-h-[42px] bg-white" 
                                         @click="dropdownAberto = true; $nextTick(() => $refs.inputBusca.focus())">
                                        <template x-for="mun in municipiosSelecionados" :key="mun">
                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full flex items-center gap-1">
                                                <span x-text="mun"></span>
                                                <button type="button" @click.stop="removerMunicipio(mun)" class="hover:text-blue-900 font-bold px-1">×</button>
                                            </span>
                                        </template>
                                        <input type="text" 
                                               x-ref="inputBusca"
                                               x-model="buscaMunicipio" 
                                               @focus="dropdownAberto = true"
                                               class="outline-none text-sm flex-1 min-w-[120px] border-none focus:ring-0 p-0" 
                                               placeholder="Buscar município...">
                                    </div>
                                    
                                    <div x-show="dropdownAberto && municipiosFiltrados().length > 0" 
                                         class="absolute z-20 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto mt-1">
                                        <template x-for="mun in municipiosFiltrados()" :key="mun.id">
                                            <div class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm text-gray-700" 
                                                 @click="adicionarMunicipio(mun.nome)">
                                                <span x-text="mun.nome"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <div x-show="dropdownAberto && municipiosFiltrados().length === 0 && buscaMunicipio.length > 0" 
                                         class="absolute z-20 w-full bg-white border border-gray-200 rounded-lg shadow-lg p-3 text-sm text-gray-500 mt-1">
                                        Nenhum município encontrado
                                    </div>
                                </div>
                                
                                <p class="mt-1 text-xs text-gray-500">
                                    <span x-show="tabelaSelecionada === 'III'">Municípios que receberam descentralização para fiscalizar esta atividade.</span>
                                    <span x-show="tabelaSelecionada === 'IV'">Municípios descentralizados (se resposta for SIM).</span>
                                    <span x-show="tabelaSelecionada === 'V'">Municípios descentralizados (se resposta for SIM e sujeito à VISA).</span>
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Observações
                                    <span class="text-xs text-gray-500">(opcional)</span>
                                </label>
                                <textarea 
                                    x-model="observacaoTexto" 
                                    rows="3"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Ex: Aplica-se apenas se não for produto artesanal"></textarea>
                            </div>
                        </div>
                        
                        {{-- Coluna Direita: Adicionar CNAEs --}}
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Adicionar Atividades: <span x-text="tabelaSelecionada ? 'Tabela ' + tabelaSelecionada : 'Selecione a tabela'"></span>
                                </label>
                                
                                {{-- Campo de entrada para CNAE com autocomplete --}}
                                <div class="flex gap-2 mb-3">
                                    <div class="flex-1 relative">
                                        <input type="text" 
                                               x-model="cnaeInput" 
                                               @input="buscarCnaeAutocomplete()"
                                               @keyup.enter="adicionarCnae()"
                                               @keydown.down.prevent="navegarSugestoes(1)"
                                               @keydown.up.prevent="navegarSugestoes(-1)"
                                               @blur="setTimeout(() => sugestoesCnae = [], 200)"
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Digite o CNAE (ex: 4711-3/02 ou 4711302)">
                                        
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
                                    </div>
                                    <button type="button" 
                                            @click="adicionarCnae()"
                                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                                        Adicionar
                                    </button>
                                </div>
                                
                                <p class="text-xs text-gray-500 mb-3">
                                    💡 Digite o CNAE com ou sem formatação (4711-3/02 ou 4711302). O sistema busca automaticamente a descrição.
                                </p>
                                
                                {{-- Área para colar múltiplos CNAEs --}}
                                <div class="mb-3">
                                    <div class="flex gap-2">
                                        <textarea 
                                            x-model="cnaesTextoMultiplo" 
                                            rows="2"
                                            class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Ou cole vários CNAEs de uma vez (separados por vírgula, quebra de linha ou espaço)"></textarea>
                                        <button type="button" 
                                                @click="importarCnaesMultiplos()"
                                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                                            Importar Todos
                                        </button>
                                    </div>
                                </div>
                                
                                {{-- Lista de atividades adicionadas --}}
                                <div class="border border-gray-200 rounded-lg p-3 bg-gray-50 min-h-[300px]">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-sm font-medium text-gray-700">
                                            Atividades a serem cadastradas (<span x-text="atividadesParaCadastro.length"></span>)
                                        </h4>
                                        <button type="button" 
                                                @click="limparTodasAtividades()"
                                                x-show="atividadesParaCadastro.length > 0"
                                                class="text-xs text-red-600 hover:text-red-800">
                                            Limpar Todas
                                        </button>
                                    </div>
                                    
                                    <div x-show="atividadesParaCadastro.length === 0" class="text-center py-8 text-gray-500">
                                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-sm">Nenhum CNAE adicionado ainda</p>
                                    </div>
                                    
                                    <div x-show="atividadesParaCadastro.length > 0" class="space-y-3 max-h-96 overflow-y-auto">
                                        <template x-for="(atividade, index) in atividadesParaCadastro" :key="index">
                                            <div class="p-3 bg-white border border-gray-200 rounded-lg">
                                                <div class="flex items-start gap-3 mb-2">
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <span class="font-mono text-sm font-semibold text-gray-900" x-text="atividade.codigo"></span>
                                                            <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full" x-text="atividade.status || 'Novo'"></span>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-600 mb-1">Descrição da Atividade:</label>
                                                            <textarea 
                                                                x-model="atividade.descricao"
                                                                rows="2"
                                                                class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                placeholder="Digite ou edite a descrição da atividade"></textarea>
                                                        </div>
                                                    </div>
                                                    <button type="button" 
                                                            @click="removerAtividade(index)"
                                                            class="text-red-500 hover:text-red-700 p-1 flex-shrink-0">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Botões --}}
                    <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 mt-6">
                        <button type="button" 
                                @click="fecharModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                :disabled="processando || atividadesParaCadastro.length === 0"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                            <span x-show="!processando">Salvar <span x-text="atividadesParaCadastro.length"></span> Atividade<span x-show="atividadesParaCadastro.length !== 1">s</span></span>
                            <span x-show="processando">Processando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Adicionar Exceção --}}
    <div x-show="modalExcecao" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="modalExcecao"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

            <div x-show="modalExcecao"
                 class="inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle bg-white rounded-lg shadow-xl z-10">
                
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-white">
                            Adicionar Município Descentralizado
                        </h3>
                        <button @click="modalExcecao = false" class="text-white hover:text-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <form @submit.prevent="adicionarExcecao" class="p-4">
                    <p class="text-sm text-gray-600 mb-3">
                        CNAE: <strong x-text="excecaoCnae"></strong>
                    </p>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nome do Município
                        </label>
                        <select 
                            x-model="excecaoMunicipio" 
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                            <option value="">Selecione o município...</option>
                            @foreach($todosMunicipios as $municipio)
                                <option value="{{ $municipio->nome }}">{{ $municipio->nome }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Este município terá competência para fiscalizar esta atividade.
                        </p>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" 
                                @click="modalExcecao = false"
                                class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                :disabled="processando"
                                class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!processando">Adicionar</span>
                            <span x-show="processando">Processando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Editar Observação --}}
    <div x-show="modalEditar" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="modalEditar"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

            <div x-show="modalEditar"
                 class="inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle bg-white rounded-lg shadow-xl z-10">
                
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-white">
                            Editar Observação
                        </h3>
                        <button @click="modalEditar = false" class="text-white hover:text-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <form @submit.prevent="salvarObservacao" class="p-4">
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Observação
                        </label>
                        <textarea 
                            x-model="editarObservacao" 
                            rows="3"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ex: Aplica-se apenas se não for produto artesanal"></textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" 
                                @click="modalEditar = false"
                                class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                :disabled="processando"
                                class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!processando">Salvar</span>
                            <span x-show="processando">Salvando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function pactuacaoManager() {
    return {
        // Dados básicos
        todosMunicipios: @json($todosMunicipios),
        
        // Estado da interface
        abaAtiva: 'tabela-i',
        modalAdicionar: false,
        modalExcecao: false,
        modalEditar: false,
        processando: false,
        
        // Dados do formulário
        tipoModal: 'estadual',
        municipioModal: null,
        tabelaSelecionada: '',
        classificacaoRisco: '',
        perguntaQuestionario: '',
        observacaoTexto: '',
        
        // Municípios
        municipiosSelecionados: [],
        buscaMunicipio: '',
        dropdownAberto: false,
        
        // CNAEs - nova lógica
        cnaeInput: '',
        cnaesTextoMultiplo: '',
        atividadesParaCadastro: [],
        buscandoCnae: false,
        
        // Autocomplete de CNAE
        sugestoesCnae: [],
        indiceSugestaoSelecionada: -1,
        timeoutAutocomplete: null,
        
        // Edição
        editarId: null,
        editarObservacao: '',
        
        // Exceções
        excecaoId: null,
        excecaoCnae: '',
        excecaoMunicipio: '',
        
        // Pesquisa
        termoPesquisa: '',
        resultadosPesquisa: [],
        pesquisando: false,
        timeoutPesquisa: null,

        adicionarMunicipio(nome) {
            if (!this.municipiosSelecionados.includes(nome)) {
                this.municipiosSelecionados.push(nome);
                this.municipiosSelecionados.sort();
            }
            this.buscaMunicipio = '';
            // Mantém o dropdown aberto para selecionar mais
            this.$refs.inputBusca.focus();
        },

        removerMunicipio(nome) {
            this.municipiosSelecionados = this.municipiosSelecionados.filter(m => m !== nome);
        },
        
        municipiosFiltrados() {
            const busca = this.buscaMunicipio.toLowerCase();
            return this.todosMunicipios.filter(m => 
                m.nome.toLowerCase().includes(busca) && 
                !this.municipiosSelecionados.includes(m.nome)
            );
        },
        
        // Normaliza CNAE removendo pontos, hífens, barras e espaços
        normalizarCnae(cnae) {
            return cnae.replace(/[.\-\s\/]/g, '');
        },
        
        // Busca sugestões de CNAE enquanto digita (autocomplete)
        async buscarCnaeAutocomplete() {
            clearTimeout(this.timeoutAutocomplete);
            
            const termo = this.cnaeInput.trim();
            if (termo.length < 4) {
                this.sugestoesCnae = [];
                return;
            }
            
            this.timeoutAutocomplete = setTimeout(async () => {
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
        
        // Seleciona uma sugestão do autocomplete
        selecionarSugestao(sugestao) {
            const cnaeNormalizado = this.normalizarCnae(sugestao.codigo);
            
            // Verifica se já foi adicionado
            if (this.atividadesParaCadastro.find(a => this.normalizarCnae(a.codigo) === cnaeNormalizado)) {
                alert('Este CNAE já foi adicionado à lista');
                this.cnaeInput = '';
                this.sugestoesCnae = [];
                return;
            }
            
            // Adiciona à lista
            this.atividadesParaCadastro.push({
                codigo: cnaeNormalizado,
                descricao: sugestao.descricao,
                status: 'Encontrado'
            });
            
            this.cnaeInput = '';
            this.sugestoesCnae = [];
        },

        // Funções para gerenciar CNAEs
        async adicionarCnae() {
            let codigo = this.cnaeInput.trim();
            if (!codigo) return;
            
            // Normaliza o CNAE (remove pontos, hífens, barras, espaços)
            codigo = this.normalizarCnae(codigo);
            
            // Verifica se já foi adicionado
            if (this.atividadesParaCadastro.find(a => this.normalizarCnae(a.codigo) === codigo)) {
                alert('Este CNAE já foi adicionado à lista');
                this.cnaeInput = '';
                this.sugestoesCnae = [];
                return;
            }
            
            this.buscandoCnae = true;
            
            try {
                // Busca a descrição do CNAE
                const url = `{{ route('admin.configuracoes.pactuacao.buscar-cnaes') }}?termo=${encodeURIComponent(codigo)}`;
                console.log('Buscando CNAE:', url);
                
                const response = await fetch(url);
                const data = await response.json();
                
                console.log('Resposta buscar-cnaes:', data);
                
                let descricao = `Atividade ${codigo}`;
                let status = 'Novo';
                
                if (data.length > 0) {
                    // Procura correspondência exata primeiro
                    const match = data.find(d => d.codigo === codigo) || data[0];
                    descricao = match.descricao;
                    status = 'Encontrado';
                }
                
                // Adiciona à lista
                this.atividadesParaCadastro.push({
                    codigo: codigo,
                    descricao: descricao,
                    status: status
                });
                
                this.cnaeInput = '';
                
            } catch (error) {
                console.error(`Erro ao buscar CNAE ${codigo}:`, error);
                // Adiciona mesmo com erro
                this.atividadesParaCadastro.push({
                    codigo: codigo,
                    descricao: `Atividade ${codigo}`,
                    status: 'Erro na busca'
                });
                this.cnaeInput = '';
            } finally {
                this.buscandoCnae = false;
            }
        },

        async importarCnaesMultiplos() {
            const texto = this.cnaesTextoMultiplo.trim();
            if (!texto) return;
            
            // Separa por vírgula, quebra de linha ou espaço e normaliza cada CNAE
            const cnaes = texto.split(/[,\n\s]+/)
                .map(c => this.normalizarCnae(c.trim()))
                .filter(c => c && c.length > 0);
            
            if (cnaes.length === 0) {
                alert('Nenhum código CNAE válido encontrado');
                return;
            }
            
            this.buscandoCnae = true;
            
            for (const codigo of cnaes) {
                // Pula se já foi adicionado
                if (this.atividadesParaCadastro.find(a => this.normalizarCnae(a.codigo) === codigo)) {
                    continue;
                }
                
                try {
                    const response = await fetch(`{{ route('admin.configuracoes.pactuacao.buscar-cnaes') }}?termo=${encodeURIComponent(codigo)}`);
                    const data = await response.json();
                    
                    let descricao = `Atividade ${codigo}`;
                    let status = 'Novo';
                    
                    if (data.length > 0) {
                        const match = data.find(d => d.codigo === codigo) || data[0];
                        descricao = match.descricao;
                        status = 'Encontrado';
                    }
                    
                    this.atividadesParaCadastro.push({
                        codigo: codigo,
                        descricao: descricao,
                        status: status
                    });
                    
                } catch (error) {
                    console.error(`Erro ao buscar CNAE ${codigo}:`, error);
                    this.atividadesParaCadastro.push({
                        codigo: codigo,
                        descricao: `Atividade ${codigo}`,
                        status: 'Erro na busca'
                    });
                }
            }
            
            this.cnaesTextoMultiplo = '';
            this.buscandoCnae = false;
        },

        removerAtividade(index) {
            this.atividadesParaCadastro.splice(index, 1);
        },

        limparTodasAtividades() {
            if (confirm('Deseja remover todas as atividades da lista?')) {
                this.atividadesParaCadastro = [];
            }
        },

        async adicionarAtividades() {
            if (this.atividadesParaCadastro.length === 0) {
                alert('Adicione pelo menos uma atividade à lista');
                return;
            }

            this.processando = true;

            try {
                // Prepara municípios de exceção se for estadual
                let municipiosExcecao = null;
                if (this.tipoModal === 'estadual' && this.municipiosSelecionados.length > 0) {
                    municipiosExcecao = this.municipiosSelecionados;
                }

                // Verifica se é edição ou criação
                let url, method;
                if (this.editarId) {
                    // Modo edição - atualizar registro existente
                    url = `{{ url('admin/configuracoes/pactuacao') }}/${this.editarId}`;
                    method = 'POST'; // Usamos POST com _method PUT
                } else {
                    // Modo criação - criar novos registros
                    url = '{{ route('admin.configuracoes.pactuacao.store-multiple') }}';
                    method = 'POST';
                }

                const bodyData = {
                    tipo: this.tipoModal,
                    municipio: this.municipioModal,
                    tabela: this.tabelaSelecionada,
                    classificacao_risco: this.classificacaoRisco,
                    pergunta: (this.perguntaQuestionario && this.perguntaQuestionario.trim) ? this.perguntaQuestionario.trim() : null,
                    municipios_excecao: municipiosExcecao,
                    observacao: (this.observacaoTexto && this.observacaoTexto.trim) ? this.observacaoTexto.trim() : null
                };

                // Se for edição, adiciona _method PUT
                if (this.editarId) {
                    bodyData._method = 'PUT';
                } else {
                    // Se for criação, adiciona as atividades
                    bodyData.atividades = this.atividadesParaCadastro.map(a => ({
                        codigo: a.codigo,
                        descricao: a.descricao
                    }));
                }

                // Envia a requisição
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(bodyData)
                });

                // Debug: ver o que o servidor retornou
                const responseText = await response.text();
                console.log('Resposta do servidor:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('Erro ao fazer parse do JSON:', e);
                    console.error('Resposta recebida:', responseText.substring(0, 500));
                    alert('Erro no servidor. Verifique o console para mais detalhes.');
                    return;
                }
                
                if (data.success) {
                    alert(data.message);
                    this.fecharModal();
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao adicionar atividades: ' + error.message);
            } finally {
                this.processando = false;
            }
        },

        fecharModal() {
            this.modalAdicionar = false;
            this.editarId = null;
            this.cnaeInput = '';
            this.cnaesTextoMultiplo = '';
            this.atividadesParaCadastro = [];
            this.tabelaSelecionada = '';
            this.classificacaoRisco = '';
            this.perguntaQuestionario = '';
            this.municipiosSelecionados = [];
            this.observacaoTexto = '';
        },

        async toggleStatus(id) {
            if (!confirm('Deseja alterar o status desta atividade?')) return;

            try {
                const response = await fetch(`{{ url('admin/configuracoes/pactuacao') }}/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const responseText = await response.text();
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('Erro ao fazer parse:', e);
                    alert('Erro no servidor ao alterar status');
                    return;
                }
                
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao alterar status: ' + error.message);
            }
        },

        async remover(id) {
            if (!confirm('Deseja realmente remover esta atividade?')) return;

            try {
                const response = await fetch(`{{ url('admin/configuracoes/pactuacao') }}/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        _method: 'DELETE'
                    })
                });

                const responseText = await response.text();
                console.log('Resposta remover:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('Erro ao fazer parse:', e);
                    alert('Erro no servidor ao remover');
                    return;
                }
                
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao remover atividade: ' + error.message);
            }
        },

        abrirModalExcecao(id, cnae) {
            this.excecaoId = id;
            this.excecaoCnae = cnae;
            this.excecaoMunicipio = '';
            this.modalExcecao = true;
        },

        async adicionarExcecao() {
            if (!this.excecaoMunicipio.trim()) {
                alert('Digite o nome do município');
                return;
            }

            this.processando = true;

            try {
                const response = await fetch(`{{ url('admin/configuracoes/pactuacao') }}/${this.excecaoId}/adicionar-excecao`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        municipio: this.excecaoMunicipio.trim()
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao adicionar exceção');
            } finally {
                this.processando = false;
            }
        },

        async removerExcecao(id, municipio) {
            if (!confirm(`Deseja remover ${municipio} das exceções?`)) return;

            try {
                const response = await fetch(`{{ url('admin/configuracoes/pactuacao') }}/${id}/remover-excecao`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        municipio: municipio
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao remover exceção');
            }
        },

        abrirModalEditar(id, observacao) {
            this.editarId = id;
            this.editarObservacao = observacao;
            this.modalEditar = true;
        },

        abrirModalEditarCompleto(id) {
            // Buscar dados da pactuação via AJAX
            fetch(`{{ url("/admin/configuracoes/pactuacao") }}/${id}`)
                .then(response => response.json())
                .then(data => {
                    this.editarId = id;
                    this.tabelaSelecionada = data.tabela;
                    this.classificacaoRisco = data.classificacao_risco;
                    this.perguntaQuestionario = data.pergunta || '';
                    // Preencher municípios selecionados (array)
                    this.municipiosSelecionados = data.municipios_excecao || [];
                    this.observacaoTexto = data.observacao || '';
                    
                    // Adiciona a atividade atual à lista
                    this.atividadesParaCadastro = [{
                        codigo: data.cnae_codigo,
                        descricao: data.cnae_descricao,
                        status: 'Existente'
                    }];
                    
                    this.modalAdicionar = true; // Reusar o mesmo modal
                })
                .catch(error => {
                    console.error('Erro ao carregar pactuação:', error);
                    alert('Erro ao carregar dados para edição');
                });
        },

        async salvarObservacao() {
            this.processando = true;

            try {
                const response = await fetch(`{{ url('admin/configuracoes/pactuacao') }}/${this.editarId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        observacao: this.editarObservacao.trim() || null
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao salvar observação');
            } finally {
                this.processando = false;
            }
        },

        // Função de pesquisa com debounce
        pesquisarAtividade() {
            clearTimeout(this.timeoutPesquisa);
            
            if (this.termoPesquisa.trim().length < 2) {
                this.resultadosPesquisa = [];
                return;
            }
            
            this.pesquisando = true;
            
            this.timeoutPesquisa = setTimeout(async () => {
                try {
                    const response = await fetch(`{{ url('admin/configuracoes/pactuacao') }}/pesquisar?termo=${encodeURIComponent(this.termoPesquisa)}`);
                    const data = await response.json();
                    this.resultadosPesquisa = data;
                } catch (error) {
                    console.error('Erro ao pesquisar:', error);
                    this.resultadosPesquisa = [];
                } finally {
                    this.pesquisando = false;
                }
            }, 500);
        },

        limparPesquisa() {
            this.termoPesquisa = '';
            this.resultadosPesquisa = [];
        },

        irParaAba(tabela) {
            const mapa = {
                'I': 'tabela-i',
                'II': 'tabela-ii',
                'III': 'tabela-iii',
                'IV': 'tabela-iv',
                'V': 'tabela-v'
            };
            this.abaAtiva = mapa[tabela] || 'tabela-i';
            
            // Scroll suave para o topo das tabs
            setTimeout(() => {
                document.querySelector('.border-b.border-gray-200')?.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
            }, 100);
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
