@extends('layouts.company')

@section('title', 'Cadastrar Pessoa Jurídica')
@section('page-title', 'Cadastrar Pessoa Jurídica')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('company.estabelecimentos.create') }}" class="text-sm text-blue-600 hover:text-blue-700 flex items-center mb-2">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
        <p class="text-sm text-gray-600">Digite o CNPJ para buscar os dados automaticamente na Receita Federal</p>
    </div>

    {{-- Alerta Informativo --}}
    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-blue-900">Processo de Aprovação</h3>
                <p class="text-sm text-blue-800 mt-1">
                    Após o cadastro, seu estabelecimento ficará com status <strong>Pendente</strong> até que a Vigilância Sanitária analise e aprove.
                </p>
            </div>
        </div>
    </div>

    {{-- Modal de Erro do Servidor (Popup) --}}
    @if ($errors->any())
    <div x-data="{ showModal: true }" x-cloak>
        {{-- Overlay --}}
        <div x-show="showModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            
            {{-- Modal --}}
            <div x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.away="showModal = false"
                 class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">
                
                {{-- Header com ícone --}}
                <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-5">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Não foi possível cadastrar</h3>
                            <p class="text-red-100 text-sm mt-0.5">Verifique as informações abaixo</p>
                        </div>
                    </div>
                </div>
                
                {{-- Conteúdo --}}
                <div class="px-6 py-5">
                    @if($errors->has('cidade') && str_contains($errors->first('cidade'), 'InfoVISA'))
                        {{-- Erro específico de município que não usa InfoVISA --}}
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 mb-2">Município não habilitado</h4>
                                <p class="text-gray-600 text-sm leading-relaxed">{{ $errors->first('cidade') }}</p>
                            </div>
                        </div>
                    @else
                        {{-- Outros erros --}}
                        <ul class="space-y-3">
                            @foreach ($errors->all() as $error)
                            <li class="flex items-start gap-3">
                                <span class="flex-shrink-0 w-5 h-5 bg-red-100 rounded-full flex items-center justify-center mt-0.5">
                                    <svg class="w-3 h-3 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                                <span class="text-gray-700 text-sm">{{ $error }}</span>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                
                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                    <a href="{{ route('company.estabelecimentos.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Voltar aos Estabelecimentos
                    </a>
                    <button type="button" @click="showModal = false" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        Entendi
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Formulário --}}
    <form id="formEstabelecimento" method="POST" action="{{ route('company.estabelecimentos.store') }}" 
          x-data="estabelecimentoFormCompany()" 
          @submit="handleSubmit($event)"
          class="space-y-6"
          novalidate>
        @csrf
        <input type="hidden" name="tipo_pessoa" value="juridica">

        {{-- Modal de Erros --}}
        <div x-cloak x-show="modalErro.visivel" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            <div class="w-full max-w-md bg-white rounded-xl shadow-2xl border border-red-200">
                <div class="flex items-start justify-between px-5 py-4 border-b border-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Revisar campos obrigatórios</h3>
                        <p class="text-sm text-gray-500 mt-1">Preencha os itens abaixo para continuar.</p>
                    </div>
                    <button type="button" @click="fecharModalErro" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="px-5 py-4">
                    <ul class="space-y-2">
                        <template x-for="(erro, index) in modalErro.mensagens" :key="index">
                            <li class="flex items-start gap-2 text-sm text-gray-700">
                                <span class="text-red-500 font-semibold mt-0.5">•</span>
                                <span x-text="erro"></span>
                            </li>
                        </template>
                    </ul>
                </div>
                <div class="px-5 py-4 bg-gray-50 rounded-b-xl flex justify-end">
                    <button type="button" @click="fecharModalErro" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg">Entendi</button>
                </div>
            </div>
        </div>

        {{-- Busca por CNPJ --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-base font-medium text-gray-900 mb-3">Consulta por CNPJ</h3>
            
            <div class="space-y-3">
                <div>
                    <label for="cnpj_busca" class="block text-sm font-medium text-gray-700 mb-1.5">
                        CNPJ <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="cnpj_busca" 
                           x-model="cnpjBusca"
                           @input="formatarCnpj"
                           placeholder="00.000.000/0000-00"
                           maxlength="18"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Digite apenas números ou use pontuação</p>
                </div>

                <div>
                    <button type="button" 
                            @click="buscarCnpj"
                            :disabled="loading || cnpjBusca.length < 18"
                            class="w-full sm:w-auto px-6 py-2 text-sm text-white rounded-lg font-semibold transition-all bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2">
                        <svg x-show="loading" x-cloak class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="loading ? 'Buscando...' : 'Buscar CNPJ'"></span>
                    </button>
                </div>
            </div>

            {{-- Mensagens --}}
            <div x-show="mensagem" x-cloak class="mt-3">
                <div x-show="tipoMensagem === 'success'" class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                    <p class="text-sm font-semibold text-green-900" x-text="mensagem"></p>
                </div>
                <div x-show="tipoMensagem === 'error'" class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                    <div class="text-sm text-red-900" x-html="mensagem"></div>
                </div>
                <div x-show="tipoMensagem === 'warning'" class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                    <p class="text-sm font-semibold text-yellow-900" x-text="mensagem"></p>
                </div>
            </div>
        </div>

        {{-- Dados Completos em Abas --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200" x-show="dadosCarregados" x-cloak>
            {{-- Navegação das Abas --}}
            <div class="border-b border-gray-200 bg-gray-50">
                <nav class="flex space-x-0 px-6" aria-label="Tabs">
                    <button type="button" @click="abaAtiva = 'dados-gerais'"
                            :class="abaAtiva === 'dados-gerais' ? 'bg-white border-b-2 border-blue-500 text-blue-600' : 'bg-gray-50 border-b-2 border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-4 py-3 font-medium text-sm focus:outline-none transition-colors">
                        Dados Gerais
                    </button>
                    <button type="button" @click="abaAtiva = 'endereco'"
                            :class="abaAtiva === 'endereco' ? 'bg-white border-b-2 border-blue-500 text-blue-600' : 'bg-gray-50 border-b-2 border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-4 py-3 font-medium text-sm focus:outline-none transition-colors">
                        Endereço
                    </button>
                    <button type="button" @click="abaAtiva = 'atividades'"
                            :class="abaAtiva === 'atividades' ? 'bg-white border-b-2 border-blue-500 text-blue-600' : 'bg-gray-50 border-b-2 border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-4 py-3 font-medium text-sm focus:outline-none transition-colors">
                        Atividades
                    </button>
                    <button type="button" @click="abaAtiva = 'contato'"
                            :class="abaAtiva === 'contato' ? 'bg-white border-b-2 border-blue-500 text-blue-600' : 'bg-gray-50 border-b-2 border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-4 py-3 font-medium text-sm focus:outline-none transition-colors">
                        Contato
                    </button>
                </nav>
            </div>

            {{-- Barra de Progresso --}}
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-800">
                        Etapa <span x-text="getEtapaAtual()"></span> de 4 - <span x-text="getNomeAba(abaAtiva)"></span>
                    </span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                          :class="getEtapaAtual() === 4 ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'"
                          x-text="Math.round((getEtapaAtual() / 4) * 100) + '%'"></span>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-2 mb-5 overflow-hidden">
                    <div class="h-2 rounded-full transition-all duration-700"
                         :class="getEtapaAtual() === 4 ? 'bg-gradient-to-r from-green-400 to-green-600' : 'bg-gradient-to-r from-blue-400 to-blue-600'"
                         :style="'width: ' + (getEtapaAtual() / 4) * 100 + '%'">
                    </div>
                </div>
                
                {{-- Indicadores das Etapas --}}
                <div class="relative">
                    <div class="absolute top-4 left-0 right-0 h-0.5 bg-gray-300" style="margin: 0 5%;"></div>
                    <div class="absolute top-4 left-0 h-0.5 transition-all duration-700"
                         :class="getEtapaAtual() === 4 ? 'bg-green-500' : 'bg-blue-500'"
                         :style="'width: ' + ((getEtapaAtual() - 1) / 3) * 90 + '%; margin-left: 5%;'"></div>
                    
                    <div class="flex justify-between items-start relative">
                        <template x-for="(aba, index) in ['dados-gerais', 'endereco', 'atividades', 'contato']" :key="index">
                            <div class="flex flex-col items-center" style="width: 25%;">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-white border-2"
                                     :class="getEtapaAtual() > index + 1 ? 'border-green-500 text-green-600' : (abaAtiva === aba ? 'border-blue-500 text-blue-600' : 'border-gray-300 text-gray-400')">
                                    <template x-if="getEtapaAtual() > index + 1">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </template>
                                    <template x-if="getEtapaAtual() <= index + 1">
                                        <span x-text="index + 1"></span>
                                    </template>
                                </div>
                                <span class="text-[9px] mt-1.5 font-medium text-center" 
                                      :class="getEtapaAtual() > index + 1 ? 'text-green-600' : (abaAtiva === aba ? 'text-blue-600' : 'text-gray-500')"
                                      x-text="['Dados', 'Endereço', 'Atividades', 'Contato'][index]"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Conteúdo das Abas --}}
            <div class="p-6">
                {{-- Aba: Dados Gerais --}}
                <div x-show="abaAtiva === 'dados-gerais'" x-cloak>
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Dados Gerais da Empresa</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">CNPJ <span class="text-red-500">*</span></label>
                            <input type="text" x-model="dados.cnpj" readonly
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700 font-mono">
                            <input type="hidden" name="cnpj" :value="dados.cnpj.replace(/\D/g, '')">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Razão Social <span class="text-red-500">*</span></label>
                            <input type="text" name="razao_social" x-model="dados.razao_social" readonly
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nome Fantasia <span class="text-red-500">*</span></label>
                            <input type="text" name="nome_fantasia" x-model="dados.nome_fantasia"
                                   @input="dados.nome_fantasia = $event.target.value.toUpperCase()"
                                   :class="dados.tipo_setor === 'publico' ? 'border-2 border-yellow-400 bg-yellow-50' : ''"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 uppercase">
                            
                            {{-- Alerta para estabelecimentos públicos --}}
                            <div x-show="dados.tipo_setor === 'publico'" 
                                 x-cloak
                                 class="mt-2 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-lg">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-yellow-800 mb-1">⚠️ Atenção: Estabelecimento Público</p>
                                        <p class="text-xs text-yellow-700 leading-relaxed">
                                            O nome fantasia que veio da API pode ser genérico (ex: "Fundo Municipal de Saúde"). 
                                            <strong>Altere para o nome específico da unidade</strong>, como:
                                        </p>
                                        <ul class="text-xs text-yellow-700 mt-2 space-y-1 ml-4">
                                            <li>• Hospital Municipal [Nome]</li>
                                            <li>• Laboratório Central de Saúde Pública</li>
                                            <li>• UBS [Nome do Bairro]</li>
                                            <li>• HPP - Hospital de Pequeno Porte</li>
                                            <li>• Centro de Especialidades</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Natureza Jurídica</label>
                            <input type="text" name="natureza_juridica" x-model="dados.natureza_juridica" readonly
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Porte da Empresa</label>
                            <input type="text" name="porte" x-model="dados.porte" readonly
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Situação Cadastral</label>
                            <input type="text" name="descricao_situacao_cadastral" x-model="dados.descricao_situacao_cadastral" readonly
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data Início Atividade</label>
                            <input type="text" x-model="dados.data_inicio_atividade" readonly
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                            <input type="hidden" name="data_inicio_atividade" :value="dados.data_inicio_atividade_raw">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Capital Social</label>
                            <input type="text" x-model="formatarMoeda(dados.capital_social)" readonly
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700 font-mono">
                            <input type="hidden" name="capital_social" :value="dados.capital_social">
                        </div>
                    </div>

                    {{-- Tipo de Setor --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de Setor</label>
                        <div class="flex items-center gap-4 p-4 rounded-xl border-2"
                             :class="dados.tipo_setor === 'publico' ? 'bg-green-50 border-green-300' : 'bg-blue-50 border-blue-300'">
                            <div class="text-3xl" x-text="dados.tipo_setor === 'publico' ? '🏛️' : '🏢'"></div>
                            <div class="flex-1">
                                <div class="font-semibold text-lg text-gray-900" x-text="dados.tipo_setor === 'publico' ? 'Estabelecimento Público' : 'Estabelecimento Privado'"></div>
                                <div class="text-sm text-gray-600 mt-1" x-text="dados.tipo_setor === 'publico' ? 'Permite múltiplos estabelecimentos com mesmo CNPJ' : 'CNPJ deve ser único no sistema'"></div>
                            </div>
                        </div>
                        <input type="hidden" name="tipo_setor" x-model="dados.tipo_setor">
                    </div>

                    {{-- Botões de Navegação --}}
                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="button" @click="proximaAba('dados-gerais')" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                            Próximo: Endereço →
                        </button>
                    </div>
                </div>

                {{-- Aba: Endereço --}}
                <div x-show="abaAtiva === 'endereco'" x-cloak>
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Endereço do Estabelecimento</h3>
                    
                    {{-- Alerta para estabelecimentos públicos --}}
                    <div x-show="dados.tipo_setor === 'publico'" 
                         x-cloak
                         class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-yellow-800 mb-2">⚠️ Atenção: Endereço do Estabelecimento Público</p>
                                <p class="text-xs text-yellow-700 leading-relaxed mb-2">
                                    O endereço que veio da API é o endereço da <strong>sede administrativa</strong> (Prefeitura, Secretaria de Saúde, etc.).
                                </p>
                                <p class="text-xs text-yellow-700 leading-relaxed">
                                    <strong>Altere para o endereço real da unidade de saúde</strong> que está sendo cadastrada (Hospital, UBS, Laboratório, etc.).
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">CEP <span class="text-red-500">*</span></label>
                            <input type="text" x-model="dados.cep"
                                   @input="dados.cep = dados.cep.replace(/\D/g, '').replace(/(\d{5})(\d)/, '$1-$2').substring(0, 9)"
                                   @blur="buscarCep()"
                                   placeholder="00000-000" maxlength="9"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <input type="hidden" name="cep" :value="dados.cep.replace(/\D/g, '')">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logradouro <span class="text-red-500">*</span></label>
                            <input type="text" name="endereco" x-model="dados.endereco"
                                   @input="dados.endereco = $event.target.value.toUpperCase()"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 uppercase">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Número <span class="text-red-500">*</span></label>
                            <input type="text" name="numero" x-model="dados.numero"
                                   @input="dados.numero = $event.target.value.toUpperCase()"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 uppercase">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Complemento</label>
                            <input type="text" name="complemento" x-model="dados.complemento"
                                   @input="dados.complemento = $event.target.value.toUpperCase()"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 uppercase">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bairro <span class="text-red-500">*</span></label>
                            <input type="text" name="bairro" x-model="dados.bairro"
                                   @input="dados.bairro = $event.target.value.toUpperCase()"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 uppercase">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cidade <span class="text-red-500">*</span></label>
                            <input type="text" name="cidade" x-model="dados.cidade" readonly
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado <span class="text-red-500">*</span></label>
                            <input type="text" name="estado" x-model="dados.estado" readonly
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Código IBGE</label>
                            <input type="text" name="codigo_municipio_ibge" x-model="dados.codigo_municipio_ibge" readonly
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                        </div>
                    </div>

                    {{-- Botões de Navegação --}}
                    <div class="flex justify-between pt-4 border-t border-gray-200">
                        <button type="button" @click="abaAtiva = 'dados-gerais'" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">
                            ← Voltar
                        </button>
                        <button type="button" @click="proximaAba('endereco')" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                            Próximo: Atividades →
                        </button>
                    </div>
                </div>

                {{-- Aba: Atividades --}}
                <div x-show="abaAtiva === 'atividades'" x-cloak>
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Atividades Econômicas</h3>
                    
                    {{-- Hidden inputs --}}
                    <input type="hidden" name="cnae_fiscal" :value="dados.cnae_fiscal">
                    <input type="hidden" name="cnae_fiscal_descricao" :value="dados.cnae_fiscal_descricao">
                    <input type="hidden" name="cnaes_secundarios" :value="JSON.stringify(dados.cnaes_secundarios)">

                    {{-- Lista de Atividades (Principal + Secundárias) --}}
                    <div class="mb-6" x-show="!apenasAtividadesEspeciais" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Selecione as atividades que o estabelecimento exerce <span class="text-red-500">*</span>
                        </label>
                        <p class="text-xs text-gray-500 mb-3">Marque apenas as atividades que serão efetivamente exercidas neste estabelecimento.</p>
                        
                        <div class="space-y-2 max-h-80 overflow-y-auto border border-gray-200 rounded-lg p-3">
                            {{-- Atividade Principal --}}
                            <label x-show="dados.cnae_fiscal" class="flex items-start gap-3 p-3 bg-blue-50 border border-blue-200 rounded-lg cursor-pointer hover:bg-blue-100 transition-colors">
                                <input type="checkbox" 
                                       x-model="atividadePrincipalMarcada"
                                       @change="buscarQuestionarios()"
                                       class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 bg-blue-600 text-white text-xs font-bold rounded">Principal</span>
                                        <span class="font-mono text-sm text-gray-900" x-text="dados.cnae_fiscal"></span>
                                    </div>
                                    <span class="text-sm text-gray-700" x-text="dados.cnae_fiscal_descricao"></span>
                                </div>
                            </label>

                            {{-- Atividades Secundárias --}}
                            <template x-for="(cnae, index) in dados.cnaes_secundarios" :key="index">
                                <label class="flex items-start gap-3 p-3 hover:bg-gray-50 rounded-lg cursor-pointer border border-gray-100 transition-colors">
                                    <input type="checkbox" 
                                           :value="String(cnae.codigo)"
                                           x-model="atividadesExercidas"
                                           @change="buscarQuestionarios()"
                                           class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-sm text-gray-900" x-text="cnae.codigo"></span>
                                            <span x-show="cnae.manual" class="px-1.5 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded">Manual</span>
                                        </div>
                                        <span class="text-sm text-gray-600" x-text="' - ' + (cnae.descricao || cnae.texto || '')"></span>
                                    </div>
                                </label>
                            </template>
                        </div>
                        
                        {{-- Busca de CNAE Manual (Apenas Público) --}}
                        <div x-show="dados.tipo_setor === 'publico'" class="mt-4 bg-white border-2 border-dashed border-blue-300 rounded-lg p-5">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <h4 class="text-base font-semibold text-blue-800">Adicionar Atividade Manualmente</h4>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">
                                Para estabelecimentos públicos (Prefeituras, Fundos Municipais) que não possuem os CNAEs de saúde vinculados ao CNPJ, 
                                você pode buscar e adicionar manualmente a atividade correta aqui.
                            </p>
                            
                            <div class="flex gap-2">
                                <div class="flex-1 relative">
                                    <input type="text" 
                                           x-model="cnaeBusca"
                                           @keydown.enter.prevent="buscarCnaeAdicional"
                                           placeholder="Digite o código CNAE (7 dígitos) ou descrição" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <p x-show="cnaeErro" class="absolute text-xs text-red-600 mt-1" x-text="cnaeErro"></p>
                                </div>
                                <button type="button" 
                                        @click="buscarCnaeAdicional"
                                        :disabled="loadingCnae"
                                        class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                                    <span x-show="!loadingCnae">Buscar</span>
                                    <span x-show="loadingCnae" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Buscando...
                                    </span>
                                </button>
                            </div>

                            {{-- Resultados da Busca --}}
                            <div x-show="cnaeResultados.length > 0" class="mt-4 space-y-2 max-h-60 overflow-y-auto border border-gray-200 rounded-lg">
                                <template x-for="resultado in cnaeResultados" :key="resultado.codigo">
                                    <div class="flex items-start justify-between p-3 hover:bg-gray-50 border-b border-gray-100 last:border-0">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-bold bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full" x-text="resultado.codigo"></span>
                                            </div>
                                            <p class="text-sm text-gray-800 mt-1" x-text="resultado.descricao"></p>
                                        </div>
                                        <button type="button" 
                                                @click="adicionarCnaeManual(resultado)"
                                                class="ml-3 text-sm font-medium text-blue-600 hover:text-blue-800 bg-white border border-blue-200 px-3 py-1 rounded-md hover:bg-blue-50 transition-colors">
                                            Adicionar
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Mensagem informativa quando atividades estão selecionadas --}}
                        <div x-show="atividadePrincipalMarcada || atividadesExercidas.length > 0" 
                             x-cloak
                             class="mt-4 bg-green-50 border border-green-200 rounded-lg p-3">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <div class="text-xs text-green-800">
                                    <p class="font-semibold">✓ Atividade(s) selecionada(s) para licenciamento sanitário</p>
                                    <p class="mt-1">Com atividades econômicas marcadas, você poderá solicitar <strong>Licenciamento Sanitário</strong> para este estabelecimento.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Seção: Atividades Especiais (Projeto Arquitetônico / Análise de Rotulagem) --}}
                    {{-- SÓ APARECE se NENHUMA atividade do CNPJ estiver marcada --}}
                    <div class="mb-6 mt-6" 
                         x-show="!atividadePrincipalMarcada && atividadesExercidas.length === 0"
                         x-cloak>
                        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5">
                            <div class="flex items-start gap-3 mb-4">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-bold text-indigo-900 mb-1">📋 Não deseja licenciamento sanitário?</h4>
                                    <p class="text-xs text-indigo-700">
                                        Se você deseja <strong>apenas</strong> abrir processo de <strong>Projeto Arquitetônico</strong> ou <strong>Análise de Rotulagem</strong> 
                                        (sem licenciamento sanitário), marque a opção abaixo.
                                    </p>
                                </div>
                            </div>

                            {{-- Toggle para ativar modo de atividades especiais --}}
                            <div class="mb-4">
                                <label class="flex items-center gap-3 p-3 bg-white border-2 border-indigo-200 rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors">
                                    <input type="checkbox" 
                                           x-model="apenasAtividadesEspeciais"
                                           @change="toggleAtividadesEspeciais()"
                                           class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <div class="flex-1">
                                        <span class="text-sm font-semibold text-indigo-900">Sim, desejo abrir apenas Projeto Arquitetônico e/ou Análise de Rotulagem</span>
                                        <p class="text-xs text-indigo-600 mt-0.5">Não será necessário selecionar atividades econômicas</p>
                                    </div>
                                </label>
                            </div>

                            {{-- Opções de atividades especiais --}}
                            <div x-show="apenasAtividadesEspeciais" x-cloak class="space-y-3 pt-3 border-t border-indigo-200">
                                <p class="text-xs font-medium text-indigo-800 mb-2">Selecione o(s) tipo(s) de processo que deseja abrir:</p>
                                
                                {{-- Projeto Arquitetônico --}}
                                <label class="flex items-start gap-3 p-3 bg-white border-2 rounded-lg cursor-pointer transition-all"
                                       :class="atividadeEspecialProjetoArq ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-indigo-300'">
                                    <input type="checkbox" 
                                           x-model="atividadeEspecialProjetoArq"
                                           @change="atualizarAtividadesEspeciais()"
                                           class="mt-1 h-5 w-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="px-2 py-0.5 bg-indigo-600 text-white text-xs font-bold rounded">PROJ_ARQ</span>
                                            <span class="text-sm font-semibold text-gray-900">Projeto Arquitetônico</span>
                                        </div>
                                        <p class="text-xs text-gray-600">Análise de projeto arquitetônico para adequação sanitária</p>
                                        <p class="text-xs text-indigo-600 mt-1">📍 Competência: Estadual (pode ser descentralizado para alguns municípios)</p>
                                    </div>
                                </label>

                                {{-- Análise de Rotulagem --}}
                                <label class="flex items-start gap-3 p-3 bg-white border-2 rounded-lg cursor-pointer transition-all"
                                       :class="atividadeEspecialRotulagem ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-indigo-300'">
                                    <input type="checkbox" 
                                           x-model="atividadeEspecialRotulagem"
                                           @change="atualizarAtividadesEspeciais()"
                                           class="mt-1 h-5 w-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="px-2 py-0.5 bg-indigo-600 text-white text-xs font-bold rounded">ANAL_ROT</span>
                                            <span class="text-sm font-semibold text-gray-900">Análise de Rotulagem</span>
                                        </div>
                                        <p class="text-xs text-gray-600">Análise e aprovação de rótulos de produtos</p>
                                        <p class="text-xs text-indigo-600 mt-1">📍 Competência: Estadual (pode ser descentralizado para alguns municípios)</p>
                                    </div>
                                </label>

                                {{-- Aviso importante --}}
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mt-3">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <div class="text-xs text-amber-800">
                                            <p class="font-semibold mb-1">⚠️ Importante:</p>
                                            <ul class="list-disc list-inside space-y-1">
                                                <li>Ao marcar esta opção, você <strong>não poderá</strong> abrir processo de Licenciamento Sanitário</li>
                                                <li>Se precisar de licenciamento no futuro, deverá atualizar o cadastro com as atividades do CNPJ</li>
                                                <li>A competência (Estado ou Município) será definida conforme a pactuação vigente</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden inputs para atividades especiais --}}
                    <input type="hidden" name="apenas_atividades_especiais" :value="apenasAtividadesEspeciais ? '1' : '0'">
                    <input type="hidden" name="atividade_especial_projeto_arq" :value="atividadeEspecialProjetoArq ? '1' : '0'">
                    <input type="hidden" name="atividade_especial_rotulagem" :value="atividadeEspecialRotulagem ? '1' : '0'">

                    {{-- Questionários Dinâmicos --}}
                    <div x-show="questionarios.length > 0 && !apenasAtividadesEspeciais" class="mt-6 space-y-4">
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg mb-4">
                            <div class="flex items-start">
                                <svg class="h-6 w-6 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="ml-3">
                                    <h4 class="text-sm font-bold text-yellow-900">📋 Questionários Obrigatórios</h4>
                                    <p class="text-xs text-yellow-800 mt-1">
                                        Algumas atividades selecionadas requerem informações adicionais para determinar a competência.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <template x-for="(quest, index) in questionarios" :key="quest.cnae">
                            <div class="bg-white border-2 border-purple-300 rounded-xl p-5 shadow-sm">
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-bold rounded-full" x-text="quest.cnae_formatado"></span>
                                            <span class="text-xs text-gray-600" x-text="quest.descricao"></span>
                                        </div>
                                        
                                        {{-- Primeira Pergunta --}}
                                        <p class="text-sm font-semibold text-gray-900 mb-3" x-text="quest.pergunta"></p>
                                        
                                        <div class="flex gap-3">
                                            <button type="button"
                                                    @click="respostasQuestionario[quest.cnae] = 'sim'"
                                                    :class="respostasQuestionario[quest.cnae] === 'sim' ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-green-50'"
                                                    class="flex-1 px-4 py-3 border-2 rounded-lg font-semibold text-sm transition-all duration-200 flex items-center justify-center gap-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                SIM
                                            </button>
                                            <button type="button"
                                                    @click="respostasQuestionario[quest.cnae] = 'nao'"
                                                    :class="respostasQuestionario[quest.cnae] === 'nao' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-red-50'"
                                                    class="flex-1 px-4 py-3 border-2 rounded-lg font-semibold text-sm transition-all duration-200 flex items-center justify-center gap-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                NÃO
                                            </button>
                                        </div>

                                        <div x-show="!respostasQuestionario[quest.cnae]" class="mt-2 text-xs text-red-600 font-medium">
                                            ⚠️ Resposta obrigatória
                                        </div>
                                        
                                        {{-- Segunda Pergunta (se existir) --}}
                                        <template x-if="quest.pergunta2">
                                            <div class="mt-4 pt-4 border-t border-gray-200">
                                                <p class="text-sm font-semibold text-gray-900 mb-3" x-text="quest.pergunta2"></p>
                                                
                                                <div class="flex gap-3">
                                                    <button type="button"
                                                            @click="respostasQuestionario2[quest.cnae] = 'sim'"
                                                            :class="respostasQuestionario2[quest.cnae] === 'sim' ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-green-50'"
                                                            class="flex-1 px-4 py-3 border-2 rounded-lg font-semibold text-sm transition-all duration-200 flex items-center justify-center gap-2">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        SIM
                                                    </button>
                                                    <button type="button"
                                                            @click="respostasQuestionario2[quest.cnae] = 'nao'"
                                                            :class="respostasQuestionario2[quest.cnae] === 'nao' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-red-50'"
                                                            class="flex-1 px-4 py-3 border-2 rounded-lg font-semibold text-sm transition-all duration-200 flex items-center justify-center gap-2">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                        NÃO
                                                    </button>
                                                </div>

                                                <div x-show="!respostasQuestionario2[quest.cnae]" class="mt-2 text-xs text-red-600 font-medium">
                                                    ⚠️ Resposta obrigatória
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Indicador de Competência --}}
                    <div x-show="atividadesExercidas.length > 0 || atividadePrincipalMarcada" class="mt-4">
                        {{-- Alerta NÃO SUJEITO À VISA --}}
                        <div x-show="naoSujeitoVisa" class="bg-gray-50 border-l-4 border-gray-500 p-4 rounded-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-gray-500 rounded-full flex items-center justify-center">
                                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h4 class="text-lg font-bold text-gray-900">🚫 NÃO SUJEITO À VIGILÂNCIA SANITÁRIA</h4>
                                    <p class="text-sm text-gray-700 mt-1">
                                        Com base nas respostas do questionário, as atividades selecionadas <strong>NÃO estão sujeitas à fiscalização da Vigilância Sanitária</strong>.
                                    </p>
                                    <p class="text-sm text-gray-600 mt-2">
                                        Este estabelecimento <strong>não precisa de licença sanitária</strong> para exercer estas atividades.
                                    </p>
                                    <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <p class="text-xs text-yellow-800">
                                            <strong>⚠️ Atenção:</strong> Se você acredita que esta informação está incorreta, revise as respostas do questionário acima ou entre em contato com a Vigilância Sanitária.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Alerta Estadual --}}
                        <div x-show="competenciaEstadual && !naoSujeitoVisa" class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center">
                                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h4 class="text-lg font-bold text-purple-900">🏛️ Competência ESTADUAL</h4>
                                    <p class="text-sm text-purple-800 mt-1">
                                        Com base nas atividades selecionadas, este estabelecimento será fiscalizado pela 
                                        <strong>Vigilância Sanitária Estadual</strong>.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Alerta Municipal --}}
                        <div x-show="!competenciaEstadual && !naoSujeitoVisa" class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h4 class="text-lg font-bold text-blue-900">🏠 Competência MUNICIPAL</h4>
                                    <p class="text-sm text-blue-800 mt-1">
                                        Com base nas atividades selecionadas, este estabelecimento será fiscalizado pela 
                                        <strong>Vigilância Sanitária Municipal de <span x-text="dados.cidade || 'seu município'"></span></strong>.
                                    </p>
                                    <p class="text-xs text-blue-700 mt-2">
                                        Atividades de baixa e média complexidade com atuação local.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Atividades Exercidas (hidden) --}}
                    <input type="hidden" name="atividades_exercidas" :value="JSON.stringify(getAtividadesExercidas())">
                    <input type="hidden" name="respostas_questionario" :value="JSON.stringify(respostasQuestionario)">
                    <input type="hidden" name="respostas_questionario2" :value="JSON.stringify(respostasQuestionario2)">
                    <input type="hidden" name="competencia_estadual" :value="competenciaEstadual ? '1' : '0'">
                    <input type="hidden" name="nao_sujeito_visa" :value="naoSujeitoVisa ? '1' : '0'">

                    {{-- Indicador de Carregamento --}}
                    <div x-show="carregandoQuestionarios" class="mt-4 flex items-center justify-center gap-3 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <svg class="animate-spin h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm font-medium text-blue-700">Carregando informações das atividades...</span>
                    </div>

                    {{-- Botões de Navegação --}}
                    <div class="flex justify-between pt-4 border-t border-gray-200">
                        <button type="button" @click="abaAtiva = 'endereco'" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">
                            ← Voltar
                        </button>
                        <button type="button" 
                                @click="proximaAba('atividades')" 
                                :disabled="naoSujeitoVisa || !podeAvancarAtividades()"
                                :class="(naoSujeitoVisa || !podeAvancarAtividades()) ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                                class="px-6 py-2 text-white rounded-lg font-medium flex items-center gap-2">
                            <template x-if="carregandoQuestionarios">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <span x-show="naoSujeitoVisa">Cadastro não necessário</span>
                            <span x-show="!naoSujeitoVisa && carregandoQuestionarios">Aguarde...</span>
                            <span x-show="!naoSujeitoVisa && !carregandoQuestionarios && !podeAvancarAtividades()">Selecione atividades</span>
                            <span x-show="!naoSujeitoVisa && !carregandoQuestionarios && podeAvancarAtividades()">Próximo: Contato →</span>
                        </button>
                    </div>
                </div>

                {{-- Aba: Contato --}}
                <div x-show="abaAtiva === 'contato'" x-cloak>
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Informações de Contato</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Telefone <span class="text-red-500">*</span></label>
                            <input type="text" x-model="dados.telefone"
                                   @input="formatarTelefone"
                                   placeholder="(00) 00000-0000"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <input type="hidden" name="telefone" :value="dados.telefone.replace(/\D/g, '')">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">E-mail <span class="text-red-500">*</span></label>
                            <input type="email" name="email" x-model="dados.email"
                                   placeholder="contato@empresa.com.br"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    {{-- Vínculo com Estabelecimento --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Seu vínculo com este estabelecimento <span class="text-red-500">*</span>
                        </label>
                        <select name="vinculo_usuario" x-model="dados.vinculo_usuario" required
                                class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione seu vínculo...</option>
                            <option value="responsavel_legal">Responsável Legal</option>
                            <option value="responsavel_tecnico">Responsável Técnico</option>
                            <option value="funcionario">Funcionário</option>
                            <option value="contador">Contador</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            Informe qual é a sua relação com este estabelecimento
                        </p>
                    </div>

                    {{-- Resumo --}}
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Resumo do Cadastro</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">CNPJ:</span>
                                <span class="font-medium text-gray-900 ml-2" x-text="dados.cnpj"></span>
                            </div>
                            <div>
                                <span class="text-gray-500">Razão Social:</span>
                                <span class="font-medium text-gray-900 ml-2" x-text="dados.razao_social"></span>
                            </div>
                            <div>
                                <span class="text-gray-500">Nome Fantasia:</span>
                                <span class="font-medium text-gray-900 ml-2" x-text="dados.nome_fantasia"></span>
                            </div>
                            <div>
                                <span class="text-gray-500">Cidade:</span>
                                <span class="font-medium text-gray-900 ml-2" x-text="dados.cidade + ' - ' + dados.estado"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Aviso de Status Pendente --}}
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <h4 class="text-sm font-semibold text-yellow-800">Aguardando Aprovação</h4>
                                <p class="text-sm text-yellow-700 mt-1">
                                    Após o envio, seu estabelecimento ficará com status <strong>Pendente</strong> até que a Vigilância Sanitária (Municipal ou Estadual) analise e aprove o cadastro.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Botões de Navegação --}}
                    <div class="flex justify-between pt-4 border-t border-gray-200">
                        <button type="button" @click="abaAtiva = 'atividades'" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">
                            ← Voltar
                        </button>
                        <button type="submit" 
                                :disabled="submitting"
                                class="px-8 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium disabled:bg-green-400 disabled:cursor-not-allowed inline-flex items-center gap-2">
                            <svg x-show="submitting" x-cloak class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="submitting ? 'Cadastrando...' : 'Cadastrar Estabelecimento'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal de Estabelecimentos Existentes --}}
        <div x-show="modalEstabelecimentosExistentes.visivel" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            {{-- Overlay --}}
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
            
            {{-- Modal --}}
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-lg shadow-2xl max-w-lg w-full mx-auto transform transition-all"
                     @click.away="fecharModalEstabelecimentos()">
                    
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-yellow-500 to-orange-500 px-4 py-3 rounded-t-lg">
                        <div class="flex items-center gap-2">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <h3 class="text-base font-bold text-white">Estabelecimentos Já Cadastrados</h3>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Body --}}
                    <div class="px-4 py-4">
                        {{-- Lista de Estabelecimentos --}}
                        <div class="space-y-2 mb-3">
                            <template x-for="(estabelecimento, index) in modalEstabelecimentosExistentes.estabelecimentos" :key="index">
                                <div class="flex items-center gap-2 p-2 bg-blue-50 border-l-4 border-blue-500 rounded-r">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <p class="text-sm font-medium text-gray-900" x-text="estabelecimento.nome_fantasia"></p>
                                </div>
                            </template>
                        </div>
                        
                        {{-- Informação --}}
                        <div class="bg-green-50 border-l-4 border-green-400 p-2 rounded-r">
                            <p class="text-xs text-green-700">
                                ✅ Você pode cadastrar outro estabelecimento com o mesmo CNPJ (Hospital, Laboratório, UBS, etc.)
                            </p>
                        </div>
                    </div>
                    
                    {{-- Footer --}}
                    <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end gap-2">
                        <button type="button"
                                @click="cancelarCadastro()"
                                class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="button"
                                @click="continuarCadastro()"
                                class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700 transition-colors">
                            Continuar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>


