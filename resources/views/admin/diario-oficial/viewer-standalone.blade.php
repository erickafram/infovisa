<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - Visualizador PDF</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    
    <style>
        .pdf-highlight {
            background-color: rgba(255, 255, 0, 0.3);
            border: none;
            position: absolute;
            pointer-events: auto;
            cursor: pointer;
            animation: pulse 2s ease-in-out infinite;
            z-index: 15;
            border-radius: 1px;
            box-shadow: none;
            transition: all 0.2s ease;
        }
        
        .pdf-highlight:hover {
            background-color: rgba(255, 255, 0, 0.4);
            border: none;
            box-shadow: none;
        }
        
        .pdf-highlight.active {
            background-color: rgba(255, 255, 0, 0.5);
            border: none;
            animation: none;
            box-shadow: none;
            border-radius: 1px;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 0.9; }
        }
        
        .pdf-page {
            margin: 20px auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            background: white;
            border: 1px solid #e5e7eb;
            display: inline-block;
        }
        
        .pdf-page canvas {
            display: block;
            position: relative;
            z-index: 1;
        }
        
        .page-number {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: bold;
            z-index: 20;
            font-size: 14px;
        }
        
        .highlight-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 10;
        }
        
        .text-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 2;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header + Search Bar - STICKY JUNTOS -->
    <div class="sticky top-0 z-50 bg-white shadow-lg">
        <!-- Header -->
        <div class="border-b">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h1 class="text-xl font-bold text-gray-900">{{ $title }}</h1>
                        <p class="text-sm text-gray-600">Visualizador de PDF com Busca Avançada</p>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <a href="{{ route('admin.diario-oficial.index') }}" 
                           class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Voltar
                        </a>
                        <a href="{{ $pdfUrl }}" 
                           target="_blank"
                           download
                           class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                            <i class="fas fa-download mr-2"></i>Download
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="border-b">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <input type="text" 
                                   id="searchInput" 
                                   value="{{ $searchText }}"
                                   placeholder="Digite o texto para buscar no PDF..."
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                    
                    <button onclick="searchInPDF()" 
                            id="searchButton"
                            class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
                        <i class="fas fa-search mr-2"></i>Buscar
                    </button>
                    
                    <button onclick="clearSearch()" 
                            class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors">
                        <i class="fas fa-eraser mr-2"></i>Limpar
                    </button>
                </div>
            </div>
        </div>

        <!-- Search Results Info - DENTRO DO STICKY -->
        <div id="searchResults" class="hidden bg-green-50 border-b border-green-200">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div id="resultsInfo" class="text-green-800 font-medium"></div>
                    <div class="flex items-center gap-2">
                        <button onclick="previousOccurrence()" 
                                id="prevBtn"
                                class="p-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors disabled:opacity-50"
                                title="Ocorrência anterior (Shift+F3)">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                        <span id="occurrenceCounter" class="px-3 py-1 bg-white rounded-lg font-medium border">-</span>
                        <button onclick="nextOccurrence()" 
                                id="nextBtn"
                                class="p-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors disabled:opacity-50"
                                title="Próxima ocorrência (F3)">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- No Results - DENTRO DO STICKY -->
        <div id="noResults" class="hidden bg-yellow-50 border-b border-yellow-200">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <p class="text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Texto não encontrado no PDF. Verifique se o texto está correto ou se o documento não está escaneado.
                </p>
            </div>
        </div>
    </div>
    <!-- FIM DO BLOCO STICKY -->

    <!-- Loading -->
    <div id="loading" class="flex items-center justify-center py-16">
        <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-indigo-600 mb-4"></div>
            <p class="text-gray-600 text-lg font-medium">Carregando PDF...</p>
            <p class="text-gray-500 text-sm mt-2">Renderizando páginas e preparando busca...</p>
        </div>
    </div>

    <!-- PDF Container -->
    <div id="pdfContainer" class="hidden max-w-8xl mx-auto px-4 py-6">
        <div id="pdfPages"></div>
    </div>

    <!-- Navigation -->
    <div id="navigation" class="hidden fixed bottom-6 right-6 bg-white rounded-lg shadow-lg p-4 border">
        <div class="flex items-center gap-3">
            <button onclick="scrollToPreviousPage()" 
                    class="p-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                <i class="fas fa-chevron-up"></i>
            </button>
            <div class="text-center">
                <div id="pageInfo" class="px-4 py-2 bg-gray-100 rounded-lg font-medium">-</div>
                <div class="text-xs text-gray-500 mt-1">Páginas</div>
            </div>
            <button onclick="scrollToNextPage()" 
                    class="p-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
    </div>

    <script>
        // Configurar PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        // Variáveis globais
        let pdfDoc = null;
        let totalPages = 0;
        let currentPage = 1;
        let allHighlights = [];
        let currentOccurrence = 0;
        let isSearching = false;

        const pdfUrl = @json($pdfUrl);
        const initialSearchText = @json($searchText);

        console.log('PDF URL:', pdfUrl);
        console.log('Search Text:', initialSearchText);

        // Inicializar
        document.addEventListener('DOMContentLoaded', async () => {
            await loadPDF();
            
            if (initialSearchText && initialSearchText.trim()) {
                document.getElementById('searchInput').value = initialSearchText;
                setTimeout(() => {
                    searchInPDF();
                }, 1000); // Aguardar PDF carregar
            }
        });

        /**
         * Carrega e renderiza o PDF
         */
        async function loadPDF() {
            try {
                console.log('Iniciando carregamento do PDF...');
                
                // URL do proxy corrigida
                const proxyUrl = `/admin/diario-oficial/pdf/proxy?url=${encodeURIComponent(pdfUrl)}`;
                console.log('Proxy URL:', proxyUrl);
                
                const loadingTask = pdfjsLib.getDocument({
                    url: proxyUrl,
                    cMapUrl: 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/cmaps/',
                    cMapPacked: true
                });

                pdfDoc = await loadingTask.promise;
                totalPages = pdfDoc.numPages;

                console.log('PDF carregado com sucesso:', totalPages, 'páginas');

                document.getElementById('loading').classList.add('hidden');
                document.getElementById('pdfContainer').classList.remove('hidden');
                document.getElementById('navigation').classList.remove('hidden');

                await renderAllPages();
                updatePageInfo();

            } catch (error) {
                console.error('Erro ao carregar PDF:', error);
                showError('Erro ao carregar PDF: ' + error.message);
            }
        }

        /**
         * Renderiza todas as páginas do PDF
         */
        async function renderAllPages() {
            const container = document.getElementById('pdfPages');
            container.innerHTML = '';

            console.log('Renderizando', totalPages, 'páginas...');

            for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                try {
                    const page = await pdfDoc.getPage(pageNum);
                    const scale = 1.5;
                    const viewport = page.getViewport({ scale });

                    // Container da página
                    const pageDiv = document.createElement('div');
                    pageDiv.className = 'pdf-page';
                    pageDiv.id = `page-${pageNum}`;

                    // Canvas para renderizar o PDF
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    canvas.className = 'w-full';

                    // Layer para highlights
                    const highlightLayer = document.createElement('div');
                    highlightLayer.className = 'highlight-layer';
                    highlightLayer.id = `highlight-layer-${pageNum}`;

                    // Layer para texto (invisível, usado para busca)
                    const textLayer = document.createElement('div');
                    textLayer.className = 'text-layer';
                    textLayer.id = `text-layer-${pageNum}`;

                    // Número da página
                    const pageNumber = document.createElement('div');
                    pageNumber.className = 'page-number';
                    pageNumber.textContent = `Página ${pageNum} de ${totalPages}`;

                    pageDiv.appendChild(canvas);
                    pageDiv.appendChild(textLayer);
                    pageDiv.appendChild(highlightLayer);
                    pageDiv.appendChild(pageNumber);
                    container.appendChild(pageDiv);

                    // Renderizar página
                    await page.render({
                        canvasContext: context,
                        viewport: viewport
                    }).promise;

                    console.log(`Página ${pageNum} renderizada`);
                } catch (error) {
                    console.error(`Erro ao renderizar página ${pageNum}:`, error);
                }
            }

            console.log('Todas as páginas foram renderizadas');
        }

        /**
         * Busca texto no PDF usando PDF.js
         */
        async function searchInPDF() {
            const searchText = document.getElementById('searchInput').value.trim();
            
            if (!searchText) {
                alert('Digite um texto para buscar');
                return;
            }

            if (isSearching) {
                console.log('Busca já em andamento...');
                return;
            }

            console.log('Iniciando busca por:', searchText);
            isSearching = true;

            // Limpar busca anterior
            clearHighlights();
            hideSearchResults();

            // Mostrar loading na busca
            showSearchLoading();

            try {
                allHighlights = [];
                let totalOccurrences = 0;

                // Buscar em cada página usando PDF.js
                for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                    const page = await pdfDoc.getPage(pageNum);
                    const textContent = await page.getTextContent();
                    const viewport = page.getViewport({ scale: 1.5 });
                    const highlightLayer = document.getElementById(`highlight-layer-${pageNum}`);

                    if (!highlightLayer) continue;

                    let pageOccurrences = 0;

                    // Buscar texto nos itens da página
                    textContent.items.forEach((item, itemIndex) => {
                        const text = item.str;
                        const searchLower = searchText.toLowerCase();
                        const textLower = text.toLowerCase();
                        
                        // Verificar se o item contém o texto buscado
                        if (textLower.includes(searchLower)) {
                            // Encontrar todas as ocorrências dentro deste item
                            const regex = new RegExp(searchLower.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
                            let match;
                            
                            while ((match = regex.exec(text)) !== null) {
                                const startIndex = match.index;
                                const endIndex = startIndex + match[0].length;
                                
                                // Calcular a largura proporcional baseada nos caracteres
                                const charWidth = item.width / text.length;
                                const highlightWidth = charWidth * match[0].length;
                                const highlightLeft = item.transform[4] + (charWidth * startIndex);
                                
                                // Calcular posição do highlight
                                const transform = pdfjsLib.Util.transform(
                                    viewport.transform,
                                    [highlightWidth, 0, 0, item.height, highlightLeft, item.transform[5]]
                                );

                                const highlight = document.createElement('div');
                                highlight.className = 'pdf-highlight';
                                highlight.dataset.page = pageNum;
                                highlight.dataset.occurrence = allHighlights.length;
                                highlight.title = `Clique para navegar - "${match[0]}"`;
                                
                                // Posicionamento preciso
                                highlight.style.left = `${transform[4]}px`;
                                highlight.style.top = `${viewport.height - transform[5] - item.height}px`;
                                highlight.style.width = `${transform[0]}px`;
                                highlight.style.height = `${item.height}px`;

                                // Event listener para clique
                                highlight.addEventListener('click', () => {
                                    setActiveOccurrence(parseInt(highlight.dataset.occurrence));
                                });

                                // Permitir interação
                                highlight.style.pointerEvents = 'auto';

                                highlightLayer.appendChild(highlight);
                                allHighlights.push({
                                    element: highlight,
                                    page: pageNum,
                                    index: allHighlights.length,
                                    text: match[0]
                                });
                                
                                pageOccurrences++;
                                totalOccurrences++;
                            }
                        }
                    });

                    if (pageOccurrences > 0) {
                        console.log(`Página ${pageNum}: ${pageOccurrences} ocorrências encontradas`);
                    }
                }

                console.log(`Busca concluída: ${totalOccurrences} ocorrências em ${allHighlights.length} elementos`);

                if (allHighlights.length > 0) {
                    showSearchResults(totalOccurrences, searchText);
                    setActiveOccurrence(0); // Ir para primeira ocorrência
                } else {
                    showNoResults();
                }

            } catch (error) {
                console.error('Erro na busca:', error);
                alert('Erro ao buscar no PDF: ' + error.message);
            } finally {
                hideSearchLoading();
                isSearching = false;
            }
        }

        /**
         * Define ocorrência ativa
         */
        function setActiveOccurrence(index) {
            if (index < 0 || index >= allHighlights.length) return;

            console.log(`Navegando para ocorrência ${index + 1} de ${allHighlights.length}`);

            // Remover classe active de todos
            allHighlights.forEach(h => h.element.classList.remove('active'));

            // Adicionar classe active ao atual
            const current = allHighlights[index];
            current.element.classList.add('active');
            currentOccurrence = index;

            // Scroll para a ocorrência
            const pageElement = document.getElementById(`page-${current.page}`);
            if (pageElement) {
                pageElement.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });

                // Destacar página temporariamente
                pageElement.style.border = '3px solid #10b981';
                setTimeout(() => {
                    pageElement.style.border = '1px solid #e5e7eb';
                }, 2000);
            }

            // Atualizar contador
            updateOccurrenceCounter();
        }

        /**
         * Próxima ocorrência
         */
        function nextOccurrence() {
            if (allHighlights.length === 0) return;
            const next = (currentOccurrence + 1) % allHighlights.length;
            setActiveOccurrence(next);
        }

        /**
         * Ocorrência anterior
         */
        function previousOccurrence() {
            if (allHighlights.length === 0) return;
            const prev = currentOccurrence === 0 ? allHighlights.length - 1 : currentOccurrence - 1;
            setActiveOccurrence(prev);
        }

        /**
         * Limpa todos os highlights
         */
        function clearHighlights() {
            console.log('Limpando highlights...');
            for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                const highlightLayer = document.getElementById(`highlight-layer-${pageNum}`);
                if (highlightLayer) {
                    highlightLayer.innerHTML = '';
                }
            }
            allHighlights = [];
            currentOccurrence = 0;
        }

        /**
         * Limpa busca
         */
        function clearSearch() {
            document.getElementById('searchInput').value = '';
            clearHighlights();
            hideSearchResults();
        }

        /**
         * Mostra resultados da busca
         */
        function showSearchResults(totalOccurrences, searchText) {
            const resultsDiv = document.getElementById('searchResults');
            const resultsInfo = document.getElementById('resultsInfo');

            // Encontrar o primeiro trecho encontrado
            let firstSnippet = '';
            if (allHighlights.length > 0) {
                firstSnippet = allHighlights[0].text;
                // Limitar tamanho do trecho se for muito grande
                if (firstSnippet.length > 100) {
                    firstSnippet = firstSnippet.substring(0, 100) + '...';
                }
            }

            resultsInfo.innerHTML = `
                <div class="flex items-center justify-between w-full">
                    <div>
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>${totalOccurrences}</strong> ocorrência(s) de "<strong>${searchText}</strong>" encontrada(s)
                    </div>
                    ${firstSnippet ? `
                        <div class="text-sm text-green-700 ml-4">
                            <i class="fas fa-quote-left mr-1"></i>
                            <em>"${firstSnippet}"</em>
                        </div>
                    ` : ''}
                </div>
            `;

            resultsDiv.classList.remove('hidden');
            updateOccurrenceCounter();
        }

        /**
         * Mostra que não há resultados
         */
        function showNoResults() {
            document.getElementById('noResults').classList.remove('hidden');
        }

        /**
         * Esconde resultados da busca
         */
        function hideSearchResults() {
            document.getElementById('searchResults').classList.add('hidden');
            document.getElementById('noResults').classList.add('hidden');
        }

        /**
         * Atualiza contador de ocorrências
         */
        function updateOccurrenceCounter() {
            const counter = document.getElementById('occurrenceCounter');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            
            if (allHighlights.length > 0) {
                counter.textContent = `${currentOccurrence + 1} de ${allHighlights.length}`;
                prevBtn.disabled = false;
                nextBtn.disabled = false;
            } else {
                counter.textContent = '-';
                prevBtn.disabled = true;
                nextBtn.disabled = true;
            }
        }

        /**
         * Mostra loading da busca
         */
        function showSearchLoading() {
            const button = document.getElementById('searchButton');
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Buscando...';
            button.disabled = true;
        }

        /**
         * Esconde loading da busca
         */
        function hideSearchLoading() {
            const button = document.getElementById('searchButton');
            button.innerHTML = '<i class="fas fa-search mr-2"></i>Buscar';
            button.disabled = false;
        }

        /**
         * Scroll para página específica
         */
        function scrollToPage(pageNum) {
            const pageElement = document.getElementById(`page-${pageNum}`);
            if (pageElement) {
                pageElement.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }
        }

        /**
         * Navegação entre páginas
         */
        function scrollToPreviousPage() {
            if (currentPage > 1) {
                currentPage--;
                scrollToPage(currentPage);
            }
        }

        function scrollToNextPage() {
            if (currentPage < totalPages) {
                currentPage++;
                scrollToPage(currentPage);
            }
        }

        /**
         * Atualiza informações da página
         */
        function updatePageInfo() {
            document.getElementById('pageInfo').textContent = `${totalPages}`;
        }

        /**
         * Mostra erro
         */
        function showError(message) {
            document.getElementById('loading').innerHTML = `
                <div class="text-center">
                    <i class="fas fa-exclamation-circle text-red-500 text-6xl mb-4"></i>
                    <p class="text-red-800 font-semibold text-xl mb-2">Erro ao carregar PDF</p>
                    <p class="text-red-600 mb-4">${message}</p>
                    <a href="${pdfUrl}" target="_blank" 
                       class="inline-block px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">
                        <i class="fas fa-external-link-alt mr-2"></i>Abrir PDF em nova aba
                    </a>
                </div>
            `;
        }

        // Event listeners para teclado
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.getElementById('searchInput').focus();
            } else if (e.key === 'F3' || (e.ctrlKey && e.key === 'g')) {
                e.preventDefault();
                if (e.shiftKey) {
                    previousOccurrence();
                } else {
                    nextOccurrence();
                }
            } else if (e.key === 'Escape') {
                clearSearch();
            }
        });

        // Enter no campo de busca
        document.getElementById('searchInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                searchInPDF();
            }
        });

        // Detectar scroll para atualizar página atual
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                const scrollTop = window.pageYOffset;
                const windowHeight = window.innerHeight;
                const centerY = scrollTop + windowHeight / 2;

                for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                    const pageElement = document.getElementById(`page-${pageNum}`);
                    if (pageElement) {
                        const rect = pageElement.getBoundingClientRect();
                        const pageTop = rect.top + scrollTop;
                        const pageBottom = pageTop + rect.height;

                        if (centerY >= pageTop && centerY <= pageBottom) {
                            currentPage = pageNum;
                            break;
                        }
                    }
                }
            }, 100);
        });
    </script>
</body>
</html>
