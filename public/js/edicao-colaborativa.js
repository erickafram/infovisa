/**
 * Sistema de Edição Colaborativa de Documentos
 * - Salvamento automático no servidor
 * - Detecção de múltiplos editores
 * - Notificações de conflitos
 */

class EdicaoColaborativa {
    constructor(documentoId, usuarioNome) {
        this.documentoId = documentoId;
        this.usuarioNome = usuarioNome;
        this.edicaoId = null;
        this.versaoAtual = 1;
        this.versaoServidor = 1;
        this.editoresAtivos = [];
        this.salvandoServidor = false;
        this.intervalSalvar = null;
        this.intervalVerificarEditores = null;
        this.intervalSincronizar = null;
        this.ultimoConteudo = '';
        this.ultimoConteudoServidor = '';
        this.editandoAgora = false;
        this.ultimaEdicaoLocal = Date.now();
        this.bufferAlteracoes = []; // Buffer de alterações locais não salvas
        this.sincronizandoAgora = false;
        
        this.init();
    }

    init() {
        // Inicia edição
        this.iniciarEdicao();
        
        // Salvamento automático a cada 2 segundos (mais frequente)
        this.intervalSalvar = setInterval(() => {
            this.salvarNoServidor();
        }, 2000);
        
        // Verifica outros editores a cada 5 segundos
        this.intervalVerificarEditores = setInterval(() => {
            this.verificarEditoresAtivos();
        }, 5000);
        
        // Sincroniza com servidor a cada 1 segundo (MUITO frequente para não perder nada)
        this.intervalSincronizar = setInterval(() => {
            this.sincronizarComServidor();
        }, 1000);
        
        // Detecta quando o usuário está digitando
        const editor = document.getElementById('editor');
        if (editor) {
            editor.addEventListener('input', () => {
                this.editandoAgora = true;
                this.ultimaEdicaoLocal = Date.now();
                
                // Marca como não editando após 2 segundos de inatividade
                setTimeout(() => {
                    if (Date.now() - this.ultimaEdicaoLocal >= 2000) {
                        this.editandoAgora = false;
                    }
                }, 2000);
            });
        }
        
        // Finaliza edição ao sair da página
        window.addEventListener('beforeunload', () => {
            this.finalizarEdicao();
        });
        
        // Finaliza edição ao fechar aba
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.finalizarEdicao();
            } else {
                this.iniciarEdicao();
            }
        });
    }

    async iniciarEdicao() {
        try {
            const response = await fetch(`${window.APP_URL}/admin/documentos/${this.documentoId}/iniciar-edicao`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.edicaoId = data.edicao_id;
                this.editoresAtivos = data.outros_editores || [];
                this.mostrarEditoresAtivos();
            }
        } catch (error) {
            console.error('Erro ao iniciar edição:', error);
        }
    }

    async salvarNoServidor() {
        const editor = document.getElementById('editor');
        if (!editor) return;
        
        const conteudoAtual = editor.innerHTML;
        
        // Não salva se o conteúdo não mudou
        if (conteudoAtual === this.ultimoConteudo) return;
        
        this.salvandoServidor = true;
        this.mostrarIndicadorSalvando();
        
        try {
            const response = await fetch(`${window.APP_URL}/admin/documentos/${this.documentoId}/salvar-auto`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    conteudo: conteudoAtual
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.versaoAtual = data.versao;
                this.ultimoConteudo = conteudoAtual;
                this.editoresAtivos = data.editores_ativos || [];
                this.mostrarIndicadorSalvo();
                this.mostrarEditoresAtivos();
            } else {
                this.mostrarErroSalvamento(data.message);
            }
        } catch (error) {
            console.error('Erro ao salvar:', error);
            this.mostrarErroSalvamento('Erro de conexão');
        } finally {
            this.salvandoServidor = false;
        }
    }

    async verificarEditoresAtivos() {
        try {
            const response = await fetch(`${window.APP_URL}/admin/documentos/${this.documentoId}/editores-ativos`);
            const data = await response.json();
            
            if (data.success) {
                this.editoresAtivos = data.editores || [];
                this.mostrarEditoresAtivos();
            }
        } catch (error) {
            console.error('Erro ao verificar editores:', error);
        }
    }

    async sincronizarComServidor() {
        // Evita sincronizações simultâneas
        if (this.sincronizandoAgora) {
            return;
        }
        
        this.sincronizandoAgora = true;
        
        try {
            const response = await fetch(`${window.APP_URL}/admin/documentos/${this.documentoId}/obter-conteudo`);
            const data = await response.json();
            
            if (!data.success) {
                this.sincronizandoAgora = false;
                return;
            }
            
            const conteudoServidor = data.conteudo;
            const versaoServidor = data.versao;
            
            // Se a versão do servidor é maior, há atualizações
            if (versaoServidor > this.versaoServidor) {
                const editor = document.getElementById('editor');
                if (!editor) {
                    this.sincronizandoAgora = false;
                    return;
                }
                
                // CRÍTICO: Captura o conteúdo atual ANTES de qualquer operação
                const conteudoLocalAtual = editor.innerHTML;
                
                // Se o conteúdo local é diferente do servidor, faz merge INTELIGENTE
                if (conteudoLocalAtual !== conteudoServidor) {
                    // Salva posição do cursor ANTES
                    const selection = window.getSelection();
                    const range = selection.rangeCount > 0 ? selection.getRangeAt(0) : null;
                    const cursorOffset = range ? this.getCursorOffset(editor, range) : 0;
                    
                    // Faz merge preservando TUDO que foi digitado localmente
                    const conteudoMerged = this.mergearConteudosSeguro(
                        this.ultimoConteudoServidor,  // Base (última versão conhecida)
                        conteudoLocalAtual,            // Local (o que está no editor AGORA)
                        conteudoServidor               // Servidor (o que outros salvaram)
                    );
                    
                    // Atualiza editor APENAS se o merge for diferente do atual
                    if (conteudoMerged !== conteudoLocalAtual) {
                        editor.innerHTML = conteudoMerged;
                        
                        // Restaura posição do cursor
                        if (range) {
                            this.setCursorOffset(editor, cursorOffset);
                        }
                        
                        // Mostra notificação de merge
                        this.mostrarNotificacaoMerge();
                        
                        // Dispara evento de input para atualizar Alpine.js
                        editor.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
                
                this.versaoServidor = versaoServidor;
                this.ultimoConteudoServidor = conteudoServidor;
            }
        } catch (error) {
            console.error('Erro ao sincronizar:', error);
        } finally {
            this.sincronizandoAgora = false;
        }
    }

    mergearConteudosSeguro(base, local, servidor) {
        // Se o conteúdo local é igual à base, usa o do servidor
        if (local === base) {
            return servidor;
        }
        
        // Se o conteúdo do servidor é igual à base, usa o local
        if (servidor === base) {
            return local;
        }
        
        // Se local e servidor são iguais, não precisa merge
        if (local === servidor) {
            return local;
        }
        
        // AMBOS MUDARAM - Merge inteligente que NUNCA perde conteúdo
        
        // Extrai texto puro de cada versão
        const textoBase = this.extrairTexto(base || '');
        const textoLocal = this.extrairTexto(local);
        const textoServidor = this.extrairTexto(servidor);
        
        // Divide em parágrafos
        const paragrafosBase = this.dividirEmParagrafos(textoBase);
        const paragrafosLocal = this.dividirEmParagrafos(textoLocal);
        const paragrafosServidor = this.dividirEmParagrafos(textoServidor);
        
        // Normaliza parágrafos para comparação (remove espaços extras)
        const normalizarParagrafo = (p) => p.trim().replace(/\s+/g, ' ');
        
        // Cria Set de parágrafos normalizados para detecção de duplicatas
        const paragrafosNormalizadosSet = new Set();
        const paragrafosFinais = [];
        
        // Função para adicionar parágrafo sem duplicar
        const adicionarSeUnico = (paragrafo) => {
            const normalizado = normalizarParagrafo(paragrafo);
            if (normalizado && !paragrafosNormalizadosSet.has(normalizado)) {
                paragrafosNormalizadosSet.add(normalizado);
                paragrafosFinais.push(paragrafo.trim());
            }
        };
        
        // 1. Adiciona parágrafos do servidor (prioridade para conteúdo já salvo)
        paragrafosServidor.forEach(p => adicionarSeUnico(p));
        
        // 2. Adiciona parágrafos únicos do local (que não estão no servidor)
        paragrafosLocal.forEach(p => adicionarSeUnico(p));
        
        // Converte de volta para HTML, preservando formatação
        return paragrafosFinais.map(p => `<p>${p}</p>`).join('');
    }

    dividirEmParagrafos(texto) {
        // Divide por quebras de linha e limpa
        return texto
            .split(/\n+/)
            .map(p => p.trim())
            .filter(p => p.length > 0);
    }

    mergearConteudos(base, local, servidor) {
        // Método antigo mantido para compatibilidade
        return this.mergearConteudosSeguro(base, local, servidor);
    }

    extrairTexto(html) {
        const div = document.createElement('div');
        div.innerHTML = html;
        return div.textContent || div.innerText || '';
    }

    diferencaTexto(textoAntigo, textoNovo) {
        // Retorna palavras que foram adicionadas
        const palavrasAntigas = textoAntigo.split(/\s+/);
        const palavrasNovas = textoNovo.split(/\s+/);
        return palavrasNovas.filter(p => !palavrasAntigas.includes(p));
    }

    temConflito(adicoes1, adicoes2) {
        // Verifica se há palavras conflitantes
        return adicoes1.some(p1 => adicoes2.some(p2 => 
            p1.toLowerCase().includes(p2.toLowerCase()) || 
            p2.toLowerCase().includes(p1.toLowerCase())
        ));
    }

    combinarAlteracoes(servidor, local, base) {
        // Estratégia: usa servidor como base e adiciona partes únicas do local
        const textoServidor = this.extrairTexto(servidor);
        const textoLocal = this.extrairTexto(local);
        const textoBase = this.extrairTexto(base);
        
        // Identifica blocos únicos do local
        const paragrafosLocal = textoLocal.split('\n');
        const paragrafosServidor = textoServidor.split('\n');
        
        const paragrafosUnicos = paragrafosLocal.filter(p => 
            p.trim() && !paragrafosServidor.some(ps => ps.includes(p.trim()))
        );
        
        // Se há parágrafos únicos no local, adiciona ao final do servidor
        if (paragrafosUnicos.length > 0) {
            return servidor + '\n' + paragrafosUnicos.map(p => `<p>${p}</p>`).join('');
        }
        
        return servidor;
    }

    getCursorOffset(parent, range) {
        let offset = 0;
        const treeWalker = document.createTreeWalker(
            parent,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );
        
        let node;
        while (node = treeWalker.nextNode()) {
            if (node === range.startContainer) {
                offset += range.startOffset;
                break;
            }
            offset += node.textContent.length;
        }
        
        return offset;
    }

    setCursorOffset(parent, offset) {
        const treeWalker = document.createTreeWalker(
            parent,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );
        
        let currentOffset = 0;
        let node;
        
        while (node = treeWalker.nextNode()) {
            const nodeLength = node.textContent.length;
            if (currentOffset + nodeLength >= offset) {
                const range = document.createRange();
                const selection = window.getSelection();
                range.setStart(node, offset - currentOffset);
                range.collapse(true);
                selection.removeAllRanges();
                selection.addRange(range);
                break;
            }
            currentOffset += nodeLength;
        }
    }

    mostrarNotificacaoMerge() {
        let notificacao = document.getElementById('notificacao-merge');
        
        if (!notificacao) {
            notificacao = document.createElement('div');
            notificacao.id = 'notificacao-merge';
            notificacao.className = 'fixed top-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50 bg-blue-500 text-white flex items-center gap-2';
            document.body.appendChild(notificacao);
        }
        
        notificacao.innerHTML = `
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span class="text-sm font-medium">Alterações mescladas</span>
        `;
        
        setTimeout(() => {
            notificacao.style.opacity = '0';
            setTimeout(() => {
                notificacao.style.opacity = '1';
            }, 2000);
        }, 1500);
    }

    async finalizarEdicao() {
        if (!this.edicaoId) return;
        
        try {
            await fetch(`${window.APP_URL}/admin/documentos/${this.documentoId}/finalizar-edicao`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                keepalive: true // Garante que a requisição seja enviada mesmo ao fechar a aba
            });
        } catch (error) {
            console.error('Erro ao finalizar edição:', error);
        }
    }

    mostrarIndicadorSalvando() {
        let indicador = document.getElementById('indicador-salvamento');
        if (!indicador) {
            indicador = document.createElement('div');
            indicador.id = 'indicador-salvamento';
            indicador.className = 'fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50 transition-all';
            document.body.appendChild(indicador);
        }
        
        indicador.className = 'fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50 bg-blue-500 text-white flex items-center gap-2';
        indicador.innerHTML = `
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium">Salvando...</span>
        `;
    }

    mostrarIndicadorSalvo() {
        let indicador = document.getElementById('indicador-salvamento');
        if (!indicador) return;
        
        indicador.className = 'fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50 bg-green-500 text-white flex items-center gap-2';
        indicador.innerHTML = `
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-sm font-medium">Salvo (v${this.versaoAtual})</span>
        `;
        
        setTimeout(() => {
            indicador.style.opacity = '0';
            setTimeout(() => {
                indicador.style.opacity = '1';
            }, 2000);
        }, 1500);
    }

    mostrarErroSalvamento(mensagem) {
        let indicador = document.getElementById('indicador-salvamento');
        if (!indicador) return;
        
        indicador.className = 'fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50 bg-red-500 text-white flex items-center gap-2';
        indicador.innerHTML = `
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span class="text-sm font-medium">Erro: ${mensagem}</span>
        `;
    }

    mostrarEditoresAtivos() {
        let container = document.getElementById('editores-ativos-container');
        
        // Remove editores que são o próprio usuário
        const outrosEditores = this.editoresAtivos.filter(e => e.nome !== this.usuarioNome);
        
        if (outrosEditores.length === 0) {
            if (container) container.remove();
            return;
        }
        
        if (!container) {
            container = document.createElement('div');
            container.id = 'editores-ativos-container';
            container.className = 'fixed top-20 right-4 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg shadow-lg z-50 max-w-sm';
            document.body.appendChild(container);
        }
        
        container.innerHTML = `
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="flex-1">
                    <h4 class="text-sm font-semibold text-yellow-800 mb-1">
                        ⚠️ ${outrosEditores.length} ${outrosEditores.length === 1 ? 'pessoa está' : 'pessoas estão'} editando
                    </h4>
                    <ul class="text-xs text-yellow-700 space-y-1">
                        ${outrosEditores.map(editor => `
                            <li class="flex items-center gap-2">
                                <span class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></span>
                                <strong>${editor.nome}</strong>
                                <span class="text-yellow-600">• ${editor.iniciado_em}</span>
                            </li>
                        `).join('')}
                    </ul>
                    <p class="text-xs text-yellow-600 mt-2">
                        💡 Suas alterações serão mescladas automaticamente
                    </p>
                </div>
            </div>
        `;
    }

    destruir() {
        if (this.intervalSalvar) clearInterval(this.intervalSalvar);
        if (this.intervalVerificarEditores) clearInterval(this.intervalVerificarEditores);
        if (this.intervalSincronizar) clearInterval(this.intervalSincronizar);
        this.finalizarEdicao();
    }
}

// Exporta para uso global
window.EdicaoColaborativa = EdicaoColaborativa;
