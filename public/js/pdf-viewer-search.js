/**
 * Visualizador de PDF com Busca de Texto Integrada
 */

// Configurar PDF.js
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

// Variáveis globais
let pdfDoc = null;
let totalPages = 0;
let searchResults = [];
let currentOccurrence = 0;
let allHighlights = [];

// Inicializar
document.addEventListener('DOMContentLoaded', async () => {
    await loadPDF();
    
    if (initialSearchText) {
        document.getElementById('searchInput').value = initialSearchText;
        await searchInPDF();
    }
    
    // Event listener para Enter no campo de busca
    document.getElementById('searchInput').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            searchInPDF();
        }
    });
});

/**
 * Carrega e renderiza o PDF
 */
async function loadPDF() {
    try {
        console.log('Carregando PDF:', pdfUrl);
        
        const proxyPdfUrl = `${proxyUrl}?url=${encodeURIComponent(pdfUrl)}`;
        
        const loadingTask = pdfjsLib.getDocument({
            url: proxyPdfUrl,
            cMapUrl: 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/cmaps/',
            cMapPacked: true
        });

        pdfDoc = await loadingTask.promise;
        totalPages = pdfDoc.numPages;

        console.log('PDF carregado:', totalPages, 'páginas');

        document.getElementById('loading').classList.add('hidden');
        document.getElementById('pdfContainer').classList.remove('hidden');

        await renderAllPages();

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

    for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
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

        // Número da página
        const pageNumber = document.createElement('div');
        pageNumber.className = 'page-number';
        pageNumber.textContent = `Página ${pageNum} de ${totalPages}`;

        pageDiv.appendChild(canvas);
        pageDiv.appendChild(highlightLayer);
        pageDiv.appendChild(pageNumber);
        container.appendChild(pageDiv);

        // Renderizar página
        await page.render({
            canvasContext: context,
            viewport: viewport
        }).promise;

        console.log(`Página ${pageNum} renderizada`);
    }
}

/**
 * Busca texto no PDF
 */
async function searchInPDF() {
    const searchText = document.getElementById('searchInput').value.trim();
    
    if (!searchText) {
        alert('Digite um texto para buscar');
        return;
    }

    console.log('Buscando texto:', searchText);

    // Limpar busca anterior
    clearHighlights();
    hideSearchResults();

    // Mostrar loading na busca
    showSearchLoading();

    try {
        // Buscar no backend
        const response = await fetch(searchApiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                url: pdfUrl,
                texto: searchText
            })
        });

        const result = await response.json();
        console.log('Resultado da busca:', result);
        console.log('Success:', result.success);
        console.log('Total Occurrences:', result.totalOccurrences);
        console.log('Pages:', result.pages);

        if (result.success && result.totalOccurrences > 0) {
            searchResults = result.pages;
            await highlightTextInPDF(searchText);
            showSearchResults(result.totalOccurrences, searchText);
        } else {
            console.log('Nenhum resultado encontrado ou erro');
            if (!result.success) {
                console.error('Erro da API:', result.message);
            }
            showNoResults();
        }

    } catch (error) {
        console.error('Erro na busca:', error);
        alert('Erro ao buscar no PDF: ' + error.message);
    }

    hideSearchLoading();
}

/**
 * Destaca texto no PDF usando PDF.js
 */
