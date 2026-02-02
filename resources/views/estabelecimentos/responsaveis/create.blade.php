@extends('layouts.admin')

@section('title', 'Adicionar Responsável')
@section('page-title', 'Adicionar Responsável')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.estabelecimentos.responsaveis.index', $estabelecimento->id) }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Adicionar Responsável {{ $tipo === 'legal' ? 'Legal' : 'Técnico' }}
                </h1>
                <p class="text-sm text-gray-600 mt-1">{{ $estabelecimento->nome_fantasia }}</p>
            </div>
        </div>
    </div>

    {{-- Mensagens de Sucesso/Erro --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-red-800 mb-2">Há erros no formulário:</p>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Formulário --}}
    <form method="POST" 
          action="{{ route('admin.estabelecimentos.responsaveis.store', $estabelecimento->id) }}"
          enctype="multipart/form-data"
          x-data="responsavelForm('{{ $tipo }}')"
          class="space-y-6">
        @csrf
        <input type="hidden" name="tipo" value="{{ $tipo }}">

        {{-- Card de Busca por CPF --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">1. Buscar por CPF</h2>
            <p class="text-sm text-gray-600 mb-4">Primeiro, verifique se o responsável já está cadastrado no sistema.</p>
            
            <div class="flex gap-3">
                <div class="flex-1">
                    <input type="text" 
                           x-model="cpfBusca"
                           @input="cpfBusca = formatarCpf($event.target.value)"
                           placeholder="000.000.000-00"
                           maxlength="14"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button type="button"
                        @click="buscarCpf"
                        :disabled="cpfBusca.length < 14 || buscando"
                        class="px-6 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <span x-show="!buscando">Buscar</span>
                    <span x-show="buscando">Buscando...</span>
                </button>
            </div>

            {{-- Mensagem de resultado --}}
            <div x-show="mensagemBusca" x-cloak class="mt-4">
                <div :class="responsavelEncontrado ? 'bg-green-50 border-green-500' : 'bg-blue-50 border-blue-500'" 
                     class="border-l-4 p-4 rounded-lg">
                    <p class="text-sm font-medium" :class="responsavelEncontrado ? 'text-green-800' : 'text-blue-800'" x-text="mensagemBusca"></p>
                </div>
            </div>
        </div>

        {{-- Card de Dados do Responsável --}}
        <div x-show="mostrarFormulario" x-cloak class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">2. Dados do Responsável</h2>

            <div class="grid grid-cols-2 gap-4">
                {{-- CPF --}}
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        CPF <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="cpf"
                           x-model="dados.cpf"
                           @input="dados.cpf = formatarCpf($event.target.value)"
                           maxlength="14"
                           :readonly="cpfJaCadastrado"
                           required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           :class="cpfJaCadastrado ? 'bg-gray-100' : ''">
                    @error('cpf')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Telefone --}}
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Telefone <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="telefone"
                           x-model="dados.telefone"
                           @input="dados.telefone = formatarTelefone($event.target.value)"
                           maxlength="15"
                           :readonly="cpfJaCadastrado"
                           required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           :class="cpfJaCadastrado ? 'bg-gray-100' : ''">
                    @error('telefone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nome --}}
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Nome Completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="nome"
                           x-model="dados.nome"
                           :readonly="cpfJaCadastrado"
                           required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           :class="cpfJaCadastrado ? 'bg-gray-100' : ''">
                    @error('nome')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           name="email"
                           x-model="dados.email"
                           :readonly="cpfJaCadastrado"
                           required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           :class="cpfJaCadastrado ? 'bg-gray-100' : ''">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Campos específicos para Responsável Legal --}}
                <template x-if="tipo === 'legal' && !responsavelEncontrado">
                    <div class="md:col-span-2 space-y-4 border-t pt-4 mt-4">
                        <h3 class="text-md font-semibold text-gray-900">Documento de Identificação</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Tipo de Documento --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Tipo de Documento <span class="text-red-500">*</span>
                                </label>
                                <select name="tipo_documento" 
                                        x-model="dados.tipo_documento"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Selecione...</option>
                                    <option value="rg">RG</option>
                                    <option value="cnh">CNH</option>
                                </select>
                                @error('tipo_documento')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Upload do Documento --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Documento (PDF) <span class="text-red-500">*</span>
                                </label>
                                <input type="file" 
                                       name="documento_identificacao"
                                       accept=".pdf"
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Apenas PDF, máx. 5MB</p>
                                @error('documento_identificacao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Exibir documentos já cadastrados para Responsável Legal --}}
                <template x-if="tipo === 'legal' && responsavelEncontrado">
                    <div class="md:col-span-2 space-y-4 border-t pt-4 mt-4">
                        <h3 class="text-md font-semibold text-gray-900">Documento de Identificação</h3>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-blue-900">Documento já cadastrado</p>
                                    <p class="text-sm text-blue-700 mt-1">
                                        <span class="font-semibold">Tipo:</span> 
                                        <span x-text="dados.tipo_documento ? dados.tipo_documento.toUpperCase() : 'N/A'"></span>
                                    </p>
                                    <a x-show="dados.documento_identificacao" 
                                       :href="'/storage/' + dados.documento_identificacao" 
                                       target="_blank"
                                       class="inline-flex items-center gap-2 mt-2 text-sm font-medium text-blue-600 hover:text-blue-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Visualizar documento
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Campos específicos para Responsável Técnico --}}
                <template x-if="tipo === 'tecnico' && !responsavelEncontrado">
                    <div class="col-span-2 space-y-4 border-t pt-4 mt-4">
                        <h3 class="text-md font-semibold text-gray-900">Informações do Conselho</h3>
                        
                        <div class="grid grid-cols-1 gap-4">
                            {{-- Conselho --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Conselho <span class="text-red-500">*</span>
                                </label>
                                <select name="conselho"
                                        x-model="dados.conselho"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Selecione o conselho...</option>
                                    <option value="CRA">CRA - Conselho Regional de Administração</option>
                                    <option value="CAU">CAU - Conselho de Arquitetura e Urbanismo</option>
                                    <option value="CRB">CRB - Conselho Regional de Biblioteconomia</option>
                                    <option value="CRBIO">CRBIO - Conselho Regional de Biologia</option>
                                    <option value="CRC">CRC - Conselho Regional de Contabilidade</option>
                                    <option value="CREA">CREA - Conselho Regional de Engenharia e Agronomia</option>
                                    <option value="CREF">CREF - Conselho Regional de Educação Física</option>
                                    <option value="CRF">CRF - Conselho Regional de Farmácia</option>
                                    <option value="CFN">CFN - Conselho Federal de Nutricionistas</option>
                                    <option value="CRM">CRM - Conselho Regional de Medicina</option>
                                    <option value="CRMV">CRMV - Conselho Regional de Medicina Veterinária</option>
                                    <option value="CRN">CRN - Conselho Regional de Nutrição</option>
                                    <option value="CRO">CRO - Conselho Regional de Odontologia</option>
                                    <option value="CRP">CRP - Conselho Regional de Psicologia</option>
                                    <option value="CRPRE">CRPRE - Conselho Regional de Profissionais de Relações Públicas</option>
                                    <option value="CRQ">CRQ - Conselho Regional de Química</option>
                                    <option value="COREN">COREN - Conselho Regional de Enfermagem</option>
                                    <option value="COFFITO">COFFITO - Conselho Federal de Fisioterapia e Terapia Ocupacional</option>
                                    <option value="CONTER">CONTER - Conselho Nacional de Técnicos em Radiologia</option>
                                    <option value="OUTRO">Outro</option>
                                </select>
                                @error('conselho')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Número de Registro --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Nº Registro <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="numero_registro_conselho"
                                       x-model="dados.numero_registro_conselho"
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('numero_registro_conselho')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Upload da Carteirinha --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Carteirinha do Conselho <span class="text-red-500">*</span>
                                </label>
                                <input type="file" 
                                       name="carteirinha_conselho"
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-xs text-gray-500">PDF, JPG ou PNG, máx. 5MB</p>
                                @error('carteirinha_conselho')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Exibir informações já cadastradas para Responsável Técnico --}}
                <template x-if="tipo === 'tecnico' && responsavelEncontrado">
                    <div class="col-span-2 space-y-4 border-t pt-4 mt-4">
                        <h3 class="text-md font-semibold text-gray-900">Informações do Conselho</h3>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-blue-900">Informações já cadastradas</p>
                                    <div class="mt-2 space-y-1">
                                        <p class="text-sm text-blue-700">
                                            <span class="font-semibold">Conselho:</span> 
                                            <span x-text="dados.conselho || 'N/A'"></span>
                                        </p>
                                        <p class="text-sm text-blue-700">
                                            <span class="font-semibold">Nº Registro:</span> 
                                            <span x-text="dados.numero_registro_conselho || 'N/A'"></span>
                                        </p>
                                    </div>
                                    <a x-show="dados.carteirinha_conselho" 
                                       :href="'/storage/' + dados.carteirinha_conselho" 
                                       target="_blank"
                                       class="inline-flex items-center gap-2 mt-3 text-sm font-medium text-blue-600 hover:text-blue-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Visualizar carteirinha
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Botões de ação --}}
        <div x-show="mostrarFormulario" x-cloak class="flex items-center justify-between gap-4 pt-4">
            <a href="{{ route('admin.estabelecimentos.responsaveis.index', $estabelecimento->id) }}"
               class="px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-text="responsavelEncontrado ? 'Vincular Responsável' : 'Cadastrar e Vincular'"></span>
            </button>
        </div>
    </form>
