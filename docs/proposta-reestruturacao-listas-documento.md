# Proposta de Reestruturação - Listas de Documentos por Atividade

## Problema Atual

A estrutura atual tem muita informação redundante e é complexa para configurar:

1. **ListaDocumento** - Uma entidade intermediária que agrupa atividades e documentos
2. **TipoDocumentoObrigatorio** - Tipos de documentos com campos de escopo e setor
3. **Atividade** - Atividades com código CNAE

### Fluxo Atual (Complexo):
```
TipoServico → Atividade → ListaDocumento → TipoDocumentoObrigatorio
```

Para cada combinação de atividades, é necessário criar uma "Lista" separada, o que gera:
- Muitas listas para gerenciar
- Dificuldade em manter consistência
- Documentos podem se repetir quando estabelecimento tem múltiplas atividades

---

## Proposta de Nova Estrutura

### Conceito Principal
Vincular documentos **diretamente às atividades**, eliminando a entidade intermediária "ListaDocumento".

### Nova Estrutura de Dados:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        TIPOS DE DOCUMENTO                                    │
│  (TipoDocumentoObrigatorio)                                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│ - nome (CNPJ, Contrato Social, Alvará, etc.)                                │
│ - descricao                                                                  │
│ - documento_comum (bool) - Se true, aplica a TODOS                          │
│ - escopo_competencia (estadual/municipal/todos)                             │
│ - tipo_setor (publico/privado/todos)                                        │
│ - prazo_validade_dias                                                        │
│ - observacao_publica                                                         │
│ - observacao_privada                                                         │
│ - nomenclatura_arquivo (ex: "CNPJ", "CONTRATO SOCIAL")                      │
│ - instrucoes (instruções de como obter/preencher)                           │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    │ (documento_comum = true)
                                    ▼
                    ┌───────────────────────────────┐
                    │   APLICADO AUTOMATICAMENTE    │
                    │   A TODOS OS SERVIÇOS         │
                    └───────────────────────────────┘

                                    │
                                    │ (documento_comum = false)
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                    ATIVIDADE_DOCUMENTO (Pivot)                               │
├─────────────────────────────────────────────────────────────────────────────┤
│ - atividade_id                                                               │
│ - tipo_documento_obrigatorio_id                                              │
│ - obrigatorio (bool)                                                         │
│ - observacao_especifica                                                      │
│ - ordem                                                                      │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                           ATIVIDADES                                         │
├─────────────────────────────────────────────────────────────────────────────┤
│ - tipo_servico_id                                                            │
│ - nome (Hospital, Laboratório, Restaurante, etc.)                           │
│ - codigo_cnae                                                                │
│ - descricao                                                                  │
│ - escopo_competencia (estadual/municipal) - Define competência padrão       │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Fluxo de Geração de Lista para Estabelecimento

Quando um estabelecimento faz cadastro:

```
1. Identificar atividades_exercidas do estabelecimento (CNAEs)
2. Buscar Atividades correspondentes aos CNAEs
3. Para cada Atividade:
   - Buscar documentos vinculados (atividade_documento)
4. Adicionar documentos comuns (documento_comum = true)
5. Filtrar por:
   - escopo_competencia (baseado na competência do estabelecimento)
   - tipo_setor (baseado se é público ou privado)
6. DEDUPLICAR documentos (mesmo documento não aparece 2x)
7. Ordenar por ordem/nome
8. Retornar lista final
```

---

## Exemplo Prático

### Estabelecimento: Hospital com Laboratório e Radiologia

**Atividades exercidas:**
- 8610-1/01 - Atividades de atendimento hospitalar
- 8640-2/01 - Laboratórios de anatomia patológica
- 8640-2/02 - Laboratórios clínicos

**Tipo:** Privado
**Competência:** Estadual

### Documentos Resultantes (sem repetição):

#### Documentos Comuns (aplicados automaticamente):
1. CNPJ (validade 30 dias)
2. CONTRATO SOCIAL (apenas privado)
3. DARE (apenas privado)
4. COMP PAGAMENTO (apenas privado)
5. PARECER PROJETO

#### Documentos da Atividade "Hospital":
6. ALVARA LOC
7. CERT RT (CRM)
8. CAD NOTIVISA
9. CNES
10. REL EQUIPAMENTOS (radiação ionizante)

#### Documentos da Atividade "Laboratório":
- ALVARA LOC (já incluído - não repete)
- CERT RT (já incluído - não repete)
11. DEC LACEN
12. REL EXAMES
13. REL POSTO COLETA

