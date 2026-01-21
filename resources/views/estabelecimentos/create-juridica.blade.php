@extends('layouts.admin')

@section('title', 'Cadastrar Pessoa Jurídica')
@section('page-title', 'Cadastrar Pessoa Jurídica')

@section('content')
<div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.estabelecimentos.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Cadastrar Pessoa Jurídica</h1>
                <p class="text-sm text-gray-600 mt-1">Digite o CNPJ para buscar os dados automaticamente</p>
            </div>
        </div>
    </div>

    {{-- Alerta para Usuários Municipais --}}
    @if(auth('interno')->check() && auth('interno')->user()->isMunicipal())
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-blue-900">Restrição de Município</h3>
                    <p class="text-sm text-blue-800 mt-1">
                        Você só pode cadastrar estabelecimentos do município de 
                        <strong>{{ auth('interno')->user()->municipioRelacionado->nome ?? 'seu município' }}</strong>.
                        Estabelecimentos de outros municípios serão rejeitados automaticamente.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Formulário --}}
    <form id="formEstabelecimento" method="POST" action="{{ route('admin.estabelecimentos.store') }}" 
          x-data="estabelecimentoForm()" 
          @submit="handleSubmit($event)"
          class="space-y-6"
          novalidate>
        @csrf
        <input type="hidden" name="tipo_pessoa" value="juridica">

        {{-- Modal de Erros das Etapas --}}
        <div x-cloak x-show="modalErro.visivel" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            <div class="w-full max-w-md bg-white rounded-xl shadow-2xl border border-red-200">
                <div class="flex items-start justify-between px-5 py-4 border-b border-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Revisar campos obrigatórios</h3>
                        <p class="text-sm text-gray-500 mt-1">Preencha os itens abaixo para continuar para a próxima etapa.</p>
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
                    <button type="button" @click="fecharModalErro" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">Entendi</button>
                </div>
            </div>
        </div>

        {{-- Busca por CNPJ --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
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
                    <p class="text-xs text-gray-500 mt-1 mb-3">Digite apenas números ou use pontuação</p>
                </div>

                <div>
                    <button type="button" 
                            @click="buscarCnpj"
                            :disabled="loading || cnpjBusca.length < 18"
                            class="w-full sm:w-auto px-6 py-2 text-sm text-white rounded-lg font-semibold transition-all duration-200 whitespace-nowrap inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:bg-blue-500 disabled:hover:bg-blue-500 disabled:opacity-80 disabled:cursor-wait">
                        <svg x-show="loading" x-transition.opacity x-cloak class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="loading ? 'Buscando...' : 'Buscar'"></span>
                    </button>
                </div>
            </div>

            {{-- Mensagens --}}
            <div x-show="mensagem" x-cloak class="mt-3">
                {{-- Alerta de Sucesso (Verde) --}}
                <template x-if="tipoMensagem === 'success'">
                    <div class="bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-500 p-4 rounded-lg shadow-sm">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-semibold text-green-900" x-text="mensagem"></p>
                            </div>
                        </div>
                    </div>
                </template>
                
                {{-- Alerta de Erro (Vermelho) --}}
                <template x-if="tipoMensagem === 'error'">
                    <div class="bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <div class="text-sm text-red-900" x-html="mensagem"></div>
                            </div>
                        </div>
                    </div>
                </template>
                
                {{-- Alerta de Warning (Amarelo) --}}
                <template x-if="tipoMensagem === 'warning'">
                    <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 border-l-4 border-yellow-500 p-4 rounded-lg shadow-sm">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-semibold text-yellow-900" x-text="mensagem"></p>
                            </div>
                        </div>
                    </div>
                </template>
                
                {{-- Alerta de Info (Azul) --}}
                <template x-if="tipoMensagem === 'info'">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-blue-500 p-4 rounded-lg shadow-sm">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-semibold text-blue-900" x-text="mensagem"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

         {{-- Dados Completos em Abas --}}
         <div class="bg-white rounded-lg shadow-sm border border-gray-200" x-show="dadosCarregados">
             {{-- Navegação das Abas --}}
             <div class="border-b border-gray-200 bg-gray-50">
                 <nav class="flex space-x-0 px-6" aria-label="Tabs">
                     <button type="button" @click="abaAtiva = 'dados-gerais'"
                             :class="abaAtiva === 'dados-gerais' ? 'bg-white border-b-2 border-blue-500 text-blue-600' : 'bg-gray-50 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                             class="px-4 py-3 font-medium text-sm focus:outline-none transition-colors duration-200 first:rounded-tl-lg">
                         Dados Gerais
                     </button>
                     <button type="button" @click="abaAtiva = 'endereco'"
                             :class="abaAtiva === 'endereco' ? 'bg-white border-b-2 border-blue-500 text-blue-600' : 'bg-gray-50 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                             class="px-4 py-3 font-medium text-sm focus:outline-none transition-colors duration-200">
                         Endereço
                     </button>
                     <button type="button" @click="abaAtiva = 'atividades'"
                             :class="abaAtiva === 'atividades' ? 'bg-white border-b-2 border-blue-500 text-blue-600' : 'bg-gray-50 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                             class="px-4 py-3 font-medium text-sm focus:outline-none transition-colors duration-200">
                         Atividades
                     </button>
                     <button type="button" @click="abaAtiva = 'contato'"
                             :class="abaAtiva === 'contato' ? 'bg-white border-b-2 border-blue-500 text-blue-600' : 'bg-gray-50 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                             class="px-4 py-3 font-medium text-sm focus:outline-none transition-colors duration-200">
                         Contato
                     </button>
                 </nav>
             </div>

             {{-- Barra de Progresso Animada --}}
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-800">
                        Etapa <span x-text="getEtapaAtual()"></span> de 4 - <span x-text="getNomeAba(abaAtiva)"></span>
                    </span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                          :class="getEtapaAtual() === 4 ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'"
                          x-text="Math.round((getEtapaAtual() / 4) * 100) + '%'"></span>
                </div>
                
                {{-- Barra de Progresso Visual com Animação --}}
                <div class="w-full bg-gray-200 rounded-full h-2 mb-5 shadow-inner overflow-hidden">
                    <div class="h-2 rounded-full transition-all duration-700 ease-out relative"
                         :class="getEtapaAtual() === 4 ? 'bg-gradient-to-r from-green-400 via-green-500 to-green-600' : 'bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600'"
                         :style="'width: ' + (getEtapaAtual() / 4) * 100 + '%'">
                        {{-- Animação de brilho --}}
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-40 animate-pulse"></div>
                    </div>
                </div>
                
                {{-- Indicadores das Etapas com Linha de Conexão --}}
                <div class="relative">
                    {{-- Linha de fundo conectando todos os círculos --}}
                    <div class="absolute top-4 left-0 right-0 h-0.5 bg-gray-300" style="margin: 0 5%;"></div>
                    
                    {{-- Linha de progresso verde/azul --}}
                    <div class="absolute top-4 left-0 h-0.5 transition-all duration-700"
                         :class="getEtapaAtual() === 4 ? 'bg-green-500' : 'bg-blue-500'"
                         :style="'width: ' + ((getEtapaAtual() - 1) / 3) * 90 + '%; margin-left: 5%;'"></div>
                    
                    {{-- Círculos das etapas --}}
                    <div class="flex justify-between items-start relative">
                        <template x-for="(aba, index) in ['dados-gerais', 'endereco', 'atividades', 'contato']" :key="index">
                            <div class="flex flex-col items-center" style="width: 25%;">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-500 transform relative bg-white border-2"
                                     :class="getEtapaAtual() > index + 1 ? 'border-green-500 text-green-600 scale-105 shadow-lg' : (abaAtiva === aba ? 'border-blue-500 text-blue-600 scale-105 shadow-lg' : 'border-gray-300 text-gray-400 scale-100')"
                                     >
                                    {{-- Check verde para etapas concluídas --}}
                                    <template x-if="getEtapaAtual() > index + 1">
                                        <svg class="w-4 h-4 text-green-500" 
                                             fill="none" 
                                             stroke="currentColor" 
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" 
                                                  stroke-linejoin="round" 
                                                  stroke-width="2.5" 
                                                  d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </template>
                                    {{-- Número para etapas não concluídas --}}
                                    <template x-if="getEtapaAtual() <= index + 1">
                                        <span x-text="index + 1"></span>
                                    </template>
                                </div>
                                <span class="text-[9px] mt-1.5 font-medium transition-colors duration-300 text-center" 
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
                     
                     {{-- Linha 1: CNPJ e Razão Social --}}
                    <div class="grid grid-cols-2 gap-6 mb-4">
                         {{-- CNPJ --}}
                         <div>
                             <label for="cnpj" class="block text-sm font-medium text-gray-700 mb-2">
                                 CNPJ <span class="text-red-500">*</span>
                             </label>
                             <input type="text" 
                                    id="cnpj" 
                                    name="cnpj"
                                    x-model="dados.cnpj"
                                    readonly
                                    class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700 font-mono">
                         </div>

                         {{-- Razão Social --}}
                         <div>
                             <label for="razao_social" class="block text-sm font-medium text-gray-700 mb-2">
                                 Razão Social <span class="text-red-500">*</span>
                             </label>
                             <input type="text" 
                                    id="razao_social" 
                                    name="razao_social"
                                    x-model="dados.razao_social"
                                    readonly
                                    class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                         </div>
                     </div>

                     {{-- Linha 2: Nome Fantasia e Natureza Jurídica --}}
                    <div class="grid grid-cols-2 gap-6 mb-4">
                        {{-- Nome Fantasia --}}
                        <div>
                            <label for="nome_fantasia" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome Fantasia <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                  id="nome_fantasia" 
                                  name="nome_fantasia"
                                  x-model="dados.nome_fantasia"
                                  @input="dados.nome_fantasia = toUpperCase($event.target.value)"
                                  :class="dados.tipo_setor === 'publico' ? 'border-2 border-yellow-400 bg-yellow-50' : ''"
                                  class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase"
                                  style="text-transform: uppercase;">
                           
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

                        {{-- Natureza Jurídica --}}
                         <div>
                             <label for="natureza_juridica" class="block text-sm font-medium text-gray-700 mb-2">
                                 Natureza Jurídica
                             </label>
                             <input type="text" 
                                    id="natureza_juridica" 
                                    name="natureza_juridica"
                                    x-model="dados.natureza_juridica"
                                    readonly
                                    class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                         </div>
                     </div>

                     {{-- Linha 3: Porte e Situação Cadastral --}}
                    <div class="grid grid-cols-2 gap-6 mb-4">
                         {{-- Porte --}}
                         <div>
                             <label for="porte" class="block text-sm font-medium text-gray-700 mb-2">
                                 Porte da Empresa
                             </label>
                             <input type="text" 
                                    id="porte" 
                                    name="porte"
                                    x-model="dados.porte"
                                    readonly
                                    class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                         </div>

                         {{-- Situação Cadastral --}}
                         <div>
                             <label for="descricao_situacao_cadastral" class="block text-sm font-medium text-gray-700 mb-2">
                                 Situação Cadastral
                             </label>
                             <input type="text" 
                                    id="descricao_situacao_cadastral" 
                                    name="descricao_situacao_cadastral"
                                    x-model="dados.descricao_situacao_cadastral"
                                    readonly
                                    class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                         </div>
                     </div>

                     {{-- Linha 4: Datas --}}
                    <div class="grid grid-cols-2 gap-6 mb-4">
                         {{-- Data Situação Cadastral --}}
                         <div>
                             <label for="data_situacao_cadastral_display" class="block text-sm font-medium text-gray-700 mb-2">
                                 Data da Situação Cadastral
                             </label>
                             <input type="text" 
                                    id="data_situacao_cadastral_display"
                                    x-model="dados.data_situacao_cadastral"
                                    readonly
                                    class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                         </div>

                         {{-- Data Início Atividade --}}
                         <div>
                             <label for="data_inicio_atividade_display" class="block text-sm font-medium text-gray-700 mb-2">
                                 Data de Início da Atividade
                             </label>
                             <input type="text" 
                                    id="data_inicio_atividade_display"
                                    x-model="dados.data_inicio_atividade"
                                    readonly
                                    class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                         </div>
                     </div>

                     {{-- Linha 5: Capital Social --}}
                    <div class="grid grid-cols-2 gap-6 mb-6">
                         {{-- Capital Social --}}
                         <div>
                             <label for="capital_social_display" class="block text-sm font-medium text-gray-700 mb-2">
                                 Capital Social
                             </label>
                             <input type="text" 
                                    id="capital_social_display"
                                    x-model="formatarMoeda(dados.capital_social)"
                                    readonly
                                    class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700 font-mono">
                         </div>
                     </div>

                     {{-- Tipo de Setor (Público/Privado) --}}
                     <div class="mb-6">
                         <label class="block text-sm font-medium text-gray-700 mb-3">
                             Tipo de Setor
                         </label>
                         <div class="flex items-center gap-4 p-4 rounded-xl border-2 transition-all duration-300"
                              :class="dados.tipo_setor === 'publico' ? 'bg-green-50 border-green-300 shadow-green-100' : 'bg-blue-50 border-blue-300 shadow-blue-100'">
                             <div class="text-3xl" x-text="dados.tipo_setor === 'publico' ? '🏛️' : '🏢'"></div>
                             <div class="flex-1">
                                 <div class="font-semibold text-lg text-gray-900" x-text="dados.tipo_setor === 'publico' ? 'Estabelecimento Público' : 'Estabelecimento Privado'"></div>
                                 <div class="text-sm text-gray-600 mt-1" x-text="dados.tipo_setor === 'publico' ? 'Permite múltiplos estabelecimentos com mesmo CNPJ' : 'CNPJ deve ser único no sistema'"></div>
                             </div>
                         </div>
                         <input type="hidden" name="tipo_setor" x-model="dados.tipo_setor">
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
                                <p class="text-sm font-semibold text-yellow-800 mb-2">⚠️ Atenção: Endereço de Estabelecimento Público</p>
                                <p class="text-xs text-yellow-700 leading-relaxed mb-2">
                                    O endereço que veio da API pode ser da <strong>Prefeitura ou Fundo Municipal</strong>, mas o estabelecimento real pode estar em outro local.
                                </p>
                                <p class="text-xs text-yellow-700 leading-relaxed">
                                    <strong>Verifique e corrija o endereço</strong> para o local onde o estabelecimento realmente funciona:
                                </p>
                                <ul class="text-xs text-yellow-700 mt-2 space-y-1 ml-4">
                                    <li>• Hospital Municipal → endereço do hospital</li>
                                    <li>• Laboratório Central → endereço do laboratório</li>
                                    <li>• UBS → endereço da unidade de saúde</li>
                                    <li>• HPP → endereço do hospital</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Linha 1: CEP e Logradouro --}}
                     <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                         {{-- CEP --}}
                        <div>
                            <label for="cep" class="block text-sm font-medium text-gray-700 mb-2">
                                CEP <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       id="cep_display" 
                                       x-model="dados.cep"
                                       @blur="buscarCep()"
                                       @input="dados.cep = dados.cep.replace(/\D/g, '').replace(/(\d{5})(\d)/, '$1-$2').substring(0, 9)"
                                       placeholder="00000-000"
                                       maxlength="9"
                                       class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                {{-- Campo hidden que envia apenas os números (8 dígitos) --}}
                                <input type="hidden" 
                                       name="cep" 
                                       :value="dados.cep.replace(/\D/g, '')">
                                <div x-show="loading" class="absolute right-3 top-3">
                                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Digite o CEP e o endereço será preenchido automaticamente</p>
                        </div>

                         {{-- Logradouro --}}
                         <div class="md:col-span-2">
                             <label for="endereco" class="block text-sm font-medium text-gray-700 mb-2">
                                 Logradouro <span class="text-red-500">*</span>
                             </label>
                             <input type="text" 
                                   id="endereco" 
                                   name="endereco"
                                   x-model="dados.endereco"
                                   @input="dados.endereco = toUpperCase($event.target.value)"
                                   :class="dados.tipo_setor === 'publico' ? 'border-2 border-yellow-400 bg-yellow-50' : ''"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase"
                                   style="text-transform: uppercase;">
                         </div>
                     </div>

                     {{-- Linha 2: Número, Complemento e Bairro --}}
                     <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                         {{-- Número --}}
                         <div>
                             <label for="numero" class="block text-sm font-medium text-gray-700 mb-2">
                                 Número <span class="text-red-500">*</span>
                             </label>
                             <input type="text" 
                                   id="numero" 
                                   name="numero"
                                   x-model="dados.numero"
                                   @input="dados.numero = toUpperCase($event.target.value)"
                                   :class="dados.tipo_setor === 'publico' ? 'border-2 border-yellow-400 bg-yellow-50' : ''"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase"
                                   style="text-transform: uppercase;">
                         </div>

                         {{-- Complemento --}}
                         <div>
                             <label for="complemento" class="block text-sm font-medium text-gray-700 mb-2">
                                 Complemento
                             </label>
                             <input type="text" 
                                   id="complemento" 
                                   name="complemento"
                                   x-model="dados.complemento"
                                   @input="dados.complemento = toUpperCase($event.target.value)"
                                   :class="dados.tipo_setor === 'publico' ? 'border-2 border-yellow-400 bg-yellow-50' : ''"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase"
                                   style="text-transform: uppercase;">
                         </div>

                         {{-- Bairro --}}
                         <div>
                             <label for="bairro" class="block text-sm font-medium text-gray-700 mb-2">
                                 Bairro <span class="text-red-500">*</span>
                             </label>
                             <input type="text" 
                                   id="bairro" 
                                   name="bairro"
                                   x-model="dados.bairro"
                                   @input="dados.bairro = toUpperCase($event.target.value)"
                                   :class="dados.tipo_setor === 'publico' ? 'border-2 border-yellow-400 bg-yellow-50' : ''"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase"
                                   style="text-transform: uppercase;">
                         </div>
                     </div>

                     {{-- Linha 3: Cidade, Estado e Código IBGE --}}
                     <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                         {{-- Cidade --}}
                         <div>
                             <label for="cidade" class="block text-sm font-medium text-gray-700 mb-2">
                                 Cidade <span class="text-red-500">*</span>
                             </label>
                             <input type="text" 
                                   id="cidade" 
                                   name="cidade"
                                   x-model="dados.cidade"
                                   readonly
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700 uppercase">
                         </div>

                         {{-- Estado --}}
                         <div>
                             <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                 Estado <span class="text-red-500">*</span>
                             </label>
                             <input type="text" 
                                   id="estado" 
                                   name="estado"
                                   x-model="dados.estado"
                                   readonly
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700 uppercase">
                         </div>

                         {{-- Código Município IBGE --}}
                         <div>
                             <label for="codigo_municipio_ibge_display" class="block text-sm font-medium text-gray-700 mb-2">
                                 Código IBGE
                             </label>
                             <input type="text" 
                                    id="codigo_municipio_ibge_display"
                                    x-model="dados.codigo_municipio_ibge"
                                    readonly
                                    class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                         </div>
                     </div>
                 </div>

                 {{-- Aba: Atividades --}}
                <div x-show="abaAtiva === 'atividades'" x-cloak>
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Atividades da Empresa</h3>
                    
                    {{-- Alerta Informativo --}}
                    <div class="mb-6 bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-blue-500 p-4 rounded-lg shadow-sm">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-bold text-blue-900">
                                    📝 Marque apenas as atividades que o estabelecimento <span class="text-red-600">realmente exerce</span>
                                </h3>
                                <p class="mt-2 text-xs text-blue-700">
                                    Selecione somente as atividades secundárias que são praticadas no dia a dia do estabelecimento.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    {{-- CNAE Principal --}}
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h4 class="text-base font-semibold text-green-800">Atividade Principal</h4>
                            <span class="ml-auto px-2 py-1 bg-green-600 text-white text-xs font-bold rounded-full">PRINCIPAL</span>
                        </div>
                        <div class="bg-white rounded-lg p-4 border border-green-200 hover:bg-green-50 transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 pt-1">
                                    <input type="checkbox" 
                                           id="cnae_principal"
                                           :value="dados.cnae_fiscal"
                                           x-model="atividadePrincipalMarcada"
                                           class="h-5 w-5 text-green-600 border-green-300 rounded cursor-pointer focus:ring-green-500">
                                </div>
                                <div class="flex-1">
                                    <label for="cnae_principal" class="cursor-pointer">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-600 text-white" x-text="dados.cnae_fiscal"></span>
                                        </div>
                                        <p class="text-sm text-gray-900 font-medium" x-text="dados.cnae_fiscal_descricao"></p>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CNAEs Secundários --}}
                    <div>
                        <h4 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Atividades Secundárias
                        </h4>
                        <div class="space-y-2 max-h-80 overflow-y-auto border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <template x-for="(cnae, index) in dados.cnaes_secundarios" :key="index">
                                <div class="flex items-start gap-3 p-3 bg-white hover:bg-blue-50 rounded-lg transition-colors border border-gray-200 hover:border-blue-300">
                                    <div class="flex-shrink-0 pt-1">
                                        <input type="checkbox" 
                                               :id="'cnae_' + cnae.codigo"
                                               :value="cnae.codigo"
                                               x-model="atividadesExercidas"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                                    </div>
                                    <div class="flex-1">
                                        <label :for="'cnae_' + cnae.codigo" class="cursor-pointer">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800" x-text="cnae.codigo"></span>
                                            </div>
                                            <p class="text-sm text-gray-900" x-text="cnae.descricao"></p>
                                        </label>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="mt-3 flex items-center justify-between bg-gray-100 px-4 py-2 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">
                                <svg class="w-4 h-4 inline mr-1 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                                </svg>
                                Atividades secundárias selecionadas:
                            </span>
                            <span class="text-sm font-bold text-blue-600 bg-blue-100 px-3 py-1 rounded-full" x-text="atividadesExercidas.length"></span>
                        </div>

                        {{-- Busca de CNAE Manual (Apenas Público) --}}
                        <div x-show="dados.tipo_setor === 'publico'" class="mb-6 mt-6 bg-white border-2 border-dashed border-blue-300 rounded-lg p-5">
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

                        {{-- Questionários Dinâmicos --}}
                        <div x-show="questionarios.length > 0" class="mt-6 space-y-4">
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
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Alerta de Competência (apenas para usuários municipais) --}}
                        @if(auth('interno')->check() && auth('interno')->user()->isMunicipal())
                        <div x-show="atividadesExercidas.length > 0 || atividadePrincipalMarcada" class="mt-4">
                            {{-- Alerta Estadual --}}
                            <div x-show="competenciaEstadual" class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-lg">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-bold text-purple-900">
                                            ⚠️ ATENÇÃO: Este estabelecimento será de COMPETÊNCIA ESTADUAL
                                        </p>
                                        <p class="mt-2 text-sm text-purple-800">
                                            <strong>Motivo:</strong> Pelo menos uma das atividades selecionadas está configurada como de competência estadual.
                                        </p>
                                        <p class="mt-2 text-sm text-purple-700">
                                            <strong>Importante:</strong> Após o cadastro, este estabelecimento será visível apenas para <strong>Gestores e Técnicos Estaduais</strong>. Você (usuário municipal) não terá acesso a ele.
                                        </p>
                                        <p class="mt-2 text-xs text-purple-600">
                                            💡 Se isso não estiver correto, revise as atividades selecionadas ou entre em contato com o administrador.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Alerta Municipal --}}
                            <div x-show="!competenciaEstadual" class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-bold text-blue-900">
                                            ✅ Este estabelecimento será de COMPETÊNCIA MUNICIPAL
                                        </p>
                                        <p class="mt-2 text-sm text-blue-800">
                                            Todas as atividades selecionadas são de competência municipal. Você terá acesso a este estabelecimento após o cadastro.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                 {{-- Aba: Contato --}}
                 <div x-show="abaAtiva === 'contato'" x-cloak>
                     <h3 class="text-lg font-medium text-gray-900 mb-6">Informações de Contato</h3>
                     
                     {{-- Aviso sobre dados da API --}}
                     <div x-show="!dados.telefone && !dados.email" class="mb-6 bg-gradient-to-r from-yellow-50 to-yellow-100 border-l-4 border-yellow-500 p-4 rounded-lg shadow-sm">
                         <div class="flex items-start">
                             <div class="flex-shrink-0">
                                 <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                     <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                         <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                     </svg>
                                 </div>
                             </div>
                             <div class="ml-3 flex-1">
                                 <h3 class="text-sm font-bold text-yellow-900">⚠️ Informações de contato não encontradas na Receita Federal</h3>
                                 <p class="mt-2 text-xs text-yellow-700">Por favor, preencha os dados de contato do estabelecimento abaixo.</p>
                             </div>
                         </div>
                     </div>
                     
                     {{-- Sucesso ao carregar dados --}}
                     <div x-show="dados.telefone || dados.email" class="mb-6 bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-500 p-4 rounded-lg shadow-sm">
                         <div class="flex items-start">
                             <div class="flex-shrink-0">
                                 <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                     <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                         <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                     </svg>
                                 </div>
                             </div>
                             <div class="ml-3 flex-1">
                                 <h3 class="text-sm font-bold text-green-900">✅ Dados de contato encontrados na Receita Federal</h3>
                                 <p class="mt-2 text-xs text-green-700">Você pode editar as informações abaixo se necessário.</p>
                             </div>
                         </div>
                     </div>

                     <div class="space-y-4">
                         {{-- Telefone 1 --}}
                         <div>
                             <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">
                                 Telefone 1 <span class="text-red-500">*</span>
                                 <span class="text-sm text-gray-500">(fixo ou celular)</span>
                             </label>
                             <input type="text" 
                                    id="telefone" 
                                    name="telefone"
                                    x-model="dados.telefone"
                                    @input="dados.telefone = formatarTelefone($event.target.value)"
                                    placeholder="(00) 00000-0000"
                                    maxlength="15"
                                    required
                                    class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                             <p class="mt-1 text-xs text-gray-500">Formato: (11) 1234-5678 ou (11) 91234-5678</p>
                         </div>
                         
                         {{-- Telefone 2 (Opcional) --}}
                         <div>
                             <label for="telefone2" class="block text-sm font-medium text-gray-700 mb-2">
                                 Telefone 2 <span class="text-sm text-gray-500">(opcional)</span>
                             </label>
                             <input type="text" 
                                    id="telefone2" 
                                    name="telefone2"
                                    x-model="dados.telefone2"
                                    @input="dados.telefone2 = formatarTelefone($event.target.value)"
                                    placeholder="(00) 00000-0000"
                                    maxlength="15"
                                    class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                             <p class="mt-1 text-xs text-gray-500">Telefone adicional (se houver)</p>
                         </div>

                         {{-- Email --}}
                         <div>
                             <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                 E-mail <span class="text-red-500">*</span>
                             </label>
                             <input type="email" 
                                    id="email" 
                                    name="email"
                                    x-model="dados.email"
                                    placeholder="contato@empresa.com.br"
                                    required
                                    class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                             <p class="mt-1 text-xs text-gray-500">E-mail principal para contato da vigilância sanitária</p>
                         </div>
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

        {{-- Campos ocultos para dados da API --}}
        <div x-show="dadosCarregados" style="display: none;">
            <input type="hidden" name="situacao_cadastral" x-model="dados.situacao_cadastral">
            <input type="hidden" name="descricao_situacao_cadastral" x-model="dados.descricao_situacao_cadastral">
            <input type="hidden" name="data_situacao_cadastral" x-model="dados.data_situacao_cadastral">
            <input type="hidden" name="data_inicio_atividade" x-model="dados.data_inicio_atividade">
            <input type="hidden" name="cnae_fiscal" x-model="dados.cnae_fiscal">
            <input type="hidden" name="cnae_fiscal_descricao" x-model="dados.cnae_fiscal_descricao">
            <input type="hidden" name="motivo_situacao_cadastral" x-model="dados.motivo_situacao_cadastral">
            <input type="hidden" name="descricao_motivo_situacao_cadastral" x-model="dados.descricao_motivo_situacao_cadastral">
            <input type="hidden" name="cnaes_secundarios" x-model="JSON.stringify(dados.cnaes_secundarios)">
            <input type="hidden" name="qsa" x-model="JSON.stringify(dados.qsa)">
            <input type="hidden" name="capital_social" x-model="dados.capital_social">
            <input type="hidden" name="logradouro" x-model="dados.logradouro">
            <input type="hidden" name="codigo_municipio_ibge" x-model="dados.codigo_municipio_ibge">
            <input type="hidden" name="atividades_exercidas" :value="getAtividadesJSON()">
            <input type="hidden" name="respostas_questionario" :value="JSON.stringify(respostasQuestionario)">
            <input type="hidden" name="atividade_principal_marcada" x-model="atividadePrincipalMarcada">
        </div>

        {{-- Navegação entre Abas --}}
        <div class="flex justify-between items-center pt-6 border-t border-gray-200" x-show="dadosCarregados">
            {{-- Botão Anterior --}}
            <button type="button" 
                    @click="abaAnterior()" 
                    x-show="abaAtiva !== 'dados-gerais'"
                    class="flex items-center gap-2 px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200">
                ← Anterior
            </button>
            
            {{-- Cancelar (apenas na primeira aba) --}}
            <a href="{{ route('admin.estabelecimentos.index') }}" 
               x-show="abaAtiva === 'dados-gerais'"
               class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200">
                Cancelar
            </a>

            {{-- Botão Próximo --}}
            <button type="button" 
                    @click="proximaAba()" 
                    x-show="abaAtiva !== 'contato'"
                    class="flex items-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 font-medium">
                Próximo →
            </button>

            {{-- Botão Cadastrar (apenas na última aba) --}}
            <button type="submit" 
                    x-show="abaAtiva === 'contato'"
                    class="flex items-center gap-2 px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200 font-medium">
                ✅ Cadastrar Estabelecimento
            </button>
        </div>
    </form>
