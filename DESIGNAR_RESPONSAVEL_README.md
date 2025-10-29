# Funcionalidade: Designar Responsável

## 📋 Resumo

Implementação completa da funcionalidade "Designar Responsável" que permite atribuir processos a usuários internos do mesmo município, com notificações na dashboard.

---

## 🗄️ Banco de Dados

### Migration Criada
**Arquivo:** `database/migrations/2025_10_29_134400_create_processo_designacoes_table.php`

**Tabela:** `processo_designacoes`

**Campos:**
- `id` - ID único da designação
- `processo_id` - FK para processos
- `usuario_designado_id` - FK para usuários_internos (quem vai executar)
- `usuario_designador_id` - FK para usuários_internos (quem designou)
- `descricao_tarefa` - TEXT - Descrição do que precisa ser feito
- `data_limite` - DATE (nullable) - Prazo para conclusão
- `status` - ENUM('pendente', 'em_andamento', 'concluida', 'cancelada')
- `observacoes_conclusao` - TEXT (nullable) - Observações ao concluir
- `concluida_em` - TIMESTAMP (nullable) - Data/hora da conclusão
- `timestamps` - created_at, updated_at
- `softDeletes` - deleted_at

**Índices:**
- `usuario_designado_id` - Para buscar rapidamente designações de um usuário
- `status` - Para filtrar por status

**Para executar:**
```bash
php artisan migrate
```

---

## 📦 Models

### 1. ProcessoDesignacao
**Arquivo:** `app/Models/ProcessoDesignacao.php`

**Relacionamentos:**
- `processo()` - BelongsTo Processo
- `usuarioDesignado()` - BelongsTo UsuarioInterno
- `usuarioDesignador()` - BelongsTo UsuarioInterno

**Métodos Úteis:**
- `isAtrasada()` - Verifica se passou do prazo
- `isProximoDoPrazo()` - Verifica se faltam 3 dias ou menos
- `scopePendentes()` - Filtra apenas pendentes
- `scopeDoUsuario($usuarioId)` - Filtra por usuário designado

### 2. Processo (Atualizado)
**Arquivo:** `app/Models/Processo.php`

**Novos Relacionamentos:**
- `designacoes()` - HasMany ProcessoDesignacao
- `designacoesPendentes()` - HasMany ProcessoDesignacao (apenas pendentes)

---

## 🎮 Controllers

### ProcessoController (Atualizado)
**Arquivo:** `app/Http/Controllers/ProcessoController.php`

**Novos Métodos:**

#### 1. `buscarUsuariosParaDesignacao($estabelecimentoId, $processoId)`
- **Rota:** GET `/admin/estabelecimentos/{id}/processos/{processo}/usuarios-designacao`
- **Função:** Retorna JSON com usuários internos ativos do mesmo município
- **Filtros:** `ativo = true` AND `municipio_id = estabelecimento.municipio_id`

#### 2. `designarResponsavel(Request $request, $estabelecimentoId, $processoId)`
- **Rota:** POST `/admin/estabelecimentos/{id}/processos/{processo}/designar`
- **Validação:**
  - `usuario_designado_id` - required, exists:usuarios_internos
  - `descricao_tarefa` - required, string, max:1000
  - `data_limite` - nullable, date, after_or_equal:today
- **Função:** Cria nova designação
- **Segurança:** Verifica se usuário é do mesmo município

#### 3. `atualizarDesignacao(Request $request, $estabelecimentoId, $processoId, $designacaoId)`
- **Rota:** PATCH `/admin/estabelecimentos/{id}/processos/{processo}/designacoes/{designacao}`
- **Validação:**
  - `status` - required, in:pendente,em_andamento,concluida,cancelada
  - `observacoes_conclusao` - nullable, string, max:1000
- **Função:** Atualiza status da designação
- **Automático:** Define `concluida_em` quando status = 'concluida'

### DashboardController (Atualizado)
**Arquivo:** `app/Http/Controllers/Admin/DashboardController.php`

**Adicionado:**
- Busca processos designados pendentes para o usuário logado
- Adiciona contagem em `$stats['processos_designados_pendentes']`
- Passa `$processos_designados` para a view

---

## 🛣️ Rotas

**Arquivo:** `routes/web.php`

```php
// Designação de Responsável
Route::get('/estabelecimentos/{id}/processos/{processo}/usuarios-designacao', 
    [ProcessoController::class, 'buscarUsuariosParaDesignacao'])
    ->name('estabelecimentos.processos.usuarios.designacao');

Route::post('/estabelecimentos/{id}/processos/{processo}/designar', 
    [ProcessoController::class, 'designarResponsavel'])
    ->name('estabelecimentos.processos.designar');

Route::patch('/estabelecimentos/{id}/processos/{processo}/designacoes/{designacao}', 
    [ProcessoController::class, 'atualizarDesignacao'])
    ->name('estabelecimentos.processos.designacoes.atualizar');
```

---

## 🎨 Views

### Processo Show (Atualizado)
**Arquivo:** `resources/views/estabelecimentos/processos/show.blade.php`

**Alterações:**

#### 1. Botão "Designar Responsável"
- Localização: Menu de Opções (coluna esquerda)
- Ação: Abre modal e carrega usuários do município
- Código: `@click="modalDesignar = true; carregarUsuarios()"`

