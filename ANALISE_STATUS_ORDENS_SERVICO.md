# Análise dos Status de Ordens de Serviço

## Status Atuais no Sistema

### Definidos no Banco de Dados
```sql
CHECK (status IN ('aberta', 'em_andamento', 'concluida', 'finalizada', 'cancelada'))
```

### Uso Real no Código

#### 1. **`aberta`** - Status Inicial
- **Criação**: Definido automaticamente ao criar nova OS (`OrdemServicoController.php` linha 206)
- **Uso**: Apenas como status inicial, não há transições para este status
- **Problema**: Redundante com `em_andamento` - não há diferença funcional

#### 2. **`em_andamento`** - Em Execução
- **Uso**: Status após reiniciar uma OS finalizada (linha 747)
- **Lógica**: Indica que técnicos estão executando a OS
- **Problema**: Deveria ser o status inicial ao invés de `aberta`

#### 3. **`concluida`** - Concluída (?)
- **Uso**: Definido no banco mas **NUNCA usado no código**
- **Problema**: Status fantasma sem função real no sistema

#### 4. **`finalizada`** - Finalizada
- **Uso**: Status final quando técnico finaliza a OS (linha 642)
- **Validações**: Bloqueia edição (linhas 239, 274)
- **Transição**: Pode voltar para `em_andamento` via "Reiniciar OS" (linha 747)
- **Função**: Status definitivo que requer observações e confirmação de atividades

#### 5. **`cancelada`** - Cancelada
- **Uso**: Definido no banco mas **NUNCA usado no código**
- **Problema**: Não há funcionalidade de cancelamento implementada

## Problemas Identificados

### 1. Status Redundantes
- **`aberta` vs `em_andamento`**: Ambos indicam OS ativa, sem diferença funcional
- **`concluida`**: Nunca usado, redundante com `finalizada`

### 2. Status Não Implementados
- **`cancelada`**: Existe no banco mas não há botão/ação para cancelar

### 3. Fluxo Confuso
```
Criação → aberta (inicial)
         ↓
      (nenhuma transição automática)
         ↓
      em_andamento (só ao reiniciar)
         ↓
      finalizada (ao finalizar)
         ↓
      em_andamento (ao reiniciar)
```

## Proposta de Unificação

### Opção 1: Simplificação Máxima (Recomendada)
**3 Status apenas:**

1. **`em_andamento`** (inicial e ativo)
   - Status padrão ao criar OS
   - Status ao reiniciar OS finalizada
   - Permite edição

2. **`finalizada`** (concluída)
   - Status final após execução
   - Bloqueia edição
   - Requer observações

3. **`cancelada`** (cancelada)
   - Para OSs que não serão executadas
   - Implementar botão "Cancelar OS"
   - Bloqueia edição

**Fluxo:**
```
Criação → em_andamento
         ↓
      finalizada ⟷ em_andamento (reiniciar)
         ↓
      cancelada (irreversível)
```

### Opção 2: Manter Diferenciação (Alternativa)
**4 Status:**

1. **`aberta`** - Aguardando início
2. **`em_andamento`** - Em execução
3. **`finalizada`** - Concluída
4. **`cancelada`** - Cancelada

**Requer implementar:**
- Botão "Iniciar OS" (aberta → em_andamento)
- Lógica diferente para cada status

## Recomendação Final

### ✅ Implementar Opção 1 (Simplificação)

**Motivos:**
1. Sistema atual não diferencia `aberta` de `em_andamento`
2. `concluida` nunca é usado
3. Fluxo mais simples e direto
4. Menos confusão para usuários
5. Menos código para manter

**Mudanças Necessárias:**

### 1. Migration
```php
DB::statement("
    ALTER TABLE ordens_servico 
    DROP CONSTRAINT ordens_servico_status_check
");
DB::statement("
    ALTER TABLE ordens_servico 
    ADD CONSTRAINT ordens_servico_status_check 
    CHECK (status IN ('em_andamento', 'finalizada', 'cancelada'))
");

// Atualizar OSs existentes
DB::table('ordens_servico')
    ->whereIn('status', ['aberta', 'concluida'])
    ->update(['status' => 'em_andamento']);
```

### 2. Controller (OrdemServicoController.php)
```php
// Linha 206: Mudar status inicial
$validated['status'] = 'em_andamento'; // era 'aberta'

// Linha 75-81: Atualizar opções de filtro
$statusOptions = [
    'em_andamento' => 'Em Andamento',
    'finalizada' => 'Finalizada',
    'cancelada' => 'Cancelada',
];

// Adicionar método para cancelar
public function cancelar(OrdemServico $ordemServico)
{
    $usuario = Auth::guard('interno')->user();
    
    if ($ordemServico->status === 'finalizada') {
        return redirect()->back()
            ->with('error', 'Não é possível cancelar uma OS finalizada.');
    }
    
    $ordemServico->update(['status' => 'cancelada']);
    
    return redirect()->route('admin.ordens-servico.index')
        ->with('success', "OS #{$ordemServico->numero} cancelada com sucesso!");
}
```

### 3. Modelo (OrdemServico.php)
```php
// Atualizar labels
public function getStatusLabelAttribute()
{
    return match($this->status) {
        'em_andamento' => 'Em Andamento',
        'finalizada' => 'Finalizada',
        'cancelada' => 'Cancelada',
        default => $this->status
    };
}

// Atualizar badges
public function getStatusBadgeAttribute()
{
    $colors = [
        'em_andamento' => 'bg-blue-100 text-blue-800',
        'finalizada' => 'bg-green-100 text-green-800',
        'cancelada' => 'bg-red-100 text-red-800',
    ];
    // ...
}
```

### 4. Views
- **show.blade.php**: Adicionar botão "Cancelar OS" (se não finalizada)
- **index.blade.php**: Já atualizado com novo $statusOptions

### 5. Rotas
```php
Route::post('/ordens-servico/{ordemServico}/cancelar', [OrdemServicoController::class, 'cancelar'])
    ->name('admin.ordens-servico.cancelar');
```

## Benefícios da Unificação

✅ **Simplicidade**: 3 status claros e distintos
✅ **Consistência**: Cada status tem função específica
✅ **Manutenibilidade**: Menos código, menos bugs
✅ **UX**: Fluxo mais intuitivo para usuários
✅ **Performance**: Menos verificações condicionais

## Impacto

### Baixo Risco
- Sistema novo, poucos dados em produção
- Mudança pode ser feita com migration simples
- Não quebra funcionalidades existentes

### Testes Necessários
1. Criar nova OS → deve iniciar como `em_andamento`
2. Finalizar OS → deve mudar para `finalizada`
3. Reiniciar OS → deve voltar para `em_andamento`
4. Cancelar OS → deve mudar para `cancelada`
5. Filtros → devem funcionar com novos status
6. Badges → devem exibir cores corretas
