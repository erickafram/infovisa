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
                    <p class="mt-1 text-xs text-gray-500">Digite o CEP para atualizar o endereço automaticamente</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Endereço <span class="text-red-500">*</span></label>
                    <input type="text" id="endereco" name="endereco" value="{{ old('endereco', $estabelecimento->endereco) }}" required class="w-full px-3 py-2 border rounded-md uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número <span class="text-red-500">*</span></label>
                    <input type="text" id="numero" name="numero" value="{{ old('numero', $estabelecimento->numero) }}" required class="w-full px-3 py-2 border rounded-md uppercase">
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
                    <input type="text" id="cidade" name="cidade" value="{{ old('cidade', $estabelecimento->cidade) }}" readonly required class="w-full px-3 py-2 border rounded-md bg-gray-100 uppercase">
                    <input type="hidden" id="codigo_municipio_ibge" name="codigo_municipio_ibge" value="{{ old('codigo_municipio_ibge', $estabelecimento->codigo_municipio_ibge) }}">
                    <p id="cidade_info" class="mt-1 text-xs text-gray-500">A cidade será atualizada automaticamente pelo CEP</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UF <span class="text-red-500">*</span></label>
                    <input type="text" id="estado" name="estado" value="{{ old('estado', $estabelecimento->estado ?? 'TO') }}" readonly class="w-full px-3 py-2 border rounded-md bg-gray-100">
                </div>
            </div>
            
            {{-- Alerta informativo sobre competência municipal --}}
            <div class="mt-4 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-900">Competência Municipal</h4>
                        <p class="text-xs text-blue-800 mt-1">
                            Estabelecimentos de pessoa física são de competência municipal. O município é determinado automaticamente pelo CEP informado.
                        </p>
                    </div>
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
            // Mostra loading
            cepDisplay.classList.add('bg-gray-100');
            
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(r => r.json())
                .then(d => {
                    if (!d.erro) {
                        // Preenche os campos de endereço
                        document.getElementById('endereco').value = d.logradouro ? d.logradouro.toUpperCase() : '';
                        document.getElementById('bairro').value = d.bairro ? d.bairro.toUpperCase() : '';
                        
                        // Preenche a cidade (readonly)
                        const cidadeInput = document.getElementById('cidade');
                        cidadeInput.value = d.localidade ? d.localidade.toUpperCase() : '';
                        
                        // Preenche o estado
                        const estadoInput = document.getElementById('estado');
                        estadoInput.value = d.uf ? d.uf.toUpperCase() : 'TO';
                        
                        // Preenche o código IBGE do município (importante para vinculação)
                        const codigoIbgeInput = document.getElementById('codigo_municipio_ibge');
                        codigoIbgeInput.value = d.ibge || '';
                        
                        // Atualiza mensagem informativa
                        const cidadeInfo = document.getElementById('cidade_info');
                        if (cidadeInfo) {
                            cidadeInfo.textContent = '✓ Cidade atualizada pelo CEP';
                            cidadeInfo.classList.remove('text-gray-500');
                            cidadeInfo.classList.add('text-green-600');
                        }
                        
                        // Foca no campo número
                        setTimeout(() => {
                            const numeroInput = document.getElementById('numero');
                            if (numeroInput) {
                                numeroInput.focus();
                            }
                        }, 100);
                        
                        // Validação para usuários municipais
                        @if(auth('interno')->check() && auth('interno')->user()->isMunicipal())
                            const municipioUsuario = '{{ auth('interno')->user()->municipioRelacionado->nome ?? '' }}';
                            
                            // Função para remover acentos
                            function removerAcentos(texto) {
                                return texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                            }
                            
                            // Normaliza os nomes para comparação
                            const cidadeEstabelecimento = removerAcentos(d.localidade.toUpperCase());
                            const municipioUsuarioNormalizado = removerAcentos(municipioUsuario.toUpperCase());
                            
                            if (cidadeEstabelecimento !== municipioUsuarioNormalizado) {
                                alert(`⚠️ ATENÇÃO!\n\nVocê só pode editar estabelecimentos do município de ${municipioUsuario}.\n\nO CEP informado pertence a ${d.localidade}.\n\nPor favor, verifique o CEP.`);
                                
                                // Restaura os valores originais
                                cidadeInput.value = '{{ $estabelecimento->cidade }}';
                                codigoIbgeInput.value = '{{ $estabelecimento->codigo_municipio_ibge }}';
                                document.getElementById('endereco').value = '{{ $estabelecimento->endereco }}';
                                document.getElementById('bairro').value = '{{ $estabelecimento->bairro }}';
                                cepDisplay.value = '{{ $estabelecimento->cep_formatado }}';
                                cepHidden.value = '{{ $estabelecimento->cep }}';
                                
                                if (cidadeInfo) {
                                    cidadeInfo.textContent = 'A cidade será atualizada automaticamente pelo CEP';
                                    cidadeInfo.classList.remove('text-green-600');
                                    cidadeInfo.classList.add('text-gray-500');
                                }
                            }
                        @endif
                    } else {
                        alert('CEP não encontrado. Verifique o número informado.');
                        
                        // Atualiza mensagem informativa
                        const cidadeInfo = document.getElementById('cidade_info');
                        if (cidadeInfo) {
                            cidadeInfo.textContent = '❌ CEP não encontrado';
                            cidadeInfo.classList.remove('text-gray-500', 'text-green-600');
                            cidadeInfo.classList.add('text-red-600');
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar CEP:', error);
                    alert('Erro ao buscar CEP. Tente novamente.');
                })
                .finally(() => {
                    cepDisplay.classList.remove('bg-gray-100');
                });
        }
    });
});
</script>
@endsection
