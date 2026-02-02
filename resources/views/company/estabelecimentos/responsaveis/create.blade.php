@extends('layouts.company')

@section('title', 'Adicionar Responsável')
@section('page-title', 'Adicionar Responsável')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Header --}}
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('company.estabelecimentos.responsaveis.index', $estabelecimento->id) }}" 
           class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">
                Adicionar Responsável {{ $tipo === 'tecnico' ? 'Técnico' : 'Legal' }}
            </h1>
            <p class="text-sm text-gray-600 mt-1">{{ $estabelecimento->nome_fantasia }} - {{ $estabelecimento->documento_formatado }}</p>
        </div>
    </div>

    {{-- Formulário --}}
    <form action="{{ route('company.estabelecimentos.responsaveis.store', $estabelecimento->id) }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @csrf
        <input type="hidden" name="tipo_vinculo" value="{{ $tipo }}">

        {{-- Header do Card --}}
        <div class="bg-gradient-to-r {{ $tipo === 'tecnico' ? 'from-green-50 to-emerald-50' : 'from-blue-50 to-indigo-50' }} px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 {{ $tipo === 'tecnico' ? 'text-green-600' : 'text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Dados do Responsável {{ $tipo === 'tecnico' ? 'Técnico' : 'Legal' }}
            </h3>
        </div>

        <div class="p-6 space-y-6">
            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Campo CPF primeiro para validação --}}
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">
                    CPF <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-3">
                    <input type="text" name="cpf" id="cpf" value="{{ old('cpf') }}" required
                           class="flex-1 px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono transition-colors"
                           placeholder="000.000.000-00" maxlength="14">
                    <button type="button" id="btnBuscarCpf"
                            class="px-4 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Buscar
                    </button>
                </div>
                <p class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Digite o CPF e clique em "Buscar" ou aguarde o preenchimento automático
                </p>
            </div>

            {{-- Dados Pessoais --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                        Nome Completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nome" id="nome" value="{{ old('nome') }}" required
                           class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="Nome completo do responsável">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        E-mail
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="email@exemplo.com">
                </div>

                <div>
                    <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">
                        Telefone
                    </label>
                    <input type="text" name="telefone" id="telefone" value="{{ old('telefone') }}"
                           class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono transition-colors"
                           placeholder="(00) 00000-0000" maxlength="15">
                </div>
            </div>

            @if($tipo === 'tecnico')
            {{-- Dados do Conselho (apenas para RT) --}}
            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Dados do Conselho Profissional
                </h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="conselho" class="block text-sm font-medium text-gray-700 mb-2">
                            Conselho <span class="text-red-500">*</span>
                        </label>
                        <select name="conselho" id="conselho" required
                                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="">Selecione o conselho</option>
                            <option value="CRF" {{ old('conselho') == 'CRF' ? 'selected' : '' }}>CRF - Conselho Regional de Farmácia</option>
                            <option value="CRM" {{ old('conselho') == 'CRM' ? 'selected' : '' }}>CRM - Conselho Regional de Medicina</option>
                            <option value="CRMV" {{ old('conselho') == 'CRMV' ? 'selected' : '' }}>CRMV - Conselho Regional de Medicina Veterinária</option>
                            <option value="CRO" {{ old('conselho') == 'CRO' ? 'selected' : '' }}>CRO - Conselho Regional de Odontologia</option>
                            <option value="COREN" {{ old('conselho') == 'COREN' ? 'selected' : '' }}>COREN - Conselho Regional de Enfermagem</option>
                            <option value="CRN" {{ old('conselho') == 'CRN' ? 'selected' : '' }}>CRN - Conselho Regional de Nutrição</option>
                            <option value="CRBIO" {{ old('conselho') == 'CRBIO' ? 'selected' : '' }}>CRBIO - Conselho Regional de Biologia</option>
                            <option value="CRQ" {{ old('conselho') == 'CRQ' ? 'selected' : '' }}>CRQ - Conselho Regional de Química</option>
                            <option value="CREA" {{ old('conselho') == 'CREA' ? 'selected' : '' }}>CREA - Conselho Regional de Engenharia</option>
                            <option value="OUTRO" {{ old('conselho') == 'OUTRO' ? 'selected' : '' }}>Outro</option>
                        </select>
                    </div>

                    <div>
                        <label for="numero_registro" class="block text-sm font-medium text-gray-700 mb-2">
                            Número do Registro <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="numero_registro" id="numero_registro" value="{{ old('numero_registro') }}" required
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono transition-colors"
                               placeholder="Número do registro no conselho">
                    </div>

                    {{-- Campo de upload - escondido quando já tem documento --}}
                    <div class="md:col-span-2" id="secaoCarteirinha">
                        <label for="carteirinha_conselho" class="block text-sm font-medium text-gray-700 mb-2">
                            Carteirinha do Conselho (PDF ou Imagem)
                        </label>
                        <input type="file" name="carteirinha_conselho" id="carteirinha_conselho"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        <p class="mt-1 text-xs text-gray-500">Formatos aceitos: PDF, JPG, PNG. Tamanho máximo: 5MB</p>
                    </div>

                    {{-- Aviso quando já tem documento --}}
                    <div class="md:col-span-2 hidden" id="avisoCarteirinhaExistente">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-green-800">Carteirinha do Conselho já cadastrada</p>
                                <p class="text-xs text-green-700 mt-1">Este responsável já possui carteirinha do conselho em arquivo. Por segurança, o documento não é exibido.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            {{-- Documento de Identificação (para RL) --}}
            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                    </svg>
                    Documento de Identificação
                </h4>
                
                {{-- Campo de upload - escondido quando já tem documento --}}
                <div id="secaoDocumento">
                    <label for="documento_identificacao" class="block text-sm font-medium text-gray-700 mb-2">
                        RG ou CNH (PDF ou Imagem)
                    </label>
                    <input type="file" name="documento_identificacao" id="documento_identificacao"
                           accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-xs text-gray-500">Formatos aceitos: PDF, JPG, PNG. Tamanho máximo: 5MB</p>
                </div>

                {{-- Aviso quando já tem documento --}}
                <div class="hidden" id="avisoDocumentoExistente">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-green-800">Documento de identificação já cadastrado</p>
                            <p class="text-xs text-green-700 mt-1">Este responsável já possui documento de identificação em arquivo. Por segurança, o documento não é exibido.</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Botões --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
            <a href="{{ route('company.estabelecimentos.responsaveis.index', $estabelecimento->id) }}" 
               class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 text-sm font-medium text-white {{ $tipo === 'tecnico' ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700' }} rounded-lg shadow-sm transition-all">
                Adicionar Responsável
            </button>
        </div>
    </form>
</div>

<script>
const cpfInput = document.getElementById('cpf');
const btnBuscarCpf = document.getElementById('btnBuscarCpf');
const nomeInput = document.getElementById('nome');
const emailInput = document.getElementById('email');
const telefoneInput = document.getElementById('telefone');
const conselhoInput = document.getElementById('conselho');
const numeroRegistroInput = document.getElementById('numero_registro');

let buscandoCpf = false;

// Máscara para CPF
cpfInput.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    
    if (value.length > 9) {
        value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    } else if (value.length > 6) {
        value = value.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
    } else if (value.length > 3) {
        value = value.replace(/(\d{3})(\d{1,3})/, '$1.$2');
    }
    
    e.target.value = value;
    
    // Remove estilo de sucesso ao editar
    cpfInput.classList.remove('bg-green-50', 'border-green-500');
    
    // Busca automática quando CPF completo
    if (value.replace(/\D/g, '').length === 11 && !buscandoCpf) {
        buscarResponsavelPorCpf(value);
    }
});

// Botão de busca manual
btnBuscarCpf.addEventListener('click', function() {
    const cpf = cpfInput.value;
    if (cpf.replace(/\D/g, '').length === 11) {
        buscarResponsavelPorCpf(cpf);
    } else {
        mostrarMensagem('Digite um CPF válido com 11 dígitos', 'error');
    }
});

// Busca responsável por CPF
async function buscarResponsavelPorCpf(cpf) {
    if (buscandoCpf) return;
    buscandoCpf = true;
    
    // Mostra indicador de carregamento
    cpfInput.classList.add('bg-yellow-50');
    btnBuscarCpf.disabled = true;
    btnBuscarCpf.innerHTML = `
        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Buscando...
    `;
    
    try {
        const response = await fetch('{{ route("company.responsaveis.buscar-cpf") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ cpf: cpf })
        });
        
        const data = await response.json();
        
        if (data.encontrado) {
            // Identifica a fonte dos dados
            const fonte = data.fonte || 'responsavel';
            const isFromUsuarioExterno = fonte === 'usuario_externo';
            
            // Preenche os campos automaticamente
            if (data.dados.nome) nomeInput.value = data.dados.nome;
            if (data.dados.email) emailInput.value = data.dados.email;
            if (data.dados.telefone) {
                // Formata telefone
                let tel = data.dados.telefone.replace(/\D/g, '');
                if (tel.length > 10) {
                    tel = tel.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                } else if (tel.length > 6) {
                    tel = tel.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                }
                telefoneInput.value = tel;
            }
            
            // Campos específicos de RT (só preenche se não vier de usuario_externo)
            if (!isFromUsuarioExterno) {
                if (conselhoInput && data.dados.conselho) {
                    conselhoInput.value = data.dados.conselho;
                }
                if (numeroRegistroInput && data.dados.numero_registro) {
                    numeroRegistroInput.value = data.dados.numero_registro;
                }
            }
            
            // Controla exibição dos campos de documento
            controlarCamposDocumento(data.dados);
            
            // Feedback visual de sucesso
            cpfInput.classList.remove('bg-yellow-50');
            cpfInput.classList.add('bg-green-50', 'border-green-500');
            
            // Mostra mensagem diferente se veio de usuario_externo
            if (isFromUsuarioExterno) {
                mostrarMensagem('Usuário encontrado no sistema! Dados básicos preenchidos. Complete as informações específicas.', 'success');
            } else {
                mostrarMensagem('Dados encontrados e preenchidos automaticamente!', 'success');
            }
        } else {
            cpfInput.classList.remove('bg-yellow-50');
            // Mostra campos de upload quando CPF não encontrado
            mostrarCamposUpload();
            mostrarMensagem('CPF não encontrado. Preencha os dados manualmente.', 'info');
        }
    } catch (error) {
        console.error('Erro ao buscar CPF:', error);
        cpfInput.classList.remove('bg-yellow-50');
        mostrarMensagem('Erro ao buscar CPF. Tente novamente.', 'error');
    }
    
    // Restaura botão
    btnBuscarCpf.disabled = false;
    btnBuscarCpf.innerHTML = `
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        Buscar
    `;
    
    buscandoCpf = false;
}

// Função para mostrar mensagem temporária
function mostrarMensagem(texto, tipo) {
    const existente = document.getElementById('mensagem-cpf');
    if (existente) existente.remove();
    
    const div = document.createElement('div');
    div.id = 'mensagem-cpf';
    
    let bgClass, textClass, icon;
    if (tipo === 'success') {
        bgClass = 'bg-green-100';
        textClass = 'text-green-700';
        icon = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>';
    } else if (tipo === 'error') {
        bgClass = 'bg-red-100';
        textClass = 'text-red-700';
        icon = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>';
    } else {
        bgClass = 'bg-blue-100';
        textClass = 'text-blue-700';
        icon = '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>';
    }
    
    div.className = `mt-3 p-3 ${bgClass} ${textClass} text-sm rounded-lg flex items-center gap-2`;
    div.innerHTML = `
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">${icon}</svg>
        <span>${texto}</span>
    `;
    
    cpfInput.closest('.bg-gray-50').appendChild(div);
    
    setTimeout(() => div.remove(), 5000);
}

// Máscara para telefone
telefoneInput.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    
    if (value.length > 10) {
        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    } else if (value.length > 6) {
        value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
    } else if (value.length > 2) {
        value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
    }
    
    e.target.value = value;
});

