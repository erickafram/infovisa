{{-- Componente de visualização de PDF com anotações --}}
{{-- Espera que as variáveis documentoIdAnotacoes e pdfUrlAnotacoes estejam disponíveis no contexto Alpine.js --}}
    
    {{-- Toolbar --}}
    <div class="pdf-toolbar bg-gray-50 border-b border-gray-200 p-4 flex items-center justify-between gap-4 flex-wrap">
        {{-- Navegação de Páginas --}}
        <div class="flex items-center gap-3">
            <button @click="previousPage()" 
                    :disabled="currentPage === 1"
                    class="p-2 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600">Página</span>
                <input type="number" 
                       x-model.number="currentPage" 
                       @change="goToPage($event.target.value)"
                       min="1" 
                       :max="totalPages"
                       class="w-16 px-2 py-1 text-center border border-gray-300 rounded-md text-sm">
                <span class="text-sm text-gray-600">de <span x-text="totalPages"></span></span>
            </div>
            
            <button @click="nextPage()" 
                    :disabled="currentPage === totalPages"
                    class="p-2 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        {{-- Zoom --}}
        <div class="flex items-center gap-2">
            <button @click="zoomOut()" class="p-2 rounded-lg hover:bg-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/>
                </svg>
            </button>
            <span class="text-sm text-gray-600 min-w-[60px] text-center" x-text="Math.round(scale * 100) + '%'"></span>
            <button @click="zoomIn()" class="p-2 rounded-lg hover:bg-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                </svg>
            </button>
            <button @click="fitToWidth()" class="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                Ajustar Largura
            </button>
        </div>

        {{-- Ferramentas de Anotação --}}
        <div class="flex items-center gap-2 border-l border-gray-300 pl-4">
            <span class="text-sm font-medium text-gray-700">Ferramentas:</span>
            
            <button @click="setTool('select')" 
                    :class="currentTool === 'select' ? 'bg-purple-100 text-purple-700 border-purple-300' : 'bg-white border-gray-300'"
                    class="p-2 rounded-lg border hover:bg-gray-50 transition" title="Selecionar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                </svg>
            </button>

            <button @click="setTool('highlight')" 
                    :class="currentTool === 'highlight' ? 'bg-yellow-100 text-yellow-700 border-yellow-300' : 'bg-white border-gray-300'"
                    class="p-2 rounded-lg border hover:bg-gray-50 transition" title="Destacar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </button>

            <button @click="setTool('text')" 
                    :class="currentTool === 'text' ? 'bg-blue-100 text-blue-700 border-blue-300' : 'bg-white border-gray-300'"
                    class="p-2 rounded-lg border hover:bg-gray-50 transition" title="Texto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </button>

            <button @click="setTool('drawing')" 
                    :class="currentTool === 'drawing' ? 'bg-red-100 text-red-700 border-red-300' : 'bg-white border-gray-300'"
                    class="p-2 rounded-lg border hover:bg-gray-50 transition" title="Desenhar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
            </button>

            <button @click="setTool('area')" 
                    :class="currentTool === 'area' ? 'bg-green-100 text-green-700 border-green-300' : 'bg-white border-gray-300'"
                    class="p-2 rounded-lg border hover:bg-gray-50 transition" title="Área/Retângulo">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5z"/>
                </svg>
            </button>

            <button @click="setTool('comment')" 
                    :class="currentTool === 'comment' ? 'bg-indigo-100 text-indigo-700 border-indigo-300' : 'bg-white border-gray-300'"
                    class="p-2 rounded-lg border hover:bg-gray-50 transition" title="Comentário">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
            </button>
        </div>

        {{-- Ações --}}
        <div class="flex items-center gap-2 border-l border-gray-300 pl-4">
            <button @click="salvarAnotacoes()" 
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                Salvar
            </button>

            <button @click="exportarPDF()" 
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exportar
            </button>
        </div>
    </div>

    {{-- Canvas Container --}}
    <div class="pdf-canvas-container relative overflow-auto bg-gray-100" style="height: 800px;">
        <div class="pdf-canvas-wrapper inline-block relative">
            <canvas id="pdf-canvas" class="shadow-lg"></canvas>
            <canvas id="annotation-canvas" 
                    class="absolute top-0 left-0 cursor-crosshair" 
                    @mousedown="startAnnotation($event)"
                    @mousemove="drawAnnotation($event)"
                    @mouseup="endAnnotation($event)"
                    @click="addComment($event)"></canvas>
        </div>
    </div>

    {{-- Lista de Anotações da Página Atual --}}
    <div class="pdf-annotations-list border-t border-gray-200 p-4 bg-gray-50">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Anotações nesta página:</h3>
        <div class="space-y-2 max-h-40 overflow-y-auto">
            <template x-for="(anotacao, index) in getCurrentPageAnnotations()" :key="index">
                <div class="bg-white p-3 rounded-lg border border-gray-200 flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                                  :class="{
                                      'bg-yellow-100 text-yellow-700': anotacao.tipo === 'highlight',
                                      'bg-blue-100 text-blue-700': anotacao.tipo === 'text',
                                      'bg-red-100 text-red-700': anotacao.tipo === 'drawing',
                                      'bg-green-100 text-green-700': anotacao.tipo === 'area',
                                      'bg-indigo-100 text-indigo-700': anotacao.tipo === 'comment'
                                  }"
                                  x-text="getTipoLabel(anotacao.tipo)"></span>
                            <span class="text-xs text-gray-500" x-text="anotacao.usuario || 'Você'"></span>
                        </div>
                        <p class="text-sm text-gray-700" x-text="anotacao.comentario || 'Sem comentário'"></p>
                    </div>
                    <button @click="deleteAnnotation(index)" 
                            class="text-red-600 hover:text-red-700 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </template>
            <div x-show="getCurrentPageAnnotations().length === 0" class="text-sm text-gray-500 text-center py-4">
                Nenhuma anotação nesta página
            </div>
        </div>
    </div>
