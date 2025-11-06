/**
 * Visualizador de PDF com busca e navegação
 */
class PDFViewer {
    constructor() {
        this.pdfDoc = null;
        this.pageNum = 1;
        this.pageRendering = false;
        this.pageNumPending = null;
        this.scale = 1.5;
        this.canvas = document.getElementById('pdfCanvas');
        this.ctx = this.canvas.getContext('2d');
        
        this.init();
    }

    async init() {
        this.initializeEventListeners();
        await this.loadPDF();
    }

    /**
     * Inicializa event listeners
     */
    initializeEventListeners() {
        document.getElementById('prevPage').addEventListener('click', () => {
            this.onPrevPage();
        });

        document.getElementById('nextPage').addEventListener('click', () => {
            this.onNextPage();
        });

        document.getElementById('zoomIn').addEventListener('click', () => {
            this.zoomIn();
        });

        document.getElementById('zoomOut').addEventListener('click', () => {
            this.zoomOut();
        });

        // Atalhos de teclado
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                this.onPrevPage();
            } else if (e.key === 'ArrowRight') {
                this.onNextPage();
            } else if (e.key === '+' || e.key === '=') {
                this.zoomIn();
            } else if (e.key === '-') {
                this.zoomOut();
            }
        });
    }

    /**
     * Carrega PDF
     */
    async loadPDF() {
        try {
            // Usar proxy para evitar problemas de CORS
            const pdfUrlWithProxy = `${proxyUrl}?url=${encodeURIComponent(pdfUrl)}`;
            
            const loadingTask = pdfjsLib.getDocument(pdfUrlWithProxy);
            
            this.pdfDoc = await loadingTask.promise;
            
            document.getElementById('pageCount').textContent = this.pdfDoc.numPages;
            document.getElementById('pdfLoading').style.display = 'none';
            
            // Renderizar primeira página
            this.renderPage(this.pageNum);
            
        } catch (error) {
            console.error('Erro ao carregar PDF:', error);
            this.showError('Não foi possível carregar o PDF. Tente fazer o download direto.');
        }
    }

    /**
     * Renderiza página
     */
    async renderPage(num) {
        this.pageRendering = true;
        
        try {
            const page = await this.pdfDoc.getPage(num);
            
            const viewport = page.getViewport({ scale: this.scale });
            this.canvas.height = viewport.height;
            this.canvas.width = viewport.width;

            const renderContext = {
                canvasContext: this.ctx,
                viewport: viewport
            };

            const renderTask = page.render(renderContext);
            await renderTask.promise;
            
            this.pageRendering = false;
            
            if (this.pageNumPending !== null) {
                this.renderPage(this.pageNumPending);
                this.pageNumPending = null;
            }

            // Atualizar UI
            document.getElementById('pageNum').textContent = num;
            this.updateNavigationButtons();
            
            // Se houver texto de busca, tentar destacar
            if (searchText) {
                this.highlightSearchText(page, viewport);
            }
            
        } catch (error) {
            console.error('Erro ao renderizar página:', error);
            this.pageRendering = false;
        }
    }

    /**
     * Destaca texto de busca na página (simplificado)
     */
    async highlightSearchText(page, viewport) {
        try {
            const textContent = await page.getTextContent();
            const searchLower = searchText.toLowerCase();
            
            textContent.items.forEach(item => {
                if (item.str.toLowerCase().includes(searchLower)) {
                    // Aqui poderíamos adicionar overlay de destaque
                    // Por simplicidade, apenas logamos
                    console.log('Texto encontrado na página:', this.pageNum);
                }
            });
        } catch (error) {
            console.error('Erro ao buscar texto:', error);
        }
    }

    /**
     * Enfileira renderização de página
     */
    queueRenderPage(num) {
        if (this.pageRendering) {
            this.pageNumPending = num;
        } else {
            this.renderPage(num);
        }
    }

    /**
     * Página anterior
     */
    onPrevPage() {
        if (this.pageNum <= 1) {
            return;
        }
        this.pageNum--;
        this.queueRenderPage(this.pageNum);
    }

    /**
     * Próxima página
     */
    onNextPage() {
        if (this.pageNum >= this.pdfDoc.numPages) {
            return;
        }
        this.pageNum++;
        this.queueRenderPage(this.pageNum);
    }

    /**
     * Aumentar zoom
     */
    zoomIn() {
        this.scale += 0.25;
        if (this.scale > 3) {
            this.scale = 3;
        }
        this.updateZoomLevel();
        this.queueRenderPage(this.pageNum);
    }

    /**
     * Diminuir zoom
     */
    zoomOut() {
        this.scale -= 0.25;
        if (this.scale < 0.5) {
            this.scale = 0.5;
        }
        this.updateZoomLevel();
        this.queueRenderPage(this.pageNum);
    }

    /**
     * Atualiza nível de zoom na UI
     */
    updateZoomLevel() {
        const percentage = Math.round(this.scale * 100);
        document.getElementById('zoomLevel').textContent = `${percentage}%`;
    }

    /**
     * Atualiza botões de navegação
     */
    updateNavigationButtons() {
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        
        if (this.pageNum <= 1) {
            prevBtn.disabled = true;
            prevBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            prevBtn.disabled = false;
            prevBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
        
        if (this.pageNum >= this.pdfDoc.numPages) {
            nextBtn.disabled = true;
            nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            nextBtn.disabled = false;
            nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    /**
     * Mostra erro
     */
    showError(message) {
        document.getElementById('pdfLoading').style.display = 'none';
        document.getElementById('pdfError').classList.remove('hidden');
        document.getElementById('pdfError').classList.add('flex');
        document.getElementById('pdfErrorMessage').textContent = message;
    }
}

// Inicializar quando DOM e PDF.js estiverem prontos
if (typeof pdfjsLib !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        new PDFViewer();
    });
} else {
    console.error('PDF.js não está carregado');
}
