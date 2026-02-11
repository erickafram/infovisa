/**
 * Visualizador de PDF simples com zoom completo (mouse wheel, botões, pinch)
 * Sem ferramentas de anotação - apenas visualização e navegação.
 */
let _simplePdfDoc = null;
let _simpleRenderTask = null;

function pdfViewerSimple() {
    return {
        pdfUrl: '',
        currentPage: 1,
        totalPages: 0,
        scale: 1.0,
        minScale: 0.25,
        maxScale: 5.0,
        canvas: null,
        ctx: null,
        isLoading: false,
        errorMsg: '',
        isPanning: false,
        panStartX: 0,
        panStartY: 0,
        scrollStartX: 0,
        scrollStartY: 0,

        async init() {
            this.canvas = this.$refs.pdfCanvas;
            if (!this.canvas) return;
            this.ctx = this.canvas.getContext('2d');

            // Mouse wheel zoom (captura no container do PDF)
            const container = this.$refs.pdfContainer;
            if (container) {
                container.addEventListener('wheel', (e) => {
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        e.stopPropagation();
                        const delta = e.deltaY > 0 ? -0.1 : 0.1;
                        const newScale = Math.max(this.minScale, Math.min(this.maxScale, this.scale + delta));
                        if (newScale !== this.scale) {
                            this.scale = Math.round(newScale * 100) / 100;
                            this.renderPage(this.currentPage);
                        }
                    }
                }, { passive: false });

                // Pan com mouse (arrastar com botão do meio ou segurando espaço)
                container.addEventListener('mousedown', (e) => {
                    if (e.button === 1) { // Botão do meio
                        e.preventDefault();
                        this.startPan(e, container);
                    }
                });
                container.addEventListener('mousemove', (e) => {
                    if (this.isPanning) {
                        e.preventDefault();
                        container.scrollLeft = this.scrollStartX - (e.clientX - this.panStartX);
                        container.scrollTop = this.scrollStartY - (e.clientY - this.panStartY);
                    }
                });
                container.addEventListener('mouseup', () => { this.isPanning = false; });
                container.addEventListener('mouseleave', () => { this.isPanning = false; });
            }
        },

        startPan(e, container) {
            this.isPanning = true;
            this.panStartX = e.clientX;
            this.panStartY = e.clientY;
            this.scrollStartX = container.scrollLeft;
            this.scrollStartY = container.scrollTop;
        },

        async loadPdf(url) {
            if (!url) return;

            this.pdfUrl = url;
            this.currentPage = 1;
            this.scale = 1.0;
            this.isLoading = true;
            this.errorMsg = '';

            // Limpar instância anterior
            if (_simpleRenderTask) {
                try { _simpleRenderTask.cancel(); } catch (e) {}
                _simpleRenderTask = null;
            }
            if (_simplePdfDoc) {
                try { _simplePdfDoc.destroy(); } catch (e) {}
                _simplePdfDoc = null;
            }

            try {
                const urlComTimestamp = url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now();

                const loadingTask = pdfjsLib.getDocument({
                    url: urlComTimestamp,
                    cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                    cMapPacked: true
                });

                _simplePdfDoc = await loadingTask.promise;
                this.totalPages = _simplePdfDoc.numPages;

                // Calcular scale para ajustar à largura do container
                await this.fitToWidth();
            } catch (error) {
                console.error('Erro ao carregar PDF:', error);
                this.errorMsg = 'Não foi possível carregar o documento.';
            } finally {
                this.isLoading = false;
            }
        },

        async renderPage(pageNum) {
            if (!_simplePdfDoc || !this.canvas) return;

            try {
                if (_simpleRenderTask) {
                    _simpleRenderTask.cancel();
                    _simpleRenderTask = null;
                }

                const page = await _simplePdfDoc.getPage(pageNum);
                const viewport = page.getViewport({ scale: this.scale });

                this.canvas.height = viewport.height;
                this.canvas.width = viewport.width;

                const renderContext = {
                    canvasContext: this.ctx,
                    viewport: viewport
                };

                _simpleRenderTask = page.render(renderContext);
                await _simpleRenderTask.promise;
                _simpleRenderTask = null;
            } catch (error) {
                if (error.name === 'RenderingCancelledException') return;
                console.error('Erro ao renderizar página:', error);
            }
        },

        async previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                await this.renderPage(this.currentPage);
            }
        },

        async nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                await this.renderPage(this.currentPage);
            }
        },

        async goToPage(pageNum) {
            const num = parseInt(pageNum);
            if (num >= 1 && num <= this.totalPages) {
                this.currentPage = num;
                await this.renderPage(this.currentPage);
            }
        },

        async zoomIn() {
            if (this.scale < this.maxScale) {
                this.scale = Math.round((this.scale + 0.25) * 100) / 100;
                await this.renderPage(this.currentPage);
            }
        },

        async zoomOut() {
            if (this.scale > this.minScale) {
                this.scale = Math.round((this.scale - 0.25) * 100) / 100;
                await this.renderPage(this.currentPage);
            }
        },

        async fitToWidth() {
            if (!_simplePdfDoc || !this.canvas) return;

            try {
                const page = await _simplePdfDoc.getPage(this.currentPage);
                const viewport = page.getViewport({ scale: 1.0 });
                const container = this.$refs.pdfContainer;
                if (!container) return;

                const containerWidth = container.clientWidth - 40;
                this.scale = Math.round((containerWidth / viewport.width) * 100) / 100;
                await this.renderPage(this.currentPage);
            } catch (error) {
                console.error('Erro ao ajustar largura:', error);
            }
        },

        async fitToPage() {
            if (!_simplePdfDoc || !this.canvas) return;

            try {
                const page = await _simplePdfDoc.getPage(this.currentPage);
                const viewport = page.getViewport({ scale: 1.0 });
                const container = this.$refs.pdfContainer;
                if (!container) return;

                const containerWidth = container.clientWidth - 40;
                const containerHeight = container.clientHeight - 20;
                const scaleW = containerWidth / viewport.width;
                const scaleH = containerHeight / viewport.height;
                this.scale = Math.round(Math.min(scaleW, scaleH) * 100) / 100;
                await this.renderPage(this.currentPage);
            } catch (error) {
                console.error('Erro ao ajustar página:', error);
            }
        },

        async setZoom(percent) {
            this.scale = Math.round((percent / 100) * 100) / 100;
            await this.renderPage(this.currentPage);
        },

        getZoomPercent() {
            return Math.round(this.scale * 100);
        },

        downloadPdf() {
            if (this.pdfUrl) {
                const a = document.createElement('a');
                a.href = this.pdfUrl;
                a.download = '';
                a.target = '_blank';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
        },

        cleanup() {
            if (_simpleRenderTask) {
                try { _simpleRenderTask.cancel(); } catch (e) {}
                _simpleRenderTask = null;
            }
            if (_simplePdfDoc) {
                try { _simplePdfDoc.destroy(); } catch (e) {}
                _simplePdfDoc = null;
            }
        }
    };
}
