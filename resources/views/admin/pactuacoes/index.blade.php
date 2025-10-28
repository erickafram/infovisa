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
                                    <button @click="abrirModalEditar({{ $pactuacao->id }}, '{{ addslashes($pactuacao->observacao ?? '') }}')" 
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
                 @click="modalAdicionar = false"
                 class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

            <div x-show="modalAdicionar"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-white">
                            <span x-text="tipoModal === 'estadual' ? 'Competência Estadual' : municipioModal"></span>
                        </h3>
                        <button @click="modalAdicionar = false" class="text-white hover:text-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <form @submit.prevent="adicionarAtividades" class="p-4">
                    <div class="mb-3">
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

                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Códigos CNAE *
                            <span class="text-xs text-gray-500">(separados por vírgula)</span>
                        </label>
                        <textarea 
                            x-model="cnaesTexto" 
                            rows="3"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ex: 4711-3/01, 4712-1/00, 4713-0/02"
                            required></textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            Digite os códigos CNAE separados por vírgula. As descrições serão buscadas automaticamente.
                        </p>
                    </div>

                    <div class="mb-3">
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

                    <div class="mb-3" x-show="tabelaSelecionada === 'IV' || tabelaSelecionada === 'V'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Pergunta do Questionário *
                        </label>
                        <textarea 
                            x-model="perguntaQuestionario" 
                            rows="2"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ex: O resultado do exercício da atividade será diferente de produto artesanal?"
                            :required="tabelaSelecionada === 'IV' || tabelaSelecionada === 'V'"></textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            <span x-show="tabelaSelecionada === 'IV'">Resposta SIM = Estadual | NÃO = Municipal</span>
                            <span x-show="tabelaSelecionada === 'V'">Resposta SIM = Sujeito à VISA | NÃO = Não sujeito</span>
                        </p>
                    </div>
                    
                    <div class="mb-3" x-show="tabelaSelecionada === 'III' || tabelaSelecionada === 'IV'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Municípios Descentralizados (Exceções)
                            <span class="text-xs text-gray-500">(separados por vírgula)</span>
                        </label>
                        <textarea 
                            x-model="municipiosExcecaoTexto" 
                            rows="2"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ex: Araguaína, Palmas, Gurupi"></textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            Municípios que receberam descentralização para fiscalizar esta atividade.
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Observações
                            <span class="text-xs text-gray-500">(opcional)</span>
                        </label>
                        <textarea 
                            x-model="observacaoTexto" 
                            rows="2"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ex: Aplica-se apenas se não for produto artesanal"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" 
                                @click="modalAdicionar = false"
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

    {{-- Modal Adicionar Exceção --}}
    <div x-show="modalExcecao" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="modalExcecao"
                 @click="modalExcecao = false"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

            <div x-show="modalExcecao"
                 class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle bg-white rounded-lg shadow-xl z-10">
                
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
                 @click="modalEditar = false"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

            <div x-show="modalEditar"
                 class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle bg-white rounded-lg shadow-xl z-10">
                
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
        abaAtiva: 'tabela-i',
        modalAdicionar: false,
        modalExcecao: false,
        modalEditar: false,
        tipoModal: 'estadual',
        municipioModal: null,
        tabelaSelecionada: '',
        classificacaoRisco: '',
        perguntaQuestionario: '',
        cnaesTexto: '',
        municipiosExcecaoTexto: '',
        observacaoTexto: '',
        excecaoId: null,
        excecaoCnae: '',
        excecaoMunicipio: '',
        editarId: null,
        editarObservacao: '',
        processando: false,

        async adicionarAtividades() {
            if (!this.cnaesTexto.trim()) {
                alert('Digite pelo menos um código CNAE');
                return;
            }

            this.processando = true;

            try {
                // Separa os CNAEs por vírgula e limpa espaços
                const cnaes = this.cnaesTexto.split(',').map(c => c.trim()).filter(c => c);
                
                if (cnaes.length === 0) {
                    alert('Nenhum código CNAE válido encontrado');
                    this.processando = false;
                    return;
                }

                // Busca as descrições dos CNAEs
                const atividades = [];
                for (const cnae of cnaes) {
                    // Busca a descrição do CNAE
                    try {
                        const response = await fetch(`{{ route('admin.configuracoes.pactuacao.buscar-cnaes') }}?termo=${cnae}`);
                        const data = await response.json();
                        
                        if (data.length > 0) {
                            // Pega a primeira correspondência exata ou mais próxima
                            const match = data.find(d => d.codigo === cnae) || data[0];
                            atividades.push({
                                codigo: cnae,
                                descricao: match.descricao
                            });
                        } else {
                            // Se não encontrar, usa o código como descrição
                            atividades.push({
                                codigo: cnae,
                                descricao: `Atividade ${cnae}`
                            });
                        }
                    } catch (error) {
                        console.error(`Erro ao buscar CNAE ${cnae}:`, error);
                        atividades.push({
                            codigo: cnae,
                            descricao: `Atividade ${cnae}`
                        });
                    }
                }

                // Prepara municípios de exceção se for estadual
                let municipiosExcecao = null;
                if (this.tipoModal === 'estadual' && this.municipiosExcecaoTexto.trim()) {
                    municipiosExcecao = this.municipiosExcecaoTexto.split(',').map(m => m.trim()).filter(m => m);
                }

                // Envia todas as atividades de uma vez
                const response = await fetch('{{ route('admin.configuracoes.pactuacao.store-multiple') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        tipo: this.tipoModal,
                        municipio: this.municipioModal,
                        atividades: atividades,
                        municipios_excecao: municipiosExcecao,
                        observacao: this.observacaoTexto.trim() || null
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
                alert('Erro ao adicionar atividades');
            } finally {
                this.processando = false;
            }
        },

        async toggleStatus(id) {
            if (!confirm('Deseja alterar o status desta atividade?')) return;

            try {
                const response = await fetch(`{{ route('admin.configuracoes.pactuacao.index') }}/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
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
                alert('Erro ao alterar status');
            }
        },

        async remover(id) {
            if (!confirm('Deseja realmente remover esta atividade?')) return;

            try {
                const response = await fetch(`{{ route('admin.configuracoes.pactuacao.index') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
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
                alert('Erro ao remover atividade');
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
                const response = await fetch(`{{ route('admin.configuracoes.pactuacao.index') }}/${this.excecaoId}/adicionar-excecao`, {
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
                const response = await fetch(`{{ route('admin.configuracoes.pactuacao.index') }}/${id}/remover-excecao`, {
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
            fetch(`/admin/configuracoes/pactuacao/${id}`)
                .then(response => response.json())
                .then(data => {
                    this.editarId = id;
                    this.tabelaSelecionada = data.tabela;
                    this.cnaesTexto = data.cnae_codigo;
                    this.classificacaoRisco = data.classificacao_risco;
                    this.perguntaQuestionario = data.pergunta || '';
                    this.municipiosExcecaoTexto = data.municipios_excecao ? data.municipios_excecao.join(', ') : '';
                    this.observacaoTexto = data.observacao || '';
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
                const response = await fetch(`{{ route('admin.configuracoes.pactuacao.index') }}/${this.editarId}`, {
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
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
