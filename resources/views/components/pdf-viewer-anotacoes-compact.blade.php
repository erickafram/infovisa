{{-- Componente de visualização de PDF com anotações - Versão Compacta --}}
{{-- PDF centralizado e lista de anotações menor --}}
    
    {{-- Toolbar Compacta --}}
    <div class="pdf-toolbar bg-gray-100 border-b border-gray-200 px-3 py-1.5 flex items-center justify-between gap-2 flex-wrap">
        {{-- Navegação --}}
        <div class="flex items-center gap-1">
            <button @click="previousPage()" 
                    :disabled="currentPage === 1"
                    class="px-2 py-1 text-xs bg-gray-600 text-white rounded hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                ←
            </button>
            
            <div class="flex items-center gap-1">
                <input type="number" 
                       x-model.number="currentPage" 
                       @change="goToPage(currentPage)"
                       min="1" 
                       :max="totalPages"
                       class="w-10 px-1 py-0.5 text-xs border border-gray-300 rounded text-center">
                <span class="text-xs text-gray-600">/ <span x-text="totalPages"></span></span>
            </div>

            <button @click="nextPage()" 
                    :disabled="currentPage === totalPages"
                    class="px-2 py-1 text-xs bg-gray-600 text-white rounded hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                →
            </button>
        </div>

        {{-- Zoom --}}
        <div class="flex items-center gap-1">
            <button @click="zoomOut()" 
                    class="p-1 bg-gray-600 text-white rounded hover:bg-gray-700 transition" title="Reduzir (Ctrl + Scroll)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/>
                </svg>
            </button>
            
            {{-- Dropdown de zoom com valores predefinidos --}}
            <div x-data="{ openZoom: false }" class="relative">
                <button @click="openZoom = !openZoom" 
                        class="text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded px-2 py-1 hover:bg-gray-50 transition flex items-center gap-1 min-w-[60px] justify-center">
                    <span x-text="Math.round(scale * 100) + '%'"></span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                
                <div x-show="openZoom" 
                     @click.outside="openZoom = false"
                     x-transition
                     class="absolute top-full mt-1 left-0 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-50 min-w-[100px]">
                    <button @click="setZoom(50); openZoom = false" class="w-full px-3 py-1.5 text-xs text-left hover:bg-gray-100 transition">50%</button>
                    <button @click="setZoom(75); openZoom = false" class="w-full px-3 py-1.5 text-xs text-left hover:bg-gray-100 transition">75%</button>
                    <button @click="setZoom(100); openZoom = false" class="w-full px-3 py-1.5 text-xs text-left hover:bg-gray-100 transition">100%</button>
                    <button @click="setZoom(125); openZoom = false" class="w-full px-3 py-1.5 text-xs text-left hover:bg-gray-100 transition">125%</button>
                    <button @click="setZoom(150); openZoom = false" class="w-full px-3 py-1.5 text-xs text-left hover:bg-gray-100 transition">150%</button>
                    <button @click="setZoom(200); openZoom = false" class="w-full px-3 py-1.5 text-xs text-left hover:bg-gray-100 transition">200%</button>
                    <button @click="setZoom(300); openZoom = false" class="w-full px-3 py-1.5 text-xs text-left hover:bg-gray-100 transition">300%</button>
                    <button @click="setZoom(400); openZoom = false" class="w-full px-3 py-1.5 text-xs text-left hover:bg-gray-100 transition">400%</button>
                </div>
            </div>
            
            <button @click="zoomIn()" 
                    class="p-1 bg-gray-600 text-white rounded hover:bg-gray-700 transition" title="Ampliar (Ctrl + Scroll)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                </svg>
            </button>

            <button @click="fitToWidth()" 
                    class="px-2 py-1 text-xs bg-gray-500 text-white rounded hover:bg-gray-600 transition"
                    title="Ajustar à largura">
                Ajustar
            </button>
            
            {{-- Dica de navegação e performance --}}
            <div class="flex items-center gap-2">
                <div class="text-[10px] text-gray-500 px-2 py-1 bg-blue-50 rounded border border-blue-200 flex items-center gap-1">
                    <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-blue-700 font-medium">Espaço + Arrastar</span> para mover
                </div>
                
                {{-- Dica de performance --}}
                <div class="text-[10px] text-gray-500 px-2 py-1 bg-amber-50 rounded border border-amber-200 flex items-center gap-1">
                    <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span class="text-amber-700 font-medium">Dica:</span> Feche outras abas para melhor performance
                </div>
            </div>
        </div>

        {{-- Ferramentas de Anotação --}}
        <div class="flex items-center gap-0.5">
            <button @click="setTool('select')" 
                    :class="currentTool === 'select' ? 'bg-purple-100 text-purple-700 border-purple-300' : 'bg-white border-gray-300'"
                    class="p-1 rounded border hover:bg-gray-50 transition" title="Selecionar">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                </svg>
            </button>

            <button @click="setTool('highlight')" 
                    :class="currentTool === 'highlight' ? 'bg-yellow-100 text-yellow-700 border-yellow-300' : 'bg-white border-gray-300'"
                    class="p-1 rounded border hover:bg-gray-50 transition" title="Destacar">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </button>

            <button @click="setTool('text')" 
                    :class="currentTool === 'text' ? 'bg-blue-100 text-blue-700 border-blue-300' : 'bg-white border-gray-300'"
                    class="p-1 rounded border hover:bg-gray-50 transition" title="Texto">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </button>

            <button @click="setTool('drawing')" 
                    :class="currentTool === 'drawing' ? 'bg-red-100 text-red-700 border-red-300' : 'bg-white border-gray-300'"
                    class="p-1 rounded border hover:bg-gray-50 transition" title="Desenhar">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
            </button>

            <button @click="setTool('comment')" 
                    :class="currentTool === 'comment' ? 'bg-indigo-100 text-indigo-700 border-indigo-300' : 'bg-white border-gray-300'"
                    class="p-1 rounded border hover:bg-gray-50 transition" title="Comentário">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
            </button>
        </div>

        {{-- Ações --}}
        <div class="flex items-center gap-1">
            <button @click="desfazerAnotacao()" 
                    :disabled="annotations.length === 0"
                    :class="annotations.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-orange-700'"
                    class="px-2 py-1 text-xs bg-orange-600 text-white rounded transition flex items-center gap-1"
                    title="Desfazer (Ctrl+Z)">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                <span x-show="annotations.length > 0" class="text-xs bg-white text-orange-600 px-1 rounded-full font-bold" x-text="annotations.length"></span>
            </button>

            <button @click="salvarAnotacoes()" 
                    class="px-2 py-1 text-xs bg-purple-600 text-white rounded hover:bg-purple-700 transition flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                Salvar
            </button>

            <button @click="exportarPDF()" 
                    class="px-2 py-1 text-xs bg-gray-600 text-white rounded hover:bg-gray-700 transition flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Área Principal: PDF Centralizado + Painel de Anotações Lateral --}}
    <div class="flex-1 flex overflow-hidden">
        {{-- Canvas Container - PDF Centralizado --}}
        <div class="flex-1 pdf-canvas-container relative overflow-auto bg-gray-200 flex items-start justify-center" style="min-height: 0;">
            {{-- Indicador de carregamento com progresso --}}
            <div x-show="isRendering" 
                 x-transition
                 class="absolute top-4 left-1/2 transform -translate-x-1/2 z-10 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-medium">
                    <span x-show="renderQuality === 'preview'">Carregando preview...</span>
                    <span x-show="renderQuality !== 'preview'">Renderizando...</span>
                </span>
            </div>
            
            {{-- Indicador de qualidade --}}
            <div x-show="renderQuality === 'preview'" 
                 class="absolute top-4 right-4 z-10 bg-blue-100 text-blue-800 px-3 py-1 rounded-lg text-xs font-medium border border-blue-300 flex items-center gap-1">
                <svg class="animate-pulse w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                Melhorando qualidade...
            </div>
            
            <div x-show="renderQuality === 'low'" 
                 class="absolute top-4 right-4 z-10 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-lg text-xs font-medium border border-yellow-300">
                ⚡ Modo Rápido
            </div>
            
            <div x-show="renderQuality === 'medium'" 
                 class="absolute top-4 right-4 z-10 bg-orange-100 text-orange-800 px-3 py-1 rounded-lg text-xs font-medium border border-orange-300">
                ⚡ Qualidade Média
            </div>
            
            <div class="pdf-canvas-wrapper inline-block relative my-4">
                <canvas id="pdf-canvas" class="shadow-xl"></canvas>
                <canvas id="annotation-canvas" 
                        class="absolute top-0 left-0 cursor-crosshair" 
                        @mousedown="startAnnotation($event)"
                        @mousemove="drawAnnotation($event)"
                        @mouseup="endAnnotation($event)"
                        @click="addComment($event)"></canvas>
            </div>
        </div>

        {{-- Painel Lateral de Anotações - Compacto --}}
        <div class="w-64 border-l border-gray-200 bg-gray-50 flex flex-col overflow-hidden">
            <div class="px-3 py-2 border-b border-gray-200 bg-white">
                <h3 class="text-xs font-semibold text-gray-700 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    Anotações
                    <span class="ml-auto px-1.5 py-0.5 text-[10px] bg-purple-100 text-purple-700 rounded-full" x-text="getCurrentPageAnnotations().length"></span>
                </h3>
            </div>
            
            <div class="flex-1 overflow-y-auto p-2 space-y-1.5">
                <template x-for="(anotacao, index) in getCurrentPageAnnotations()" :key="index">
                    <div class="bg-white p-2 rounded border border-gray-200 hover:border-purple-300 transition cursor-pointer text-xs"
                         @click="selectedAnnotationIndex = annotations.indexOf(anotacao); redrawAnnotations();"
                         :class="{'ring-1 ring-purple-500 border-purple-500': selectedAnnotationIndex === annotations.indexOf(anotacao)}">
                        <div class="flex items-start gap-2">
                            {{-- Número --}}
                            <div class="flex-shrink-0 w-5 h-5 rounded-full bg-purple-600 text-white flex items-center justify-center text-[10px] font-bold">
                                <span x-text="index + 1"></span>
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1 mb-0.5">
                                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full"
                                          :class="{
                                              'bg-yellow-100 text-yellow-700': anotacao.tipo === 'highlight',
                                              'bg-blue-100 text-blue-700': anotacao.tipo === 'text',
                                              'bg-red-100 text-red-700': anotacao.tipo === 'drawing',
                                              'bg-green-100 text-green-700': anotacao.tipo === 'area',
                                              'bg-indigo-100 text-indigo-700': anotacao.tipo === 'comment'
                                          }"
                                          x-text="getTipoLabel(anotacao.tipo)"></span>
                                </div>
                                <p class="text-gray-700 truncate" x-text="anotacao.comentario || 'Sem comentário'"></p>
                                <template x-if="anotacao.usuario_nome">
                                    <p class="text-[10px] text-gray-400 mt-0.5" x-text="anotacao.usuario_nome"></p>
                                </template>
                            </div>
                            
                            {{-- Botão excluir --}}
                            <template x-if="!anotacao.usuario_id || anotacao.usuario_id == {{ auth('interno')->id() }}">
                                <button @click.stop="deleteAnnotation(index)" 
                                        class="text-red-500 hover:text-red-700 p-0.5 flex-shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
                
                <div x-show="getCurrentPageAnnotations().length === 0" class="text-center py-6 text-gray-400">
                    <svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    <p class="text-xs">Nenhuma anotação</p>
                </div>
            </div>
        </div>
    </div>