---

## Vantagens da Nova Estrutura

1. **Simplicidade**: Configurar documentos por atividade é mais intuitivo
2. **Sem Redundância**: Documentos não se repetem na lista final
3. **Manutenção Fácil**: Alterar documentos de uma atividade afeta todos os estabelecimentos
4. **Flexibilidade**: Documentos comuns são gerenciados separadamente
5. **Consistência**: Mesma atividade sempre terá os mesmos documentos

---

## Migração Proposta

### Fase 1: Criar nova tabela pivot
```sql
CREATE TABLE atividade_documento (
    id BIGSERIAL PRIMARY KEY,
    atividade_id BIGINT NOT NULL REFERENCES atividades(id),
    tipo_documento_obrigatorio_id BIGINT NOT NULL REFERENCES tipos_documento_obrigatorio(id),
    obrigatorio BOOLEAN DEFAULT true,
    observacao TEXT,
    ordem INTEGER DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(atividade_id, tipo_documento_obrigatorio_id)
);
```

### Fase 2: Migrar dados existentes
- Converter listas existentes para vínculos diretos atividade→documento

### Fase 3: Atualizar interface
- Simplificar tela de configuração
- Permitir vincular documentos diretamente às atividades

### Fase 4: Atualizar lógica de busca
- Implementar método `getDocumentosParaEstabelecimento()` com deduplicação

---

## Interface Proposta

### Tela Principal: Configuração de Documentos por Atividade

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ CONFIGURAÇÃO DE DOCUMENTOS POR ATIVIDADE                                    │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│ [Tab: Documentos Comuns] [Tab: Por Atividade] [Tab: Tipos de Documento]     │
│                                                                              │
│ ═══════════════════════════════════════════════════════════════════════════ │
│                                                                              │
│ DOCUMENTOS COMUNS A TODOS OS SERVIÇOS                                       │
│ (Aplicados automaticamente baseado no escopo e tipo de setor)               │
│                                                                              │
│ ┌─────────────────────────────────────────────────────────────────────────┐ │
│ │ ☑ CNPJ                    │ Todos │ Todos    │ 30 dias │ [Editar]      │ │
│ │ ☑ CONTRATO SOCIAL         │ Todos │ Privado  │ -       │ [Editar]      │ │
│ │ ☑ DARE                    │ Todos │ Privado  │ -       │ [Editar]      │ │
│ │ ☑ COMP PAGAMENTO          │ Todos │ Privado  │ -       │ [Editar]      │ │
│ │ ☑ PARECER PROJETO         │ Todos │ Todos    │ -       │ [Editar]      │ │
│ └─────────────────────────────────────────────────────────────────────────┘ │
│                                                                              │
│ ═══════════════════════════════════════════════════════════════════════════ │
│                                                                              │
│ DOCUMENTOS POR ATIVIDADE                                                     │
│                                                                              │
│ Filtrar: [Tipo de Serviço ▼] [Buscar atividade...]                          │
│                                                                              │
│ ┌─────────────────────────────────────────────────────────────────────────┐ │
│ │ ▼ SERVIÇOS DE SAÚDE                                                     │ │
│ │   ├─ Hospital (8610-1/01)                              [5 docs] [Editar]│ │
│ │   │   └─ ALVARA LOC, CERT RT, CAD NOTIVISA, CNES, REL EQUIPAMENTOS     │ │
│ │   ├─ Laboratório Clínico (8640-2/02)                   [5 docs] [Editar]│ │
│ │   │   └─ ALVARA LOC, CERT RT, DEC LACEN, REL EXAMES, REL POSTO COLETA  │ │
│ │   ├─ Medicina Nuclear (8640-2/99)                      [7 docs] [Editar]│ │
│ │   │   └─ ALVARA LOC, CERT RT, DEC RT FISICO, TITULO FISICO, ...        │ │
│ │                                                                         │ │
│ │ ▼ DISTRIBUIDORAS                                                        │ │
│ │   ├─ Distribuidora de Medicamentos (4644-3/01)         [5 docs] [Editar]│ │
│ │   │   └─ ALVARA LOC, CERT RT, REL PRODUTOS, AFE ANVISA, AE ANVISA      │ │
│ │                                                                         │ │
│ │ ▼ INDÚSTRIAS                                                            │ │
│ │   ├─ Indústria de Alimentos (1091-1/02)                [2 docs] [Editar]│ │
│ │   │   └─ ALVARA LOC, REL ALIMENTOS                                      │ │
│ └─────────────────────────────────────────────────────────────────────────┘ │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Modal: Editar Documentos da Atividade

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ DOCUMENTOS PARA: Hospital (8610-1/01)                              [X]      │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│ Documentos vinculados a esta atividade:                                      │
│                                                                              │
│ ┌─────────────────────────────────────────────────────────────────────────┐ │
│ │ ☑ ALVARA LOC          │ Obrigatório ● Opcional ○ │ [Observação...]     │ │
│ │ ☑ CERT RT             │ Obrigatório ● Opcional ○ │ [Emitido pelo CRM]  │ │
│ │ ☑ CAD NOTIVISA        │ Obrigatório ● Opcional ○ │ [Print da tela...]  │ │
│ │ ☑ CNES                │ Obrigatório ● Opcional ○ │ [Ficha atualizada]  │ │
│ │ ☑ REL EQUIPAMENTOS    │ Obrigatório ● Opcional ○ │ [Se possuir Rx...]  │ │
│ └─────────────────────────────────────────────────────────────────────────┘ │
│                                                                              │
│ Adicionar documento:                                                         │
│ [Selecione um documento ▼] [+ Adicionar]                                    │
│                                                                              │
│ ─────────────────────────────────────────────────────────────────────────── │
│                                                                              │
│                                              [Cancelar] [Salvar Alterações] │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Código de Exemplo: Buscar Documentos para Estabelecimento