// Controla exibição dos campos de documento baseado nos dados do responsável
function controlarCamposDocumento(dados) {
    const tipoVinculo = '{{ $tipo }}';
    
    if (tipoVinculo === 'tecnico') {
        const secaoCarteirinha = document.getElementById('secaoCarteirinha');
        const avisoCarteirinha = document.getElementById('avisoCarteirinhaExistente');
        const inputCarteirinha = document.getElementById('carteirinha_conselho');
        
        if (dados.tem_carteirinha_conselho) {
            // Esconde campo de upload, desabilita e remove do form
            if (secaoCarteirinha) secaoCarteirinha.classList.add('hidden');
            if (avisoCarteirinha) avisoCarteirinha.classList.remove('hidden');
            if (inputCarteirinha) {
                inputCarteirinha.disabled = true;
                inputCarteirinha.value = '';
                inputCarteirinha.removeAttribute('name');
            }
        } else {
            // Mostra campo de upload e habilita
            if (secaoCarteirinha) secaoCarteirinha.classList.remove('hidden');
            if (avisoCarteirinha) avisoCarteirinha.classList.add('hidden');
            if (inputCarteirinha) {
                inputCarteirinha.disabled = false;
                inputCarteirinha.setAttribute('name', 'carteirinha_conselho');
            }
        }
    } else {
        const secaoDocumento = document.getElementById('secaoDocumento');
        const avisoDocumento = document.getElementById('avisoDocumentoExistente');
        const inputDocumento = document.getElementById('documento_identificacao');
        
        if (dados.tem_documento_identificacao) {
            // Esconde campo de upload, desabilita e remove do form
            if (secaoDocumento) secaoDocumento.classList.add('hidden');
            if (avisoDocumento) avisoDocumento.classList.remove('hidden');
            if (inputDocumento) {
                inputDocumento.disabled = true;
                inputDocumento.value = '';
                inputDocumento.removeAttribute('name');
            }
        } else {
            // Mostra campo de upload e habilita
            if (secaoDocumento) secaoDocumento.classList.remove('hidden');
            if (avisoDocumento) avisoDocumento.classList.add('hidden');
            if (inputDocumento) {
                inputDocumento.disabled = false;
                inputDocumento.setAttribute('name', 'documento_identificacao');
            }
        }
    }
}