</div>

<script>
function responsavelForm(tipo) {
    return {
        tipo: tipo,
        cpfBusca: '',
        buscando: false,
        mensagemBusca: '',
        responsavelEncontrado: false,
        cpfJaCadastrado: false, // CPF encontrado (qualquer tipo)
        fonteEncontrada: '', // 'responsavel' ou 'usuario_externo'
        mostrarFormulario: false,
        dados: {
            cpf: '',
            nome: '',
            email: '',
            telefone: '',
            tipo_documento: '',
            documento_identificacao: '',
            conselho: '',
            numero_registro_conselho: '',
            carteirinha_conselho: ''
        },

        formatarCpf(valor) {
            // Remove tudo que não é dígito
            valor = valor.replace(/\D/g, '');
            
            // Limita a 11 dígitos
            valor = valor.substring(0, 11);
            
            // Aplica a máscara
            if (valor.length <= 3) {
                return valor;
            } else if (valor.length <= 6) {
                return valor.replace(/(\d{3})(\d{0,3})/, '$1.$2');
            } else if (valor.length <= 9) {
                return valor.replace(/(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
            } else {
                return valor.replace(/(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4');
            }
        },

        formatarTelefone(valor) {
            // Remove tudo que não é dígito
            valor = valor.replace(/\D/g, '');
            
            // Limita a 11 dígitos
            valor = valor.substring(0, 11);
            
            // Aplica a máscara
            if (valor.length <= 2) {
                return valor;
            } else if (valor.length <= 6) {
                return valor.replace(/(\d{2})(\d{0,4})/, '($1) $2');
            } else if (valor.length <= 10) {
                return valor.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else {
                return valor.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
            }
        },

        async buscarCpf() {
            if (this.cpfBusca.length < 14) return;

            this.buscando = true;
            this.mensagemBusca = '';

            try {
                const response = await fetch('{{ route("admin.responsaveis.buscar-cpf") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        cpf: this.cpfBusca,
                        tipo: this.tipo
                    })
                });

                const data = await response.json();

                if (data.encontrado) {
                    // Guarda a fonte dos dados
                    this.fonteEncontrada = data.fonte || 'responsavel';
                    
                    // Marca que CPF foi encontrado (para readonly dos dados básicos)
                    this.cpfJaCadastrado = true;
                    
                    // Sempre preenche dados básicos
                    this.dados.cpf = this.cpfBusca;
                    this.dados.nome = data.responsavel.nome;
                    this.dados.email = data.responsavel.email;
                    this.dados.telefone = data.responsavel.telefone ? this.formatarTelefone(data.responsavel.telefone) : '';
                    
                    // Se veio de usuario_externo, nunca marca como encontrado completo
                    // pois sempre precisa preencher documentos específicos
                    if (this.fonteEncontrada === 'usuario_externo') {
                        this.responsavelEncontrado = false;
                        this.mensagemBusca = '✓ Usuário encontrado no sistema! Dados básicos preenchidos automaticamente. Complete as informações específicas abaixo.';
                    }
                    // Se for do mesmo tipo em responsaveis, marca como encontrado (readonly também nos específicos)
                    else if (data.mesmo_tipo) {
                        this.responsavelEncontrado = true;
                        
                        // Preencher dados específicos se for legal
                        if (this.tipo === 'legal') {
                            this.dados.tipo_documento = data.responsavel.tipo_documento || '';
                            this.dados.documento_identificacao = data.responsavel.documento_identificacao || '';
                        }
                        
                        // Preencher dados específicos se for técnico
                        if (this.tipo === 'tecnico') {
                            this.dados.conselho = data.responsavel.conselho || '';
                            this.dados.numero_registro_conselho = data.responsavel.numero_registro_conselho || '';
                            this.dados.carteirinha_conselho = data.responsavel.carteirinha_conselho || '';
                        }
                        
                        this.mensagemBusca = '✓ Responsável encontrado! Os dados serão preenchidos automaticamente.';
                    } else {
                        // Tipo diferente: preenche básicos mas permite editar campos específicos
                        this.responsavelEncontrado = false;
                        this.mensagemBusca = '✓ CPF encontrado! Dados básicos preenchidos. Complete as informações específicas abaixo.';
                    }
                } else {
                    this.responsavelEncontrado = false;
                    this.cpfJaCadastrado = false;
                    this.fonteEncontrada = '';
                    this.dados.cpf = this.cpfBusca;
                    this.mensagemBusca = 'CPF não encontrado. Preencha todos os dados abaixo para cadastrar.';
                }

                this.mostrarFormulario = true;
            } catch (error) {
                console.error('Erro ao buscar CPF:', error);
                this.mensagemBusca = 'Erro ao buscar CPF. Tente novamente.';
            } finally {
                this.buscando = false;
            }
        }
    }
}
</script>
@endsection
