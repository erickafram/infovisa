@extends('layouts.admin')

@section('title', 'Editar Pessoa Física')
@section('page-title', 'Editar Pessoa Física')

@section('content')
<div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Editar Pessoa Física</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $estabelecimento->nome_fantasia }}</p>
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

    <form method="POST" action="{{ route('admin.estabelecimentos.update', $estabelecimento->id) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="tipo_pessoa" value="fisica">
        <input type="hidden" name="tipo_setor" value="privado">

        {{-- 1. Dados Cadastrais --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">1. Dados Cadastrais</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CPF <span class="text-red-500">*</span></label>
                    <input type="text" id="cpf_display" value="{{ old('cpf', $estabelecimento->cpf_formatado) }}" placeholder="000.000.000-00" maxlength="14" required class="w-full px-3 py-2 border rounded-md bg-gray-100" readonly>
                    <input type="hidden" id="cpf" name="cpf" value="{{ old('cpf', $estabelecimento->cpf) }}">
                    <p class="text-xs text-gray-500 mt-1">CPF não pode ser alterado</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo <span class="text-red-500">*</span></label>
                    <input type="text" name="nome_completo" value="{{ old('nome_completo', $estabelecimento->nome_completo) }}" required class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">RG <span class="text-red-500">*</span></label>
                    <input type="text" name="rg" value="{{ old('rg', $estabelecimento->rg) }}" required class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Órgão Emissor <span class="text-red-500">*</span></label>
                    <input type="text" name="orgao_emissor" value="{{ old('orgao_emissor', $estabelecimento->orgao_emissor) }}" placeholder="Ex: SSP/TO" required class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia <span class="text-red-500">*</span></label>
                    <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia', $estabelecimento->nome_fantasia) }}" required class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-mail <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $estabelecimento->email) }}" required class="w-full px-3 py-2 border rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone <span class="text-red-500">*</span></label>
                    <input type="text" id="telefone" name="telefone" value="{{ old('telefone', $estabelecimento->telefone) }}" placeholder="(00) 00000-0000" maxlength="15" required class="w-full px-3 py-2 border rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Início de Funcionamento <span class="text-red-500">*</span></label>
                    <input type="date" name="data_inicio_funcionamento" value="{{ old('data_inicio_funcionamento', $estabelecimento->data_inicio_atividade ? $estabelecimento->data_inicio_atividade->format('Y-m-d') : '') }}" required class="w-full px-3 py-2 border rounded-md">
                </div>
            </div>
        </div>

        {{-- 2. Endereço --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">2. Endereço Completo</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CEP <span class="text-red-500">*</span></label>
                    <input type="text" id="cep_display" value="{{ old('cep', $estabelecimento->cep_formatado) }}" placeholder="00000-000" maxlength="9" required class="w-full px-3 py-2 border rounded-md">
                    <input type="hidden" id="cep" name="cep" value="{{ old('cep', $estabelecimento->cep) }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Endereço <span class="text-red-500">*</span></label>
                    <input type="text" id="endereco" name="endereco" value="{{ old('endereco', $estabelecimento->endereco) }}" required class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número <span class="text-red-500">*</span></label>
                    <input type="text" name="numero" value="{{ old('numero', $estabelecimento->numero) }}" required class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                    <input type="text" name="complemento" value="{{ old('complemento', $estabelecimento->complemento) }}" class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bairro <span class="text-red-500">*</span></label>
                    <input type="text" id="bairro" name="bairro" value="{{ old('bairro', $estabelecimento->bairro) }}" required class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cidade <span class="text-red-500">*</span></label>
                    <select id="cidade" name="cidade" required class="w-full px-3 py-2 border rounded-md">
                        <option value="">Selecione...</option>
                        @php
                            $cidadesTO = ['PALMAS', 'ARAGUAÍNA', 'GURUPI', 'PORTO NACIONAL', 'PARAÍSO DO TOCANTINS', 'COLINAS DO TOCANTINS', 'GUARAÍ', 'TOCANTINÓPOLIS', 'MIRACEMA DO TOCANTINS', 'ARAGUATINS'];
                        @endphp
                        @foreach($cidadesTO as $cidade)
                            <option value="{{ $cidade }}" {{ old('cidade', $estabelecimento->cidade) == $cidade ? 'selected' : '' }}>{{ $cidade }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UF <span class="text-red-500">*</span></label>
                    <input type="text" name="estado" value="TO" readonly class="w-full px-3 py-2 border rounded-md bg-gray-100">
                </div>
            </div>
        </div>

        <div class="flex justify-between pt-6 pb-8">
            <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}" class="px-6 py-2.5 border text-gray-700 rounded-md hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-md hover:bg-blue-700">Salvar Alterações</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara de CPF (readonly, apenas para exibição)
    const cpfDisplay = document.getElementById('cpf_display');
    
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
                        
                        // Seleciona a cidade e desabilita
                        const cidadeSelect = document.getElementById('cidade');
                        cidadeSelect.value = d.localidade.toUpperCase();
                        cidadeSelect.disabled = true;
                        cidadeSelect.classList.add('bg-gray-100', 'cursor-not-allowed');
                        
                        // Adiciona campo hidden para enviar o valor
                        let hiddenCidade = document.getElementById('cidade_hidden');
                        if (!hiddenCidade) {
                            hiddenCidade = document.createElement('input');
                            hiddenCidade.type = 'hidden';
                            hiddenCidade.name = 'cidade';
                            hiddenCidade.id = 'cidade_hidden';
                            cidadeSelect.parentNode.appendChild(hiddenCidade);
                        }
                        hiddenCidade.value = d.localidade.toUpperCase();
                    }
                });
        }
    });
});
</script>
@endsection