// Mostra campos de upload (quando CPF não encontrado ou ao limpar)
function mostrarCamposUpload() {
    const tipoVinculo = '{{ $tipo }}';
    
    if (tipoVinculo === 'tecnico') {
        const secaoCarteirinha = document.getElementById('secaoCarteirinha');
        const avisoCarteirinha = document.getElementById('avisoCarteirinhaExistente');
        const inputCarteirinha = document.getElementById('carteirinha_conselho');
        
        if (secaoCarteirinha) secaoCarteirinha.classList.remove('hidden');
        if (avisoCarteirinha) avisoCarteirinha.classList.add('hidden');
        if (inputCarteirinha) {
            inputCarteirinha.disabled = false;
            inputCarteirinha.setAttribute('name', 'carteirinha_conselho');
        }
    } else {
        const secaoDocumento = document.getElementById('secaoDocumento');
        const avisoDocumento = document.getElementById('avisoDocumentoExistente');
        const inputDocumento = document.getElementById('documento_identificacao');
        
        if (secaoDocumento) secaoDocumento.classList.remove('hidden');
        if (avisoDocumento) avisoDocumento.classList.add('hidden');
        if (inputDocumento) {
            inputDocumento.disabled = false;
            inputDocumento.setAttribute('name', 'documento_identificacao');
        }
    }
}

// Reseta campos de documento quando CPF é alterado manualmente
cpfInput.addEventListener('input', function() {
    // Só reseta se o CPF foi alterado (não está completo)
    const cpfLimpo = this.value.replace(/\D/g, '');
    if (cpfLimpo.length < 11) {
        mostrarCamposUpload();
    }
});
</script>
@endsection
