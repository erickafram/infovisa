@extends('layouts.admin')

@section('title', 'Editar Município')
@section('page-title', 'Editar Município')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="form-municipio" action="{{ route('admin.configuracoes.municipios.update', $municipio->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nome do Município <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="nome" 
                           value="{{ old('nome', $municipio->nome) }}" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase @error('nome') border-red-500 @enderror">
                    @error('nome')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Código IBGE <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="codigo_ibge" 
                           value="{{ old('codigo_ibge', $municipio->codigo_ibge) }}" 
                           required
                           maxlength="7"
                           placeholder="1721000"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('codigo_ibge') border-red-500 @enderror">
                    @error('codigo_ibge')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">7 dígitos do código IBGE</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        UF <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="uf" 
                           value="{{ old('uf', $municipio->uf) }}" 
                           required
                           maxlength="2"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase @error('uf') border-red-500 @enderror">
                    @error('uf')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Logomarca do Município
                    </label>
                    
                    @if($municipio->logomarca)
                        <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-start gap-4">
                                <img src="{{ asset($municipio->logomarca) }}" 
                                     alt="Logomarca de {{ $municipio->nome }}"
                                     class="w-32 h-32 object-contain bg-white border border-gray-300 rounded-lg p-2">
                                <div class="flex-1">
                                    <p class="text-sm text-gray-700 font-medium mb-2">Logomarca atual</p>
                                    <p class="text-xs text-gray-500 mb-3">Esta logomarca será exibida nos documentos digitais gerados por usuários deste município.</p>
                                    <label class="flex items-center" id="label-remover-logomarca">
                                        <input type="checkbox" 
                                               id="remover-logomarca-checkbox"
                                               name="remover_logomarca" 
                                               value="1"
                                               class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                        <span class="ml-2 text-sm text-red-600">Remover logomarca</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="flex items-center gap-3">
                        <div class="flex-1 cursor-pointer" onclick="document.getElementById('logomarca-input').click()">
                            <div class="flex items-center justify-center w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors">
                                <div class="text-center">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="mt-1 text-sm text-gray-600">
                                        <span class="font-medium text-blue-600">Clique para selecionar</span> ou arraste a imagem
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">PNG, JPG, JPEG ou SVG (máx. 2MB)</p>
                                    <p id="filename-display" class="mt-2 text-xs text-green-600 font-medium hidden"></p>
                                </div>
                            </div>
                        </div>
                        <input type="file" 
                               id="logomarca-input"
                               name="logomarca" 
                               accept="image/jpeg,image/png,image/jpg,image/svg+xml"
                               class="hidden"
                               onchange="previewLogo(event)">
                    </div>
                    
                    <div id="preview-container" class="hidden mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-700 font-medium mb-2">Prévia da nova logomarca:</p>
                        <img id="preview-image" src="" alt="Prévia" class="w-32 h-32 object-contain bg-white border border-gray-300 rounded-lg p-2">
                    </div>
                    
                    @error('logomarca')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="ativo" 
                               {{ old('ativo', $municipio->ativo) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Município ativo</span>
                    </label>
                </div>
            </div>

            <script>
            function previewLogo(event) {
                const file = event.target.files[0];
                console.log('Arquivo selecionado:', file);
                if (file) {
                    // Desmarca e DESABILITA o checkbox de remover logomarca
                    const removerCheckbox = document.getElementById('remover-logomarca-checkbox');
                    const labelRemover = document.getElementById('label-remover-logomarca');
                    if (removerCheckbox) {
                        removerCheckbox.checked = false;
                        removerCheckbox.disabled = true;
                        if (labelRemover) {
                            labelRemover.style.opacity = '0.5';
                            labelRemover.style.pointerEvents = 'none';
                        }
                    }
                    
                    // Mostra o nome do arquivo
                    const filenameDisplay = document.getElementById('filename-display');
                    filenameDisplay.textContent = '✓ Arquivo selecionado: ' + file.name;
                    filenameDisplay.classList.remove('hidden');
                    
                    // Mostra prévia
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('preview-image').src = e.target.result;
                        document.getElementById('preview-container').classList.remove('hidden');
                    }
                    reader.readAsDataURL(file);
                }
            }

            // Debug: Verificar se o arquivo está anexado antes de enviar
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('form-municipio');
                console.log('Form município encontrado:', form);
                
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const fileInput = document.getElementById('logomarca-input');
                        console.log('=== ENVIANDO FORMULÁRIO DE MUNICÍPIO ===');
                        console.log('Input file:', fileInput);
                        console.log('Arquivos anexados:', fileInput.files);
                        console.log('Quantidade de arquivos:', fileInput.files.length);
                        
                        if (fileInput.files.length > 0) {
                            console.log('✅ Nome do arquivo:', fileInput.files[0].name);
                            console.log('✅ Tamanho:', fileInput.files[0].size, 'bytes');
                            console.log('✅ Tipo:', fileInput.files[0].type);
                        } else {
                            console.warn('⚠️ NENHUM ARQUIVO ANEXADO!');
                        }
                        
                        // Verificar FormData que será enviado
                        const formData = new FormData(form);
                        console.log('📦 FormData entries:');
                        for (let pair of formData.entries()) {
                            if (pair[1] instanceof File) {
                                console.log('  ' + pair[0] + ': [FILE]', pair[1].name, pair[1].size + ' bytes');
                            } else {
                                console.log('  ' + pair[0] + ':', pair[1]);
                            }
                        }
                    });
                }
            });
            </script>

            <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.configuracoes.municipios.index') }}" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Atualizar Município
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