@push('scripts')
<script>
function estabelecimentoFormCompany() {
    return {
        cnpjBusca: '',
        loading: false,
        submitting: false,
        carregandoQuestionarios: false,
        mensagem: '',
        tipoMensagem: '',
        dadosCarregados: false,
        abaAtiva: 'dados-gerais',
        atividadesSelecionadas: [],
        atividadesExercidas: [],
        atividadePrincipalMarcada: false,
        questionarios: [],
        respostasQuestionario: {},
        respostasQuestionario2: {},
        competenciaEstadual: false,
        naoSujeitoVisa: false,
        // Atividades Especiais (Projeto Arquitetônico / Análise de Rotulagem)
        apenasAtividadesEspeciais: false,
        atividadeEspecialProjetoArq: false,
        atividadeEspecialRotulagem: false,
        modalErro: {
            visivel: false,
            mensagens: []
        },
        modalEstabelecimentosExistentes: {
            visivel: false,
            estabelecimentos: []
        },
        // Busca manual de CNAE (para estabelecimentos públicos)
        cnaeBusca: '',
        cnaeErro: '',
        loadingCnae: false,
        cnaeResultados: [],
        dados: {
            cnpj: '',
            razao_social: '',
            nome_fantasia: '',
            natureza_juridica: '',
            porte: '',
            descricao_situacao_cadastral: '',
            data_situacao_cadastral: '',
            data_inicio_atividade: '',
            data_inicio_atividade_raw: '',
            capital_social: '',
            cnae_fiscal: '',
            cnae_fiscal_descricao: '',
            cnaes_secundarios: [],
            endereco: '',
            numero: '',
            complemento: '',
            bairro: '',
            cidade: '',
            estado: '',
            cep: '',
            codigo_municipio_ibge: '',
            telefone: '',
            email: '',
            tipo_setor: 'privado',
            vinculo_usuario: ''
        },

        init() {
            // Watchers para verificar competência quando atividades mudarem
            this.$watch('atividadesExercidas', (value) => {
                this.verificarCompetencia();
                this.buscarQuestionarios();
                // Se marcou alguma atividade, desmarcar automaticamente o modo de atividades especiais
                if (value.length > 0) {
                    this.desmarcarAtividadesEspeciais();
                }
            });
            this.$watch('atividadePrincipalMarcada', (value) => {
                this.verificarCompetencia();
                this.buscarQuestionarios();
                // Se marcou a atividade principal, desmarcar automaticamente o modo de atividades especiais
                if (value) {
                    this.desmarcarAtividadesEspeciais();
                }
            });
            // Recalcula competência quando as respostas mudam
            this.$watch('respostasQuestionario', () => {
                this.verificarCompetencia();
            }, { deep: true });
            // Recalcula competência quando as respostas da segunda pergunta mudam
            this.$watch('respostasQuestionario2', () => {
                this.verificarCompetencia();
            }, { deep: true });
        },

        async verificarCompetencia() {
            const atividades = [];
            
            // Se está no modo de atividades especiais
            if (this.apenasAtividadesEspeciais) {
                if (this.atividadeEspecialProjetoArq) {
                    atividades.push('PROJ_ARQ');
                }
                if (this.atividadeEspecialRotulagem) {
                    atividades.push('ANAL_ROT');
                }
                
                console.log('🔍 Verificando competência (atividades especiais):', {
                    atividades: atividades,
                    municipio: this.dados.cidade
                });
                
                if (atividades.length === 0) {
                    this.competenciaEstadual = false;
                    this.naoSujeitoVisa = false;
                    return;
                }
            } else {
                // Adiciona CNAE principal se marcado
                if (this.atividadePrincipalMarcada && this.dados.cnae_fiscal) {
                    atividades.push(this.dados.cnae_fiscal);
                }
                
                // Adiciona atividades secundárias selecionadas
                this.atividadesExercidas.forEach(codigo => {
                    atividades.push(codigo);
                });
                
                console.log('🔍 Verificando competência:', {
                    atividadePrincipalMarcada: this.atividadePrincipalMarcada,
                    cnae_fiscal: this.dados.cnae_fiscal,
                    atividadesExercidas: this.atividadesExercidas,
                    atividades: atividades,
                    municipio: this.dados.cidade,
                    respostas: JSON.parse(JSON.stringify(this.respostasQuestionario)),
                    respostas2: JSON.parse(JSON.stringify(this.respostasQuestionario2))
                });
            }
            
            if (atividades.length === 0) {
                this.competenciaEstadual = false;
                this.naoSujeitoVisa = false;
                return;
            }
            
            // Consulta API para verificar competência
            try {
                const response = await fetch('{{ url('/api/verificar-competencia') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        atividades: atividades,
                        municipio: this.dados.cidade,
                        respostas_questionario: this.respostasQuestionario,
                        respostas_questionario2: this.respostasQuestionario2
                    })
                });
                
                const result = await response.json();
                console.log('✅ Resultado da API:', result);
                
                this.competenciaEstadual = result.competencia === 'estadual';
                this.naoSujeitoVisa = result.competencia === 'nao_sujeito_visa';
                
                console.log('📊 Competência definida:', {
                    competenciaEstadual: this.competenciaEstadual,
                    naoSujeitoVisa: this.naoSujeitoVisa,
                    resultado: result.competencia
                });
            } catch (error) {
                console.error('❌ Erro ao verificar competência:', error);
                this.competenciaEstadual = false;
                this.naoSujeitoVisa = false;
            }
        },

        formatarCnpj() {
            let valor = this.cnpjBusca.replace(/\D/g, '');
            if (valor.length > 14) valor = valor.substring(0, 14);
            valor = valor.replace(/^(\d{2})(\d)/, '$1.$2');
            valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            valor = valor.replace(/\.(\d{3})(\d)/, '.$1/$2');
            valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
            this.cnpjBusca = valor;
        },

        formatarTelefone() {
            let valor = this.dados.telefone.replace(/\D/g, '');
            if (valor.length > 11) valor = valor.substring(0, 11);
            if (valor.length > 10) {
                valor = valor.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
            } else if (valor.length > 6) {
                valor = valor.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
            } else if (valor.length > 2) {
                valor = valor.replace(/^(\d{2})(\d{0,5})$/, '($1) $2');
            }
            this.dados.telefone = valor;
        },

        formatarMoeda(valor) {
            if (!valor) return 'R$ 0,00';
            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor);
        },

        async buscarCnpj() {
            const cnpj = this.cnpjBusca.replace(/\D/g, '');
            if (cnpj.length !== 14) {
                this.mensagem = 'CNPJ deve ter 14 dígitos';
                this.tipoMensagem = 'error';
                return;
            }

            this.loading = true;
            this.mensagem = '';

            try {
                // Primeiro busca na API externa (BrasilAPI)
                const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`);
                if (!response.ok) throw new Error('CNPJ não encontrado');
                
                const data = await response.json();
                
                // Preenche os dados
                this.dados.cnpj = this.cnpjBusca;
                this.dados.razao_social = data.razao_social || '';
                this.dados.nome_fantasia = data.nome_fantasia || data.razao_social || '';
                this.dados.natureza_juridica = data.natureza_juridica || '';
                this.dados.porte = data.porte || '';
                this.dados.descricao_situacao_cadastral = data.descricao_situacao_cadastral || '';
                this.dados.capital_social = data.capital_social || 0;
                this.dados.cnae_fiscal = data.cnae_fiscal?.toString() || '';
                this.dados.cnae_fiscal_descricao = data.cnae_fiscal_descricao || '';
                
                // Data de início
                if (data.data_inicio_atividade) {
                    this.dados.data_inicio_atividade_raw = data.data_inicio_atividade;
                    const parts = data.data_inicio_atividade.split('-');
                    this.dados.data_inicio_atividade = `${parts[2]}/${parts[1]}/${parts[0]}`;
                }

                // CNAEs secundários
                this.dados.cnaes_secundarios = data.cnaes_secundarios || [];
                
                // Endereço
                this.dados.endereco = data.logradouro || '';
                this.dados.numero = data.numero || '';
                this.dados.complemento = data.complemento || '';
                this.dados.bairro = data.bairro || '';
                this.dados.cidade = data.municipio || '';
                this.dados.estado = data.uf || '';
                this.dados.cep = data.cep?.replace(/\D/g, '') || '';
                if (this.dados.cep) {
                    this.dados.cep = this.dados.cep.replace(/(\d{5})(\d{3})/, '$1-$2');
                }
                this.dados.codigo_municipio_ibge = data.codigo_municipio_ibge?.toString() || '';
                
                // Telefone e email
                if (data.ddd_telefone_1) {
                    this.dados.telefone = data.ddd_telefone_1.replace(/\D/g, '');
                    this.formatarTelefone();
                }
                this.dados.email = data.email || '';

                // Tipo de setor baseado na natureza jurídica
                const natureza = (data.natureza_juridica || '').toLowerCase();
                const tipoSetor = (natureza.includes('público') || natureza.includes('administração pública')) ? 'publico' : 'privado';
                this.dados.tipo_setor = tipoSetor;

                // Se for PÚBLICO, verifica se já existem estabelecimentos com este CNPJ
                if (tipoSetor === 'publico') {
                    try {
                        const verificarResponse = await fetch(`{{ url('/api/verificar-cnpj') }}/${cnpj}`);
                        if (verificarResponse.ok) {
                            const verificarData = await verificarResponse.json();
                            if (verificarData.existe && verificarData.estabelecimentos && verificarData.estabelecimentos.length > 0) {
                                this.modalEstabelecimentosExistentes.estabelecimentos = verificarData.estabelecimentos;
                                this.modalEstabelecimentosExistentes.visivel = true;
                                return; // Aguarda decisão do usuário no modal
                            }
                        }
                    } catch (e) {
                        console.log('Erro ao verificar estabelecimentos existentes:', e);
                    }
                }

                this.dadosCarregados = true;
                this.mensagem = 'Dados carregados com sucesso!';
                this.tipoMensagem = 'success';

            } catch (error) {
                this.mensagem = 'Erro ao buscar CNPJ: ' + error.message;
                this.tipoMensagem = 'error';
            } finally {
                this.loading = false;
            }
        },

        async buscarCep() {
            const cep = this.dados.cep.replace(/\D/g, '');
            if (cep.length !== 8) return;

            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();
                if (!data.erro) {
                    this.dados.endereco = data.logradouro?.toUpperCase() || this.dados.endereco;
                    this.dados.bairro = data.bairro?.toUpperCase() || this.dados.bairro;
                    this.dados.cidade = data.localidade || this.dados.cidade;
                    this.dados.estado = data.uf || this.dados.estado;
                    this.dados.codigo_municipio_ibge = data.ibge || this.dados.codigo_municipio_ibge;
                }
            } catch (error) {
                console.error('Erro ao buscar CEP:', error);
            }
        },

        getEtapaAtual() {
            const abas = ['dados-gerais', 'endereco', 'atividades', 'contato'];
            return abas.indexOf(this.abaAtiva) + 1;
        },

        getNomeAba(aba) {
            const nomes = {
                'dados-gerais': 'Dados Gerais',
                'endereco': 'Endereço',
                'atividades': 'Atividades',
                'contato': 'Contato'
            };
            return nomes[aba] || '';
        },

        // Funções para Atividades Especiais (Projeto Arquitetônico / Análise de Rotulagem)
        desmarcarAtividadesEspeciais() {
            // Desmarca o modo de atividades especiais quando o usuário marca uma atividade do CNPJ
            if (this.apenasAtividadesEspeciais || this.atividadeEspecialProjetoArq || this.atividadeEspecialRotulagem) {
                this.apenasAtividadesEspeciais = false;
                this.atividadeEspecialProjetoArq = false;
                this.atividadeEspecialRotulagem = false;
                console.log('🔄 Atividades especiais desmarcadas automaticamente (usuário selecionou atividade do CNPJ)');
            }
        },

        toggleAtividadesEspeciais() {
            if (this.apenasAtividadesEspeciais) {
                // Desmarca todas as atividades do CNPJ
                this.atividadePrincipalMarcada = false;
                this.atividadesExercidas = [];
                this.questionarios = [];
                this.respostasQuestionario = {};
                this.respostasQuestionario2 = {};
                console.log('🔄 Modo atividades especiais ativado - atividades do CNPJ desmarcadas');
            } else {
                // Desmarca atividades especiais
                this.atividadeEspecialProjetoArq = false;
                this.atividadeEspecialRotulagem = false;
                console.log('🔄 Modo atividades especiais desativado');
            }
            this.verificarCompetencia();
        },

        atualizarAtividadesEspeciais() {
            console.log('📋 Atividades especiais atualizadas:', {
                projetoArq: this.atividadeEspecialProjetoArq,
                rotulagem: this.atividadeEspecialRotulagem
            });
            this.verificarCompetencia();
        },

        getAtividadesExercidas() {
            let atividades = [];
            
            // Se está no modo de atividades especiais, retorna apenas as atividades especiais
            if (this.apenasAtividadesEspeciais) {
                if (this.atividadeEspecialProjetoArq) {
                    atividades.push({ 
                        codigo: 'PROJ_ARQ', 
                        descricao: 'Projeto Arquitetônico - Análise de projeto arquitetônico para adequação sanitária',
                        principal: false,
                        especial: true
                    });
                }
                if (this.atividadeEspecialRotulagem) {
                    atividades.push({ 
                        codigo: 'ANAL_ROT', 
                        descricao: 'Análise de Rotulagem - Análise e aprovação de rótulos de produtos',
                        principal: false,
                        especial: true
                    });
                }
                console.log('Atividades especiais selecionadas:', atividades);
                return atividades;
            }
            
            // Adiciona atividade principal se marcada
            if (this.atividadePrincipalMarcada && this.dados.cnae_fiscal) {
                atividades.push({ 
                    codigo: String(this.dados.cnae_fiscal), 
                    descricao: this.dados.cnae_fiscal_descricao,
                    principal: true 
                });
            }
            
            // Adiciona atividades secundárias selecionadas
            // Converte para string para garantir comparação correta
            this.atividadesExercidas.forEach(codigoSelecionado => {
                const codigoStr = String(codigoSelecionado);
                const cnae = this.dados.cnaes_secundarios.find(c => String(c.codigo) === codigoStr);
                if (cnae) {
                    atividades.push({ 
                        codigo: String(cnae.codigo), 
                        descricao: cnae.descricao || cnae.texto || '',
                        principal: false 
                    });
                }
            });
            
            console.log('Atividades exercidas:', atividades);
            console.log('Array atividadesExercidas:', this.atividadesExercidas);
            
            return atividades;
        },

        async buscarQuestionarios() {
            // Monta lista de CNAEs selecionados
            const cnaes = [];
            
            if (this.atividadePrincipalMarcada && this.dados.cnae_fiscal) {
                cnaes.push(this.dados.cnae_fiscal);
            }
            
            this.atividadesExercidas.forEach(codigo => {
                cnaes.push(codigo);
            });
            
            if (cnaes.length === 0) {
                this.questionarios = [];
                this.respostasQuestionario = {};
                this.carregandoQuestionarios = false;
                return;
            }
            
            // Ativa indicador de carregamento
            this.carregandoQuestionarios = true;
            
            try {
                const response = await fetch('{{ route('company.estabelecimentos.buscar-questionarios') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ cnaes })
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.questionarios = data;
                    
                    // Remove respostas de questionários que não existem mais
                    const cnaesComQuestionario = data.map(q => q.cnae);
                    Object.keys(this.respostasQuestionario).forEach(cnae => {
                        if (!cnaesComQuestionario.includes(cnae)) {
                            delete this.respostasQuestionario[cnae];
                        }
                    });
                    
                    console.log('Questionários encontrados:', data);
                } else {
                    console.error('Erro ao buscar questionários');
                }
            } catch (error) {
                console.error('Erro ao buscar questionários:', error);
            } finally {
                // Desativa indicador de carregamento
                this.carregandoQuestionarios = false;
            }
        },

        // Verifica se pode avançar da aba de atividades
        podeAvancarAtividades() {
            // Se está no modo de atividades especiais
            if (this.apenasAtividadesEspeciais) {
                // Deve ter pelo menos uma atividade especial selecionada
                return this.atividadeEspecialProjetoArq || this.atividadeEspecialRotulagem;
            }
            
            // Deve ter pelo menos uma atividade selecionada
            const temAtividade = this.atividadePrincipalMarcada || this.atividadesExercidas.length > 0;
            if (!temAtividade) return false;
            
            // Não pode estar carregando questionários
            if (this.carregandoQuestionarios) return false;
            
            // Se houver questionários, todos devem estar respondidos
            if (this.questionarios.length > 0) {
                for (const quest of this.questionarios) {
                    // Verifica primeira pergunta
                    if (!this.respostasQuestionario[quest.cnae]) {
                        return false;
                    }
                    // Verifica segunda pergunta se existir
                    if (quest.pergunta2 && !this.respostasQuestionario2[quest.cnae]) {
                        return false;
                    }
                }
            }
            
            return true;
        },

        validarAba(aba) {
            let erros = [];
            
            if (aba === 'dados-gerais') {
                if (!this.dados.cnpj) erros.push('CNPJ é obrigatório');
                if (!this.dados.razao_social) erros.push('Razão Social é obrigatória');
                if (!this.dados.nome_fantasia) erros.push('Nome Fantasia é obrigatório');
            }
            
            if (aba === 'endereco') {
                if (!this.dados.cep) erros.push('CEP é obrigatório');
                if (!this.dados.endereco) erros.push('Logradouro é obrigatório');
                if (!this.dados.numero) erros.push('Número é obrigatório');
                if (!this.dados.bairro) erros.push('Bairro é obrigatório');
                if (!this.dados.cidade) erros.push('Cidade é obrigatória');
                if (!this.dados.estado) erros.push('Estado é obrigatório');
            }
            
            if (aba === 'atividades') {
                // Se está no modo de atividades especiais, valida se pelo menos uma foi selecionada
                if (this.apenasAtividadesEspeciais) {
                    if (!this.atividadeEspecialProjetoArq && !this.atividadeEspecialRotulagem) {
                        erros.push('Selecione pelo menos uma atividade especial (Projeto Arquitetônico ou Análise de Rotulagem)');
                    }
                } else {
                    // Validar se pelo menos uma atividade foi selecionada
                    if (!this.atividadePrincipalMarcada && this.atividadesExercidas.length === 0) {
                        erros.push('Selecione pelo menos uma atividade que será exercida');
                    }
                    
                    // Validar questionários
                    if (this.questionarios.length > 0) {
                        const questionariosNaoRespondidos = this.questionarios.filter(q => !this.respostasQuestionario[q.cnae]);
                        if (questionariosNaoRespondidos.length > 0) {
                            erros.push('Responda todos os questionários obrigatórios');
                        }
                        
                        // Validar segunda pergunta (se existir)
                        const questionarios2NaoRespondidos = this.questionarios.filter(q => q.pergunta2 && !this.respostasQuestionario2[q.cnae]);
                        if (questionarios2NaoRespondidos.length > 0) {
                            erros.push('Responda todas as perguntas dos questionários (incluindo a segunda pergunta)');
                        }
                    }
                }
            }
            
            return erros;
        },

        proximaAba(abaAtual) {
            const erros = this.validarAba(abaAtual);
            if (erros.length > 0) {
                this.modalErro.mensagens = erros;
                this.modalErro.visivel = true;
                return;
            }
            
            const abas = ['dados-gerais', 'endereco', 'atividades', 'contato'];
            const indexAtual = abas.indexOf(abaAtual);
            if (indexAtual < abas.length - 1) {
                this.abaAtiva = abas[indexAtual + 1];
            }
        },

        fecharModalErro() {
            this.modalErro.visivel = false;
            this.modalErro.mensagens = [];
        },

        handleSubmit(event) {
            let erros = [];
            
            // Validar aba de contato
            if (!this.dados.vinculo_usuario) {
                erros.push('Selecione o seu vínculo com o estabelecimento');
            }
            if (!this.dados.telefone) {
                erros.push('Telefone é obrigatório');
            }
            if (!this.dados.email) {
                erros.push('E-mail é obrigatório');
            }
            
            // Validar atividades
            if (this.apenasAtividadesEspeciais) {
                // Se está no modo de atividades especiais, valida se pelo menos uma foi selecionada
                if (!this.atividadeEspecialProjetoArq && !this.atividadeEspecialRotulagem) {
                    erros.push('Selecione pelo menos uma atividade especial (Projeto Arquitetônico ou Análise de Rotulagem)');
                }
            } else {
                // Validar questionários
                if (this.questionarios.length > 0) {
                    const questionariosNaoRespondidos = this.questionarios.filter(q => !this.respostasQuestionario[q.cnae]);
                    if (questionariosNaoRespondidos.length > 0) {
                        erros.push('Responda todos os questionários obrigatórios na aba Atividades');
                    }
                    
                    // Validar segunda pergunta (se existir)
                    const questionarios2NaoRespondidos = this.questionarios.filter(q => q.pergunta2 && !this.respostasQuestionario2[q.cnae]);
                    if (questionarios2NaoRespondidos.length > 0) {
                        erros.push('Responda todas as perguntas dos questionários (incluindo a segunda pergunta)');
                    }
                }
                
                // Validar se pelo menos uma atividade foi selecionada
                if (!this.atividadePrincipalMarcada && this.atividadesExercidas.length === 0) {
                    erros.push('Selecione pelo menos uma atividade que será exercida');
                }
            }
            
            if (erros.length > 0) {
                event.preventDefault();
                this.modalErro.mensagens = erros;
                this.modalErro.visivel = true;
                return;
            }
            
            this.submitting = true;
        },

        // Funções do Modal de Estabelecimentos Existentes
        fecharModalEstabelecimentos() {
            this.modalEstabelecimentosExistentes.visivel = false;
            this.modalEstabelecimentosExistentes.estabelecimentos = [];
        },

        cancelarCadastro() {
            this.fecharModalEstabelecimentos();
            this.dadosCarregados = false;
            this.cnpjBusca = '';
            this.dados = {
                cnpj: '',
                razao_social: '',
                nome_fantasia: '',
                natureza_juridica: '',
                porte: '',
                descricao_situacao_cadastral: '',
                data_situacao_cadastral: '',
                data_inicio_atividade: '',
                data_inicio_atividade_raw: '',
                capital_social: '',
                cnae_fiscal: '',
                cnae_fiscal_descricao: '',
                cnaes_secundarios: [],
                endereco: '',
                numero: '',
                complemento: '',
                bairro: '',
                cidade: '',
                estado: '',
                cep: '',
                codigo_municipio_ibge: '',
                telefone: '',
                email: '',
                tipo_setor: 'privado'
            };
        },

        continuarCadastro() {
            this.fecharModalEstabelecimentos();
            this.mensagem = '✅ Dados encontrados com sucesso! Você pode continuar o cadastro.';
            this.tipoMensagem = 'success';
            this.dadosCarregados = true;
        },

        // Métodos para busca manual de CNAE
        async buscarCnaeAdicional() {
            if (!this.cnaeBusca || this.cnaeBusca.length < 3) {
                this.cnaeErro = 'Digite pelo menos 3 caracteres para buscar';
                return;
            }
            
            this.cnaeErro = '';
            this.loadingCnae = true;
            this.cnaeResultados = [];

            try {
                // Se for código (apenas números)
                if (/^\d+$/.test(this.cnaeBusca)) {
                    const response = await fetch(`https://servicodados.ibge.gov.br/api/v2/cnae/subclasses/${this.cnaeBusca}`);
                    if (response.ok) {
                        const data = await response.json();
                        if (data && data.id) {
                            this.cnaeResultados = [{
                                codigo: data.id,
                                descricao: data.descricao
                            }];
                        }
                    }
                } else {
                    // Busca na nossa rota local por descrição
                    const response = await fetch(`{{ route('company.estabelecimentos.buscar-cnaes') }}?q=${encodeURIComponent(this.cnaeBusca)}`);
                    if (response.ok) {
                        this.cnaeResultados = await response.json();
                    }
                }

                if (this.cnaeResultados.length === 0) {
                    this.cnaeErro = 'Nenhum CNAE encontrado';
                }
            } catch (error) {
                console.error('Erro na busca:', error);
                this.cnaeErro = 'Erro ao buscar CNAE';
            } finally {
                this.loadingCnae = false;
            }
        },

        adicionarCnaeManual(cnae) {
            // Verifica se já existe na lista de secundários
            const codigoCnae = String(cnae.codigo);
            const existe = this.dados.cnaes_secundarios.some(c => String(c.codigo) === codigoCnae);
            
            if (!existe) {
                // Adiciona à lista de secundários
                this.dados.cnaes_secundarios.unshift({
                    codigo: cnae.codigo,
                    descricao: cnae.descricao,
                    manual: true
                });
            }
            
            // Marca automaticamente
            if (!this.atividadesExercidas.includes(codigoCnae)) {
                this.atividadesExercidas.push(codigoCnae);
            }
            
            // Limpa busca
            this.cnaeBusca = '';
            this.cnaeResultados = [];
            this.mensagem = 'CNAE adicionado com sucesso!';
            this.tipoMensagem = 'success';
        }
    }
}
</script>
@endpush
@endsection
