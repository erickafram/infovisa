@extends('layouts.admin')

@section('title', 'Editar Responsável')
@section('page-title', 'Editar Responsável')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('admin.responsaveis.show', $responsavel->id) }}" 
           class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Editar Responsável</h1>
            <p class="text-sm text-gray-600 mt-1">{{ $responsavel->nome }} - {{ $responsavel->cpf_formatado }}</p>
        </div>
    </div>

    {{-- Formulário --}}
    <form action="{{ route('admin.responsaveis.update', $responsavel->id) }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @csrf
        @method('PUT')

        {{-- Header do Card --}}
        <div class="bg-gradient-to-r {{ $responsavel->tipo === 'tecnico' ? 'from-green-50 to-emerald-50' : 'from-blue-50 to-indigo-50' }} px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 {{ $responsavel->tipo === 'tecnico' ? 'text-green-600' : 'text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Responsável {{ $responsavel->tipo === 'tecnico' ? 'Técnico' : 'Legal' }}
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

            {{-- CPF (somente leitura) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">CPF</label>
                <input type="text" value="{{ $responsavel->cpf_formatado }}" disabled
                       class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-500 font-mono">
                <p class="mt-1 text-xs text-gray-500">O CPF não pode ser alterado</p>
            </div>

            {{-- Dados Pessoais --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                        Nome Completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nome" id="nome" value="{{ old('nome', $responsavel->nome) }}" required
                           class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $responsavel->email) }}"
                           class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>

                <div>
                    <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                    <input type="text" name="telefone" id="telefone" value="{{ old('telefone', $responsavel->telefone_formatado) }}"
                           class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono transition-colors"
                           placeholder="(00) 00000-0000" maxlength="15">
                </div>
            </div>

            @if($responsavel->tipo === 'tecnico')
            {{-- Dados do Conselho --}}
            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Dados do Conselho Profissional</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="conselho" class="block text-sm font-medium text-gray-700 mb-2">Conselho</label>
                        <select name="conselho" id="conselho"
                                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="">Selecione</option>
                            @foreach(['CRF', 'CRM', 'CRMV', 'CRO', 'COREN', 'CRN', 'CRBIO', 'CRQ', 'CREA', 'OUTRO'] as $conselho)
                            <option value="{{ $conselho }}" {{ old('conselho', $responsavel->conselho) == $conselho ? 'selected' : '' }}>{{ $conselho }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="numero_registro_conselho" class="block text-sm font-medium text-gray-700 mb-2">Número do Registro</label>
                        <input type="text" name="numero_registro_conselho" id="numero_registro_conselho" 
                               value="{{ old('numero_registro_conselho', $responsavel->numero_registro_conselho) }}"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono transition-colors">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Carteirinha do Conselho</label>
                        @if($responsavel->carteirinha_conselho)
                        <div class="mb-3 p-3 bg-green-50 border border-green-200 rounded-lg flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm text-green-800">Carteirinha cadastrada</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ asset('storage/' . $responsavel->carteirinha_conselho) }}" target="_blank"
                                   class="text-sm text-blue-600 hover:text-blue-800">Ver</a>
                                <form method="POST" action="{{ route('admin.responsaveis.remover-carteirinha', $responsavel->id) }}" 
                                      onsubmit="return confirm('Remover carteirinha?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">Remover</button>
                                </form>
                            </div>
                        </div>
                        @endif
                        <input type="file" name="carteirinha_conselho" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        <p class="mt-1 text-xs text-gray-500">{{ $responsavel->carteirinha_conselho ? 'Envie um novo arquivo para substituir' : 'Formatos: PDF, JPG, PNG. Máx: 5MB' }}</p>
                    </div>
                </div>
            </div>
            @else
            {{-- Documento de Identificação --}}
            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Documento de Identificação</h4>
                
                @if($responsavel->documento_identificacao)
                <div class="mb-3 p-3 bg-green-50 border border-green-200 rounded-lg flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm text-green-800">Documento cadastrado</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ asset('storage/' . $responsavel->documento_identificacao) }}" target="_blank"
                           class="text-sm text-blue-600 hover:text-blue-800">Ver</a>
                        <form method="POST" action="{{ route('admin.responsaveis.remover-documento', $responsavel->id) }}" 
                              onsubmit="return confirm('Remover documento?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">Remover</button>
                        </form>
                    </div>
                </div>
                @endif
                <input type="file" name="documento_identificacao" accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="mt-1 text-xs text-gray-500">{{ $responsavel->documento_identificacao ? 'Envie um novo arquivo para substituir' : 'Formatos: PDF, JPG, PNG. Máx: 5MB' }}</p>
            </div>
            @endif
        </div>

        {{-- Botões --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
            <a href="{{ route('admin.responsaveis.show', $responsavel->id) }}" 
               class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-all">
                Salvar Alterações
            </button>
        </div>
    </form>
</div>

<script>
// Máscara para telefone
document.getElementById('telefone')?.addEventListener('input', function(e) {
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