</div>

<script>
function estabelecimentoForm() {
    return {
        cnpjBusca: '',
        loading: false,
        dadosCarregados: false,
        mensagem: '',
        tipoMensagem: 'error',
        abaAtiva: 'dados-gerais',
        atividadesExercidas: [],
        atividadePrincipalMarcada: false,
        competenciaEstadual: false,
        questionarios: [],
        respostasQuestionario: {},
        modalErro: {
            visivel: false,
            mensagens: []
        },
        modalEstabelecimentosExistentes: {
            visivel: false,
            estabelecimentos: []
        },
        dados: {
            tipo_setor: '',
            cnpj: '',
            razao_social: '',
            nome_fantasia: '',
            natureza_juridica: '',
            porte: '',
            situacao_cadastral: '',
            descricao_situacao_cadastral: '',
            endereco: '',
            numero: '',
            complemento: '',
            bairro: '',
            cidade: '',
            estado: '',
            cep: '',
            telefone: '',
            telefone2: '',
            email: '',
            tipo_estabelecimento: '',
            inscricao_estadual: '',
            atividade_principal: '',
            // Campos da API
            data_situacao_cadastral: '',
            data_inicio_atividade: '',
            cnae_fiscal: '',
            cnae_fiscal_descricao: '',
            cnaes_secundarios: [],
            qsa: [],
            capital_social: '',
            logradouro: '',
            codigo_municipio_ibge: '',
            // Campos adicionais da API
            ddd_telefone_1: '',
            ddd_telefone_2: '',
            ddd_fax: '',
            opcao_pelo_mei: null,
            opcao_pelo_simples: null,
            regime_tributario: [],
            situacao_especial: '',
            motivo_situacao_cadastral: '',
            descricao_motivo_situacao_cadastral: '',
            identificador_matriz_filial: '',
            qualificacao_do_responsavel: ''
        },

        // Variáveis para busca manual de CNAE
        cnaeBusca: '',
        cnaeErro: '',
        loadingCnae: false,
        cnaeResultados: [],

        init() {
            // Watchers para verificar competência quando atividades mudarem
            this.$watch('atividadesExercidas', () => {
                this.verificarCompetencia();
                this.buscarQuestionarios();
            });
            this.$watch('atividadePrincipalMarcada', () => {
                this.verificarCompetencia();
                this.buscarQuestionarios();
            });
            // Recalcula competência quando as respostas mudam
            this.$watch('respostasQuestionario', () => {
                this.verificarCompetencia();
            }, { deep: true });
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
                    const response = await fetch(`{{ route('admin.configuracoes.pactuacao.buscar-cnaes') }}?q=${encodeURIComponent(this.cnaeBusca)}`);
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
            // Converte para string para garantir comparação correta
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
            this.mostrarMensagem('CNAE adicionado com sucesso!', 'success');
        },

        async verificarCompetencia() {
            const atividades = [];
            
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
                respostas: JSON.parse(JSON.stringify(this.respostasQuestionario)) // Debug das respostas
            });
            
            if (atividades.length === 0) {
                this.competenciaEstadual = false;
                return;
            }
            
            // Consulta API para verificar competência
            try {
                const response = await fetch('/api/verificar-competencia', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        atividades: atividades,
                        municipio: this.dados.cidade,
                        respostas_questionario: this.respostasQuestionario
                    })
                });
                
                const result = await response.json();
                console.log('✅ Resultado da API:', result);
                
                this.competenciaEstadual = result.competencia === 'estadual';
                
                console.log('📊 Competência definida:', {
                    competenciaEstadual: this.competenciaEstadual,
                    resultado: result.competencia
                });
            } catch (error) {
                console.error('❌ Erro ao verificar competência:', error);
                this.competenciaEstadual = false;
            }
        },

        formatarCnpj() {
            let valor = this.cnpjBusca.replace(/\D/g, '');
            valor = valor.replace(/^(\d{2})(\d)/, '$1.$2');
            valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            valor = valor.replace(/\.(\d{3})(\d)/, '.$1/$2');
            valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
            this.cnpjBusca = valor;
        },

        async buscarCnpj() {
            if (this.cnpjBusca.length < 18) {
                this.mostrarMensagem('Digite um CNPJ válido', 'error');
                return;
            }

            this.loading = true;
            this.mensagem = '';

            try {
                // Debug: vamos ver o que está sendo enviado
                console.log('CNPJ sendo enviado:', this.cnpjBusca);
                console.log('Tamanho do CNPJ:', this.cnpjBusca.length);
                
                 const response = await fetch('{{ url("/api/consultar-cnpj") }}', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'Accept': 'application/json'
                     },
                     body: JSON.stringify({
                         cnpj: this.cnpjBusca
                     })
                 });

                console.log('Status da resposta:', response.status);
                const result = await response.json();
                console.log('Resultado:', result);

                if (response.ok && result.success) {
                    this.preencherDados(result.data);
                    
                    // Armazena a fonte da API para exibir na mensagem
                    const apiSource = result.api_source || 'API';
                    
                    // VALIDAÇÃO DE MUNICÍPIO PARA USUÁRIOS MUNICIPAIS
                    @if(auth('interno')->check() && auth('interno')->user()->isMunicipal())
                        const municipioUsuario = '{{ auth('interno')->user()->municipioRelacionado->nome ?? '' }}';
                        const municipioUsuarioId = {{ auth('interno')->user()->municipio_id ?? 'null' }};
                        
                        if (!municipioUsuarioId) {
                            this.mostrarMensagem('❌ Seu usuário não possui município vinculado. Entre em contato com o administrador.', 'error');
                            this.dadosCarregados = false;
                            this.limparFormulario();
                            return;
                        }
                        
                        // Função para remover acentos
                        function removerAcentos(texto) {
                            return texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        }
                        
                        // Normaliza o município do estabelecimento (remove " - TO" ou "/TO" e acentos)
                        let cidadeEstabelecimento = result.data.cidade || '';
                        cidadeEstabelecimento = cidadeEstabelecimento.replace(/\s*[-\/]\s*TO\s*$/i, '').trim().toUpperCase();
                        cidadeEstabelecimento = removerAcentos(cidadeEstabelecimento);
                        
                        const municipioUsuarioNormalizado = removerAcentos(municipioUsuario.toUpperCase());
                        
                        if (cidadeEstabelecimento !== municipioUsuarioNormalizado) {
                            this.mostrarMensagem(`❌ MUNICÍPIO NÃO PERMITIDO!\n\nVocê só pode cadastrar estabelecimentos do município de ${municipioUsuario}.\nO estabelecimento consultado pertence a ${result.data.cidade.replace(/\s*[-\/]\s*TO\s*$/i, '').trim()}.`, 'error');
                            this.dadosCarregados = false;
                            this.limparFormulario();
                            return;
                        }
                    @endif
                    
                    // Verifica situação cadastral
                    if (result.data.descricao_situacao_cadastral && result.data.descricao_situacao_cadastral !== 'ATIVA') {
                        const situacao = result.data.descricao_situacao_cadastral;
                        const motivo = result.data.descricao_motivo_situacao_cadastral || 'Não informado';
                        
                        if (!confirm(`⚠️ ATENÇÃO!\n\nEste estabelecimento está com situação cadastral: ${situacao}\nMotivo: ${motivo}\n\nDeseja continuar o cadastro mesmo assim?`)) {
                            this.dadosCarregados = false;
                            this.limparFormulario();
                            return;
                        }
                    }
                    
                    // Verifica se já existe cadastro (privado ou público)
                    await this.verificarCnpjExistente(result.data.cnpj, apiSource, result.data.tipo_setor);
                } else {
                    this.mostrarMensagem(result.message || 'CNPJ não encontrado em nenhuma base de dados', 'error');
                }
            } catch (error) {
                this.mostrarMensagem('Erro ao consultar CNPJ. Tente novamente.', 'error');
            } finally {
                this.loading = false;
            }
        },

        async verificarCnpjExistente(cnpj, apiSource = 'API', tipoSetor = 'privado') {
            try {
                const response = await fetch(`/api/verificar-cnpj/${cnpj}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (response.ok && result.existe) {
                    // Se for PRIVADO, bloqueia o cadastro
                    if (tipoSetor === 'privado') {
                        this.dadosCarregados = false;
                        this.mostrarMensagem('⚠️ Este CNPJ já está cadastrado no sistema. Estabelecimentos privados devem ter CNPJ único. Por favor, verifique a listagem de estabelecimentos.', 'error');
                        return;
                    }
                    
                    // Se for PÚBLICO, mostra os estabelecimentos já cadastrados no modal
                    if (tipoSetor === 'publico' && result.estabelecimentos && result.estabelecimentos.length > 0) {
                        this.modalEstabelecimentosExistentes.estabelecimentos = result.estabelecimentos;
                        this.modalEstabelecimentosExistentes.visivel = true;
                        return; // Aguarda decisão do usuário no modal
                    }
                    
                    this.mostrarMensagem(`✅ Dados encontrados com sucesso na ${apiSource}!`, 'success');
                    this.dadosCarregados = true;
                } else {
                    this.mostrarMensagem(`✅ Dados encontrados com sucesso na ${apiSource}!`, 'success');
                    this.dadosCarregados = true;
                }
            } catch (error) {
                console.error('Erro ao verificar CNPJ:', error);
                this.mostrarMensagem(`✅ Dados encontrados com sucesso na ${apiSource}!`, 'success');
                this.dadosCarregados = true;
            }
        },

        preencherDados(apiData) {
            // Filtra CNAEs secundários inválidos (codigo 0 ou vazio)
            if (apiData.cnaes_secundarios && Array.isArray(apiData.cnaes_secundarios)) {
                apiData.cnaes_secundarios = apiData.cnaes_secundarios.filter(cnae => cnae.codigo && parseInt(cnae.codigo) !== 0);
            }

            this.dados = {
                ...this.dados,
                ...apiData
            };
            
            // Processar telefones da API
            if (apiData.ddd_telefone_1) {
                this.dados.telefone = this.formatarTelefone(apiData.ddd_telefone_1);
            }
            if (apiData.ddd_telefone_2) {
                this.dados.telefone2 = this.formatarTelefone(apiData.ddd_telefone_2);
            }
            
            // Se for estabelecimento público, destaca o campo Nome Fantasia
            if (apiData.tipo_setor === 'publico') {
                setTimeout(() => {
                    const nomeFantasiaInput = document.getElementById('nome_fantasia');
                    if (nomeFantasiaInput) {
                        // Foca no campo
                        nomeFantasiaInput.focus();
                        // Seleciona todo o texto para facilitar a edição
                        nomeFantasiaInput.select();
                        // Scroll suave até o campo
                        nomeFantasiaInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }, 500);
            }
            
            // Verifica competência após carregar dados (para usuários municipais)
            @if(auth('interno')->check() && auth('interno')->user()->isMunicipal())
            // Aguarda um pouco para garantir que os dados foram processados
            setTimeout(() => {
                this.verificarCompetencia();
            }, 100);
            @endif
        },

        mostrarMensagem(texto, tipo) {
            this.mensagem = texto;
            this.tipoMensagem = tipo;
            setTimeout(() => {
                this.mensagem = '';
            }, 5000);
        },

        mostrarMensagemHTML(html, tipo) {
            this.mensagem = html;
            this.tipoMensagem = tipo;
            setTimeout(() => {
                this.mensagem = '';
            }, 8000); // Mais tempo para ler a lista de erros
        },

        abrirModalErro(erros) {
            this.modalErro.mensagens = erros;
            this.modalErro.visivel = true;
        },

        fecharModalErro() {
            this.modalErro.visivel = false;
        },

        async proximaAba() {
            // Valida a aba atual antes de avançar
            if (!this.validarAbaAtual()) {
                return; // Não avança se houver erros
            }
            
            // Validação de duplicidade para estabelecimentos públicos
            if (this.abaAtiva === 'dados-gerais' && this.dados.tipo_setor === 'publico') {
                const duplicado = await this.verificarDuplicidadePublico();
                if (duplicado) {
                    return;
                }
            }
            
            const abas = ['dados-gerais', 'endereco', 'atividades', 'contato'];
            const abaAtualIndex = abas.indexOf(this.abaAtiva);
            
            if (abaAtualIndex < abas.length - 1) {
                this.abaAtiva = abas[abaAtualIndex + 1];
                this.mostrarMensagem(`Avançando para: ${this.getNomeAba(abas[abaAtualIndex + 1])}`, 'success');
            }
        },

        async verificarDuplicidadePublico() {
            try {
                this.loading = true;
                const response = await fetch('{{ route('admin.estabelecimentos.verificar-duplicidade-publico') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        cnpj: this.dados.cnpj,
                        nome_fantasia: this.dados.nome_fantasia
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.existe) {
                        this.abrirModalErro([
                            '⚠️ DUPLICIDADE ENCONTRADA:',
                            'Já existe um estabelecimento com este CNPJ e Nome Fantasia cadastrado.',
                            'Para estabelecimentos públicos, o Nome Fantasia deve ser ÚNICO para diferenciar as unidades.',
                            'Por favor, altere o Nome Fantasia para algo específico (ex: Hospital Municipal CENTRO, UBS BAIRRO TAL).'
                        ]);
                        
                        // Destaca e foca no campo
                        setTimeout(() => {
                            const input = document.getElementById('nome_fantasia');
                            if (input) {
                                input.focus();
                                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }, 300);
                        
                        return true; // Existe duplicidade
                    }
                }
                return false;
            } catch (error) {
                console.error('Erro ao verificar duplicidade:', error);
                return false;
            } finally {
                this.loading = false;
            }
        },

        validarAbaAtual() {
            const erros = [];
            
            switch(this.abaAtiva) {
                case 'dados-gerais':
                    if (!this.dados.tipo_setor) {
                        erros.push('Tipo de Setor é obrigatório');
                    }
                    if (!this.dados.nome_fantasia) {
                        erros.push('Nome Fantasia é obrigatório');
                    }
                    if (!this.dados.razao_social) {
                        erros.push('Razão Social é obrigatória');
                    }
                    if (!this.dados.cnpj) {
                        erros.push('CNPJ é obrigatório');
                    }
                    break;
                    
                case 'endereco':
                    if (!this.dados.cep) {
                        erros.push('CEP é obrigatório');
                    }
                    if (!this.dados.endereco) {
                        erros.push('Logradouro é obrigatório');
                    }
                    if (!this.dados.numero) {
                        erros.push('Número é obrigatório');
                    }
                    if (!this.dados.bairro) {
                        erros.push('Bairro é obrigatório');
                    }
                    if (!this.dados.cidade) {
                        erros.push('Município é obrigatório');
                    }
                    if (!this.dados.estado) {
                        erros.push('Estado é obrigatório');
                    }
                    break;
                    
                case 'atividades':
                    // Validação obrigatória: pelo menos uma atividade deve ser marcada
                    if (!this.atividadePrincipalMarcada && this.atividadesExercidas.length === 0) {
                        erros.push('Você deve marcar pelo menos uma atividade que o estabelecimento exerce');
                    }
                    
                    // Validação de questionários: todas as perguntas devem ser respondidas
                    if (this.questionarios.length > 0) {
                        this.questionarios.forEach(quest => {
                            if (!this.respostasQuestionario[quest.cnae]) {
                                erros.push(`Responda o questionário da atividade: ${quest.cnae_formatado} - ${quest.descricao}`);
                            }
                        });
                    }
                    break;
                    
                case 'contato':
                    // Validação opcional: contatos não são obrigatórios
                    break;
            }
            
            if (erros.length > 0) {
                this.abrirModalErro(erros);
                return false;
            }

            return true;
        },

        abaAnterior() {
            const abas = ['dados-gerais', 'endereco', 'atividades', 'contato'];
            const abaAtualIndex = abas.indexOf(this.abaAtiva);
            
            if (abaAtualIndex > 0) {
                this.abaAtiva = abas[abaAtualIndex - 1];
                this.mostrarMensagem(`Voltando para: ${this.getNomeAba(abas[abaAtualIndex - 1])}`, 'success');
            }
        },

        getNomeAba(aba) {
            const nomes = {
                'dados-gerais': 'Dados Gerais',
                'endereco': 'Endereço',
                'atividades': 'Atividades',
                'contato': 'Contato'
            };
            return nomes[aba] || aba;
        },

        getEtapaAtual() {
            const abas = ['dados-gerais', 'endereco', 'atividades', 'contato'];
            return abas.indexOf(this.abaAtiva) + 1;
        },

        formatarTelefone(valor) {
            if (!valor) return '';
            
            // Remove tudo que não é número
            const numero = valor.replace(/\D/g, '');
            
            // Aplica máscara baseada no tamanho
            if (numero.length <= 10) {
                // Telefone fixo: (xx) xxxx-xxxx
                return numero.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            } else {
                // Celular: (xx) xxxxx-xxxx
                return numero.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            }
        },

        getAtividadesJSON() {
            // Monta array de atividades com informações completas
            const atividades = [];
            
            // Adiciona atividade principal se marcada
            if (this.atividadePrincipalMarcada && this.dados.cnae_fiscal) {
                atividades.push({
                    codigo: this.dados.cnae_fiscal,
                    descricao: this.dados.cnae_fiscal_descricao || '',
                    principal: true
                });
            }
            
            // Adiciona atividades secundárias selecionadas
            if (this.dados.cnaes_secundarios && Array.isArray(this.dados.cnaes_secundarios)) {
                this.dados.cnaes_secundarios.forEach(cnae => {
                    // Converte ambos para string para garantir comparação correta
                    const codigoCnae = String(cnae.codigo);
                    const selecionado = this.atividadesExercidas.some(codigo => String(codigo) === codigoCnae);
                    
                    if (selecionado) {
                        atividades.push({
                            codigo: cnae.codigo,
                            descricao: cnae.descricao || '',
                            principal: false
                        });
                    }
                });
            }
            
            console.log('Atividades selecionadas:', this.atividadesExercidas);
            console.log('CNAEs secundários:', this.dados.cnaes_secundarios);
            console.log('Atividades montadas:', atividades);
            
            return JSON.stringify(atividades);
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
                return;
            }
            
            try {
                const response = await fetch('{{ route('admin.estabelecimentos.buscar-questionarios') }}', {
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
            }
        },

        formatarMoeda(valor) {
            if (!valor) return 'R$ 0,00';
            
            const numero = parseFloat(valor);
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(numero);
        },

        // Converte texto para MAIÚSCULAS
        toUpperCase(texto) {
            return texto ? texto.toUpperCase() : '';
        },

        // Funções do Modal de Estabelecimentos Existentes
        fecharModalEstabelecimentos() {
            this.modalEstabelecimentosExistentes.visivel = false;
            this.modalEstabelecimentosExistentes.estabelecimentos = [];
        },

        cancelarCadastro() {
            this.fecharModalEstabelecimentos();
            this.dadosCarregados = false;
            this.limparFormulario();
        },

        continuarCadastro() {
            this.fecharModalEstabelecimentos();
            this.mostrarMensagem('✅ Dados encontrados com sucesso! Você pode continuar o cadastro.', 'success');
            this.dadosCarregados = true;
        },

        // Busca endereço pelo CEP usando ViaCEP
        async buscarCep() {
            const cep = this.dados.cep.replace(/\D/g, '');
            
            if (cep.length !== 8) {
                return;
            }
            
            try {
                this.loading = true;
                
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();
                
                if (data.erro) {
                    this.mostrarMensagem('❌ CEP não encontrado', 'error');
                    return;
                }
                
                // Preenche os campos com os dados do CEP
                this.dados.endereco = this.toUpperCase(data.logradouro || '');
                this.dados.bairro = this.toUpperCase(data.bairro || '');
                this.dados.cidade = this.toUpperCase(data.localidade || '');
                this.dados.estado = this.toUpperCase(data.uf || '');
                this.dados.codigo_municipio_ibge = data.ibge || '';
                
                // Foca no campo número
                setTimeout(() => {
                    const numeroInput = document.getElementById('numero');
                    if (numeroInput) {
                        numeroInput.focus();
                    }
                }, 100);
                
                this.mostrarMensagem('✅ Endereço encontrado!', 'success');
                
            } catch (error) {
                console.error('Erro ao buscar CEP:', error);
                this.mostrarMensagem('❌ Erro ao buscar CEP. Tente novamente.', 'error');
            } finally {
                this.loading = false;
            }
        },

        limparFormulario() {
            // Limpa o CNPJ de busca
            this.cnpjBusca = '';
            
            // Reseta os dados
            this.dados = {
                tipo_setor: '',
                cnpj: '',
                razao_social: '',
                nome_fantasia: '',
                natureza_juridica: '',
                porte: '',
                situacao_cadastral: '',
                descricao_situacao_cadastral: '',
                endereco: '',
                numero: '',
                complemento: '',
                bairro: '',
                cidade: '',
                estado: '',
                cep: '',
                telefone: '',
                telefone2: '',
                email: '',
                inscricao_estadual: '',
                atividade_principal: '',
                data_situacao_cadastral: '',
                data_inicio_atividade: '',
                cnae_fiscal: '',
                cnae_fiscal_descricao: '',
                cnaes_secundarios: [],
                qsa: [],
                capital_social: 0,
                logradouro: '',
                codigo_municipio_ibge: '',
                ddd_telefone_1: '',
                ddd_telefone_2: '',
                ddd_fax: '',
                opcao_pelo_mei: null,
                opcao_pelo_simples: null,
                regime_tributario: [],
                situacao_especial: '',
                motivo_situacao_cadastral: '',
                descricao_motivo_situacao_cadastral: '',
                identificador_matriz_filial: '',
                qualificacao_do_responsavel: ''
            };
            
            // Limpa atividades
            this.atividadesExercidas = [];
            this.atividadePrincipalMarcada = false;
            
            // Reseta flags
            this.dadosCarregados = false;
            this.loading = false;
            
            // Volta para a primeira aba
            this.abaAtiva = 'dados-gerais';
        },

        handleSubmit(event) {
            // Previne o submit padrão
            event.preventDefault();

            // Valida a aba atual (contato) antes de enviar
            if (!this.validarAbaAtual()) {
                return;
            }

            // Intercepta o submit para tratar possíveis erros 419
            const form = event.target;
            
            // Envia o formulário via fetch para capturar erro 419
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.status === 419) {
                    // Token CSRF expirado - recarrega a página
                    alert('Sua sessão expirou. A página será recarregada.');
                    window.location.reload();
                    return;
                }
                
                if (response.ok) {
                    // Redireciona para a página de sucesso
                    return response.text().then(html => {
                        // Se for um redirect, segue o redirect
                        if (response.redirected) {
                            window.location.href = response.url;
                        } else {
                            // Se retornou HTML, substitui a página
                            document.open();
                            document.write(html);
                            document.close();
                        }
                    });
                }
                
                // Outros erros
                return response.json().then(data => {
                    this.mostrarMensagem(data.message || 'Erro ao cadastrar estabelecimento', 'error');
                });
            })
            .catch(error => {
                console.error('Erro:', error);
                this.mostrarMensagem('Erro ao processar requisição', 'error');
            });
        }
    }
}
</script>
@endsection
