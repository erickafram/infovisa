@extends('layouts.admin')

@section('title', 'Nova Mensagem do Suporte')
@section('page-title', 'Nova Mensagem do Suporte')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.configuracoes.chat-broadcast.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-500 to-green-600">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-semibold text-lg">Suporte InfoVISA</h3>
                    <p class="text-white/70 text-sm">Enviar mensagem para usuários</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.configuracoes.chat-broadcast.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            {{-- Níveis de Acesso --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Enviar para <span class="text-red-500">*</span>
                </label>
                
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="niveis_acesso[]" value="todos" class="w-4 h-4 text-green-600 rounded" onchange="toggleTodos(this)">
                        <div>
                            <span class="font-medium text-gray-900">Todos os usuários</span>
                            <p class="text-xs text-gray-500">Envia para todos os níveis de acesso</p>
                        </div>
                    </label>

                    <div class="border-t border-gray-200 pt-2 mt-2">
                        <p class="text-xs text-gray-500 mb-2 px-1">Ou selecione níveis específicos:</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="niveis-especificos">
                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="niveis_acesso[]" value="administrador" class="w-4 h-4 text-green-600 rounded nivel-especifico">
                                <span class="text-sm text-gray-700">Administrador</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="niveis_acesso[]" value="gestor_estadual" class="w-4 h-4 text-green-600 rounded nivel-especifico">
                                <span class="text-sm text-gray-700">Gestor Estadual</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="niveis_acesso[]" value="tecnico_estadual" class="w-4 h-4 text-green-600 rounded nivel-especifico">
                                <span class="text-sm text-gray-700">Técnico Estadual</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="niveis_acesso[]" value="gestor_municipal" class="w-4 h-4 text-green-600 rounded nivel-especifico">
                                <span class="text-sm text-gray-700">Gestor Municipal</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="niveis_acesso[]" value="tecnico_municipal" class="w-4 h-4 text-green-600 rounded nivel-especifico">
                                <span class="text-sm text-gray-700">Técnico Municipal</span>
                            </label>
                        </div>
                    </div>
                </div>

                @error('niveis_acesso')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Mensagem --}}
            <div>
                <label for="conteudo" class="block text-sm font-medium text-gray-700 mb-2">
                    Mensagem <span class="text-red-500">*</span>
                </label>
                <textarea id="conteudo" 
                          name="conteudo" 
                          rows="5"
                          required
                          placeholder="Digite a mensagem que será enviada aos usuários..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('conteudo') border-red-500 @enderror">{{ old('conteudo') }}</textarea>
                @error('conteudo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Arquivo --}}
            <div>
                <label for="arquivo" class="block text-sm font-medium text-gray-700 mb-2">
                    Anexar arquivo (opcional)
                </label>
                <input type="file" 
                       id="arquivo" 
                       name="arquivo"
                       accept="image/*,.pdf,.doc,.docx,.xls,.xlsx"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <p class="mt-1 text-xs text-gray-500">Imagens, PDF, Word ou Excel. Máximo 10MB.</p>
            </div>

            {{-- Preview --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-2">Preview da mensagem:</p>
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-900">Suporte InfoVISA</span>
                            <span class="text-xs text-gray-400 ml-2">Agora</span>
                        </div>
                    </div>
                    <p class="text-gray-700 text-sm" id="preview-texto">A mensagem aparecerá aqui...</p>
                </div>
            </div>

            {{-- Botões --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.configuracoes.chat-broadcast.index') }}" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Enviar Mensagem
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleTodos(checkbox) {
    const especificos = document.querySelectorAll('.nivel-especifico');
    especificos.forEach(el => {
        el.disabled = checkbox.checked;
        if (checkbox.checked) {
            el.checked = false;
        }
    });
}

document.getElementById('conteudo').addEventListener('input', function() {
    document.getElementById('preview-texto').textContent = this.value || 'A mensagem aparecerá aqui...';
});
</script>
@endsection
