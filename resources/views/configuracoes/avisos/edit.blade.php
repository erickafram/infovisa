@extends('layouts.admin')

@section('title', 'Editar Aviso')
@section('page-title', 'Editar Aviso')

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('admin.configuracoes.avisos.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para Avisos
        </a>
    </div>

    <form action="{{ route('admin.configuracoes.avisos.update', $aviso) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf
        @method('PUT')

        <div class="space-y-5">
            {{-- Título --}}
            <div>
                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
                <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $aviso->titulo) }}" required maxlength="255"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                       placeholder="Ex: Manutenção programada">
                @error('titulo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Mensagem --}}
            <div>
                <label for="mensagem" class="block text-sm font-medium text-gray-700 mb-1">Mensagem *</label>
                <textarea name="mensagem" id="mensagem" rows="3" required maxlength="1000"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                          placeholder="Descreva o aviso de forma clara e objetiva">{{ old('mensagem', $aviso->mensagem) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Máximo 1000 caracteres</p>
                @error('mensagem')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Link --}}
            <div>
                <label for="link" class="block text-sm font-medium text-gray-700 mb-1">Link (opcional)</label>
                <input type="text" name="link" id="link" value="{{ old('link', $aviso->link) }}" maxlength="500"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
                       placeholder="Ex: /admin/documentos-pendentes ou https://exemplo.com">
                <p class="mt-1 text-xs text-gray-500">URL para o usuário acessar ao clicar no aviso</p>
                @error('link')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tipo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo *</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="tipo" value="info" {{ old('tipo', $aviso->tipo) === 'info' ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Informativo</span>
                        <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700">Info</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="tipo" value="aviso" {{ old('tipo', $aviso->tipo) === 'aviso' ? 'checked' : '' }}
                               class="w-4 h-4 text-amber-600 border-gray-300 focus:ring-amber-500">
                        <span class="text-sm text-gray-700">Aviso</span>
                        <span class="px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-700">Aviso</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="tipo" value="urgente" {{ old('tipo', $aviso->tipo) === 'urgente' ? 'checked' : '' }}
                               class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                        <span class="text-sm text-gray-700">Urgente</span>
                        <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700">Urgente</span>
                    </label>
                </div>
                @error('tipo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Níveis de Acesso --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Exibir para *</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($niveisAcesso as $value => $label)
                    @if($value !== 'administrador')
                    <label class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="niveis_acesso[]" value="{{ $value }}"
                               {{ in_array($value, old('niveis_acesso', $aviso->niveis_acesso ?? [])) ? 'checked' : '' }}
                               class="w-4 h-4 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500">
                        <span class="text-sm text-gray-700">{{ $label }}</span>
                    </label>
                    @endif
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-gray-500">Selecione os níveis de acesso que verão este aviso</p>
                @error('niveis_acesso')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Data de Expiração --}}
            <div>
                <label for="data_expiracao" class="block text-sm font-medium text-gray-700 mb-1">Data de Expiração</label>
                <input type="date" name="data_expiracao" id="data_expiracao" 
                       value="{{ old('data_expiracao', $aviso->data_expiracao?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                <p class="mt-1 text-xs text-gray-500">Deixe em branco para aviso sem expiração</p>
                @error('data_expiracao')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Ativo --}}
            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="ativo" value="1" {{ old('ativo', $aviso->ativo) ? 'checked' : '' }}
                           class="w-4 h-4 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500">
                    <span class="text-sm text-gray-700">Aviso ativo</span>
                </label>
            </div>
        </div>

        {{-- Info --}}
        <div class="mt-6 p-3 bg-gray-50 rounded-lg text-xs text-gray-500">
            Criado por {{ $aviso->criador->nome ?? 'N/D' }} em {{ $aviso->created_at->format('d/m/Y H:i') }}
        </div>

        {{-- Botões --}}
        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.configuracoes.avisos.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-cyan-600 rounded-lg hover:bg-cyan-700 transition">
                Salvar Alterações
            </button>
        </div>
    </form>
</div>
@endsection
