# Documentos Comuns - Implementação Completa

## ✅ Implementado

Sistema agora exibe e gerencia documentos comuns em todas as telas relevantes.

## 📋 O que foi feito:

### 1. **Tela de Configuração - Criar Lista** (`/admin/configuracoes/listas-documento/create`)
- ✅ Seção destacada em verde mostrando todos os documentos comuns
- ✅ Cards informativos com nome, descrição e tags
- ✅ Mensagem clara: "Aplicados automaticamente a todos os serviços"
- ✅ Separação visual entre documentos comuns e específicos

### 2. **Tela de Configuração - Editar Lista** (`/admin/configuracoes/listas-documento/edit`)
- ✅ Box verde compacto mostrando os documentos comuns
- ✅ Lista em grid com os nomes dos documentos
- ✅ Contador de quantos documentos comuns existem

### 3. **Tela de Processo - Enviar Documentos** (`/company/processos/{id}`)
- ✅ Documentos comuns aparecem PRIMEIRO na lista
- ✅ Marcados como "Documentos Comuns" na origem
- ✅ Sempre obrigatórios
- ✅ Ordenação: Comuns → Obrigatórios → Opcionais

### 4. **Tela Admin - Visualizar Processo** (`/admin/estabelecimentos/{id}/processos/{processo}`)
- ✅ Documentos comuns aparecem PRIMEIRO na lista
- ✅ Marcados como "Documentos Comuns"
- ✅ Sempre obrigatórios
- ✅ Mesma ordenação da tela da empresa

## 🔧 Alterações Técnicas:

### Controller: `app/Http/Controllers/Admin/ListaDocumentoController.php`

**Método `create()`:**
```php
// Documentos específicos (podem ser selecionados)
$tiposDocumento = TipoDocumentoObrigatorio::ativos()
    ->where('documento_comum', false)
    ->ordenado()
    ->get();

// Documentos comuns (apenas para visualização/informação)
$documentosComuns = TipoDocumentoObrigatorio::ativos()
    ->where('documento_comum', true)
    ->ordenado()
    ->get();
```

**Método `edit()`:**
- Mesma lógica aplicada

### Controller: `app/Http/Controllers/Company/ProcessoController.php`

**Método `buscarDocumentosObrigatoriosParaProcesso()`:**
```php
// ADICIONA DOCUMENTOS COMUNS PRIMEIRO
$documentosComuns = \App\Models\TipoDocumentoObrigatorio::where('ativo', true)
    ->where('documento_comum', true)
    ->ordenado()
    ->get();

foreach ($documentosComuns as $doc) {
    $documentos->push([
        'id' => $doc->id,
        'nome' => $doc->nome,
        'descricao' => $doc->descricao,
        'obrigatorio' => true, // Sempre obrigatórios
        'observacao' => null,
        'lista_nome' => 'Documentos Comuns',
        'ja_enviado' => $jaEnviado,
        'status_envio' => $statusEnvio,
        'documento_comum' => true, // Flag para identificar
    ]);
}

// Ordenação final
return $documentos->sortBy([
    ['documento_comum', 'desc'], // Comuns primeiro
    ['obrigatorio', 'desc'],      // Depois obrigatórios
    ['nome', 'asc'],              // Por fim, alfabética
])->values();
```

### Controller: `app/Http/Controllers/ProcessoController.php` (Admin)

**Método `buscarDocumentosObrigatoriosParaProcesso()`:**
- Mesma lógica aplicada ao controller do admin
- Documentos comuns aparecem primeiro
- Marcados com flag `documento_comum => true`
- Sempre obrigatórios

## 📊 Documentos Comuns Cadastrados:

1. **CNPJ** - Cadastro Nacional de Pessoa Jurídica
2. **Contrato Social** - Contrato Social da empresa
3. **DARE** - Documento de Arrecadação Estadual
4. **Comprovante de Pagamento do DARE**
5. **Parecer do Projeto Arquitetônico**

## 🎯 Fluxo Completo:

### Para o Administrador:
1. Acessa `/admin/configuracoes/listas-documento/create`
2. Vê os 5 documentos comuns destacados em verde
3. Seleciona apenas os documentos específicos necessários
4. Salva a lista

### Para a Empresa:
1. Abre um processo
2. Acessa "Enviar Documentos"
3. Vê PRIMEIRO os 5 documentos comuns (obrigatórios)
4. Depois vê os documentos específicos da lista
5. Envia todos os documentos necessários

## ✨ Benefícios:

- ✅ **Clareza**: Usuários sabem exatamente quais documentos são comuns
- ✅ **Consistência**: Documentos comuns sempre aparecem em todos os processos
- ✅ **Organização**: Separação visual clara entre comuns e específicos
- ✅ **Automação**: Não precisa adicionar manualmente em cada lista
- ✅ **Manutenção**: Alterar um documento comum afeta todos os processos

## 🔍 Verificação:

Para testar:
1. Acesse um processo existente: `/company/processos/54`
2. Clique em "Enviar Documentos"
3. Verifique se os 5 documentos comuns aparecem PRIMEIRO
4. Verifique se estão marcados como "Documentos Comuns"
5. Verifique se aparecem como "Obrigatório"

## 📝 Notas:

- Documentos comuns são sempre obrigatórios
- Não podem ser marcados como opcionais
- Aparecem em TODOS os processos, independente da atividade
- Não precisam ser adicionados manualmente às listas
- São filtrados por escopo (estadual/municipal) e tipo de setor se configurado
