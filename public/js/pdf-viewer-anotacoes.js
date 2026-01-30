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
        scale: 1.0, // Alterado de 1.5 para 1.0 (100%)
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
                
                // PDF.js requer um objeto com a propriedade 'url'
                const loadingTask = pdfjsLib.getDocument({ 
                    url: urlComTimestamp,
                    cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                    cMapPacked: true
                });
                
                // Armazenar fora do Proxy do Alpine.js
                _pdfDocInstance = await loadingTask.promise;
                this.totalPages = _pdfDocInstance.numPages;
                await this.renderPage(this.currentPage);
            } catch (error) {
                console.error('Erro ao carregar PDF:', error);
                alert('Erro ao carregar o PDF. Por favor, tente novamente.');
            }
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

        async renderPage(pageNum) {
            if (!_pdfDocInstance) {
                console.error('Documento PDF não está carregado');
                return;
            }

            try {
                // Cancelar renderização anterior se existir
                if (_currentRenderTask) {
                    _currentRenderTask.cancel();
                    _currentRenderTask = null;
                }

                const page = await _pdfDocInstance.getPage(pageNum);
                const viewport = page.getViewport({ scale: this.scale });

                this.canvas.height = viewport.height;
                this.canvas.width = viewport.width;
                this.annotationCanvas.height = viewport.height;
                this.annotationCanvas.width = viewport.width;

                const renderContext = {
                    canvasContext: this.ctx,
                    viewport: viewport
                };

                // Armazenar a tarefa de renderização
                _currentRenderTask = page.render(renderContext);
                
                await _currentRenderTask.promise;
                _currentRenderTask = null;
                
                this.redrawAnnotations();
            } catch (error) {
                // Ignorar erros de cancelamento
                if (error.name === 'RenderingCancelledException') {
                    console.log('Renderização anterior cancelada');
                    return;
                }
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
            this.scale += 0.25;
            await this.renderPage(this.currentPage);
        },

        async zoomOut() {
            if (this.scale > 0.5) {
                this.scale -= 0.25;
                await this.renderPage(this.currentPage);
            }
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
