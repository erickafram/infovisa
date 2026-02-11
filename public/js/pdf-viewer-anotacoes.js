// Função global para o visualizador de PDF com anotações
// PDF.js já está carregado globalmente no layout
// Armazenar o documento PDF fora do objeto Alpine para evitar problemas com Proxy
let _pdfDocInstance = null;
let _currentRenderTask = null;
let _currentDocumentoId = null; // Rastrear qual documento está carregado

// Base URL para as requisições (definida no layout)
const APP_BASE_URL = window.APP_BASE_URL || '';

function pdfViewerAnotacoes(documentoId, pdfUrl, anotacoesIniciais) {
    return {
        documentoId: documentoId,
        pdfUrl: pdfUrl,
        currentPage: 1,
        totalPages: 0,
        scale: 1.5, // Zoom padrão de 150%
        canvas: null,
        ctx: null,
        annotationCanvas: null,
        annotationCtx: null,
        currentTool: 'select',
        isDrawing: false,
        startX: 0,
        startY: 0,
        annotations: anotacoesIniciais || [],
        currentAnnotation: null,
        hoveredAnnotationIndex: null,
        selectedAnnotationIndex: null,
        // Controles de pan/arrastar
        isPanning: false,
        panStartX: 0,
        panStartY: 0,
        scrollLeft: 0,
        scrollTop: 0,
        container: null,
        // Otimizações de performance
        renderTimeout: null,
        isRendering: false,
        renderQuality: 'high', // 'low', 'medium', 'high'
        pageCache: new Map(), // Cache de páginas renderizadas

        async init() {
            // Limpar instância anterior se for um documento diferente
            if (_currentDocumentoId !== this.documentoId) {
                if (_currentRenderTask) {
                    try {
                        _currentRenderTask.cancel();
                    } catch (e) {}
                    _currentRenderTask = null;
                }
                if (_pdfDocInstance) {
                    try {
                        _pdfDocInstance.destroy();
                    } catch (e) {}
                    _pdfDocInstance = null;
                }
                _currentDocumentoId = this.documentoId;
            }
            
            this.canvas = document.getElementById('pdf-canvas');
            this.ctx = this.canvas.getContext('2d');
            this.annotationCanvas = document.getElementById('annotation-canvas');
            this.annotationCtx = this.annotationCanvas.getContext('2d');
            this.container = this.canvas.closest('.pdf-canvas-container');

            // Adicionar atalho de teclado para desfazer (Ctrl+Z)
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
                    e.preventDefault();
                    this.desfazerAnotacao();
                }
            });

            // Adicionar event listener para clique nas anotações
            this.annotationCanvas.addEventListener('click', (e) => {
                if (this.currentTool !== 'select') return;
                this.handleAnnotationClick(e);
            });

            // Adicionar event listener para hover nas anotações
            this.annotationCanvas.addEventListener('mousemove', (e) => {
                if (this.currentTool !== 'select') return;
                this.handleAnnotationHover(e);
            });

            // Adicionar controles de pan/arrastar
            this.setupPanControls();
            
            // Adicionar zoom com scroll do mouse
            this.setupMouseWheelZoom();

            try {
                // Validar se pdfUrl está definida e não está vazia
                if (!this.pdfUrl || this.pdfUrl.trim() === '') {
                    console.error('PDF URL está vazia ou indefinida:', this.pdfUrl);
                    alert('Erro: URL do PDF não foi fornecida.');
                    return;
                }
                
                // Carregar anotações existentes do banco de dados
                await this.carregarAnotacoesExistentes();
                
                // Adicionar timestamp para evitar cache do navegador
                const urlComTimestamp = this.pdfUrl + (this.pdfUrl.includes('?') ? '&' : '?') + '_t=' + Date.now();
                
                console.log('🚀 Iniciando carregamento do PDF...');
                const startTime = performance.now();
                
                // PDF.js com configurações otimizadas para streaming
                const loadingTask = pdfjsLib.getDocument({ 
                    url: urlComTimestamp,
                    cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                    cMapPacked: true,
                    // OTIMIZAÇÕES CRÍTICAS PARA PDFS PESADOS
                    disableAutoFetch: true,      // Não baixar tudo automaticamente
                    disableStream: false,         // Habilitar streaming
                    disableRange: false,          // Habilitar range requests
                    rangeChunkSize: 65536,        // 64KB chunks (menor = mais rápido inicial)
                    disableFontFace: false,       // Manter fontes
                    useSystemFonts: true,         // Usar fontes do sistema quando possível
                    standardFontDataUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/standard_fonts/',
                });
                
                // Mostrar progresso de carregamento
                loadingTask.onProgress = (progress) => {
                    if (progress.total > 0) {
                        const percent = Math.round((progress.loaded / progress.total) * 100);
                        console.log(`📥 Carregando PDF: ${percent}%`);
                    }
                };
                
                // Armazenar fora do Proxy do Alpine.js
                _pdfDocInstance = await loadingTask.promise;
                this.totalPages = _pdfDocInstance.numPages;
                
                const loadTime = performance.now() - startTime;
                console.log(`✅ PDF carregado em ${Math.round(loadTime)}ms - ${this.totalPages} páginas`);
                
                // Renderizar primeira página IMEDIATAMENTE em preview ultra-rápido
                console.log('🎨 Renderizando primeira página (preview rápido)...');
                this.scale = 0.75; // Começar com zoom baixo para ser mais rápido
                await this.renderPage(this.currentPage, 'preview');
                
                console.log('✅ Primeira página renderizada!');
            } catch (error) {
                console.error('❌ Erro ao carregar PDF:', error);
                alert('Erro ao carregar o PDF. Por favor, tente novamente.');
            }
        },

        setupPanControls() {
            if (!this.container) return;

            // Pan com mouse (arrastar segurando espaço ou botão do meio)
            let spacePressed = false;

            document.addEventListener('keydown', (e) => {
                if (e.code === 'Space' && !e.target.matches('input, textarea')) {
                    e.preventDefault();
                    spacePressed = true;
                    if (!this.isPanning) {
                        this.container.style.cursor = 'grab';
                    }
                }
            });

            document.addEventListener('keyup', (e) => {
                if (e.code === 'Space') {
                    spacePressed = false;
                    if (!this.isPanning) {
                        this.container.style.cursor = '';
                    }
                }
            });

            this.container.addEventListener('mousedown', (e) => {
                // Pan com espaço + clique esquerdo ou botão do meio
                if ((spacePressed && e.button === 0) || e.button === 1) {
                    e.preventDefault();
                    this.isPanning = true;
                    this.panStartX = e.clientX;
                    this.panStartY = e.clientY;
                    this.scrollLeft = this.container.scrollLeft;
                    this.scrollTop = this.container.scrollTop;
                    this.container.style.cursor = 'grabbing';
                }
            });

            this.container.addEventListener('mousemove', (e) => {
                if (this.isPanning) {
                    e.preventDefault();
                    const dx = e.clientX - this.panStartX;
                    const dy = e.clientY - this.panStartY;
                    this.container.scrollLeft = this.scrollLeft - dx;
                    this.container.scrollTop = this.scrollTop - dy;
                }
            });

            this.container.addEventListener('mouseup', (e) => {
                if (this.isPanning) {
                    this.isPanning = false;
                    this.container.style.cursor = spacePressed ? 'grab' : '';
                }
            });

            this.container.addEventListener('mouseleave', () => {
                if (this.isPanning) {
                    this.isPanning = false;
                    this.container.style.cursor = '';
                }
            });

            // Prevenir menu de contexto no botão do meio
            this.container.addEventListener('contextmenu', (e) => {
                if (e.button === 1) {
                    e.preventDefault();
                }
            });
        },

        setupMouseWheelZoom() {
            if (!this.container) return;

            // Debounce para zoom com scroll
            let zoomTimeout = null;

            this.container.addEventListener('wheel', async (e) => {
                // Ctrl + Scroll para zoom
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    
                    // Calcular posição do mouse relativa ao container
                    const rect = this.container.getBoundingClientRect();
                    const mouseX = e.clientX - rect.left + this.container.scrollLeft;
                    const mouseY = e.clientY - rect.top + this.container.scrollTop;
                    
                    // Calcular posição relativa antes do zoom
                    const relX = mouseX / (this.canvas.width || 1);
                    const relY = mouseY / (this.canvas.height || 1);
                    
                    // Ajustar zoom
                    const oldScale = this.scale;
                    if (e.deltaY < 0) {
                        // Zoom in
                        this.scale = Math.min(this.scale + 0.25, 5.0);
                    } else {
                        // Zoom out
                        this.scale = Math.max(this.scale - 0.25, 0.5);
                    }
                    
                    // Usar debounce para evitar múltiplas renderizações durante scroll rápido
                    if (zoomTimeout) {
                        clearTimeout(zoomTimeout);
                    }
                    
                    zoomTimeout = setTimeout(async () => {
                        // Renderizar com novo zoom
                        await this.renderPageDebounced(this.currentPage);
                        
                        // Ajustar scroll para manter o ponto do mouse no mesmo lugar
                        await this.$nextTick();
                        const newMouseX = relX * this.canvas.width;
                        const newMouseY = relY * this.canvas.height;
                        this.container.scrollLeft = newMouseX - (e.clientX - rect.left);
                        this.container.scrollTop = newMouseY - (e.clientY - rect.top);
                    }, 100); // 100ms de debounce para zoom suave
                }
            }, { passive: false });
        },

        async carregarAnotacoesExistentes() {
            try {
                const response = await fetch(`${APP_BASE_URL}/admin/processos/documentos/${this.documentoId}/anotacoes`, {
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                const contentType = response.headers.get('content-type') || '';
                if (!response.ok) {
                    const body = await response.text();
                    console.error('Falha ao carregar anotações:', body);
                    return;
                }

                if (contentType.includes('application/json')) {
                    const anotacoes = await response.json();
                    this.annotations = anotacoes;
                } else {
                    const body = await response.text();
                    console.warn('Resposta não JSON ao carregar anotações:', body.slice(0, 200));
                }
            } catch (error) {
                console.error('Erro ao carregar anotações:', error);
            }
        },

        async renderPage(pageNum, forceQuality = null) {
            if (!_pdfDocInstance) {
                console.error('Documento PDF não está carregado');
                return;
            }

            // Evitar renderizações simultâneas
            if (this.isRendering) {
                console.log('Renderização já em andamento, aguardando...');
                return;
            }

            this.isRendering = true;
            const renderStart = performance.now();

            try {
                // Cancelar renderização anterior se existir
                if (_currentRenderTask) {
                    _currentRenderTask.cancel();
                    _currentRenderTask = null;
                }

                const page = await _pdfDocInstance.getPage(pageNum);
                
                // Calcular escala adaptativa baseada no tamanho da página
                const viewport = page.getViewport({ scale: 1.0 });
                const pageArea = viewport.width * viewport.height;
                const isLargePage = pageArea > 2000000; // ~A1 ou maior
                
                // Ajustar qualidade baseado no zoom e tamanho da página
                let renderScale = this.scale;
                
                if (forceQuality === 'preview') {
                    // Modo preview ultra-rápido para primeira visualização
                    renderScale = Math.min(this.scale, 0.5);
                    this.renderQuality = 'preview';
                } else if (isLargePage && this.scale < 1.0) {
                    // Para pranchas grandes com zoom baixo, renderizar em qualidade reduzida
                    renderScale = this.scale * 0.6; // Mais agressivo
                    this.renderQuality = 'low';
                } else if (isLargePage && this.scale < 2.0) {
                    renderScale = this.scale * 0.75; // Mais agressivo
                    this.renderQuality = 'medium';
                } else {
                    this.renderQuality = 'high';
                }

                const finalViewport = page.getViewport({ scale: renderScale });

                // Configurar canvas com tamanho otimizado
                this.canvas.height = finalViewport.height;
                this.canvas.width = finalViewport.width;
                this.annotationCanvas.height = finalViewport.height;
                this.annotationCanvas.width = finalViewport.width;

                // Limpar canvas antes de renderizar
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

                const renderContext = {
                    canvasContext: this.ctx,
                    viewport: finalViewport,
                    // Otimizações de renderização
                    intent: 'display',
                    enableWebGL: false,
                    renderInteractiveForms: false,
                    // Otimização adicional para preview
                    ...(forceQuality === 'preview' && {
                        renderTextLayer: false,
                        renderAnnotationLayer: false,
                    })
                };

                // Armazenar a tarefa de renderização
                _currentRenderTask = page.render(renderContext);
                
                await _currentRenderTask.promise;
                _currentRenderTask = null;
                
                const renderTime = performance.now() - renderStart;
                console.log(`✅ Página ${pageNum} renderizada em ${Math.round(renderTime)}ms (${this.renderQuality})`);
                
                // Redesenhar anotações após renderização
                this.redrawAnnotations();
                
                // Se foi preview, re-renderizar em qualidade melhor após um delay
                if (forceQuality === 'preview') {
                    setTimeout(() => {
                        console.log('🎨 Melhorando qualidade...');
                        this.renderPage(pageNum);
                    }, 500);
                } else {
                    // Pré-carregar páginas adjacentes em background (se não for muito pesado)
                    if (!isLargePage || this.scale < 1.5) {
                        this.preloadAdjacentPages(pageNum);
                    }
                }
                
            } catch (error) {
                // Ignorar erros de cancelamento
                if (error.name === 'RenderingCancelledException') {
                    console.log('Renderização anterior cancelada');
                    return;
                }
                console.error('Erro ao renderizar página:', error);
            } finally {
                this.isRendering = false;
            }
        },

        async preloadAdjacentPages(currentPage) {
            // Pré-carregar próxima e anterior em background (não bloqueia UI)
            const pagesToPreload = [];
            if (currentPage > 1) pagesToPreload.push(currentPage - 1);
            if (currentPage < this.totalPages) pagesToPreload.push(currentPage + 1);

            for (const pageNum of pagesToPreload) {
                if (!this.pageCache.has(pageNum)) {
                    // Marcar como "em cache" para evitar duplicação
                    this.pageCache.set(pageNum, 'loading');
                    
                    // Carregar em background sem bloquear
                    setTimeout(async () => {
                        try {
                            const page = await _pdfDocInstance.getPage(pageNum);
                            this.pageCache.set(pageNum, page);
                        } catch (e) {
                            this.pageCache.delete(pageNum);
                        }
                    }, 100);
                }
            }

            // Limpar cache de páginas distantes (manter apenas 5 páginas)
            if (this.pageCache.size > 5) {
                const keysToDelete = [];
                for (const [key] of this.pageCache) {
                    if (Math.abs(key - currentPage) > 2) {
                        keysToDelete.push(key);
                    }
                }
                keysToDelete.forEach(key => this.pageCache.delete(key));
            }
        },

        // Renderização com debounce para evitar múltiplas chamadas
        async renderPageDebounced(pageNum) {
            if (this.renderTimeout) {
                clearTimeout(this.renderTimeout);
            }

            this.renderTimeout = setTimeout(async () => {
                await this.renderPage(pageNum);
            }, 50); // 50ms de debounce
        },

        async previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                await this.renderPageDebounced(this.currentPage);
            }
        },

        async nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                await this.renderPageDebounced(this.currentPage);
            }
        },

        async goToPage(pageNum) {
            const num = parseInt(pageNum);
            if (num >= 1 && num <= this.totalPages) {
                this.currentPage = num;
                await this.renderPageDebounced(this.currentPage);
            }
        },

        async zoomIn() {
            await this.zoomToPoint(this.scale + 0.25);
        },

        async zoomOut() {
            if (this.scale > 0.5) {
                await this.zoomToPoint(this.scale - 0.25);
            }
        },

        async zoomToPoint(newScale, centerX = null, centerY = null) {
            if (!this.container) return;
            
            // Se não especificado, usar o centro do viewport
            if (centerX === null || centerY === null) {
                const rect = this.container.getBoundingClientRect();
                centerX = rect.width / 2 + this.container.scrollLeft;
                centerY = rect.height / 2 + this.container.scrollTop;
            }
            
            // Calcular posição relativa antes do zoom
            const relX = centerX / (this.canvas.width || 1);
            const relY = centerY / (this.canvas.height || 1);
            
            // Aplicar novo zoom
            this.scale = Math.max(0.5, Math.min(5.0, newScale));
            
            // Usar renderização com debounce para zoom mais suave
            await this.renderPageDebounced(this.currentPage);
            
            // Ajustar scroll para manter o ponto centralizado
            await this.$nextTick();
            const rect = this.container.getBoundingClientRect();
            this.container.scrollLeft = relX * this.canvas.width - rect.width / 2;
            this.container.scrollTop = relY * this.canvas.height - rect.height / 2;
        },

        async setZoom(percentage) {
            await this.zoomToPoint(percentage / 100);
        },

        async fitToWidth() {
            if (!this.canvas) return;
            
            const container = this.canvas.parentElement;
            const containerWidth = container.clientWidth - 40; // Margem
            const pageWidth = this.canvas.width / this.scale;
            this.scale = (containerWidth / pageWidth) * 1.0; // 100% da largura
            this.renderPage(this.currentPage);
        },

        setTool(tool) {
            this.currentTool = tool;
            this.annotationCanvas.style.cursor = tool === 'select' ? 'default' : 'crosshair';
        },

        startAnnotation(event) {
            if (this.currentTool === 'select' || this.currentTool === 'comment') return;

            this.isDrawing = true;
            const rect = this.annotationCanvas.getBoundingClientRect();
            this.startX = event.clientX - rect.left;
            this.startY = event.clientY - rect.top;

            this.currentAnnotation = {
                tipo: this.currentTool,
                pagina: this.currentPage,
                dados: {
                    startX: this.startX,
                    startY: this.startY,
                    points: this.currentTool === 'drawing' ? [[this.startX, this.startY]] : null
                }
            };
        },

        drawAnnotation(event) {
            if (!this.isDrawing) return;

            const rect = this.annotationCanvas.getBoundingClientRect();
            const currentX = event.clientX - rect.left;
            const currentY = event.clientY - rect.top;

            this.annotationCtx.clearRect(0, 0, this.annotationCanvas.width, this.annotationCanvas.height);
            this.redrawAnnotations();

            this.annotationCtx.strokeStyle = this.getToolColor(this.currentTool);
            this.annotationCtx.lineWidth = 2;
            this.annotationCtx.globalAlpha = 0.7;

            if (this.currentTool === 'drawing') {
                this.currentAnnotation.dados.points.push([currentX, currentY]);
                this.annotationCtx.beginPath();
                this.annotationCtx.moveTo(this.currentAnnotation.dados.points[0][0], this.currentAnnotation.dados.points[0][1]);
                for (let point of this.currentAnnotation.dados.points) {
                    this.annotationCtx.lineTo(point[0], point[1]);
                }
                this.annotationCtx.stroke();
            } else if (this.currentTool === 'area' || this.currentTool === 'highlight') {
                this.annotationCtx.fillStyle = this.getToolColor(this.currentTool);
                this.annotationCtx.fillRect(this.startX, this.startY, currentX - this.startX, currentY - this.startY);
                this.annotationCtx.strokeRect(this.startX, this.startY, currentX - this.startX, currentY - this.startY);
            }

            this.annotationCtx.globalAlpha = 1.0;
        },

        endAnnotation(event) {
            if (!this.isDrawing) return;

            this.isDrawing = false;
            const rect = this.annotationCanvas.getBoundingClientRect();
            const endX = event.clientX - rect.left;
            const endY = event.clientY - rect.top;

            this.currentAnnotation.dados.endX = endX;
            this.currentAnnotation.dados.endY = endY;

            // Sempre solicitar comentário
            const comentario = prompt('Adicione um comentário para esta anotação:');
            if (comentario === null) {
                // Usuário cancelou, não adicionar a anotação
                this.currentAnnotation = null;
                this.redrawAnnotations();
                return;
            }
            
            this.currentAnnotation.comentario = comentario || 'Sem comentário';
            
            // Adicionar ID único para referência
            this.currentAnnotation.id = Date.now() + Math.random();

            this.annotations.push(this.currentAnnotation);
            this.currentAnnotation = null;
            this.redrawAnnotations();
        },

        addComment(event) {
            if (this.currentTool !== 'comment') return;

            const rect = this.annotationCanvas.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;

            const comentario = prompt('Digite seu comentário:');
            if (!comentario) return;

            this.annotations.push({
                id: Date.now() + Math.random(),
                tipo: 'comment',
                pagina: this.currentPage,
                dados: { x, y },
                comentario: comentario
            });

            this.redrawAnnotations();
        },

        redrawAnnotations() {
            this.annotationCtx.clearRect(0, 0, this.annotationCanvas.width, this.annotationCanvas.height);

            const pageAnnotations = this.annotations.filter(a => a.pagina === this.currentPage);

            pageAnnotations.forEach((annotation, index) => {
                const globalIndex = this.annotations.indexOf(annotation);
                const isHovered = this.hoveredAnnotationIndex === globalIndex;
                const isSelected = this.selectedAnnotationIndex === globalIndex;
                
                this.annotationCtx.strokeStyle = this.getToolColor(annotation.tipo);
                this.annotationCtx.fillStyle = this.getToolColor(annotation.tipo);
                this.annotationCtx.lineWidth = isHovered || isSelected ? 3 : 2;
                this.annotationCtx.globalAlpha = isHovered || isSelected ? 0.9 : 0.7;

                if (annotation.tipo === 'drawing' && annotation.dados.points) {
                    this.annotationCtx.beginPath();
                    this.annotationCtx.moveTo(annotation.dados.points[0][0], annotation.dados.points[0][1]);
                    for (let point of annotation.dados.points) {
                        this.annotationCtx.lineTo(point[0], point[1]);
                    }
                    this.annotationCtx.stroke();
                    
                    // Número de referência no início do desenho
                    this.drawReferenceNumber(annotation.dados.points[0][0], annotation.dados.points[0][1], index + 1, annotation.tipo);
                    
                } else if (annotation.tipo === 'area' || annotation.tipo === 'highlight') {
                    const width = annotation.dados.endX - annotation.dados.startX;
                    const height = annotation.dados.endY - annotation.dados.startY;
                    this.annotationCtx.fillRect(annotation.dados.startX, annotation.dados.startY, width, height);
                    this.annotationCtx.strokeRect(annotation.dados.startX, annotation.dados.startY, width, height);
                    
                    // Número de referência no canto superior esquerdo
                    this.drawReferenceNumber(annotation.dados.startX, annotation.dados.startY, index + 1, annotation.tipo);
                    
                } else if (annotation.tipo === 'comment') {
                    // Desenha ícone de comentário
                    this.annotationCtx.fillStyle = '#6366f1';
                    this.annotationCtx.beginPath();
                    this.annotationCtx.arc(annotation.dados.x, annotation.dados.y, 8, 0, 2 * Math.PI);
                    this.annotationCtx.fill();
                    this.annotationCtx.fillStyle = 'white';
                    this.annotationCtx.font = '12px Arial';
                    this.annotationCtx.fillText('💬', annotation.dados.x - 6, annotation.dados.y + 4);
                }

                this.annotationCtx.globalAlpha = 1.0;
            });
        },

        drawReferenceNumber(x, y, number, tipo) {
            const radius = 12;
            const offsetX = tipo === 'highlight' || tipo === 'area' ? 5 : 0;
            const offsetY = tipo === 'highlight' || tipo === 'area' ? 5 : -15;
            
            // Círculo de fundo
            this.annotationCtx.globalAlpha = 1.0;
            this.annotationCtx.fillStyle = '#ffffff';
            this.annotationCtx.strokeStyle = '#6366f1';
            this.annotationCtx.lineWidth = 2;
            this.annotationCtx.beginPath();
            this.annotationCtx.arc(x + offsetX, y + offsetY, radius, 0, 2 * Math.PI);
            this.annotationCtx.fill();
            this.annotationCtx.stroke();
            
            // Número
            this.annotationCtx.fillStyle = '#6366f1';
            this.annotationCtx.font = 'bold 12px Arial';
            this.annotationCtx.textAlign = 'center';
            this.annotationCtx.textBaseline = 'middle';
            this.annotationCtx.fillText(number.toString(), x + offsetX, y + offsetY);
            this.annotationCtx.textAlign = 'left';
            this.annotationCtx.textBaseline = 'alphabetic';
        },

        handleAnnotationClick(event) {
            const rect = this.annotationCanvas.getBoundingClientRect();
            const clickX = event.clientX - rect.left;
            const clickY = event.clientY - rect.top;

            const pageAnnotations = this.annotations.filter(a => a.pagina === this.currentPage);
            
            for (let i = pageAnnotations.length - 1; i >= 0; i--) {
                const annotation = pageAnnotations[i];
                const globalIndex = this.annotations.indexOf(annotation);
                
                if (this.isPointInAnnotation(clickX, clickY, annotation, i)) {
                    this.selectedAnnotationIndex = globalIndex;
                    this.showAnnotationTooltip(annotation, i + 1);
                    this.redrawAnnotations();
                    return;
                }
            }
            
            // Clicou fora, desselecionar
            this.selectedAnnotationIndex = null;
            this.redrawAnnotations();
        },

        handleAnnotationHover(event) {
            const rect = this.annotationCanvas.getBoundingClientRect();
            const mouseX = event.clientX - rect.left;
            const mouseY = event.clientY - rect.top;

            const pageAnnotations = this.annotations.filter(a => a.pagina === this.currentPage);
            let foundHover = false;
            
            for (let i = pageAnnotations.length - 1; i >= 0; i--) {
                const annotation = pageAnnotations[i];
                const globalIndex = this.annotations.indexOf(annotation);
                
                if (this.isPointInAnnotation(mouseX, mouseY, annotation, i)) {
                    if (this.hoveredAnnotationIndex !== globalIndex) {
                        this.hoveredAnnotationIndex = globalIndex;
                        this.annotationCanvas.style.cursor = 'pointer';
                        this.redrawAnnotations();
                    }
                    foundHover = true;
                    break;
                }
            }
            
            if (!foundHover && this.hoveredAnnotationIndex !== null) {
                this.hoveredAnnotationIndex = null;
                this.annotationCanvas.style.cursor = this.currentTool === 'select' ? 'default' : 'crosshair';
                this.redrawAnnotations();
            }
        },

        isPointInAnnotation(x, y, annotation, index) {
            const radius = 12;
            
            if (annotation.tipo === 'drawing' && annotation.dados.points) {
                const startX = annotation.dados.points[0][0];
                const startY = annotation.dados.points[0][1] - 15;
                const dist = Math.sqrt((x - startX) ** 2 + (y - startY) ** 2);
                return dist <= radius;
                
            } else if (annotation.tipo === 'area' || annotation.tipo === 'highlight') {
                const numX = annotation.dados.startX + 5;
                const numY = annotation.dados.startY + 5;
                const dist = Math.sqrt((x - numX) ** 2 + (y - numY) ** 2);
                if (dist <= radius) return true;
                
                // Também verificar se está dentro da área
                const width = annotation.dados.endX - annotation.dados.startX;
                const height = annotation.dados.endY - annotation.dados.startY;
                return x >= annotation.dados.startX && x <= annotation.dados.startX + width &&
                       y >= annotation.dados.startY && y <= annotation.dados.startY + height;
                       
            } else if (annotation.tipo === 'comment') {
                const dist = Math.sqrt((x - annotation.dados.x) ** 2 + (y - annotation.dados.y) ** 2);
                return dist <= 8;
            }
            
            return false;
        },

        showAnnotationTooltip(annotation, number) {
            const tipo = this.getTipoLabel(annotation.tipo);
            const comentario = annotation.comentario || 'Sem comentário';
            alert(`📌 Anotação #${number}\n\nTipo: ${tipo}\nComentário: ${comentario}`);
        },

        getToolColor(tool) {
            const colors = {
                highlight: 'rgba(251, 191, 36, 0.5)',
                text: 'rgba(59, 130, 246, 0.7)',
                drawing: 'rgba(239, 68, 68, 0.7)',
                area: 'rgba(34, 197, 94, 0.5)',
                comment: 'rgba(99, 102, 241, 0.7)'
            };
            return colors[tool] || 'rgba(0, 0, 0, 0.5)';
        },

        getTipoLabel(tipo) {
            const labels = {
                highlight: 'Destaque',
                text: 'Texto',
                drawing: 'Desenho',
                area: 'Área',
                comment: 'Comentário'
            };
            return labels[tipo] || tipo;
        },

        getCurrentPageAnnotations() {
            return this.annotations.filter(a => a.pagina === this.currentPage);
        },

        deleteAnnotation(index) {
            const pageAnnotations = this.getCurrentPageAnnotations();
            const annotation = pageAnnotations[index];
            const globalIndex = this.annotations.indexOf(annotation);
            this.annotations.splice(globalIndex, 1);
            this.redrawAnnotations();
        },

        desfazerAnotacao() {
            if (this.annotations.length === 0) {
                return;
            }
            
            // Remove a última anotação adicionada
            const removida = this.annotations.pop();
            this.redrawAnnotations();
            
            // Feedback visual opcional
            console.log('Anotação desfeita:', removida.tipo);
        },

        limparTodasAnotacoes() {
            if (this.annotations.length === 0) {
                return;
            }
            
            if (confirm('Tem certeza que deseja limpar todas as anotações? Esta ação não pode ser desfeita até salvar novamente.')) {
                this.annotations = [];
                this.redrawAnnotations();
            }
        },

        async salvarAnotacoes() {
            try {
                const response = await fetch(`${APP_BASE_URL}/admin/processos/documentos/${this.documentoId}/anotacoes`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        anotacoes: this.annotations
                    })
                });

                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Resposta não JSON ao salvar anotações:', text.slice(0, 500));
                    alert('❌ Falha ao salvar anotações. Verifique se você está autenticado e tente novamente.');
                    return;
                }

                const data = await response.json();
                if (response.ok) {
                    alert('✅ ' + (data.message || 'Anotações salvas com sucesso!'));
                } else {
                    alert('❌ ' + (data.message || 'Erro ao salvar anotações. Tente novamente.'));
                }
            } catch (error) {
                console.error('Erro ao salvar:', error);
                alert('❌ Erro ao salvar anotações. Tente novamente.');
            }
        },

        async exportarPDF() {
            alert('Funcionalidade de exportação em desenvolvimento. As anotações foram salvas no sistema.');
        }
    };
}
