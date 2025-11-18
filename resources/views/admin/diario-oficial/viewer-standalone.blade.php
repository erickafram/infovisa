<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - Visualizador PDF</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf_viewer.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    
    <style>
        /* Ajustes para TextLayer e Highlights */
        .textLayer {
            opacity: 0.2; /* Útil para debug, em produção pode ser 0 ou muito baixo */
            mix-blend-mode: multiply;
        }
        
        .pdf-highlight {
            background-color: rgba(255, 255, 0, 0.4);
            position: absolute;
            z-index: 5;
            cursor: pointer;
            mix-blend-mode: multiply;
            /* Animação suave */
            transition: background-color 0.2s;
        }
        
        .pdf-highlight:hover {
            background-color: rgba(255, 255, 0, 0.6);
            outline: 1px solid rgba(200, 200, 0, 0.8);
        }
        
        .pdf-highlight.active {
            background-color: rgba(255, 165, 0, 0.6); /* Laranja para o ativo */
            border: 2px solid rgba(255, 140, 0, 1);
            z-index: 10;
            animation: pulse-highlight 2s infinite;
        }

        @keyframes pulse-highlight {
            0% { box-shadow: 0 0 0 0 rgba(255, 165, 0, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(255, 165, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 165, 0, 0); }
        }
        
        .pdf-page {
            margin: 20px auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            background: white;
            border: 1px solid #e5e7eb;
            /* Importante para posicionamento absoluto dos filhos */
        }
        
        .highlight-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none; /* Permite clicar no texto abaixo se necessário */
            z-index: 5;
        }
        
        /* Garantir que highlights recebam cliques */
        .pdf-highlight {
            pointer-events: auto;
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

            console.log('Renderizando', totalPages, 'páginas com TextLayer...');

            for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                try {
                    const page = await pdfDoc.getPage(pageNum);
                    const scale = 1.5;
                    const viewport = page.getViewport({ scale });

                    // Container da página
                    const pageDiv = document.createElement('div');
                    pageDiv.className = 'pdf-page';
                    pageDiv.id = `page-${pageNum}`;
                    pageDiv.style.width = `${viewport.width}px`;
                    pageDiv.style.height = `${viewport.height}px`;

                    // Canvas para renderizar o PDF
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    canvas.className = 'absolute inset-0 z-0'; // Z-index 0

                    // Layer para texto (classe padrão do PDF.js)
                    const textLayerDiv = document.createElement('div');
                    textLayerDiv.className = 'textLayer';
                    textLayerDiv.id = `text-layer-${pageNum}`;
                    textLayerDiv.style.width = `${viewport.width}px`;
                    textLayerDiv.style.height = `${viewport.height}px`;
                    // PDF.js CSS cuida do posicionamento absoluto

                    // Layer para highlights (acima de tudo)
                    const highlightLayer = document.createElement('div');
                    highlightLayer.className = 'highlight-layer';
                    highlightLayer.id = `highlight-layer-${pageNum}`;

                    // Número da página
                    const pageNumber = document.createElement('div');
                    pageNumber.className = 'page-number';
                    pageNumber.textContent = `Página ${pageNum} de ${totalPages}`;

                    pageDiv.appendChild(canvas);
                    pageDiv.appendChild(textLayerDiv);
                    pageDiv.appendChild(highlightLayer);
                    pageDiv.appendChild(pageNumber);
                    container.appendChild(pageDiv);

                    // Renderizar página (canvas)
                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    await page.render(renderContext).promise;

                    // Renderizar Text Layer
                    const textContent = await page.getTextContent();
                    await pdfjsLib.renderTextLayer({
                        textContentSource: textContent,
                        container: textLayerDiv,
                        viewport: viewport,
                        textDivs: []
                    }).promise;

                    console.log(`Página ${pageNum} renderizada`);
                } catch (error) {
                    console.error(`Erro ao renderizar página ${pageNum}:`, error);
                }
            }

            console.log('Todas as páginas foram renderizadas');
        }

        /**
         * Busca texto no PDF usando DOM Range na TextLayer
         */
        async function searchInPDF() {
            const rawSearchText = document.getElementById('searchInput').value.trim();
            
            if (!rawSearchText) {
                alert('Digite um texto para buscar');
                return;
            }

            if (isSearching) {
                console.log('Busca já em andamento...');
                return;
            }

            console.log('Iniciando busca precisa por:', rawSearchText);
            isSearching = true;

            // Limpar busca anterior
            clearHighlights();
            hideSearchResults();
            showSearchLoading();

            try {
                allHighlights = [];
                let totalOccurrences = 0;
                
                // Função para remover acentos mantendo tamanho 1:1 (para maioria dos latinos)
                const normalizeChar = (char) => {
                    return char.normalize('NFD').replace(/[\u0300-\u036f]/g, "").toLowerCase();
                };
                
                // Normalizar texto de busca
                const normalizedSearchText = normalizeChar(rawSearchText);

                // Buscar em cada página
                for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                    const textLayerDiv = document.getElementById(`text-layer-${pageNum}`);
                    const highlightLayer = document.getElementById(`highlight-layer-${pageNum}`);
                    const pageDiv = document.getElementById(`page-${pageNum}`);
                    
                    if (!textLayerDiv || !highlightLayer) continue;

                    // Extrair nós de texto
                    const walker = document.createTreeWalker(textLayerDiv, NodeFilter.SHOW_TEXT, null, false);
                    
                    let fullText = '';
                    const nodeMap = []; // Índice em fullText -> {node, indexInNode}

                    while(walker.nextNode()) {
                        const node = walker.currentNode;
                        const str = node.textContent;
                        
                        // Adicionar espaço implícito se necessário
                        if (fullText.length > 0 && !fullText.endsWith(' ')) {
                             fullText += ' ';
                             nodeMap.push(null); // Espaço virtual
                        }

                        // Mapear caractere por caractere
                        for(let i=0; i<str.length; i++) {
                             // Usar versão normalizada para o fullText de busca
                             fullText += normalizeChar(str[i]);
                             // Apontar para o índice original no DOM Node
                             nodeMap.push({node: node, index: i});
                        }
                    }

                    let searchIndex = 0;
                    let pageOccurrences = 0;

                    while (true) {
                        const foundIndex = fullText.indexOf(normalizedSearchText, searchIndex);
                        if (foundIndex === -1) break;
                        
                        const endIndex = foundIndex + normalizedSearchText.length;
                        
                        // Criar Ranges para os nós envolvidos
                        // Pode abranger múltiplos nós de texto
                        let currentNode = null;
                        let startOffset = -1;
                        let occurrenceHighlights = []; // Highlights desta ocorrência específica
                        
                        for (let i = foundIndex; i < endIndex; i++) {
                            const map = nodeMap[i];
                            if (!map) continue; // Pular espaços virtuais
                            
                            if (map.node !== currentNode) {
                                // Se mudou de nó, finalizar range anterior
                                if (currentNode) {
                                    createRangeHighlight(currentNode, startOffset, map.node === currentNode ? map.index : currentNode.textContent.length, pageDiv, highlightLayer, totalOccurrences, occurrenceHighlights);
                                }
                                
                                currentNode = map.node;
                                startOffset = map.index;
                            }
                        }
                        
                        // Finalizar último nó
                        if (currentNode) {
                            // O fim do range deve ser o último índice processado + 1
                            // Pegar o último mapa válido
                            let lastMap = null;
                            for(let j=endIndex-1; j>=foundIndex; j--) {
                                if (nodeMap[j] && nodeMap[j].node === currentNode) {
                                    lastMap = nodeMap[j];
                                    break;
                                }
                            }
                            if (lastMap) {
                                createRangeHighlight(currentNode, startOffset, lastMap.index + 1, pageDiv, highlightLayer, totalOccurrences, occurrenceHighlights);
                            }
                        }

                        if (occurrenceHighlights.length > 0) {
                            pageOccurrences++;
                            totalOccurrences++;
                            // Marcar início
                            const startHighlight = allHighlights.find(h => h.element === occurrenceHighlights[0]);
                            if (startHighlight) {
                                startHighlight.isOccurrenceStart = true;
                                startHighlight.text = rawSearchText;
                            }
                        }
                        
                        searchIndex = foundIndex + 1;
                    }
                    
                    if (pageOccurrences > 0) console.log(`Página ${pageNum}: ${pageOccurrences} ocorrências`);
                }

                console.log(`Busca concluída: ${totalOccurrences} ocorrências`);

                if (totalOccurrences > 0) {
                    showSearchResults(totalOccurrences, rawSearchText);
                    setActiveOccurrence(0);
                } else {
                    showNoResults();
                }

            } catch (error) {
                console.error('Erro na busca:', error);
                alert('Erro ao buscar: ' + error.message);
            } finally {
                hideSearchLoading();
                isSearching = false;
            }
        }

        /**
         * Cria highlight usando DOM Range e getClientRects
         */
        function createRangeHighlight(node, startOffset, endOffset, pageDiv, container, occurrenceIndex, occurrenceList) {
            try {
                const range = document.createRange();
                range.setStart(node, startOffset);
                range.setEnd(node, endOffset);
                
                const rects = range.getClientRects();
                const pageRect = pageDiv.getBoundingClientRect();
                
                for (const rect of rects) {
                    if (rect.width === 0 || rect.height === 0) continue;
                    
                    const highlight = document.createElement('div');
                    highlight.className = 'pdf-highlight';
                    highlight.dataset.occurrenceIndex = occurrenceIndex;
                    
                    // Coordenadas relativas à página
                    const left = rect.left - pageRect.left;
                    const top = rect.top - pageRect.top;
                    
                    highlight.style.left = `${left}px`;
                    highlight.style.top = `${top}px`;
                    highlight.style.width = `${rect.width}px`;
                    highlight.style.height = `${rect.height}px`;
                    
                    highlight.addEventListener('click', () => {
                        const index = allHighlights.findIndex(h => h.occurrenceIndex === occurrenceIndex && h.isOccurrenceStart);
                        if (index !== -1) setActiveOccurrence(index);
                    });
                    
                    container.appendChild(highlight);
                    occurrenceList.push(highlight);
                    
                    allHighlights.push({
                        element: highlight,
                        page: parseInt(pageDiv.id.replace('page-', '')),
                        occurrenceIndex: occurrenceIndex,
                        isOccurrenceStart: false
                    });
                }
            } catch (e) {
                console.warn('Erro ao criar range highlight:', e);
            }
        }

        /**
         * Define ocorrência ativa
         */
        function setActiveOccurrence(index) {
            // index é o índice no array allHighlights (filtrado por start?)
            // Vamos adaptar: index refere-se à N-ésima ocorrência encontrada (0 a total-1)
            
            // Encontrar o highlight inicial desta ocorrência
            const highlightObj = allHighlights.find(h => h.occurrenceIndex === index && h.isOccurrenceStart);
            
            if (!highlightObj) return;

            console.log(`Navegando para ocorrência ${index + 1}`);

            // Remover classe active de todos
            allHighlights.forEach(h => h.element.classList.remove('active'));

            // Adicionar classe active a TODOS os highlights desta ocorrência (pode ser multi-item)
            allHighlights.filter(h => h.occurrenceIndex === index).forEach(h => {
                h.element.classList.add('active');
            });
            
            currentOccurrence = index;

            // Scroll para a ocorrência
            const pageElement = document.getElementById(`page-${highlightObj.page}`);
            if (pageElement) {
                // Tentar scrollar para o elemento específico se possível
                // Mas scroll para página é mais seguro
                highlightObj.element.scrollIntoView({
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
         * Atualiza contador de ocorrências
         */
        function updateOccurrenceCounter() {
            const counter = document.getElementById('occurrenceCounter');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            
            // Contar quantas ocorrências únicas existem (baseado em occurrenceIndex)
            const totalUnique = new Set(allHighlights.map(h => h.occurrenceIndex)).size;
            
            if (totalUnique > 0) {
                counter.textContent = `${currentOccurrence + 1} de ${totalUnique}`;
                prevBtn.disabled = false;
                nextBtn.disabled = false;
            } else {
                counter.textContent = '-';
                prevBtn.disabled = true;
                nextBtn.disabled = true;
            }
        }

        /**
         * Próxima ocorrência
         */
        function nextOccurrence() {
            const totalUnique = new Set(allHighlights.map(h => h.occurrenceIndex)).size;
            if (totalUnique === 0) return;
            const next = (currentOccurrence + 1) % totalUnique;
            setActiveOccurrence(next);
        }

        /**
         * Ocorrência anterior
         */
        function previousOccurrence() {
            const totalUnique = new Set(allHighlights.map(h => h.occurrenceIndex)).size;
            if (totalUnique === 0) return;
            const prev = currentOccurrence === 0 ? totalUnique - 1 : currentOccurrence - 1;
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
