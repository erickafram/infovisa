{{-- Componente de visualização de PDF com anotações --}}
{{-- Espera que as variáveis documentoIdAnotacoes e pdfUrlAnotacoes estejam disponíveis no contexto Alpine.js --}}
    
    {{-- Toolbar --}}
    <div class="pdf-toolbar bg-gray-50 border-b border-gray-200 p-2 flex items-center justify-between gap-2 flex-wrap">
        {{-- Navegação --}}
        <div class="flex items-center gap-1">
            <button @click="previousPage()" 
                    :disabled="currentPage === 1"
                    class="px-2 py-1 text-sm bg-gray-600 text-white rounded hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                ← Ant
            </button>
            
            <div class="flex items-center gap-1">
                <span class="text-xs text-gray-700">Pág</span>
                <input type="number" 
                       x-model.number="currentPage" 
                       @change="goToPage(currentPage)"
                       min="1" 
                       :max="totalPages"
                       class="w-12 px-1 py-1 text-xs border border-gray-300 rounded text-center">
                <span class="text-xs text-gray-700">de <span x-text="totalPages"></span></span>
            </div>

            <button @click="nextPage()" 
                    :disabled="currentPage === totalPages"
                    class="px-2 py-1 text-sm bg-gray-600 text-white rounded hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                Prox →
            </button>
        </div>

        {{-- Zoom --}}
        <div class="flex items-center gap-1 border-l border-gray-300 pl-2">
            <button @click="zoomOut()" 
                    class="p-1.5 bg-gray-600 text-white rounded hover:bg-gray-700 transition" title="Reduzir">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/>
                </svg>
            </button>
            
            <span class="text-xs font-medium text-gray-700" x-text="Math.round(scale * 100) + '%'"></span>
            
            <button @click="zoomIn()" 
                    class="p-1.5 bg-gray-600 text-white rounded hover:bg-gray-700 transition" title="Ampliar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                </svg>
            </button>

            <button @click="fitToWidth()" 
                    class="px-2 py-1 text-xs bg-gray-600 text-white rounded hover:bg-gray-700 transition">
                Ajustar
            </button>
        </div>

        {{-- Ferramentas de Anotação --}}
        <div class="flex items-center gap-1 border-l border-gray-300 pl-2">
            <span class="text-xs font-medium text-gray-700">Ferramentas:</span>
            
            <button @click="setTool('select')" 
                    :class="currentTool === 'select' ? 'bg-purple-100 text-purple-700 border-purple-300' : 'bg-white border-gray-300'"
                    class="p-1.5 rounded border hover:bg-gray-50 transition" title="Selecionar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                </svg>
            </button>

            <button @click="setTool('highlight')" 
                    :class="currentTool === 'highlight' ? 'bg-yellow-100 text-yellow-700 border-yellow-300' : 'bg-white border-gray-300'"
                    class="p-1.5 rounded border hover:bg-gray-50 transition" title="Destacar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </button>

            <button @click="setTool('text')" 
                    :class="currentTool === 'text' ? 'bg-blue-100 text-blue-700 border-blue-300' : 'bg-white border-gray-300'"
                    class="p-1.5 rounded border hover:bg-gray-50 transition" title="Texto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </button>

            <button @click="setTool('drawing')" 
                    :class="currentTool === 'drawing' ? 'bg-red-100 text-red-700 border-red-300' : 'bg-white border-gray-300'"
                    class="p-1.5 rounded border hover:bg-gray-50 transition" title="Desenhar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
            </button>

            <button @click="setTool('area')" 
                    :class="currentTool === 'area' ? 'bg-green-100 text-green-700 border-green-300' : 'bg-white border-gray-300'"
                    class="p-1.5 rounded border hover:bg-gray-50 transition" title="Área">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z"/>
                </svg>
            </button>

            <button @click="setTool('comment')" 
                    :class="currentTool === 'comment' ? 'bg-indigo-100 text-indigo-700 border-indigo-300' : 'bg-white border-gray-300'"
                    class="p-1.5 rounded border hover:bg-gray-50 transition" title="Comentário">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
            </button>
        </div>

        {{-- Ações --}}
        <div class="flex items-center gap-1 border-l border-gray-300 pl-2">
            <button @click="desfazerAnotacao()" 
                    :disabled="annotations.length === 0"
                    :class="annotations.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-orange-700'"
                    class="px-2 py-1 text-xs bg-orange-600 text-white rounded transition flex items-center gap-1"
                    title="Desfazer última anotação (Ctrl+Z)">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                <span>Desfazer</span>
                <span x-show="annotations.length > 0" class="text-xs bg-white text-orange-600 px-1 py-0.5 rounded-full font-bold" x-text="annotations.length"></span>
            </button>

            <button @click="limparTodasAnotacoes()" 
                    :disabled="annotations.length === 0"
                    :class="annotations.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-700'"
                    class="px-2 py-1 text-xs bg-red-600 text-white rounded transition flex items-center gap-1"
                    title="Limpar todas as anotações">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Limpar</span>
            </button>

            <button @click="salvarAnotacoes()" 
                    class="px-2 py-1 text-xs bg-purple-600 text-white rounded hover:bg-purple-700 transition flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                <span>Salvar</span>
            </button>

            <button @click="exportarPDF()" 
                    class="px-2 py-1 text-xs bg-gray-600 text-white rounded hover:bg-gray-700 transition flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Exportar</span>
            </button>
        </div>
    </div>

    {{-- Canvas Container --}}
    <div class="pdf-canvas-container relative overflow-auto bg-gray-100 flex justify-center" style="height: 800px;">
        <div class="pdf-canvas-wrapper inline-block relative my-4">
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
                <div class="bg-white p-3 rounded-lg border border-gray-200 flex items-start justify-between gap-3 hover:bg-gray-50 transition cursor-pointer"
                     @click="selectedAnnotationIndex = annotations.indexOf(anotacao); redrawAnnotations();"
                     :class="{'ring-2 ring-purple-500': selectedAnnotationIndex === annotations.indexOf(anotacao)}">
                    {{-- Número de Referência --}}
                    <div class="flex-shrink-0 w-7 h-7 rounded-full bg-purple-600 text-white flex items-center justify-center text-xs font-bold">
                        <span x-text="index + 1"></span>
                    </div>
                    
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
                        </div>
                        <p class="text-sm text-gray-700" x-text="anotacao.comentario || 'Sem comentário'"></p>
                    </div>
                    
                    <button @click.stop="deleteAnnotation(index)" 
                            class="text-red-600 hover:text-red-700 p-1 flex-shrink-0">
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

{{-- A função pdfViewerAnotacoes está carregada globalmente em resources/views/layouts/admin.blade.php --}}
