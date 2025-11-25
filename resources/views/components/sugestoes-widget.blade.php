{{-- Widget de Sugestões do Sistema --}}
<div id="sugestoes-widget" class="fixed right-0 top-1/2 -translate-y-1/2 z-50">
    <button id="btn-abrir-sugestoes" class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white p-3 rounded-l-lg shadow-lg transition-all duration-300 hover:scale-105" title="Sugestões e Melhorias">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
        </svg>
    </button>
</div>

<div id="painel-sugestoes" class="fixed inset-0 z-50 hidden">
    <div id="overlay-sugestoes" class="absolute inset-0 bg-black/50"></div>
    <div id="conteudo-painel-sugestoes" class="absolute right-0 top-0 h-full w-full max-w-xl bg-white shadow-2xl transform translate-x-full transition-transform duration-300 flex flex-col">
        <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <div>
                    <h2 class="text-lg font-bold">Sugestões e Melhorias</h2>
                    <p class="text-sm text-amber-100">Página: <span id="pagina-atual-label" class="font-mono text-xs"></span></p>
                </div>
            </div>
            <button id="btn-fechar-sugestoes" class="p-2 hover:bg-white/20 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="flex border-b bg-gray-50">
            <button id="tab-lista" class="flex-1 px-4 py-3 text-sm font-medium text-amber-600 border-b-2 border-amber-500 bg-white">Todas as Sugestões</button>
            <button id="tab-nova" class="flex-1 px-4 py-3 text-sm font-medium text-gray-500 border-b-2 border-transparent">Nova Sugestão</button>
        </div>
        
        <div id="filtros-sugestoes" class="p-3 bg-gray-50 border-b flex gap-2 flex-wrap">
            <select id="filtro-status" class="text-sm border-gray-300 rounded-lg">
                <option value="">Todos os Status</option>
                <option value="pendente">Pendente</option>
                <option value="em_analise">Em Análise</option>
                <option value="em_desenvolvimento">Em Desenvolvimento</option>
                <option value="concluido">Concluído</option>
                <option value="cancelado">Cancelado</option>
            </select>
            <select id="filtro-tipo" class="text-sm border-gray-300 rounded-lg">
                <option value="">Todos os Tipos</option>
                <option value="funcionalidade">Nova Funcionalidade</option>
                <option value="melhoria">Melhoria</option>
                <option value="modulo">Novo Módulo</option>
                <option value="correcao">Correção de Bug</option>
                <option value="outro">Outro</option>
            </select>
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" id="filtro-minhas" class="rounded text-amber-500">
                Minhas sugestões
            </label>
        </div>
        
        <div class="flex-1 overflow-y-auto">
            <div id="lista-sugestoes" class="p-4 space-y-3">
                <div id="loading-sugestoes" class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-500"></div>
                </div>
            </div>
            
            <div id="form-nova-sugestao" class="p-5 hidden">
                {{-- Header do formulário --}}
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-amber-100 to-orange-100 rounded-full mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Compartilhe sua ideia!</h3>
                    <p class="text-sm text-gray-500 mt-1">Sua sugestão é muito importante para melhorarmos o sistema</p>
                </div>
                
                <form id="sugestao-form" class="space-y-5">
                    {{-- Tipo de Sugestão com ícones --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                Tipo de Sugestão
                            </span>
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="tipo-option relative cursor-pointer">
                                <input type="radio" name="tipo" value="funcionalidade" required class="sr-only peer">
                                <div class="flex items-center gap-2 p-3 border-2 border-gray-200 rounded-xl hover:border-amber-300 peer-checked:border-amber-500 peer-checked:bg-amber-50 transition-all">
                                    <span class="text-xl">✨</span>
                                    <span class="text-sm font-medium text-gray-700">Nova Funcionalidade</span>
                                </div>
                            </label>
                            <label class="tipo-option relative cursor-pointer">
                                <input type="radio" name="tipo" value="melhoria" class="sr-only peer">
                                <div class="flex items-center gap-2 p-3 border-2 border-gray-200 rounded-xl hover:border-amber-300 peer-checked:border-amber-500 peer-checked:bg-amber-50 transition-all">
                                    <span class="text-xl">📈</span>
                                    <span class="text-sm font-medium text-gray-700">Melhoria</span>
                                </div>
                            </label>
                            <label class="tipo-option relative cursor-pointer">
                                <input type="radio" name="tipo" value="modulo" class="sr-only peer">
                                <div class="flex items-center gap-2 p-3 border-2 border-gray-200 rounded-xl hover:border-amber-300 peer-checked:border-amber-500 peer-checked:bg-amber-50 transition-all">
                                    <span class="text-xl">📦</span>
                                    <span class="text-sm font-medium text-gray-700">Novo Módulo</span>
                                </div>
                            </label>
                            <label class="tipo-option relative cursor-pointer">
                                <input type="radio" name="tipo" value="correcao" class="sr-only peer">
                                <div class="flex items-center gap-2 p-3 border-2 border-gray-200 rounded-xl hover:border-amber-300 peer-checked:border-amber-500 peer-checked:bg-amber-50 transition-all">
                                    <span class="text-xl">🐛</span>
                                    <span class="text-sm font-medium text-gray-700">Correção de Bug</span>
                                </div>
                            </label>
                            <label class="tipo-option relative cursor-pointer col-span-2">
                                <input type="radio" name="tipo" value="outro" class="sr-only peer">
                                <div class="flex items-center justify-center gap-2 p-3 border-2 border-gray-200 rounded-xl hover:border-amber-300 peer-checked:border-amber-500 peer-checked:bg-amber-50 transition-all">
                                    <span class="text-xl">💡</span>
                                    <span class="text-sm font-medium text-gray-700">Outro</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    {{-- Título --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Título da Sugestão
                            </span>
                        </label>
                        <input type="text" name="titulo" required maxlength="255" 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-amber-500 focus:ring-0 transition-colors text-gray-800 placeholder-gray-400" 
                            placeholder="Ex: Adicionar filtro por data nos relatórios">
                    </div>
                    
                    {{-- Descrição --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                </svg>
                                Descrição Detalhada
                            </span>
                        </label>
                        <textarea name="descricao" required rows="4" 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-amber-500 focus:ring-0 transition-colors text-gray-800 placeholder-gray-400 resize-none" 
                            placeholder="Descreva sua sugestão com o máximo de detalhes possível. Quanto mais informações, melhor poderemos entender e implementar sua ideia!"></textarea>
                        <p class="text-xs text-gray-400 mt-1">Dica: Inclua exemplos de uso e benefícios esperados</p>
                    </div>
                    
                    {{-- Info da página --}}
                    <div class="flex items-center gap-3 p-4 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl">
                        <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-amber-800">Vinculada à página</p>
                            <p id="pagina-form-label" class="text-xs font-mono text-amber-600 bg-amber-100 px-2 py-0.5 rounded mt-1 inline-block"></p>
                        </div>
                    </div>
                    
                    {{-- Botões --}}
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white py-3 px-6 rounded-xl font-semibold shadow-lg shadow-amber-500/30 transition-all duration-200 hover:shadow-xl hover:shadow-amber-500/40 flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Enviar Sugestão
                        </button>
                        <button type="button" id="btn-cancelar-form" class="px-6 py-3 border-2 border-gray-200 rounded-xl text-gray-600 font-medium hover:bg-gray-50 hover:border-gray-300 transition-all">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
            
            <div id="detalhes-sugestao" class="p-4 hidden"></div>
        </div>
    </div>
</div>

<div id="modal-editar-sugestao" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="p-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-bold">Gerenciar Sugestão</h3>
                <button id="btn-fechar-modal-editar" class="p-2 hover:bg-gray-100 rounded-lg">&times;</button>
            </div>
            <form id="form-editar-sugestao" class="p-4 space-y-4">
                <input type="hidden" name="sugestao_id" id="edit-sugestao-id">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="edit-status" class="w-full border-gray-300 rounded-lg">
                        <option value="pendente">Pendente</option>
                        <option value="em_analise">Em Análise</option>
                        <option value="em_desenvolvimento">Em Desenvolvimento</option>
                        <option value="concluido">Concluído</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Resposta</label>
                    <textarea name="resposta_admin" id="edit-resposta" rows="3" class="w-full border-gray-300 rounded-lg" placeholder="Feedback para o usuário"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Checklist</label>
                    <div id="edit-checklist" class="space-y-2 mb-2"></div>
                    <div class="flex gap-2">
                        <input type="text" id="novo-item-checklist" class="flex-1 text-sm border-gray-300 rounded-lg" placeholder="Novo item">
                        <button type="button" id="btn-add-checklist" class="px-3 py-2 bg-gray-100 rounded-lg">+</button>
                    </div>
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-amber-500 to-orange-500 text-white py-2 px-4 rounded-lg font-medium">Salvar</button>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('js/sugestoes-widget.js') }}"></script>