#### 2. Modal de Designação
- **Campos:**
  - Select de usuários (carregado via AJAX)
  - Textarea para descrição da tarefa (max 1000 caracteres)
  - Input date para prazo (opcional, min=hoje)
- **Validação:** HTML5 + Backend
- **Design:** Modal centralizado, responsivo, com overlay

#### 3. Alpine.js Data
**Novas Variáveis:**
```javascript
modalDesignar: false,
usuarios: [],
usuarioDesignado: '',
descricaoTarefa: '',
dataLimite: '',
```

**Nova Função:**
```javascript
carregarUsuarios() {
    // Busca usuários via AJAX
    // Popula select no modal
}
```

---

## 🔔 Notificações na Dashboard

### Dashboard View (Pendente de Implementação)
**Arquivo:** `resources/views/admin/dashboard.blade.php`

**O que adicionar:**

#### 1. Card de Processos Designados
```blade
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">
            Processos Designados para Você
        </h3>
        @if($stats['processos_designados_pendentes'] > 0)
            <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-full">
                {{ $stats['processos_designados_pendentes'] }} pendente(s)
            </span>
        @endif
    </div>

    @if($processos_designados->isEmpty())
        <p class="text-sm text-gray-500">Nenhum processo designado no momento</p>
    @else
        <div class="space-y-3">
            @foreach($processos_designados as $designacao)
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <a href="{{ route('admin.estabelecimentos.processos.show', [$designacao->processo->estabelecimento_id, $designacao->processo->id]) }}" 
                               class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                                Processo #{{ $designacao->processo->numero_processo }}
                            </a>
                            <p class="text-xs text-gray-600 mt-1">
                                {{ $designacao->processo->estabelecimento->nome_fantasia }}
                            </p>
                            <p class="text-sm text-gray-700 mt-2">
                                {{ Str::limit($designacao->descricao_tarefa, 100) }}
                            </p>
                            <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                <span>Designado por: {{ $designacao->usuarioDesignador->nome }}</span>
                                @if($designacao->data_limite)
                                    <span class="{{ $designacao->isAtrasada() ? 'text-red-600 font-semibold' : ($designacao->isProximoDoPrazo() ? 'text-orange-600 font-semibold' : '') }}">
                                        Prazo: {{ $designacao->data_limite->format('d/m/Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
```

#### 2. Ícone do Sino (Notificações)
Adicionar badge com contagem de designações pendentes:

```blade
<button class="relative p-2 text-gray-600 hover:text-gray-900">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
    </svg>
    @if($stats['processos_designados_pendentes'] > 0)
        <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
            {{ $stats['processos_designados_pendentes'] }}
        </span>
    @endif
</button>
```

---

## ✅ Checklist de Implementação

- [x] Migration criada
- [x] Model ProcessoDesignacao criado
- [x] Model Processo atualizado com relacionamentos
- [x] ProcessoController atualizado com 3 novos métodos
- [x] DashboardController atualizado para buscar designações
- [x] Rotas adicionadas
- [x] Botão "Designar Responsável" adicionado
- [x] Modal de designação criado
- [x] JavaScript para carregar usuários implementado
- [ ] **Dashboard view atualizada com notificações** (PENDENTE)
- [ ] **Ícone do sino com badge** (PENDENTE)

---

## 🚀 Como Usar

### 1. Executar Migration
```bash
php artisan migrate
```

### 2. Designar Responsável
1. Acesse um processo: `/admin/estabelecimentos/{id}/processos/{processo}`
2. Clique em "Designar Responsável" no menu de opções
3. Selecione o usuário (apenas do mesmo município)
4. Descreva a tarefa
5. Opcionalmente, defina um prazo
6. Clique em "Designar Responsável"

### 3. Visualizar Designações
- O usuário designado verá na dashboard os processos atribuídos a ele
- Badge no sino indica quantidade de designações pendentes
- Processos atrasados aparecem em vermelho
- Processos próximos do prazo aparecem em laranja

### 4. Atualizar Status (Futuro)
- Implementar botões para mudar status (em andamento, concluída)
- Adicionar campo para observações de conclusão

---

## 🔒 Segurança

1. **Validação de Município:** Apenas usuários do mesmo município podem ser designados
2. **Permissões:** Usa `validarPermissaoAcesso()` do controller
3. **CSRF Protection:** Todos os formulários incluem `@csrf`
4. **Validação de Datas:** Data limite não pode ser no passado
5. **Soft Deletes:** Designações podem ser recuperadas se excluídas

---

## 📊 Estatísticas Disponíveis

- `$stats['processos_designados_pendentes']` - Contagem de designações pendentes
- `$processos_designados` - Collection com as 10 designações mais recentes

---

## 🎯 Próximos Passos (Sugestões)

1. **Notificações por Email:** Enviar email quando usuário for designado
2. **Histórico de Designações:** Mostrar todas as designações (não só pendentes)
3. **Filtros na Dashboard:** Filtrar por status, prazo, etc.
4. **Reatribuir:** Permitir mudar o responsável
5. **Comentários:** Adicionar sistema de comentários na designação
6. **Notificações Push:** Usar Laravel Echo para notificações em tempo real

---

## 📝 Notas Importantes

- Usuários inativos não aparecem na lista de seleção
- Apenas usuários do **mesmo município** do estabelecimento podem ser designados
- Data limite é opcional
- Status padrão é sempre "pendente"
- Ao marcar como "concluída", o campo `concluida_em` é preenchido automaticamente
