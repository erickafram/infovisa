@extends('layouts.admin')

@section('title', 'Editar Estabelecimento')
@section('page-title', 'Editar Estabelecimento')

@section('content')
<div class="max-w-8xl mx-auto" x-data="estabelecimentoEdit()">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}" 
               class="text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Editar Estabelecimento</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $estabelecimento->nome_fantasia }}</p>
            </div>
        </div>

        @if($estabelecimento->tipo_pessoa === 'juridica')
        {{-- Botão Atualizar pela API --}}
        <button @click="atualizarPelaApi()" 
                :disabled="loading"
                type="button"
                class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-900 bg-gradient-to-r from-green-100 to-green-200 rounded-lg hover:from-green-200 hover:to-green-300 shadow-sm hover:shadow transition-all disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-text="loading ? 'Atualizando...' : 'Atualizar pela API'"></span>
        </button>
        @endif
    </div>

    {{-- Mensagem de Feedback --}}
    <div x-show="mensagem" 
         x-transition
         :class="{
             'bg-green-50 border-green-200 text-green-800': tipoMensagem === 'success',
             'bg-red-50 border-red-200 text-red-800': tipoMensagem === 'error',
             'bg-blue-50 border-blue-200 text-blue-800': tipoMensagem === 'info'
         }"
         class="mb-6 p-4 rounded-lg border"
         x-cloak>
        <p class="text-sm font-medium" x-text="mensagem"></p>
    </div>

    <form method="POST" action="{{ route('admin.estabelecimentos.update', $estabelecimento->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Dados Gerais --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Dados Gerais
                </h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Tipo de Setor --}}
                    <div>
                        <label for="tipo_setor" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Setor <span class="text-red-500">*</span>
                        </label>
                        <select name="tipo_setor" id="tipo_setor" required
                                x-model="formData.tipo_setor"
                                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="publico" {{ old('tipo_setor', optional($estabelecimento->tipo_setor)->value) === 'publico' ? 'selected' : '' }}>🏛️ Público</option>
                            <option value="privado" {{ old('tipo_setor', optional($estabelecimento->tipo_setor)->value) === 'privado' ? 'selected' : '' }}>🏢 Privado</option>
                        </select>
                    </div>

                    @if($estabelecimento->tipo_pessoa === 'juridica')
                    {{-- CNPJ --}}
                    <div class="md:col-span-2">
                        <label for="cnpj" class="block text-sm font-medium text-gray-700 mb-2">
                            CNPJ <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="cnpj" id="cnpj" required readonly
                               value="{{ old('cnpj', $estabelecimento->documento_formatado) }}"
                               maxlength="18"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-600 font-mono">
                    </div>

                    {{-- Razão Social --}}
                    <div class="md:col-span-3">
                        <label for="razao_social" class="block text-sm font-medium text-gray-700 mb-2">
                            Razão Social <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="razao_social" id="razao_social" required
                               x-model="formData.razao_social"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                    @else
                    {{-- CPF --}}
                    <div class="md:col-span-2">
                        <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">
                            CPF <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="cpf" id="cpf" required readonly
                               value="{{ old('cpf', $estabelecimento->documento_formatado) }}"
                               maxlength="14"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-600 font-mono">
                    </div>

                    {{-- Nome Completo --}}
                    <div class="md:col-span-3">
                        <label for="nome_completo" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome Completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nome_completo" id="nome_completo" required
                               x-model="formData.nome_completo"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                    @endif

                    {{-- Nome Fantasia --}}
                    <div class="md:col-span-3">
                        <label for="nome_fantasia" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome Fantasia <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nome_fantasia" id="nome_fantasia" required
                               x-model="formData.nome_fantasia"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    @if($estabelecimento->natureza_juridica)
                    {{-- Natureza Jurídica --}}
                    <div>
                        <label for="natureza_juridica" class="block text-sm font-medium text-gray-700 mb-2">
                            Natureza Jurídica
                        </label>
                        <input type="text" name="natureza_juridica" id="natureza_juridica"
                               x-model="formData.natureza_juridica"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                    @endif

                    @if($estabelecimento->porte)
                    {{-- Porte --}}
                    <div>
                        <label for="porte" class="block text-sm font-medium text-gray-700 mb-2">
                            Porte
                        </label>
                        <input type="text" name="porte" id="porte"
                               x-model="formData.porte"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                    @endif

                    @if($estabelecimento->descricao_situacao_cadastral)
                    {{-- Situação Cadastral --}}
                    <div>
                        <label for="descricao_situacao_cadastral" class="block text-sm font-medium text-gray-700 mb-2">
                            Situação Cadastral
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium 
                                {{ $estabelecimento->descricao_situacao_cadastral === 'ATIVA' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $estabelecimento->descricao_situacao_cadastral }}
                            </span>
                            @if($estabelecimento->data_situacao_cadastral)
                            <span class="text-xs text-gray-500">Desde: {{ $estabelecimento->data_situacao_cadastral->format('d/m/Y') }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Endereço --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Endereço
                </h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- CEP --}}
                    <div>
                        <label for="cep" class="block text-sm font-medium text-gray-700 mb-2">
                            CEP <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="cep" id="cep" required
                               x-model="formData.cep"
                               maxlength="9"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono transition-colors">
                    </div>

                    {{-- Logradouro --}}
                    <div class="md:col-span-3">
                        <label for="endereco" class="block text-sm font-medium text-gray-700 mb-2">
                            Logradouro <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="endereco" id="endereco" required
                               x-model="formData.endereco"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    {{-- Número --}}
                    <div>
                        <label for="numero" class="block text-sm font-medium text-gray-700 mb-2">
                            Número <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="numero" id="numero" required
                               x-model="formData.numero"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    {{-- Complemento --}}
                    <div class="md:col-span-3">
                        <label for="complemento" class="block text-sm font-medium text-gray-700 mb-2">
                            Complemento
                        </label>
                        <input type="text" name="complemento" id="complemento"
                               x-model="formData.complemento"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    {{-- Bairro --}}
                    <div class="md:col-span-2">
                        <label for="bairro" class="block text-sm font-medium text-gray-700 mb-2">
                            Bairro <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="bairro" id="bairro" required
                               x-model="formData.bairro"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    {{-- Cidade --}}
                    <div>
                        <label for="cidade" class="block text-sm font-medium text-gray-700 mb-2">
                            Município <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="cidade" id="cidade" required readonly
                               x-model="formData.cidade"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-600 transition-colors">
                    </div>

                    {{-- Estado --}}
                    <div>
                        <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                            Estado <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="estado" id="estado" required readonly
                               x-model="formData.estado"
                               maxlength="2"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-600 uppercase transition-colors">
                    </div>
                </div>
            </div>
        </div>

        {{-- Contatos --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Contatos
                </h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Telefone --}}
                    <div>
                        <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">
                            Telefone Principal
                        </label>
                        <input type="text" name="telefone" id="telefone"
                               x-model="formData.telefone"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono transition-colors">
                    </div>

                    {{-- E-mail --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            E-mail
                        </label>
                        <input type="email" name="email" id="email"
                               x-model="formData.email"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                </div>
            </div>
        </div>

        {{-- Botões --}}
        <div class="flex items-center justify-end gap-3 bg-gray-50 rounded-lg p-4 border border-gray-200">
            <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}" 
               class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-sm hover:shadow transition-all">
                💾 Salvar Alterações
            </button>
        </div>
    </form>
</div>

<script>
function estabelecimentoEdit() {
    return {
        loading: false,
        mensagem: '',
        tipoMensagem: 'info',
        formData: {
            tipo_setor: '{{ old('tipo_setor', optional($estabelecimento->tipo_setor)->value) }}',
            razao_social: '{{ old('razao_social', $estabelecimento->razao_social) }}',
            nome_completo: '{{ old('nome_completo', $estabelecimento->nome_completo) }}',
            nome_fantasia: '{{ old('nome_fantasia', $estabelecimento->nome_fantasia) }}',
            natureza_juridica: '{{ old('natureza_juridica', $estabelecimento->natureza_juridica) }}',
            porte: '{{ old('porte', $estabelecimento->porte) }}',
            cep: '{{ old('cep', $estabelecimento->cep) }}',
            endereco: '{{ old('endereco', $estabelecimento->endereco) }}',
            numero: '{{ old('numero', $estabelecimento->numero) }}',
            complemento: '{{ old('complemento', $estabelecimento->complemento) }}',
            bairro: '{{ old('bairro', $estabelecimento->bairro) }}',
            cidade: '{{ old('cidade', $estabelecimento->cidade) }}',
            estado: '{{ old('estado', $estabelecimento->estado) }}',
            telefone: '{{ old('telefone', $estabelecimento->telefone) }}',
            email: '{{ old('email', $estabelecimento->email) }}'
        },

        async atualizarPelaApi() {
            if (!confirm('Deseja atualizar os dados deste estabelecimento consultando a API da Receita Federal? Os dados atuais serão substituídos.')) {
                return;
            }

            this.loading = true;
            this.mensagem = '';

            try {
                const cnpj = '{{ $estabelecimento->cnpj }}';
                
                const response = await fetch('{{ url("/api/consultar-cnpj") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ cnpj })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Preenche os campos com os dados da API
                    this.formData.razao_social = result.data.razao_social || this.formData.razao_social;
                    this.formData.nome_fantasia = result.data.nome_fantasia || this.formData.nome_fantasia;
                    this.formData.natureza_juridica = result.data.natureza_juridica || this.formData.natureza_juridica;
                    this.formData.porte = result.data.porte || this.formData.porte;
                    this.formData.cep = result.data.cep || this.formData.cep;
                    this.formData.endereco = result.data.endereco || this.formData.endereco;
                    this.formData.numero = result.data.numero || this.formData.numero;
                    this.formData.complemento = result.data.complemento || this.formData.complemento;
                    this.formData.bairro = result.data.bairro || this.formData.bairro;
                    this.formData.cidade = result.data.cidade || this.formData.cidade;
                    this.formData.estado = result.data.estado || this.formData.estado;
                    this.formData.telefone = result.data.telefone || this.formData.telefone;
                    this.formData.email = result.data.email || this.formData.email;

                    this.mostrarMensagem(`✅ Dados atualizados com sucesso pela ${result.api_source || 'API'}! Revise as informações e clique em "Salvar Alterações" para confirmar.`, 'success');
                } else {
                    this.mostrarMensagem(result.message || '❌ Erro ao consultar CNPJ na API', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                this.mostrarMensagem('❌ Erro ao comunicar com a API. Tente novamente.', 'error');
            } finally {
                this.loading = false;
            }
        },

        mostrarMensagem(texto, tipo) {
            this.mensagem = texto;
            this.tipoMensagem = tipo;
            
            setTimeout(() => {
                this.mensagem = '';
            }, 8000);
        }
    }
}
</script>
@endsection