async function highlightTextInPDF(searchText) {
    allHighlights = [];
    let globalOccurrenceIndex = 0;

    for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
        const page = await pdfDoc.getPage(pageNum);
        const textContent = await page.getTextContent();
        const viewport = page.getViewport({ scale: 1.5 });
        const highlightLayer = document.getElementById(`highlight-layer-${pageNum}`);

        if (!highlightLayer) continue;

        // Buscar texto nos itens da página
        textContent.items.forEach((item) => {
            const text = item.str.toLowerCase();
            const searchLower = searchText.toLowerCase();
            
            if (text.includes(searchLower)) {
                // Calcular posição do highlight
                const transform = pdfjsLib.Util.transform(
                    viewport.transform,
                    item.transform
                );

                const highlight = document.createElement('div');
                highlight.className = 'pdf-highlight';
                highlight.dataset.page = pageNum;
                highlight.dataset.occurrence = globalOccurrenceIndex;
                
                // Posicionamento
                highlight.style.left = `${transform[4]}px`;
                highlight.style.top = `${viewport.height - transform[5] - item.height}px`;
                highlight.style.width = `${item.width}px`;
                highlight.style.height = `${item.height}px`;

                // Event listener para clique
                highlight.addEventListener('click', () => {
                    setActiveOccurrence(globalOccurrenceIndex);
                });

                highlightLayer.appendChild(highlight);
                allHighlights.push({
                    element: highlight,
                    page: pageNum,
                    index: globalOccurrenceIndex
                });

                globalOccurrenceIndex++;
            }
        });
    }

    console.log(`${allHighlights.length} highlights criados`);

    // Ir para primeira ocorrência
    if (allHighlights.length > 0) {
        setActiveOccurrence(0);
    }
}

/**
 * Define ocorrência ativa
 */
function setActiveOccurrence(index) {
    if (index < 0 || index >= allHighlights.length) return;

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
    const contextsList = document.getElementById('contextsList');

    resultsInfo.innerHTML = `
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <strong>${totalOccurrences}</strong> ocorrência(s) de "<strong>${searchText}</strong>" encontrada(s) em <strong>${searchResults.length}</strong> página(s)
    `;

    // Mostrar contextos
    let contextsHtml = '';
    searchResults.forEach(pageResult => {
        pageResult.contexts.forEach(context => {
            const highlightedContext = context.replace(
                new RegExp(escapeRegex(searchText), 'gi'),
                match => `<mark class="bg-yellow-300 font-semibold">${match}</mark>` 
            );
            
            contextsHtml += `
                <div class="bg-white p-3 rounded-lg border-l-4 border-green-500 cursor-pointer hover:bg-gray-50"
                     onclick="scrollToPage(${pageResult.page})">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="font-medium text-gray-800">Página ${pageResult.page}</span>
                        <span class="text-sm text-gray-500">(${pageResult.occurrences} ocorrência(s))</span>
                    </div>
                    <p class="text-sm text-gray-700">${highlightedContext}</p>
                </div>
            `;
        });
    });

    contextsList.innerHTML = contextsHtml;
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
    if (allHighlights.length > 0) {
        counter.textContent = `${currentOccurrence + 1} de ${allHighlights.length}`;
    } else {
        counter.textContent = '-';
    }
}

/**
 * Mostra loading da busca
 */
function showSearchLoading() {
    const resultsInfo = document.getElementById('resultsInfo');
    resultsInfo.innerHTML = `
        <svg class="w-5 h-5 inline mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Buscando texto no PDF...
    `;
    document.getElementById('searchResults').classList.remove('hidden');
}

/**
 * Esconde loading da busca
 */
function hideSearchLoading() {
    // Será substituído pelos resultados ou escondido
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
        
        // Destacar página temporariamente
        pageElement.style.border = '4px solid #10b981';
        setTimeout(() => {
            pageElement.style.border = 'none';
        }, 2000);
    }
}

/**
 * Mostra erro
 */
function showError(message) {
    document.getElementById('loading').innerHTML = `
        <div class="text-center">
            <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-red-800 font-semibold text-xl mb-2">Erro ao carregar PDF</p>
            <p class="text-red-600">${message}</p>
            <a href="${pdfUrl}" target="_blank" 
               class="inline-block mt-4 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">
                Abrir PDF em nova aba
            </a>
        </div>
    `;
}

/**
 * Escapa caracteres especiais para regex
 */
function escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// Event listeners para teclado
document.addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        document.getElementById('searchInput').focus();
    } else if (e.key === 'F3' || (e.ctrlKey && e.key === 'g')) {
        e.preventDefault();
        nextOccurrence();
    } else if (e.shiftKey && e.key === 'F3' || (e.ctrlKey && e.shiftKey && e.key === 'G')) {
        e.preventDefault();
        previousOccurrence();
    }
});
