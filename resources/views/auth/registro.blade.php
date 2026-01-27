@extends('layouts.auth')

@section('title', 'Cadastro de Usuário Externo')

@section('content')
<div x-data="{
    cpf: '{{ request('cpf', old('cpf')) }}',
    telefone: '',
    nome: '{{ old('nome') }}',
    formatCpf() {
        let value = this.cpf.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            this.cpf = value;
        }
    },
    formatTelefone() {
        let value = this.telefone.replace(/\D/g, '');
        if (value.length <= 11) {
            if (value.length === 11) {
                value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
            } else {
                value = value.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
            }
            this.telefone = value;
        }
    },
    toUpperCase() {
        this.nome = this.nome.toUpperCase();
    }
}" style="width: 900px; margin: 0 auto;">
    <!-- Card de Cadastro -->
    <div class="bg-white rounded-xl shadow-lg p-8">
        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Criar Conta</h1>
            <p class="text-gray-600">Preencha seus dados para se cadastrar</p>
        </div>

        <!-- Mensagem de CPF Habilitado -->
        @if(request('cpf'))
        <div class="mb-6 bg-green-50 border-2 border-green-300 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-green-900 mb-1">✓ CPF Habilitado para Cadastro</h3>
                    <p class="text-sm text-green-800">Seu CPF está autorizado para realizar o cadastro. Complete as informações abaixo para criar sua conta.</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Alerts -->
        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Formulário -->
        <form action="{{ route('registro.submit') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Nome Completo (linha inteira) -->
            <div>
                <label for="nome" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Nome Completo <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nome" 
                    name="nome" 
                    x-model="nome"
                    @input="toUpperCase()"
                    required
                    class="w-full px-4 py-2.5 border-2 @error('nome') border-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition uppercase"
                    placeholder="DIGITE SEU NOME COMPLETO"
                    style="text-transform: uppercase;"
                >
                @error('nome')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- CPF e Email (duas colunas) -->
            <div class="grid grid-cols-2 gap-4">
                <!-- CPF -->
                <div>
                    <label for="cpf" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        CPF <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="cpf" 
                        name="cpf" 
                        x-model="cpf"
                        @input="formatCpf()"
                        value="{{ old('cpf') }}"
                        maxlength="14"
                        required
                        class="w-full px-4 py-2.5 border-2 @error('cpf') border-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="000.000.000-00"
                    >
                    @error('cpf')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        E-mail <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        required
                        class="w-full px-4 py-2.5 border-2 @error('email') border-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="seu@email.com"
                    >
                    @error('email')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Telefone e Vínculo (duas colunas) -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Telefone -->
                <div>
                    <label for="telefone" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Telefone <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="telefone" 
                        name="telefone" 
                        x-model="telefone"
                        @input="formatTelefone()"
                        value="{{ old('telefone') }}"
                        maxlength="15"
                        required
                        class="w-full px-4 py-2.5 border-2 @error('telefone') border-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="(00) 00000-0000"
                    >
                    @error('telefone')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Vínculo com Estabelecimento -->
                <div>
                    <label for="vinculo_estabelecimento" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Vínculo <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="vinculo_estabelecimento" 
                        name="vinculo_estabelecimento" 
                        required
                        class="w-full px-4 py-2.5 border-2 @error('vinculo_estabelecimento') border-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    >
                        <option value="">Selecione...</option>
                        @foreach($vinculos as $value => $label)
                            <option value="{{ $value }}" {{ old('vinculo_estabelecimento') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('vinculo_estabelecimento')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Senha e Confirmar Senha (duas colunas) -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Senha -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Senha <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-2.5 border-2 @error('password') border-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Mínimo 8 caracteres"
                    >
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmar Senha -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Confirmar Senha <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required
                        class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Digite novamente"
                    >
                </div>
            </div>

            <!-- Info sobre senha -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-xs text-blue-800">
                    <strong>Senha:</strong> Mínimo 8 caracteres incluindo letras.
                </p>
            </div>

            <!-- Aceite de Termos -->
            <div x-data="{ termosAceitos: false, modalAberto: false }">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input 
                            id="aceite_termos" 
                            name="aceite_termos" 
                            type="checkbox" 
                            required
                            x-model="termosAceitos"
                            :disabled="!termosAceitos"
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 @error('aceite_termos') border-red-300 @enderror disabled:cursor-not-allowed disabled:opacity-50"
                        >
                    </div>
                    <div class="ml-3">
                        <label for="aceite_termos" class="text-sm text-gray-700">
                            Li, compreendi e aceito os 
                            <button 
                                type="button" 
                                @click="modalAberto = true" 
                                class="text-blue-600 hover:text-blue-700 font-semibold underline"
                            >
                                Termos e Condições de Uso
                            </button>
                            <span class="text-red-500">*</span>
                        </label>
                        <p x-show="!termosAceitos" class="mt-1 text-xs text-amber-600">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Clique em "Termos e Condições de Uso" para ler e aceitar
                        </p>
                        @error('aceite_termos')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Modal de Termos e Condições -->
                <div 
                    x-show="modalAberto" 
                    x-cloak
                    class="fixed inset-0 z-50 overflow-y-auto"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <!-- Overlay -->
                        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="modalAberto = false"></div>

                        <!-- Modal -->
                        <div 
                            class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-auto transform transition-all"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            @click.stop
                        >
                            <!-- Header -->
                            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 rounded-t-2xl">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-bold text-white flex items-center">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Termos e Condições de Uso
                                    </h3>
                                    <button 
                                        type="button" 
                                        @click="modalAberto = false" 
                                        class="text-white/80 hover:text-white transition"
                                    >
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Conteúdo -->
                            <div class="px-6 py-5 max-h-96 overflow-y-auto text-left">
                                <h4 class="font-bold text-gray-900 text-center mb-5 text-lg border-b pb-3">
                                    DECLARAÇÃO E ACEITE DOS TERMOS DE USO DO SISTEMA INFOVISA
                                </h4>
                                
                                <div class="space-y-4 text-sm text-gray-700 leading-relaxed">
                                    <p>
                                        <strong class="text-gray-900">DECLARO</strong> que conheço a legislação sanitária e demais normas pertinentes às atividades CNAEs exercidas na Instituição* que neste ato represento.
                                    </p>
                                    
                                    <p>
                                        <strong class="text-gray-900">DECLARO</strong> que todo o documento protocolado é verdadeiro e está sujeito ao aceite da DIRETORIA DE VIGILÂNCIA SANITÁRIA – DVISA, podendo ser recusado se não atender aos critérios exigidos.
                                    </p>
                                    
                                    <p>
                                        Estou <strong class="text-gray-900">CIENTE E ACEITO</strong> que os documentos relacionados à instituição e ao processo de licenciamento são tramitados exclusivamente pelo sistema INFOVISA e terão <strong class="text-gray-900">EFEITO DE NOTIFICAÇÃO</strong> no quinto dia útil a partir de sua anexação ao processo ou no primeiro acesso da instituição/DVISA ao sistema INFOVISA por qualquer usuário cadastrado pela instituição – fato que ocorrer primeiro.
                                    </p>
                                    
                                    <p>
                                        Estou <strong class="text-gray-900">CIENTE</strong> que qualquer alteração de Responsável Legal ou Técnico, estrutura física, procedimentos operacionais e/ou atividade exercida devo comunicar oficialmente pelo INFOVISA** a Vigilância Sanitária Estadual no prazo de cinco dias úteis.
                                    </p>
                                    
                                    <p>
                                        <strong class="text-gray-900">DECLARO</strong> ainda, sob as penas da lei, serem verdadeiras as informações prestadas e que estou ciente de que, sendo constatada a omissão de qualquer informação relevante ou a declaração falsa no cadastro da instituição e/ou processo de licenciamento sanitário, ficará configurado <strong class="text-red-600">crime de falsidade ideológica</strong>, previsto no artigo 299 do Código Penal Brasileiro, ensejando na cassação automática da Licença Sanitária, sem prejuízo de sanções civis e criminais cabíveis.
                                    </p>
                                </div>
                                
                                <div class="mt-5 pt-4 border-t border-gray-200 text-xs text-gray-500 space-y-1">
                                    <p><strong>*Instituição:</strong> empresa, estabelecimento ou serviço de natureza jurídica pública ou privada.</p>
                                    <p><strong>**INFOVISA:</strong> Sistema oficial de informação e gerenciamento da Vigilância Sanitária Estadual.</p>
                                </div>
                            </div>

                            <!-- Footer com botões -->
                            <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex gap-3">
                                <button 
                                    type="button" 
                                    @click="modalAberto = false" 
                                    class="flex-1 px-4 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-100 transition"
                                >
                                    Cancelar
                                </button>
                                <button 
                                    type="button" 
                                    @click="termosAceitos = true; modalAberto = false" 
                                    class="flex-1 px-4 py-2.5 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg font-semibold hover:from-green-700 hover:to-green-800 transition flex items-center justify-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Li e Aceito os Termos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botão de Submit -->
            <button 
                type="submit" 
                class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition shadow-lg hover:shadow-xl flex items-center justify-center space-x-2"
            >
                <span>Criar Conta</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </button>
        </form>

        <!-- Link para Login -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Já tem uma conta? 
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                    Faça login
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
