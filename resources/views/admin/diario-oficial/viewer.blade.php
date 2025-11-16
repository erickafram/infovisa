@extends('layouts.admin')

@section('title', $title ?? 'Visualizador de PDF')
@section('page-title', $title ?? 'Visualizador de PDF')

@push('styles')
<style>
    .pdf-highlight {
        background-color: rgba(255, 255, 0, 0.4);
        border: 2px solid rgba(255, 200, 0, 0.8);
        position: absolute;
        pointer-events: none;
        animation: pulse 2s ease-in-out infinite;
    }
    
    .pdf-highlight.active {
        background-color: rgba(255, 0, 0, 0.4);
        border-color: rgba(255, 0, 0, 0.8);
        animation: none;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 0.4; }
        50% { opacity: 0.8; }
    }
    
    .pdf-page {
        margin: 20px auto;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        position: relative;
        background: white;
    }
    
    .page-number {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
        z-index: 10;
    }
    
    .highlight-layer {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 5;
    }
</style>
@endpush

@section('content')
<!-- Search Bar -->
<div class="bg-white border-b shadow-sm">
    <div class="px-4 py-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.diario-oficial.index') }}" 
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Voltar
            </a>
            
            <div class="flex-1">
                <div class="relative">
                    <input type="text" 
                           id="searchInput" 
                           value="{{ $searchText }}"
                           placeholder="Digite o texto para buscar no PDF..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <svg class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
            
            <button onclick="searchInPDF()" 
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
                Buscar
            </button>
            
            <button onclick="clearSearch()" 
                    class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors">
                Limpar
            </button>
            
            <a href="{{ $pdfUrl }}" 
               target="_blank"
               download
               class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                Download
            </a>
        </div>
    </div>
</div>

<!-- Search Results Info -->
<div id="searchResults" class="hidden bg-green-50 border-b border-green-200">
    <div class="px-4 py-3">
        <div class="flex items-center justify-between">
            <div id="resultsInfo" class="text-green-800 font-medium"></div>
            <div class="flex items-center gap-2">
                <button onclick="previousOccurrence()" 
                        class="p-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors"
                        title="Ocorrência anterior">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                    </svg>
                </button>
                <span id="occurrenceCounter" class="px-3 py-1 bg-white rounded-lg font-medium text-sm">-</span>
                <button onclick="nextOccurrence()" 
                        class="p-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors"
                        title="Próxima ocorrência">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <div id="contextsList" class="mt-3 space-y-2"></div>
    </div>
</div>

<div id="noResults" class="hidden bg-yellow-50 border-b border-yellow-200">
    <div class="px-4 py-3">
        <p class="text-yellow-800">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Texto não encontrado no PDF. O documento pode estar em formato de imagem (escaneado).
        </p>
    </div>
</div>

<!-- Loading -->
<div id="loading" class="flex items-center justify-center py-16">
    <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-indigo-600 mb-4"></div>
        <p class="text-gray-600 text-lg font-medium">Carregando PDF...</p>
        <p class="text-gray-500 text-sm mt-2">Renderizando páginas...</p>
    </div>
</div>

<!-- PDF Container -->
<div id="pdfContainer" class="hidden max-w-8xl mx-auto px-4 py-6">
    <div id="pdfPages"></div>
</div>
@endsection

@push('scripts')
<script>
    const pdfUrl = @json($pdfUrl);
    const initialSearchText = @json($searchText ?? '');
    const proxyUrl = "{{ route('admin.diario-oficial.pdf.proxy') }}";
    const searchApiUrl = "{{ route('admin.diario-oficial.pdf.search') }}";
    const csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{ asset('js/pdf-viewer-search.js') }}"></script>
@endpush
