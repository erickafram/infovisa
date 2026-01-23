# Tabela V - Municípios Descentralizados Adicionado - IMPLEMENTADO

## Mudança Solicitada

Adicionar o campo "Municípios Descentralizados" para a **Tabela V - Definir se é VISA** porque a lógica de competência é:

- **SIM** = Sujeito à VISA → Se tem município descentralizado = Municipal, senão = Estadual  
- **NÃO** = Não sujeito à VISA

## Implementação Realizada

### ✅ **Campo de Municípios Descentralizados**
Adicionado o campo para a Tabela V junto com as Tabelas III e IV:

```html
<div class="mb-3" x-show="tabelaSelecionada === 'III' || tabelaSelecionada === 'IV' || tabelaSelecionada === 'V'">
```

### ✅ **Títulos Específicos por Tabela**
Cada tabela agora tem um título específico que explica sua função:

- **Tabela III**: "Municípios Descentralizados (Exceções)"
- **Tabela IV**: "Municípios Descentralizados (se SIM)" 
- **Tabela V**: "Municípios Descentralizados (se SIM e VISA)"

### ✅ **Textos Explicativos Específicos**
Cada tabela tem sua própria explicação:

- **Tabela III**: "Municípios que receberam descentralização para fiscalizar esta atividade."
- **Tabela IV**: "Municípios descentralizados (se resposta for SIM)."
- **Tabela V**: "Municípios descentralizados (se resposta for SIM e sujeito à VISA)."

## Lógica de Competência por Tabela

### **Tabela I - Municipal**
- Sempre municipal (139 municípios do Tocantins)

### **Tabela II - Estadual Exclusiva** 
- Sempre estadual (não descentralizada)

### **Tabela III - Alto Risco Pactuado**
- Estadual por padrão
- Municipal se município estiver na lista de exceções

### **Tabela IV - Com Questionário (Estadual/Municipal)**
- **SIM** = Estadual (exceto se município estiver descentralizado)
- **NÃO** = Municipal

### **Tabela V - Definir se é VISA** ✅ **NOVO**
- **SIM** = Sujeito à VISA:
  - Se município estiver descentralizado = **Municipal**
  - Se não estiver descentralizado = **Estadual**
- **NÃO** = Não sujeito à VISA (não precisa licença)

## Interface Atualizada

### ✅ **Campo Condicional**
O campo de municípios descentralizados agora aparece para:
- Tabela III ✅
- Tabela IV ✅  
- Tabela V ✅ **NOVO**

### ✅ **Funcionalidade Completa**
- Dropdown com busca de municípios
- Seleção múltipla com tags
- Remoção individual de municípios
- Validação e feedback visual

## Arquivos Modificados

### **resources/views/admin/pactuacoes/index.blade.php**
- Condição `x-show` atualizada para incluir Tabela V
- Títulos específicos por tabela adicionados
- Textos explicativos específicos adicionados

## Status: ✅ IMPLEMENTADO

A Tabela V agora possui o campo de "Municípios Descentralizados" com a mesma funcionalidade das outras tabelas, permitindo configurar corretamente a lógica de competência para atividades que precisam definir se são sujeitas à VISA.

### Fluxo de Uso para Tabela V:
1. Seleciona "Tabela V - Definir se é VISA"
2. Adiciona CNAEs das atividades
3. Define pergunta do questionário
4. **NOVO**: Seleciona municípios descentralizados (se houver)
5. Salva a configuração

Agora a lógica está completa para todas as tabelas! 🎉