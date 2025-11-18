/**
 * Sistema de Busca no Diário Oficial
 */
class DiarioSearch {
    constructor() {
        this.currentSearch = null;
        this.savedSearches = [];
        this.init();
    }

    init() {
        this.initializeEventListeners();
        this.loadSavedSearches();
        this.setDefaultDates();
    }

    /**
     * Inicializa event listeners
     */
    initializeEventListeners() {
        // Formulário de busca
        document.getElementById('searchForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.performSearch();
        });

        // Botão salvar busca
        document.getElementById('saveSearchButton').addEventListener('click', () => {
            this.showSaveSearchModal();
        });

        // Modal salvar busca
        document.getElementById('saveSearchForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveSearch();
        });

        document.getElementById('cancelSaveButton').addEventListener('click', () => {
            this.hideSaveSearchModal();
        });

        // Toggle buscas salvas
        document.getElementById('toggleSavedSearches').addEventListener('click', () => {
            this.toggleSavedSearches();
        });
    }

    /**
     * Define datas padrão (último mês)
     */
    setDefaultDates() {
        const today = new Date();
        const lastMonth = new Date(today);
        lastMonth.setMonth(lastMonth.getMonth() - 1);

        document.getElementById('data_final').value = this.formatDate(today);
        document.getElementById('data_inicial').value = this.formatDate(lastMonth);
    }

    /**
     * Formata data para input type="date"
     */
    formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    /**
     * Realiza busca no Diário Oficial
     */
    async performSearch() {
        const formData = new FormData(document.getElementById('searchForm'));
        const data = {
            texto: formData.get('texto'),
            data_inicial: formData.get('data_inicial'),
            data_final: formData.get('data_final')
        };

        // Validação básica
        if (data.texto.length < 3) {
            this.showError('O texto de busca deve ter pelo menos 3 caracteres');
            return;
        }

        this.currentSearch = data;
        this.showLoading();

        try {
            const response = await fetch('/admin/diario-oficial/buscar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            this.hideLoading();

            if (result.success) {
                this.displayResults(result.results, result.totalResults);
            } else {
                this.showError(result.message || 'Erro ao realizar busca');
            }
        } catch (error) {
            this.hideLoading();
            this.showError('Erro ao conectar com o servidor: ' + error.message);
        }
    }

    /**
     * Exibe resultados da busca
     */
    displayResults(results, total) {
        const resultsList = document.getElementById('resultsList');
        const resultsCount = document.getElementById('resultsCount');
        const resultsDiv = document.getElementById('results');

        resultsCount.textContent = total;

        if (total === 0) {
            resultsList.innerHTML = `
                <div class="p-8 text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-lg font-medium mb-2">Nenhum resultado encontrado</p>
                    <p class="text-sm">Tente ajustar os termos de busca ou o período</p>
                </div>
            `;
        } else {
            resultsList.innerHTML = results.map((result, index) => this.createResultCard(result, index)).join('');
        }

        resultsDiv.classList.remove('hidden');
        resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /**
     * Cria card de resultado
     */
    createResultCard(result, index) {
        const viewerUrl = `/admin/diario-oficial/pdf/viewer?url=${encodeURIComponent(result.pdf_url)}&texto=${encodeURIComponent(this.currentSearch.texto)}&titulo=${encodeURIComponent(result.titulo)}`;
        
        return `
            <div class="p-6 hover:bg-gradient-to-r hover:from-gray-50 hover:to-indigo-50 transition-all border-l-4 border-transparent hover:border-indigo-500">
                <div class="flex items-start justify-between gap-6">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-xl text-sm font-bold shadow-md flex-shrink-0">
                                ${index + 1}
                            </span>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-bold text-gray-900 mb-2 leading-tight">${this.escapeHtml(result.titulo)}</h3>
                                <div class="flex flex-wrap gap-2 text-sm">
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="font-medium">${result.data}</span>
                            </div>
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-purple-50 text-purple-700 rounded-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span class="font-medium">Ed. ${result.edicao}</span>
                            </div>
                            ${result.paginas ? `
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-green-50 text-green-700 rounded-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="font-medium">${result.paginas}</span>
                            </div>
                            ` : ''}
                            ${result.tamanho ? `
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-orange-50 text-orange-700 rounded-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                </svg>
                                <span class="font-medium">${result.tamanho}</span>
                            </div>
                            ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 min-w-[140px]">
                        <a href="${viewerUrl}" 
                           target="_blank"
                           style="color: white !important; text-decoration: none !important;"
                           class="flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-sm font-semibold rounded-lg transition-all shadow-md hover:shadow-lg whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span style="color: white !important;">Visualizar</span>
                        </a>
                        <a href="${result.pdf_url}" 
                           target="_blank"
                           download
                           style="color: #4f46e5 !important; text-decoration: none !important;"
                           class="flex items-center justify-center gap-2 px-4 py-2.5 bg-white border-2 border-indigo-600 text-indigo-600 text-sm font-semibold rounded-lg hover:bg-indigo-50 transition-all whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span style="color: #4f46e5 !important;">Download</span>
                        </a>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Mostra modal para salvar busca
     */
    showSaveSearchModal() {
        if (!this.currentSearch) {
            this.showError('Realize uma busca primeiro antes de salvar');
            return;
        }

        const modal = document.getElementById('saveSearchModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('search_name').focus();
    }

    /**
     * Esconde modal de salvar busca
     */
    hideSaveSearchModal() {
        const modal = document.getElementById('saveSearchModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('saveSearchForm').reset();
    }

    /**
     * Salva busca atual
     */
    async saveSearch() {
        const nome = document.getElementById('search_name').value;
        const executarDiariamente = document.getElementById('executar_diariamente').checked;

        if (!nome || !this.currentSearch) {
            return;
        }

        try {
            const response = await fetch('/admin/diario-oficial/salvar-busca', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    nome: nome,
                    texto: this.currentSearch.texto,
                    data_inicial: this.currentSearch.data_inicial,
                    data_final: this.currentSearch.data_final,
                    executar_diariamente: executarDiariamente
                })
            });

            const result = await response.json();

            if (result.success) {
                this.hideSaveSearchModal();
                this.loadSavedSearches();
                this.showSuccess('Busca salva com sucesso!');
            } else {
                this.showError(result.message || 'Erro ao salvar busca');
            }
        } catch (error) {
            this.showError('Erro ao salvar busca: ' + error.message);
        }
    }

    /**
     * Carrega buscas salvas
     */
    async loadSavedSearches() {
        try {
            const response = await fetch('/admin/diario-oficial/buscas-salvas', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (result.success) {
                this.savedSearches = result.buscas;
                this.displaySavedSearches();
            }
        } catch (error) {
            console.error('Erro ao carregar buscas salvas:', error);
        }
    }

    /**
     * Exibe buscas salvas
     */
    displaySavedSearches() {
        const content = document.getElementById('savedSearchesContent');
        const noSearches = document.getElementById('noSavedSearches');
        const count = document.getElementById('savedSearchesCount');

        count.textContent = this.savedSearches.length;

        if (this.savedSearches.length === 0) {
            content.classList.add('hidden');
            noSearches.classList.remove('hidden');
        } else {
            content.classList.remove('hidden');
            noSearches.classList.add('hidden');

            content.innerHTML = this.savedSearches.map((search, index) => `
                <div class="group relative flex items-center justify-between p-5 bg-gradient-to-r from-gray-50 to-white rounded-xl hover:from-indigo-50 hover:to-purple-50 transition-all border-2 border-transparent hover:border-indigo-200 shadow-sm hover:shadow-md">
                    <div class="flex items-start gap-4 flex-1">
                        <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-lg text-sm font-bold shadow-md flex-shrink-0">
                            ${index + 1}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="font-bold text-gray-900 mb-2 text-base">${this.escapeHtml(search.nome)}</h4>
                                ${search.executar_diariamente ? `
                                <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-full mb-2 border border-blue-200 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Automático
                                </span>
                                ` : ''}
                            </div>
                            <div class="flex flex-wrap items-center gap-3 text-sm">
                                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <span>"${this.escapeHtml(search.texto)}"</span>
                                </div>
                                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-green-50 text-green-700 rounded-lg font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>${this.formatDateBR(search.data_inicial)} até ${this.formatDateBR(search.data_final)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 ml-4">
                        <button onclick="diarioSearch.executeSearch(${search.id})" 
                                class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center gap-2"
                                title="Executar busca">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Executar</span>
                        </button>
                        <button onclick="diarioSearch.loadSearch(${search.id})" 
                                class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                title="Carregar no formulário">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button onclick="diarioSearch.deleteSearch(${search.id})" 
                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                title="Excluir busca">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            `).join('');
        }
    }

    /**
     * Executa busca salva automaticamente
     */
    async executeSearch(id) {
        const search = this.savedSearches.find(s => s.id === id);
        if (!search) return;

        // Carrega os dados no formulário
        document.getElementById('texto').value = search.texto;
        document.getElementById('data_inicial').value = search.data_inicial;
        document.getElementById('data_final').value = search.data_final;

        // Fecha a lista de buscas salvas
        const list = document.getElementById('savedSearchesList');
        if (list && !list.classList.contains('hidden')) {
            list.classList.add('hidden');
        }

        // Scroll para o topo
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Aguarda um pouco para o scroll e executa a busca
        setTimeout(() => {
            this.performSearch();
        }, 300);
    }

    /**
     * Carrega busca salva no formulário (sem executar)
     */
    loadSearch(id) {
        const search = this.savedSearches.find(s => s.id === id);
        if (!search) return;

        document.getElementById('texto').value = search.texto;
        document.getElementById('data_inicial').value = search.data_inicial;
        document.getElementById('data_final').value = search.data_final;

        this.showSuccess('Busca carregada! Clique em "Buscar" para executar.');
        
        // Scroll para o formulário
        document.getElementById('searchForm').scrollIntoView({ behavior: 'smooth' });
    }

    /**
     * Exclui busca salva
     */
    async deleteSearch(id) {
        if (!confirm('Deseja realmente excluir esta busca salva?')) {
            return;
        }

        try {
            const response = await fetch(`/admin/diario-oficial/excluir-busca/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (result.success) {
                this.loadSavedSearches();
                this.showSuccess('Busca excluída com sucesso!');
            } else {
                this.showError(result.message || 'Erro ao excluir busca');
            }
        } catch (error) {
            this.showError('Erro ao excluir busca: ' + error.message);
        }
    }

    /**
     * Toggle buscas salvas
     */
    toggleSavedSearches() {
        const list = document.getElementById('savedSearchesList');
        const button = document.getElementById('toggleSavedSearches');
        
        list.classList.toggle('hidden');
        
        // Rotacionar ícone
        const svg = button.querySelector('svg');
        svg.style.transform = list.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    /**
     * Mostra loading
     */
    showLoading() {
        document.getElementById('loading').classList.remove('hidden');
        document.getElementById('results').classList.add('hidden');
        document.getElementById('error').classList.add('hidden');
    }

    /**
     * Esconde loading
     */
    hideLoading() {
        document.getElementById('loading').classList.add('hidden');
    }

    /**
     * Mostra erro
     */
    showError(message) {
        document.getElementById('errorMessage').textContent = message;
        document.getElementById('error').classList.remove('hidden');
        document.getElementById('results').classList.add('hidden');
        document.getElementById('loading').classList.add('hidden');
    }

    /**
     * Mostra mensagem de sucesso
     */
    showSuccess(message) {
        // Criar toast de sucesso
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in';
        toast.innerHTML = `
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>${message}</span>
            </div>
        `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    /**
     * Formata data para exibição (DD/MM/YYYY)
     */
    formatDateBR(dateString) {
        if (!dateString) return 'Data não informada';
        
        try {
            // Se já está no formato DD/MM/YYYY, retorna direto
            if (dateString.includes('/')) {
                return dateString;
            }
            
            // Se está no formato YYYY-MM-DD
            if (dateString.includes('-')) {
                const [year, month, day] = dateString.split('-');
                return `${day}/${month}/${year}`;
            }
            
            // Tenta criar Date object
            const date = new Date(dateString + 'T00:00:00');
            if (isNaN(date.getTime())) {
                return dateString; // Retorna original se não conseguir converter
            }
            
            return date.toLocaleDateString('pt-BR');
        } catch (error) {
            console.error('Erro ao formatar data:', error, dateString);
            return dateString;
        }
    }

    /**
     * Escapa HTML para prevenir XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Inicializar quando DOM estiver pronto
let diarioSearch;
document.addEventListener('DOMContentLoaded', () => {
    diarioSearch = new DiarioSearch();
});
