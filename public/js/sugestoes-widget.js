// Widget de Sugestões do Sistema
const SugestoesWidget = {
    paginaAtual: window.location.pathname,
    usuarioId: null,
    isAdmin: false,
    sugestoes: [],
    sugestaoAtual: null,
    checklistTemp: [],
    
    init(usuarioId, isAdmin) {
        this.usuarioId = usuarioId;
        this.isAdmin = isAdmin;
        this.bindEvents();
        this.atualizarLabelPagina();
    },
    
    bindEvents() {
        document.getElementById('btn-abrir-sugestoes')?.addEventListener('click', () => this.abrirPainel());
        document.getElementById('btn-fechar-sugestoes')?.addEventListener('click', () => this.fecharPainel());
        document.getElementById('overlay-sugestoes')?.addEventListener('click', () => this.fecharPainel());
        document.getElementById('tab-lista')?.addEventListener('click', () => this.mostrarTab('lista'));
        document.getElementById('tab-nova')?.addEventListener('click', () => this.mostrarTab('nova'));
        document.getElementById('filtro-status')?.addEventListener('change', () => this.carregarSugestoes());
        document.getElementById('filtro-tipo')?.addEventListener('change', () => this.carregarSugestoes());
        document.getElementById('filtro-minhas')?.addEventListener('change', () => this.carregarSugestoes());
        document.getElementById('sugestao-form')?.addEventListener('submit', (e) => this.enviarSugestao(e));
        document.getElementById('btn-cancelar-form')?.addEventListener('click', () => this.mostrarTab('lista'));
        document.getElementById('btn-fechar-modal-editar')?.addEventListener('click', () => this.fecharModalEditar());
        document.getElementById('form-editar-sugestao')?.addEventListener('submit', (e) => this.salvarEdicao(e));
        document.getElementById('btn-add-checklist')?.addEventListener('click', () => this.adicionarItemChecklist());
        document.getElementById('novo-item-checklist')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); this.adicionarItemChecklist(); }
        });
    },
    
    atualizarLabelPagina() {
        const label1 = document.getElementById('pagina-atual-label');
        const label2 = document.getElementById('pagina-form-label');
        if (label1) label1.textContent = this.paginaAtual;
        if (label2) label2.textContent = this.paginaAtual;
    },
    
    abrirPainel() {
        const painel = document.getElementById('painel-sugestoes');
        const conteudo = document.getElementById('conteudo-painel-sugestoes');
        painel?.classList.remove('hidden');
        setTimeout(() => conteudo?.classList.remove('translate-x-full'), 10);
        this.carregarSugestoes();
    },
    
    fecharPainel() {
        const painel = document.getElementById('painel-sugestoes');
        const conteudo = document.getElementById('conteudo-painel-sugestoes');
        conteudo?.classList.add('translate-x-full');
        setTimeout(() => { painel?.classList.add('hidden'); this.mostrarTab('lista'); }, 300);
    },
    
    mostrarTab(tab) {
        const tabLista = document.getElementById('tab-lista');
        const tabNova = document.getElementById('tab-nova');
        const listaSugestoes = document.getElementById('lista-sugestoes');
        const formNova = document.getElementById('form-nova-sugestao');
        const detalhesSugestao = document.getElementById('detalhes-sugestao');
        const filtros = document.getElementById('filtros-sugestoes');
        
        [tabLista, tabNova].forEach(t => {
            t?.classList.remove('text-amber-600', 'border-amber-500', 'bg-white');
            t?.classList.add('text-gray-500', 'border-transparent');
        });
        
        listaSugestoes?.classList.add('hidden');
        formNova?.classList.add('hidden');
        detalhesSugestao?.classList.add('hidden');
        filtros?.classList.add('hidden');
        
        if (tab === 'lista') {
            tabLista?.classList.add('text-amber-600', 'border-amber-500', 'bg-white');
            tabLista?.classList.remove('text-gray-500', 'border-transparent');
            listaSugestoes?.classList.remove('hidden');
            filtros?.classList.remove('hidden');
        } else if (tab === 'nova') {
            tabNova?.classList.add('text-amber-600', 'border-amber-500', 'bg-white');
            tabNova?.classList.remove('text-gray-500', 'border-transparent');
            formNova?.classList.remove('hidden');
        } else if (tab === 'detalhes') {
            tabLista?.classList.add('text-amber-600', 'border-amber-500', 'bg-white');
            tabLista?.classList.remove('text-gray-500', 'border-transparent');
            detalhesSugestao?.classList.remove('hidden');
        }
    },
    
    async carregarSugestoes() {
        const loading = document.getElementById('loading-sugestoes');
        loading?.classList.remove('hidden');
        
        const params = new URLSearchParams();
        const status = document.getElementById('filtro-status')?.value;
        const tipo = document.getElementById('filtro-tipo')?.value;
        const minhas = document.getElementById('filtro-minhas')?.checked;
        
        if (status) params.append('status', status);
        if (tipo) params.append('tipo', tipo);
        if (minhas) params.append('minhas', '1');
        
        try {
            const response = await fetch(`/admin/sugestoes?${params.toString()}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            if (data.success) {
                this.sugestoes = data.data.data || [];
                this.renderizarLista();
            }
        } catch (error) {
            console.error('Erro:', error);
        } finally {
            loading?.classList.add('hidden');
        }
    },
    
    renderizarLista() {
        const lista = document.getElementById('lista-sugestoes');
        const loading = document.getElementById('loading-sugestoes');
        
        if (this.sugestoes.length === 0) {
            lista.innerHTML = `<div class="text-center py-8 text-gray-500">
                <p class="font-medium">Nenhuma sugestão encontrada</p>
                <p class="text-sm">Seja o primeiro a enviar uma sugestão!</p>
            </div>`;
            lista.appendChild(loading);
            return;
        }
        
        const statusColors = {
            'pendente': 'bg-gray-100 text-gray-700',
            'em_analise': 'bg-yellow-100 text-yellow-700',
            'em_desenvolvimento': 'bg-blue-100 text-blue-700',
            'concluido': 'bg-green-100 text-green-700',
            'cancelado': 'bg-red-100 text-red-700'
        };
        const tipoIcons = { 'funcionalidade': '✨', 'melhoria': '📈', 'modulo': '📦', 'correcao': '🐛', 'outro': '💡' };
        const statusLabels = { 'pendente': 'Pendente', 'em_analise': 'Em Análise', 'em_desenvolvimento': 'Em Desenvolvimento', 'concluido': 'Concluído', 'cancelado': 'Cancelado' };
        
        let html = '';
        this.sugestoes.forEach(s => {
            const isMinha = s.usuario_interno_id === this.usuarioId;
            const progresso = s.checklist ? this.calcularProgresso(s.checklist) : 0;
            html += `<div class="bg-white border rounded-lg p-3 hover:shadow-md cursor-pointer ${isMinha ? 'border-l-4 border-l-amber-500' : ''}" onclick="SugestoesWidget.verDetalhes(${s.id})">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">${tipoIcons[s.tipo] || '💡'}</span>
                        <h4 class="font-medium text-gray-800 line-clamp-1">${this.escapeHtml(s.titulo)}</h4>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full ${statusColors[s.status]}">${statusLabels[s.status]}</span>
                </div>
                <p class="text-sm text-gray-600 line-clamp-2 mb-2">${this.escapeHtml(s.descricao)}</p>
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>${s.usuario?.nome || 'Usuário'}</span>
                    <span>${this.formatarData(s.created_at)}</span>
                </div>
                ${progresso > 0 ? `<div class="mt-2"><div class="h-1.5 bg-gray-200 rounded-full"><div class="h-full bg-amber-500 rounded-full" style="width:${progresso}%"></div></div></div>` : ''}
            </div>`;
        });
        
        lista.innerHTML = html;
        lista.appendChild(loading);
    },
    
    calcularProgresso(checklist) {
        if (!checklist || checklist.length === 0) return 0;
        return Math.round((checklist.filter(i => i.concluido).length / checklist.length) * 100);
    },
    
    verDetalhes(id) {
        const sugestao = this.sugestoes.find(s => s.id === id);
        if (!sugestao) return;
        
        this.sugestaoAtual = sugestao;
        this.mostrarTab('detalhes');
        
        const container = document.getElementById('detalhes-sugestao');
        const statusColors = { 'pendente': 'bg-gray-100 text-gray-700', 'em_analise': 'bg-yellow-100 text-yellow-700', 'em_desenvolvimento': 'bg-blue-100 text-blue-700', 'concluido': 'bg-green-100 text-green-700', 'cancelado': 'bg-red-100 text-red-700' };
        const tipoLabels = { 'funcionalidade': 'Nova Funcionalidade', 'melhoria': 'Melhoria', 'modulo': 'Novo Módulo', 'correcao': 'Correção de Bug', 'outro': 'Outro' };
        const statusLabels = { 'pendente': 'Pendente', 'em_analise': 'Em Análise', 'em_desenvolvimento': 'Em Desenvolvimento', 'concluido': 'Concluído', 'cancelado': 'Cancelado' };
        
        let checklistHtml = '';
        if (sugestao.checklist && sugestao.checklist.length > 0) {
            checklistHtml = `<div class="mt-4 p-3 bg-gray-50 rounded-lg">
                <h5 class="font-medium text-gray-700 mb-2">Checklist de Desenvolvimento</h5>
                <div class="space-y-2">
                    ${sugestao.checklist.map((item, i) => `<div class="flex items-center gap-2">
                        <span class="${item.concluido ? 'text-green-500' : 'text-gray-400'}">${item.concluido ? '✓' : '○'}</span>
                        <span class="${item.concluido ? 'line-through text-gray-500' : 'text-gray-700'}">${this.escapeHtml(item.texto)}</span>
                    </div>`).join('')}
                </div>
                <div class="mt-2 text-sm text-gray-500">Progresso: ${this.calcularProgresso(sugestao.checklist)}%</div>
            </div>`;
        }
        
        let respostaHtml = '';
        if (sugestao.resposta_admin) {
            respostaHtml = `<div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <h5 class="font-medium text-blue-800 mb-1">Resposta do Administrador</h5>
                <p class="text-sm text-blue-700">${this.escapeHtml(sugestao.resposta_admin)}</p>
            </div>`;
        }
        
        let acoesHtml = '';
        if (sugestao.pode_editar && sugestao.status === 'pendente') {
            acoesHtml += `<button onclick="SugestoesWidget.editarMinhaSugestao(${sugestao.id})" class="px-3 py-1.5 text-sm bg-amber-100 text-amber-700 rounded-lg hover:bg-amber-200">Editar</button>`;
        }
        if (sugestao.pode_excluir) {
            acoesHtml += `<button onclick="SugestoesWidget.excluirSugestao(${sugestao.id})" class="px-3 py-1.5 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200">Excluir</button>`;
        }
        if (sugestao.pode_gerenciar) {
            acoesHtml += `<button onclick="SugestoesWidget.abrirModalEditar(${sugestao.id})" class="px-3 py-1.5 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">Gerenciar</button>`;
        }
        
        container.innerHTML = `
            <button onclick="SugestoesWidget.mostrarTab('lista')" class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4">
                ← Voltar para lista
            </button>
            <div class="bg-white border rounded-lg p-4">
                <div class="flex items-start justify-between mb-3">
                    <span class="px-2 py-1 text-xs font-medium rounded-full ${statusColors[sugestao.status]}">${statusLabels[sugestao.status]}</span>
                    <span class="text-xs text-gray-500">${tipoLabels[sugestao.tipo]}</span>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">${this.escapeHtml(sugestao.titulo)}</h3>
                <p class="text-gray-600 whitespace-pre-wrap mb-4">${this.escapeHtml(sugestao.descricao)}</p>
                <div class="text-sm text-gray-500 border-t pt-3 space-y-1">
                    <p><strong>Autor:</strong> ${sugestao.usuario?.nome || 'Usuário'}</p>
                    <p><strong>Página:</strong> <span class="font-mono text-xs">${this.escapeHtml(sugestao.pagina_url)}</span></p>
                    <p><strong>Criado em:</strong> ${this.formatarData(sugestao.created_at)}</p>
                    ${sugestao.admin_responsavel ? `<p><strong>Responsável:</strong> ${sugestao.admin_responsavel.nome}</p>` : ''}
                    ${sugestao.concluido_em ? `<p><strong>Concluído em:</strong> ${this.formatarData(sugestao.concluido_em)}</p>` : ''}
                </div>
                ${respostaHtml}
                ${checklistHtml}
                ${acoesHtml ? `<div class="mt-4 pt-3 border-t flex gap-2">${acoesHtml}</div>` : ''}
            </div>
        `;
    },
    
    async enviarSugestao(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        formData.append('pagina_url', this.paginaAtual);
        
        try {
            const response = await fetch('/admin/sugestoes', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                form.reset();
                this.mostrarTab('lista');
                this.carregarSugestoes();
                alert('Sugestão enviada com sucesso!');
            } else {
                alert(data.message || 'Erro ao enviar sugestão');
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao enviar sugestão');
        }
    },
    
    abrirModalEditar(id) {
        const sugestao = this.sugestoes.find(s => s.id === id);
        if (!sugestao) return;
        
        this.sugestaoAtual = sugestao;
        this.checklistTemp = sugestao.checklist ? [...sugestao.checklist] : [];
        
        document.getElementById('edit-sugestao-id').value = sugestao.id;
        document.getElementById('edit-status').value = sugestao.status;
        document.getElementById('edit-resposta').value = sugestao.resposta_admin || '';
        
        this.renderizarChecklistModal();
        document.getElementById('modal-editar-sugestao')?.classList.remove('hidden');
    },
    
    fecharModalEditar() {
        document.getElementById('modal-editar-sugestao')?.classList.add('hidden');
    },
    
    renderizarChecklistModal() {
        const container = document.getElementById('edit-checklist');
        if (!container) return;
        
        container.innerHTML = this.checklistTemp.map((item, i) => `
            <div class="flex items-center gap-2 p-2 bg-gray-50 rounded">
                <input type="checkbox" ${item.concluido ? 'checked' : ''} onchange="SugestoesWidget.toggleChecklistItem(${i})" class="rounded text-amber-500">
                <span class="flex-1 text-sm ${item.concluido ? 'line-through text-gray-500' : ''}">${this.escapeHtml(item.texto)}</span>
                <button type="button" onclick="SugestoesWidget.removerChecklistItem(${i})" class="text-red-500 hover:text-red-700">&times;</button>
            </div>
        `).join('');
    },
    
    toggleChecklistItem(index) {
        if (this.checklistTemp[index]) {
            this.checklistTemp[index].concluido = !this.checklistTemp[index].concluido;
            this.renderizarChecklistModal();
        }
    },
    
    adicionarItemChecklist() {
        const input = document.getElementById('novo-item-checklist');
        const texto = input?.value.trim();
        if (!texto) return;
        
        this.checklistTemp.push({ texto, concluido: false });
        input.value = '';
        this.renderizarChecklistModal();
    },
    
    removerChecklistItem(index) {
        this.checklistTemp.splice(index, 1);
        this.renderizarChecklistModal();
    },
    
    async salvarEdicao(e) {
        e.preventDefault();
        const id = document.getElementById('edit-sugestao-id').value;
        
        const data = {
            status: document.getElementById('edit-status').value,
            resposta_admin: document.getElementById('edit-resposta').value,
            checklist: this.checklistTemp
        };
        
        try {
            const response = await fetch(`/admin/sugestoes/${id}`, {
                method: 'PUT',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                this.fecharModalEditar();
                this.carregarSugestoes();
                alert('Sugestão atualizada!');
            } else {
                alert(result.message || 'Erro ao atualizar');
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao atualizar sugestão');
        }
    },
    
    async excluirSugestao(id) {
        if (!confirm('Tem certeza que deseja excluir esta sugestão?')) return;
        
        try {
            const response = await fetch(`/admin/sugestoes/${id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await response.json();
            if (data.success) {
                this.mostrarTab('lista');
                this.carregarSugestoes();
                alert('Sugestão excluída!');
            } else {
                alert(data.message || 'Erro ao excluir');
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao excluir sugestão');
        }
    },
    
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },
    
    formatarData(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
};
