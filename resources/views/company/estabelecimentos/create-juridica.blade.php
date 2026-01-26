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

    {{-- Erros de Validação --}}
    @if ($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-red-900">Erro ao cadastrar</h3>
                <ul class="mt-2 text-sm text-red-800 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
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
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 uppercase">
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
                    <div class="mb-6">
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
                                        <span class="font-mono text-sm text-gray-900" x-text="cnae.codigo"></span>
                                        <span class="text-sm text-gray-600" x-text="' - ' + (cnae.descricao || cnae.texto || '')"></span>
                                    </div>
                                </label>
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

                    {{-- Indicador de Competência --}}
                    <div x-show="atividadesExercidas.length > 0 || atividadePrincipalMarcada" class="mt-4">
                        {{-- Alerta Estadual --}}
                        <div x-show="competenciaEstadual" class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-lg">
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
                        <div x-show="!competenciaEstadual" class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
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
                    <input type="hidden" name="competencia_estadual" :value="competenciaEstadual ? '1' : '0'">

                    {{-- Botões de Navegação --}}
                    <div class="flex justify-between pt-4 border-t border-gray-200">
                        <button type="button" @click="abaAtiva = 'endereco'" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">
                            ← Voltar
                        </button>
                        <button type="button" @click="proximaAba('atividades')" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                            Próximo: Contato →
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
    </form>
</div>


@push('scripts')
<script>
function estabelecimentoFormCompany() {
    return {
        cnpjBusca: '',
        loading: false,
        submitting: false,
        mensagem: '',
        tipoMensagem: '',
        dadosCarregados: false,
        abaAtiva: 'dados-gerais',
        atividadesSelecionadas: [],
        atividadesExercidas: [],
        atividadePrincipalMarcada: false,
        questionarios: [],
        respostasQuestionario: {},
        competenciaEstadual: false,
        modalErro: {
            visivel: false,
            mensagens: []
        },
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
            tipo_setor: 'privado'
        },

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
                respostas: JSON.parse(JSON.stringify(this.respostasQuestionario))
            });
            
            if (atividades.length === 0) {
                this.competenciaEstadual = false;
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
                const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`);
                if (!response.ok) throw new Error('CNPJ não encontrado');
                
                const data = await response.json();
                
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

                // Tipo de setor
                const natureza = (data.natureza_juridica || '').toLowerCase();
                this.dados.tipo_setor = (natureza.includes('público') || natureza.includes('administração pública')) ? 'publico' : 'privado';

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

        getAtividadesExercidas() {
            let atividades = [];
            
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
                return;
            }
            
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
            }
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
            if (!this.dados.telefone) {
                erros.push('Telefone é obrigatório');
            }
            if (!this.dados.email) {
                erros.push('E-mail é obrigatório');
            }
            
            // Validar questionários
            if (this.questionarios.length > 0) {
                const questionariosNaoRespondidos = this.questionarios.filter(q => !this.respostasQuestionario[q.cnae]);
                if (questionariosNaoRespondidos.length > 0) {
                    erros.push('Responda todos os questionários obrigatórios na aba Atividades');
                }
            }
            
            // Validar se pelo menos uma atividade foi selecionada
            if (!this.atividadePrincipalMarcada && this.atividadesExercidas.length === 0) {
                erros.push('Selecione pelo menos uma atividade que será exercida');
            }
            
            if (erros.length > 0) {
                event.preventDefault();
                this.modalErro.mensagens = erros;
                this.modalErro.visivel = true;
                return;
            }
            
            this.submitting = true;
        }
    }
}
</script>
@endpush
@endsection
