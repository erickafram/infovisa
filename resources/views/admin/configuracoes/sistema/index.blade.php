@extends('layouts.admin')

@section('title', 'Configurações do Sistema')
@section('page-title', 'Configurações Gerais do Sistema')

@section('content')
<div class="max-w-8xl mx-auto">
    
    {{-- Cabeçalho --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
            <div class="p-2 bg-purple-100 rounded-lg">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            Configurações do Sistema
        </h1>
        <p class="mt-2 text-gray-600">Gerencie as configurações globais do sistema INFOVISA</p>
    </div>

    {{-- Seção: Logomarca Estadual --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-white border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Logomarca Estadual
            </h2>
            <p class="text-sm text-gray-600 mt-1">Esta logomarca será exibida nos documentos digitais gerados por <strong>Gestores Estaduais</strong> e <strong>Técnicos Estaduais</strong></p>
        </div>

        <div class="p-6">
            <form action="{{ route('admin.configuracoes.sistema.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @if($logomarcaEstadual && $logomarcaEstadual->valor)
                    <div class="mb-6 p-4 bg-purple-50 rounded-lg border border-purple-200">
                        <div class="flex items-start gap-4">
                            <img src="{{ asset($logomarcaEstadual->valor) }}" 
                                 alt="Logomarca do Estado do Tocantins"
                                 class="w-40 h-40 object-contain bg-white border-2 border-purple-300 rounded-lg p-3 shadow-sm">
                            <div class="flex-1">
                                <p class="text-sm text-purple-900 font-semibold mb-2">
                                    <svg class="inline w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Logomarca Estadual Configurada
                                </p>
                                <p class="text-xs text-purple-700 mb-3">
                                    Esta logomarca aparecerá automaticamente nos documentos criados por:
                                </p>
                                <ul class="text-xs text-purple-700 space-y-1 mb-4">
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <strong>Gestor Estadual</strong>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <strong>Técnico Estadual</strong>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Usuários sem município vinculado
                                    </li>
                                </ul>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           name="remover_logomarca_estadual" 
                                           value="1"
                                           class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    <span class="ml-2 text-sm text-red-600 font-medium">Remover logomarca estadual</span>
                                </label>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-yellow-800">Nenhuma logomarca estadual configurada</p>
                                <p class="text-xs text-yellow-700 mt-1">
                                    Documentos criados por <strong>Gestores Estaduais</strong> e <strong>Técnicos Estaduais</strong> não terão logomarca até que você configure uma.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="space-y-4">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700 mb-2 block">
                            {{ $logomarcaEstadual && $logomarcaEstadual->valor ? 'Substituir Logomarca' : 'Fazer Upload da Logomarca' }}
                        </span>
                        <div class="flex items-center justify-center w-full px-6 py-8 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-400 hover:bg-purple-50 transition-colors cursor-pointer">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">
                                    <span class="font-semibold text-purple-600">Clique para selecionar</span> ou arraste a imagem
                                </p>
                                <p class="mt-1 text-xs text-gray-500">PNG, JPG, JPEG ou SVG (máx. 2MB)</p>
                                <p class="mt-2 text-xs text-gray-600 bg-gray-100 inline-block px-3 py-1 rounded-full">
                                    Recomendado: 400x400px ou maior
                                </p>
                            </div>
                        </div>
                        <input type="file" 
                               name="logomarca_estadual" 
                               accept="image/jpeg,image/png,image/jpg,image/svg+xml"
                               class="hidden"
                               onchange="previewLogoEstadual(event)">
                    </label>

                    <div id="preview-container-estadual" class="hidden p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-700 font-semibold mb-3">Prévia da nova logomarca:</p>
                        <img id="preview-image-estadual" src="" alt="Prévia" class="w-40 h-40 object-contain bg-white border border-gray-300 rounded-lg p-3 mx-auto">
                    </div>

                    @error('logomarca_estadual')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Voltar
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Salvar Configurações
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Seção: Assistente de IA --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-white border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                Assistente de IA
            </h2>
            <p class="text-sm text-gray-600 mt-1">Configure o assistente virtual que ajuda os usuários a navegar e usar o sistema</p>
        </div>

        <div class="p-6">
            <form action="{{ route('admin.configuracoes.sistema.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    {{-- Ativar/Desativar IA --}}
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <label class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Status do Assistente
                            </label>
                            <p class="text-xs text-gray-600 mt-1">Ative ou desative o chat de IA para todos os usuários internos</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="ia_ativa" 
                                   value="1"
                                   {{ $iaAtiva && $iaAtiva->valor === 'true' ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-900">
                                {{ $iaAtiva && $iaAtiva->valor === 'true' ? 'Ativo' : 'Inativo' }}
                            </span>
                        </label>
                    </div>

                    {{-- API Key --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            API Key (Together AI)
                        </label>
                        <input type="text" 
                               name="ia_api_key" 
                               value="{{ $iaApiKey->valor ?? '' }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Digite a chave de API">
                        <p class="text-xs text-gray-500 mt-1">Chave de autenticação para a API do Together AI</p>
                    </div>

                    {{-- API URL --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            URL da API
                        </label>
                        <input type="url" 
                               name="ia_api_url" 
                               value="{{ $iaApiUrl->valor ?? '' }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="https://api.together.xyz/v1/chat/completions">
                        <p class="text-xs text-gray-500 mt-1">Endpoint da API do Together AI</p>
                    </div>

                    {{-- Modelo --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Modelo de IA
                        </label>
                        <input type="text" 
                               name="ia_model" 
                               value="{{ $iaModel->valor ?? '' }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="meta-llama/Llama-3-70b-chat-hf">
                        <p class="text-xs text-gray-500 mt-1">Nome do modelo de linguagem a ser utilizado</p>
                    </div>

                    {{-- Busca na Internet --}}
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1">
                                Busca Complementar na Internet
                            </label>
                            <p class="text-xs text-gray-600 mt-1">Busca em sites oficiais (ANVISA, Diário Oficial) quando não houver documentos POPs relevantes</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="ia_busca_web" 
                                   value="1"
                                   {{ $iaBuscaWeb && $iaBuscaWeb->valor === 'true' ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-900">
                                {{ $iaBuscaWeb && $iaBuscaWeb->valor === 'true' ? 'Ativo' : 'Inativo' }}
                            </span>
                        </label>
                    </div>

                    {{-- Informações --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-2">O que o Assistente de IA pode fazer:</p>
                                <ul class="space-y-1 text-xs">
                                    <li>• Responder perguntas sobre funcionalidades do sistema</li>
                                    <li>• Orientar usuários sobre como realizar tarefas</li>
                                    <li>• Fornecer estatísticas e relatórios em tempo real</li>
                                    <li>• Consultar dados do banco (estabelecimentos, processos, documentos)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Salvar Configurações da IA
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Informações Adicionais --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-blue-800">
                <p class="font-semibold mb-2">Como funciona a seleção de logomarca nos documentos:</p>
                <ul class="space-y-1 text-xs">
                    <li>• <strong>Usuários Municipais</strong> (Gestor Municipal / Técnico Municipal): Usam a logomarca do município vinculado</li>
                    <li>• <strong>Usuários Estaduais</strong> (Gestor Estadual / Técnico Estadual): Usam a logomarca estadual configurada aqui</li>
                    <li>• <strong>Usuários sem município</strong>: Usam a logomarca estadual como padrão</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function previewLogoEstadual(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-image-estadual').src = e.target.result;
            document.getElementById('preview-container-estadual').classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
