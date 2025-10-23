@extends('layouts.admin')

@section('title', 'Cadastrar Pessoa Física')
@section('page-title', 'Cadastrar Pessoa Física')

@section('content')
<div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.estabelecimentos.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Cadastrar Pessoa Física</h1>
                <p class="text-sm text-gray-600 mt-1">Preencha os dados do estabelecimento</p>
            </div>
        </div>
    </div>

    {{-- Exibir erros de validação --}}
    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Há erros no formulário:</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.estabelecimentos.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="tipo_pessoa" value="fisica">
        <input type="hidden" name="tipo_setor" value="privado">

        {{-- 1. Dados Cadastrais --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">1. Dados Cadastrais</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CPF <span class="text-red-500">*</span></label>
                    <input type="text" id="cpf_display" value="{{ old('cpf') }}" placeholder="000.000.000-00" maxlength="14" required class="w-full px-3 py-2 border rounded-md">
                    <input type="hidden" id="cpf" name="cpf" value="{{ old('cpf') }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo <span class="text-red-500">*</span></label>
                    <input type="text" name="nome_completo" value="{{ old('nome_completo') }}" required class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">RG <span class="text-red-500">*</span></label>
                    <input type="text" name="rg" value="{{ old('rg') }}" required class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Órgão Emissor <span class="text-red-500">*</span></label>
                    <input type="text" name="orgao_emissor" value="{{ old('orgao_emissor') }}" placeholder="Ex: SSP/TO" required class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia <span class="text-red-500">*</span></label>
                    <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia') }}" required class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-mail <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-3 py-2 border rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone <span class="text-red-500">*</span></label>
                    <input type="text" id="telefone" name="telefone" value="{{ old('telefone') }}" placeholder="(00) 00000-0000" maxlength="15" required class="w-full px-3 py-2 border rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Início de Funcionamento <span class="text-red-500">*</span></label>
                    <input type="date" name="data_inicio_funcionamento" value="{{ old('data_inicio_funcionamento') }}" required class="w-full px-3 py-2 border rounded-md">
                </div>
            </div>
        </div>

        {{-- 2. Endereço --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">2. Endereço Completo</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CEP <span class="text-red-500">*</span></label>
                    <input type="text" id="cep_display" value="{{ old('cep') }}" placeholder="00000-000" maxlength="9" required class="w-full px-3 py-2 border rounded-md">
                    <input type="hidden" id="cep" name="cep" value="{{ old('cep') }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Endereço <span class="text-red-500">*</span></label>
                    <input type="text" id="endereco" name="endereco" value="{{ old('endereco') }}" required class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número <span class="text-red-500">*</span></label>
                    <input type="text" name="numero" value="{{ old('numero') }}" required class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                    <input type="text" name="complemento" value="{{ old('complemento') }}" class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bairro <span class="text-red-500">*</span></label>
                    <input type="text" id="bairro" name="bairro" value="{{ old('bairro') }}" required class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cidade <span class="text-red-500">*</span></label>
                    <select id="cidade" name="cidade" required class="w-full px-3 py-2 border rounded-md">
                        <option value="">Selecione...</option>
                        @foreach(['PALMAS', 'ARAGUAÍNA', 'GURUPI', 'PORTO NACIONAL', 'PARAÍSO DO TOCANTINS', 'COLINAS DO TOCANTINS', 'GUARAÍ', 'TOCANTINÓPOLIS', 'MIRACEMA DO TOCANTINS', 'ARAGUATINS'] as $cidade)
                            <option value="{{ $cidade }}">{{ $cidade }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UF <span class="text-red-500">*</span></label>
                    <input type="text" name="estado" value="TO" readonly class="w-full px-3 py-2 border rounded-md bg-gray-100">
                </div>
            </div>
        </div>

        {{-- 3. CNAEs --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">3. Atividades Econômicas (CNAE) <span class="text-red-500">*</span></h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar CNAE (7 dígitos)</label>
                <div class="flex gap-2">
                    <input type="text" id="cnae_busca" placeholder="Ex: 5611201" maxlength="7" class="flex-1 px-3 py-2 border rounded-md">
                    <button type="button" id="btn_buscar_cnae" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Buscar</button>
                </div>
                <p id="cnae_erro" class="text-red-500 text-xs mt-1 hidden"></p>
            </div>
            <div id="cnaes_lista" class="space-y-2"><p class="text-sm text-gray-500">Nenhum CNAE adicionado.</p></div>
            <input type="hidden" name="atividades_exercidas" id="cnaes_input" value="{{ old('atividades_exercidas') }}">
        </div>

        <div class="flex justify-between pt-6 pb-8">
            <a href="{{ route('admin.estabelecimentos.index') }}" class="px-6 py-2.5 border text-gray-700 rounded-md hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-6 py-2.5 bg-green-600 text-white rounded-md hover:bg-green-700">Cadastrar</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let cnaes = [];
    
    // Máscara de CPF
    const cpfDisplay = document.getElementById('cpf_display');
    const cpfHidden = document.getElementById('cpf');
    cpfDisplay.addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '');
        // Atualiza o campo hidden sem máscara
        cpfHidden.value = v;
        // Aplica máscara no display
        v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        e.target.value = v;
    });
    
    // Máscara de CEP
    const cepDisplay = document.getElementById('cep_display');
    const cepHidden = document.getElementById('cep');
    cepDisplay.addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '');
        // Atualiza o campo hidden sem máscara
        cepHidden.value = v;
        // Aplica máscara no display
        v = v.replace(/(\d{5})(\d)/, '$1-$2');
        e.target.value = v;
    });
    
    // Máscara Telefone
    document.getElementById('telefone').addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '').replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2');
        e.target.value = v;
    });
    
    // Consulta CEP
    cepDisplay.addEventListener('blur', function() {
        const cep = cepHidden.value; // Usa o valor sem máscara
        if (cep.length === 8) {
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(r => r.json())
                .then(d => {
                    if (!d.erro) {
                        document.getElementById('endereco').value = d.logradouro.toUpperCase();
                        document.getElementById('bairro').value = d.bairro.toUpperCase();
                        
                        // Seleciona a cidade (não desabilita para permitir envio)
                        const cidadeSelect = document.getElementById('cidade');
                        cidadeSelect.value = d.localidade.toUpperCase();
                        cidadeSelect.classList.add('bg-gray-100');
                    }
                });
        }
    });
    
    // Busca CNAE ao pressionar Enter
    document.getElementById('cnae_busca').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('btn_buscar_cnae').click();
        }
    });
    
    // Busca CNAE via API do IBGE
    document.getElementById('btn_buscar_cnae').addEventListener('click', function() {
        const codigo = document.getElementById('cnae_busca').value.trim();
        const erro = document.getElementById('cnae_erro');
        const btn = this;
        
        // Validação do formato
        if (codigo.length !== 7 || !/^\d+$/.test(codigo)) {
            erro.textContent = 'Digite um código CNAE válido (7 dígitos numéricos)';
            erro.classList.remove('hidden');
            return;
        }
        
        // Verifica se já foi adicionado
        if (cnaes.find(c => c.codigo === codigo)) {
            erro.textContent = 'Este CNAE já foi adicionado à lista';
            erro.classList.remove('hidden');
            return;
        }
        
        erro.classList.add('hidden');
        
        // Desabilita botão durante busca
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Buscando...';
        
        // Consulta API do IBGE
        fetch(`https://servicodados.ibge.gov.br/api/v2/cnae/subclasses/${codigo}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('CNAE não encontrado');
                }
                return response.json();
            })
            .then(data => {
                // API do IBGE retorna um objeto único (não array)
                if (data && data.id) {
                    // Adiciona à lista
                    cnaes.push({
                        codigo: data.id,
                        descricao: data.descricao,
                        grupo: data.classe && data.classe.grupo ? data.classe.grupo.descricao : null
                    });
                    
                    atualizarLista();
                    document.getElementById('cnae_busca').value = '';
                    
                    // Mensagem de sucesso
                    erro.textContent = '✓ CNAE adicionado com sucesso!';
                    erro.classList.remove('hidden', 'text-red-500');
                    erro.classList.add('text-green-600');
                    setTimeout(() => {
                        erro.classList.add('hidden');
                        erro.classList.remove('text-green-600');
                        erro.classList.add('text-red-500');
                    }, 3000);
                } else {
                    throw new Error('CNAE não encontrado');
                }
            })
            .catch(error => {
                console.error('Erro ao buscar CNAE:', error);
                erro.textContent = 'CNAE não encontrado na base do IBGE. Verifique o código.';
                erro.classList.remove('hidden');
            })
            .finally(() => {
                // Reabilita botão
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg> Buscar';
            });
    });
    
    function atualizarLista() {
        const lista = document.getElementById('cnaes_lista');
        if (cnaes.length === 0) {
            lista.innerHTML = '<p class="text-sm text-gray-500">Nenhum CNAE adicionado. Adicione pelo menos um.</p>';
        } else {
            lista.innerHTML = cnaes.map((c, i) => `
                <div class="flex items-start justify-between p-4 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-600 text-white">
                                ${c.codigo}
                            </span>
                            ${i === 0 ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Principal</span>' : ''}
                        </div>
                        <p class="text-sm font-medium text-gray-900">${c.descricao}</p>
                        ${c.grupo ? `<p class="text-xs text-gray-600 mt-1">Grupo: ${c.grupo}</p>` : ''}
                    </div>
                    <button type="button" onclick="removerCnae(${i})" 
                            class="ml-4 inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-700 bg-red-100 hover:bg-red-200 rounded-md transition-colors">
                        Remover
                    </button>
                </div>
            `).join('');
        }
        document.getElementById('cnaes_input').value = JSON.stringify(cnaes);
    }
    
    window.removerCnae = function(index) {
        cnaes.splice(index, 1);
        atualizarLista();
    };
});
</script>
@endsection
