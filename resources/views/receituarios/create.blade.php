@extends('layouts.admin')

@section('title', 'Novo Receituário')
@section('page-title', 'Novo Receituário')

@section('content')
<div class="max-w-8xl mx-auto">
    <form method="POST" action="{{ route('admin.receituarios.store') }}" x-data="receituarioForm()">
        @csrf
        <input type="hidden" name="tipo" value="{{ $tipo }}">
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">
                @if($tipo == 'medico')
                    Cadastro de Médico, Cirurgião Dentista e Médico Veterinário
                @elseif($tipo == 'instituicao')
                    Cadastro de Instituição (Hospital, Clínica e Similares)
                @elseif($tipo == 'secretaria')
                    Cadastro de Secretaria de Saúde e Vigilância Sanitária
                @elseif($tipo == 'talidomida')
                    Cadastro de Prescritor de Talidomida
                @endif
            </h2>

            @if(in_array($tipo, ['medico', 'talidomida']))
                {{-- DADOS PESSOAIS --}}
                <div class="mb-6 bg-blue-50 rounded-lg p-6 border-l-4 border-blue-500">
                    <h3 class="text-lg font-bold text-blue-900 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        DADOS PESSOAIS
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-lg">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo <span class="text-red-500">*</span></label>
                            <input type="text" name="nome" value="{{ old('nome') }}" required
                                   style="text-transform: uppercase;"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('nome')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CPF <span class="text-red-500">*</span></label>
                            <input type="text" name="cpf" value="{{ old('cpf') }}" required 
                                   x-mask="999.999.999-99"
                                   placeholder="000.000.000-00"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('cpf')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Especialidade</label>
                            <select name="especialidade" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione...</option>
                                <option value="CLÍNICO GERAL" {{ old('especialidade') == 'CLÍNICO GERAL' ? 'selected' : '' }}>Clínico Geral</option>
                                <option value="CARDIOLOGIA" {{ old('especialidade') == 'CARDIOLOGIA' ? 'selected' : '' }}>Cardiologia</option>
                                <option value="DERMATOLOGIA" {{ old('especialidade') == 'DERMATOLOGIA' ? 'selected' : '' }}>Dermatologia</option>
                                <option value="ENDOCRINOLOGIA" {{ old('especialidade') == 'ENDOCRINOLOGIA' ? 'selected' : '' }}>Endocrinologia</option>
                                <option value="GASTROENTEROLOGIA" {{ old('especialidade') == 'GASTROENTEROLOGIA' ? 'selected' : '' }}>Gastroenterologia</option>
                                <option value="GINECOLOGIA" {{ old('especialidade') == 'GINECOLOGIA' ? 'selected' : '' }}>Ginecologia</option>
                                <option value="NEUROLOGIA" {{ old('especialidade') == 'NEUROLOGIA' ? 'selected' : '' }}>Neurologia</option>
                                <option value="OFTALMOLOGIA" {{ old('especialidade') == 'OFTALMOLOGIA' ? 'selected' : '' }}>Oftalmologia</option>
                                <option value="ORTOPEDIA" {{ old('especialidade') == 'ORTOPEDIA' ? 'selected' : '' }}>Ortopedia</option>
                                <option value="OTORRINOLARINGOLOGIA" {{ old('especialidade') == 'OTORRINOLARINGOLOGIA' ? 'selected' : '' }}>Otorrinolaringologia</option>
                                <option value="PEDIATRIA" {{ old('especialidade') == 'PEDIATRIA' ? 'selected' : '' }}>Pediatria</option>
                                <option value="PSIQUIATRIA" {{ old('especialidade') == 'PSIQUIATRIA' ? 'selected' : '' }}>Psiquiatria</option>
                                <option value="CIRURGIÃO DENTISTA" {{ old('especialidade') == 'CIRURGIÃO DENTISTA' ? 'selected' : '' }}>Cirurgião Dentista</option>
                                <option value="MÉDICO VETERINÁRIO" {{ old('especialidade') == 'MÉDICO VETERINÁRIO' ? 'selected' : '' }}>Médico Veterinário</option>
                                <option value="OUTRAS" {{ old('especialidade') == 'OUTRAS' ? 'selected' : '' }}>Outras</option>
                            </select>
                            @error('especialidade')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $tipo == 'talidomida' ? 'Nº CRM' : 'Nº Conselho de Classe' }}
                            </label>
                            <input type="text" name="{{ $tipo == 'talidomida' ? 'numero_crm' : 'numero_conselho_classe' }}" 
                                   value="{{ old($tipo == 'talidomida' ? 'numero_crm' : 'numero_conselho_classe') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telefone <span class="text-red-500">*</span></label>
                            <input type="text" name="telefone" value="{{ old('telefone') }}" required x-mask="(99) 99999-9999"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('telefone')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telefone 2</label>
                            <input type="text" name="telefone2" value="{{ old('telefone2') }}" x-mask="(99) 99999-9999"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                {{-- DADOS DE ENDEREÇO --}}
                <div class="mb-6 bg-green-50 rounded-lg p-6 border-l-4 border-green-500">
                    <h3 class="text-lg font-bold text-green-900 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        DADOS DE ENDEREÇO
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-lg">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                            <div class="flex gap-2">
                                <input type="text" name="cep" value="{{ old('cep') }}" 
                                       x-mask="99999-999"
                                       placeholder="00000-000"
                                       @blur="buscarCep($event.target.value, '{{ $tipo }}')"
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <button type="button" @click="buscarCep(document.querySelector('[name=cep]').value, '{{ $tipo }}')" 
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    Buscar
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Município</label>
                            <select name="municipio_id" id="municipio_select_{{ $tipo }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione...</option>
                                @foreach($municipios as $municipio)
                                    <option value="{{ $municipio->id }}" data-nome="{{ strtoupper($municipio->nome) }}" {{ old('municipio_id') == $municipio->id ? 'selected' : '' }}>
                                        {{ $municipio->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $tipo == 'talidomida' ? 'Endereço Residencial' : 'Endereço' }}
                            </label>
                            <input type="text" name="{{ $tipo == 'talidomida' ? 'endereco_residencial' : 'endereco' }}" 
                                   id="endereco_{{ $tipo }}"
                                   value="{{ old($tipo == 'talidomida' ? 'endereco_residencial' : 'endereco') }}"
                                   style="text-transform: uppercase;"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        @if($tipo == 'talidomida')
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        @endif
                    </div>
                </div>

                {{-- LOCAIS DE TRABALHO --}}
                <div class="mb-6 bg-purple-50 rounded-lg p-6 border-l-4 border-purple-500">
                    <h3 class="text-lg font-bold text-purple-900 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        LOCAIS DE TRABALHO
                    </h3>
                    
                    <template x-for="(local, index) in locais" :key="index">
                        <div class="mb-4 p-4 bg-white rounded-lg border-2 border-purple-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                                    <div class="flex gap-2">
                                        <input type="text" :name="'locais_trabalho['+index+'][cep]'" x-model="local.cep" 
                                               x-mask="99999-999"
                                               placeholder="00000-000"
                                               @blur="buscarCepLocal($event.target.value, index)"
                                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <button type="button" @click="buscarCepLocal(local.cep, index)" 
                                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                            Buscar
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Município</label>
                                    <input type="text" :name="'locais_trabalho['+index+'][municipio]'" x-model="local.municipio"
                                           style="text-transform: uppercase;"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Local</label>
                                    <input type="text" :name="'locais_trabalho['+index+'][nome]'" x-model="local.nome"
                                           style="text-transform: uppercase;"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="md:col-span-2 flex justify-end">
                                    <button type="button" @click="removeLocal(index)" class="text-red-600 hover:text-red-800 text-sm">
                                        Remover Local
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="addLocal()" class="mt-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        + Adicionar Local de Trabalho
                    </button>
                </div>
            @endif

            @if(in_array($tipo, ['instituicao', 'secretaria']))
                {{-- CNPJ - PRIMEIRO CAMPO --}}
                <div class="mb-6 bg-yellow-50 rounded-lg p-6 border-l-4 border-yellow-500">
                    <h3 class="text-lg font-bold text-yellow-900 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        BUSCAR DADOS POR CNPJ
                    </h3>
                    
                    <div class="bg-white p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-1">CNPJ <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <input type="text" name="cnpj" value="{{ old('cnpj') }}" required 
                                   x-mask="99.999.999/9999-99" 
                                   x-model="cnpj"
                                   placeholder="00.000.000/0000-00"
                                   @blur="buscarCnpj()"
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button type="button" @click="buscarCnpj()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                                🔍 Buscar Dados
                            </button>
                        </div>
                        @error('cnpj')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        <p class="text-xs text-gray-500 mt-2">💡 Digite o CNPJ e clique em "Buscar Dados" para preencher automaticamente</p>
                    </div>
                </div>

                {{-- DADOS DA INSTITUIÇÃO --}}
                <div class="mb-6 bg-blue-50 rounded-lg p-6 border-l-4 border-blue-500">
                    <h3 class="text-lg font-bold text-blue-900 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        DADOS DA INSTITUIÇÃO
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-4 bg-white p-4 rounded-lg">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Razão Social <span class="text-red-500">*</span></label>
                            <input type="text" name="razao_social" id="razao_social" value="{{ old('razao_social') }}" required
                                   style="text-transform: uppercase;"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('razao_social')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                {{-- DADOS DE ENDEREÇO E CONTATO --}}
                <div class="mb-6 bg-green-50 rounded-lg p-6 border-l-4 border-green-500">
                    <h3 class="text-lg font-bold text-green-900 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        DADOS DE ENDEREÇO E CONTATO
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-lg">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                            <div class="flex gap-2">
                                <input type="text" name="cep" value="{{ old('cep') }}" 
                                       x-mask="99999-999"
                                       placeholder="00000-000"
                                       @blur="buscarCep($event.target.value, '{{ $tipo }}')"
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <button type="button" @click="buscarCep(document.querySelector('[name=cep]').value, '{{ $tipo }}')" 
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    Buscar
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Município</label>
                            <select name="municipio_id" id="municipio_select_{{ $tipo }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione...</option>
                                @foreach($municipios as $municipio)
                                    <option value="{{ $municipio->id }}" data-nome="{{ strtoupper($municipio->nome) }}" {{ old('municipio_id') == $municipio->id ? 'selected' : '' }}>
                                        {{ $municipio->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                            <input type="text" name="endereco" id="endereco_{{ $tipo }}" value="{{ old('endereco') }}"
                                   style="text-transform: uppercase;"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                            <input type="text" name="telefone" value="{{ old('telefone') }}" x-mask="(99) 99999-9999"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                @if($tipo == 'instituicao')
                    {{-- RESPONSÁVEL TÉCNICO --}}
                    <div class="mb-6 bg-orange-50 rounded-lg p-6 border-l-4 border-orange-500">
                        <h3 class="text-lg font-bold text-orange-900 mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            DADOS DO RESPONSÁVEL TÉCNICO
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-lg">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                                <input type="text" name="responsavel_nome" value="{{ old('responsavel_nome') }}"
                                       style="text-transform: uppercase;"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                                <input type="text" name="responsavel_cpf" value="{{ old('responsavel_cpf') }}" 
                                       x-mask="999.999.999-99"
                                       placeholder="000.000.000-00"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nº CRM-TO</label>
                                <input type="text" name="responsavel_crm" value="{{ old('responsavel_crm') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Especialidade</label>
                                <input type="text" name="responsavel_especialidade" value="{{ old('responsavel_especialidade') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                                <input type="text" name="responsavel_telefone" value="{{ old('responsavel_telefone') }}" x-mask="(99) 99999-9999"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            {{-- OBSERVAÇÕES --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea name="observacoes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('observacoes') }}</textarea>
            </div>
        </div>

        {{-- BOTÕES --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.receituarios.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Cadastrar Receituário
            </button>
        </div>
    </form>
</div>

<script>
function receituarioForm() {
    return {
        cnpj: '',
        locais: [{ nome: '', municipio: '', cep: '' }],
        
        addLocal() {
            this.locais.push({ nome: '', municipio: '', cep: '' });
        },
        
        removeLocal(index) {
            if (this.locais.length > 1) {
                this.locais.splice(index, 1);
            }
        },
        
        async buscarCep(cep, tipo) {
            if (!cep) return;
            
            const cepLimpo = cep.replace(/\D/g, '');
            if (cepLimpo.length !== 8) return;
            
            try {
                const response = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`);
                const data = await response.json();
                
                if (data.erro) {
                    alert('CEP não encontrado');
                    return;
                }
                
                // Preenche o endereço
                const enderecoField = document.getElementById(`endereco_${tipo}`);
                if (enderecoField) {
                    const endereco = `${data.logradouro || ''}, ${data.bairro || ''} - ${data.localidade || ''}`.trim();
                    enderecoField.value = endereco.toUpperCase();
                }
                
                // Seleciona o município
                const municipioSelect = document.getElementById(`municipio_select_${tipo}`);
                if (municipioSelect && data.localidade) {
                    const cidadeNormalizada = data.localidade.toUpperCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                    
                    for (let option of municipioSelect.options) {
                        const optionNome = option.getAttribute('data-nome');
                        if (optionNome) {
                            const optionNormalizada = optionNome.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                            if (optionNormalizada === cidadeNormalizada) {
                                municipioSelect.value = option.value;
                                break;
                            }
                        }
                    }
                }
                
            } catch (error) {
                console.error('Erro ao buscar CEP:', error);
            }
        },
        
        async buscarCepLocal(cep, index) {
            if (!cep) return;
            
            const cepLimpo = cep.replace(/\D/g, '');
            if (cepLimpo.length !== 8) return;
            
            try {
                const response = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`);
                const data = await response.json();
                
                if (data.erro) {
                    alert('CEP não encontrado');
                    return;
                }
                
                // Atualiza o município do local
                if (data.localidade) {
                    this.locais[index].municipio = data.localidade.toUpperCase();
                }
                
            } catch (error) {
                console.error('Erro ao buscar CEP:', error);
            }
        },
        
        async buscarCnpj() {
            if (!this.cnpj) {
                alert('Digite um CNPJ');
                return;
            }
            
            try {
                const response = await fetch(`{{ route('admin.receituarios.buscar-cnpj') }}?cnpj=${this.cnpj}`);
                const data = await response.json();
                
                if (data.success) {
                    // Preenche razão social
                    const razaoSocial = document.getElementById('razao_social');
                    if (razaoSocial) razaoSocial.value = (data.data.razao_social || '').toUpperCase();
                    
                    // Preenche endereço
                    const endereco = document.querySelector('[name="endereco"]');
                    if (endereco) {
                        const end = `${data.data.logradouro || ''}, ${data.data.numero || ''} ${data.data.complemento || ''} - ${data.data.bairro || ''}`.trim();
                        endereco.value = end.toUpperCase();
                    }
                    
                    // Preenche CEP
                    const cep = document.querySelector('[name="cep"]');
                    if (cep) cep.value = data.data.cep || '';
                    
                    // Preenche telefone
                    const telefone = document.querySelector('[name="telefone"]');
                    if (telefone) telefone.value = data.data.telefone || '';
                    
                    // Preenche email
                    const email = document.querySelector('[name="email"]');
                    if (email) email.value = data.data.email || '';
                    
                    // Busca município pelo CEP se disponível
                    if (data.data.cep) {
                        const cepLimpo = data.data.cep.replace(/\D/g, '');
                        const responseCep = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`);
                        const dataCep = await responseCep.json();
                        
                        if (!dataCep.erro && dataCep.localidade) {
                            const municipioSelect = document.querySelector('[name="municipio_id"]');
                            if (municipioSelect) {
                                const cidadeNormalizada = dataCep.localidade.toUpperCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                                
                                for (let option of municipioSelect.options) {
                                    const optionNome = option.getAttribute('data-nome');
                                    if (optionNome) {
                                        const optionNormalizada = optionNome.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                                        if (optionNormalizada === cidadeNormalizada) {
                                            municipioSelect.value = option.value;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    alert('✅ Dados preenchidos com sucesso!');
                } else {
                    alert('❌ CNPJ não encontrado');
                }
            } catch (error) {
                alert('❌ Erro ao buscar CNPJ');
                console.error(error);
            }
        }
    }
}
</script>
@endsection