```php
class Atividade extends Model
{
    public function documentosObrigatorios()
    {
        return $this->belongsToMany(TipoDocumentoObrigatorio::class, 'atividade_documento')
            ->withPivot(['obrigatorio', 'observacao', 'ordem'])
            ->withTimestamps()
            ->orderBy('atividade_documento.ordem');
    }
}

class TipoDocumentoObrigatorio extends Model
{
    /**
     * Busca todos os documentos aplicáveis para um estabelecimento
     * com deduplicação automática
     */
    public static function getDocumentosParaEstabelecimento(Estabelecimento $estabelecimento): Collection
    {
        $escopoCompetencia = $estabelecimento->getEscopoCompetencia(); // 'estadual' ou 'municipal'
        $tipoSetor = $estabelecimento->tipo_setor ?? 'privado';
        
        // 1. Buscar documentos comuns
        $documentosComuns = self::where('ativo', true)
            ->where('documento_comum', true)
            ->where(function($q) use ($escopoCompetencia) {
                $q->where('escopo_competencia', 'todos')
                  ->orWhere('escopo_competencia', $escopoCompetencia);
            })
            ->where(function($q) use ($tipoSetor) {
                $q->where('tipo_setor', 'todos')
                  ->orWhere('tipo_setor', $tipoSetor);
            })
            ->get();
        
        // 2. Buscar atividades do estabelecimento
        $atividadesIds = Atividade::where('ativo', true)
            ->whereIn('codigo_cnae', $estabelecimento->getCnaes())
            ->pluck('id');
        
        // 3. Buscar documentos específicos das atividades
        $documentosEspecificos = self::where('ativo', true)
            ->where('documento_comum', false)
            ->whereHas('atividades', function($q) use ($atividadesIds) {
                $q->whereIn('atividades.id', $atividadesIds);
            })
            ->where(function($q) use ($escopoCompetencia) {
                $q->where('escopo_competencia', 'todos')
                  ->orWhere('escopo_competencia', $escopoCompetencia);
            })
            ->where(function($q) use ($tipoSetor) {
                $q->where('tipo_setor', 'todos')
                  ->orWhere('tipo_setor', $tipoSetor);
            })
            ->get();
        
        // 4. Mesclar e deduplicar
        $todosDocumentos = $documentosComuns->merge($documentosEspecificos)
            ->unique('id')
            ->sortBy('ordem');
        
        // 5. Adicionar observação apropriada baseada no tipo de setor
        return $todosDocumentos->map(function($doc) use ($tipoSetor) {
            $doc->observacao_aplicavel = $doc->getObservacaoParaTipoSetor($tipoSetor);
            return $doc;
        });
    }
}
```

---

## Conclusão

A reestruturação proposta:

1. **Elimina a entidade ListaDocumento** - simplifica o modelo
2. **Vincula documentos diretamente às atividades** - mais intuitivo
3. **Mantém documentos comuns separados** - fácil gerenciamento
4. **Implementa deduplicação automática** - evita repetição
5. **Preserva filtros por escopo e setor** - flexibilidade mantida

A migração pode ser feita gradualmente, mantendo compatibilidade com dados existentes.
