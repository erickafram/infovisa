@extends('layouts.company')

@section('title', 'Adicionar Responsável')
@section('page-title', 'Adicionar Responsável')

@section('content')
<div class="max-w-4xl mx-auto">
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
                    <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">
                        CPF <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="cpf" id="cpf" value="{{ old('cpf') }}" required
                           class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono transition-colors"
                           placeholder="000.000.000-00" maxlength="14">
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

                    <div class="md:col-span-2">
                        <label for="carteirinha_conselho" class="block text-sm font-medium text-gray-700 mb-2">
                            Carteirinha do Conselho (PDF ou Imagem)
                        </label>
                        <input type="file" name="carteirinha_conselho" id="carteirinha_conselho"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        <p class="mt-1 text-xs text-gray-500">Formatos aceitos: PDF, JPG, PNG. Tamanho máximo: 5MB</p>
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
                
                <div>
                    <label for="documento_identificacao" class="block text-sm font-medium text-gray-700 mb-2">
                        RG ou CNH (PDF ou Imagem)
                    </label>
                    <input type="file" name="documento_identificacao" id="documento_identificacao"
                           accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-xs text-gray-500">Formatos aceitos: PDF, JPG, PNG. Tamanho máximo: 5MB</p>
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
// Máscara para CPF
document.getElementById('cpf').addEventListener('input', function(e) {
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
});

// Máscara para telefone
document.getElementById('telefone').addEventListener('input', function(e) {
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
</script>
@endsection
