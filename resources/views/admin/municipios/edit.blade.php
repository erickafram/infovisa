@extends('layouts.admin')

@section('title', 'Editar Município')
@section('page-title', 'Editar Município')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.configuracoes.municipios.update', $municipio->id) }}" method="POST" enctype="multipart/form-data">
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
                                    <label class="flex items-center">
                                        <input type="checkbox" 
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
                        <label class="flex-1 cursor-pointer">
                            <div class="flex items-center justify-center w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors">
                                <div class="text-center">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="mt-1 text-sm text-gray-600">
                                        <span class="font-medium text-blue-600">Clique para selecionar</span> ou arraste a imagem
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">PNG, JPG, JPEG ou SVG (máx. 2MB)</p>
                                </div>
                            </div>
                            <input type="file" 
                                   name="logomarca" 
                                   accept="image/jpeg,image/png,image/jpg,image/svg+xml"
                                   class="hidden"
                                   onchange="previewLogo(event)">
                        </label>
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
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('preview-image').src = e.target.result;
                        document.getElementById('preview-container').classList.remove('hidden');
                    }
                    reader.readAsDataURL(file);
                }
            }
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
