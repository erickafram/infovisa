@extends('layouts.admin')

@section('title', 'Novo Tipo de Setor')

@section('content')
<div class="container-fluid px-4 py-6">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.configuracoes.tipo-setores.index') }}" 
               class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Novo Tipo de Setor</h1>
                <p class="text-sm text-gray-600 mt-1">Cadastre um novo tipo de setor e defina os níveis de acesso</p>
            </div>
        </div>
    </div>

    {{-- Formulário --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.configuracoes.tipo-setores.store') }}" class="space-y-6">
            @csrf

            {{-- Informações Básicas --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    Informações Básicas
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Nome --}}
                    <div class="md:col-span-2">
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">
                            Nome do Setor <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nome" 
                               name="nome" 
                               value="{{ old('nome') }}"
                               required
                               placeholder="Ex: Vigilância Sanitária"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nome') border-red-500 @enderror">
                        @error('nome')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Código --}}
                    <div>
                        <label for="codigo" class="block text-sm font-medium text-gray-700 mb-1">
                            Código <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="codigo" 
                               name="codigo" 
                               value="{{ old('codigo') }}"
                               required
                               placeholder="Ex: vigilancia_sanitaria"
                               maxlength="50"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm @error('codigo') border-red-500 @enderror">
                        @error('codigo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Use apenas letras minúsculas, números e underscore (_)</p>
                    </div>

                    {{-- Status --}}
                    <div class="flex items-center pt-6">
                        <input type="checkbox" 
                               id="ativo" 
                               name="ativo" 
                               value="1"
                               {{ old('ativo', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="ativo" class="ml-2 text-sm font-medium text-gray-700">
                            Setor ativo
                        </label>
                    </div>

                    {{-- Descrição --}}
                    <div class="md:col-span-2">
                        <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">
                            Descrição
                        </label>
                        <textarea id="descricao" 
                                  name="descricao" 
                                  rows="3"
                                  placeholder="Descreva as atribuições e responsabilidades deste setor..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('descricao') border-red-500 @enderror">{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Níveis de Acesso --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2 pb-2 border-b border-gray-200">
                    Níveis de Acesso Permitidos
                </h2>
                <p class="text-sm text-gray-600 mb-4">
                    Selecione quais níveis de acesso poderão utilizar este setor. Se nenhum for selecionado, o setor estará disponível para todos os níveis.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($niveisAcesso as $nivel)
                        <label class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                            <input type="checkbox" 
                                   name="niveis_acesso[]" 
                                   value="{{ $nivel->value }}"
                                   {{ is_array(old('niveis_acesso')) && in_array($nivel->value, old('niveis_acesso')) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5">
                            <div class="ml-3">
                                <span class="text-sm font-medium text-gray-900">{{ $nivel->label() }}</span>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $nivel->descricao() }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
                
                @error('niveis_acesso')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Botões --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.configuracoes.tipo-setores.index') }}" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Criar Tipo de Setor
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Gera código automaticamente baseado no nome
document.getElementById('nome').addEventListener('input', function(e) {
    const codigoInput = document.getElementById('codigo');
    
    // Só gera automaticamente se o campo código estiver vazio
    if (!codigoInput.value || codigoInput.dataset.autoGenerated === 'true') {
        let codigo = e.target.value
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '') // Remove acentos
            .replace(/[^a-z0-9\s]/g, '') // Remove caracteres especiais
            .replace(/\s+/g, '_') // Substitui espaços por underscore
            .substring(0, 50); // Limita a 50 caracteres
        
        codigoInput.value = codigo;
        codigoInput.dataset.autoGenerated = 'true';
    }
});

// Marca que o código foi editado manualmente
document.getElementById('codigo').addEventListener('input', function() {
    if (this.value) {
        this.dataset.autoGenerated = 'false';
    }
});
</script>
@endsection