</div>

<script>
// PDF.js já está carregado globalmente no layout
function pdfViewerAnotacoes(documentoId, pdfUrl, anotacoesIniciais) {
    return {
        documentoId: documentoId,
        pdfUrl: pdfUrl,
        pdfDoc: null,
        currentPage: 1,
        totalPages: 0,
        scale: 1.5,
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

        async init() {
            this.canvas = document.getElementById('pdf-canvas');
            this.ctx = this.canvas.getContext('2d');
            this.annotationCanvas = document.getElementById('annotation-canvas');
            this.annotationCtx = this.annotationCanvas.getContext('2d');

            try {
                this.pdfDoc = await pdfjsLib.getDocument(this.pdfUrl).promise;
                this.totalPages = this.pdfDoc.numPages;
                await this.renderPage(this.currentPage);
            } catch (error) {
                console.error('Erro ao carregar PDF:', error);
                alert('Erro ao carregar o PDF. Por favor, tente novamente.');
            }
        },

        async renderPage(pageNum) {
            const page = await this.pdfDoc.getPage(pageNum);
            const viewport = page.getViewport({ scale: this.scale });

            this.canvas.height = viewport.height;
            this.canvas.width = viewport.width;
            this.annotationCanvas.height = viewport.height;
            this.annotationCanvas.width = viewport.width;

            const renderContext = {
                canvasContext: this.ctx,
                viewport: viewport
            };

            await page.render(renderContext).promise;
            this.redrawAnnotations();
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
            const container = this.canvas.parentElement.parentElement;
            const containerWidth = container.clientWidth - 40;
            this.scale = containerWidth / this.canvas.width * this.scale;
            await this.renderPage(this.currentPage);
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

            const comentario = prompt('Adicione um comentário (opcional):');
            this.currentAnnotation.comentario = comentario || '';

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

            for (let annotation of pageAnnotations) {
                this.annotationCtx.strokeStyle = this.getToolColor(annotation.tipo);
                this.annotationCtx.fillStyle = this.getToolColor(annotation.tipo);
                this.annotationCtx.lineWidth = 2;
                this.annotationCtx.globalAlpha = 0.7;

                if (annotation.tipo === 'drawing' && annotation.dados.points) {
                    this.annotationCtx.beginPath();
                    this.annotationCtx.moveTo(annotation.dados.points[0][0], annotation.dados.points[0][1]);
                    for (let point of annotation.dados.points) {
                        this.annotationCtx.lineTo(point[0], point[1]);
                    }
                    this.annotationCtx.stroke();
                } else if (annotation.tipo === 'area' || annotation.tipo === 'highlight') {
                    const width = annotation.dados.endX - annotation.dados.startX;
                    const height = annotation.dados.endY - annotation.dados.startY;
                    this.annotationCtx.fillRect(annotation.dados.startX, annotation.dados.startY, width, height);
                    this.annotationCtx.strokeRect(annotation.dados.startX, annotation.dados.startY, width, height);
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
            }
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

        async salvarAnotacoes() {
            try {
                const response = await fetch(`/admin/processos/documentos/${this.documentoId}/anotacoes`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        anotacoes: this.annotations
                    })
                });

                if (response.ok) {
                    alert('Anotações salvas com sucesso!');
                } else {
                    alert('Erro ao salvar anotações. Tente novamente.');
                }
            } catch (error) {
                console.error('Erro ao salvar:', error);
                alert('Erro ao salvar anotações. Tente novamente.');
            }
        },

        async exportarPDF() {
            alert('Funcionalidade de exportação em desenvolvimento. As anotações foram salvas no sistema.');
        }
    };
}
</script>
