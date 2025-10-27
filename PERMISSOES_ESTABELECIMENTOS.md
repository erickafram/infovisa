# 🔐 Sistema de Permissões por Perfil e Município

## 🎯 Objetivo

Controlar o acesso aos estabelecimentos baseado no **perfil do usuário interno** e no **município vinculado**, respeitando as regras de **pactuação de competências**.

## 👥 Perfis e Regras de Acesso

### **1. Administrador**
- ✅ Vê **TODOS** os estabelecimentos
- ✅ Pode cadastrar estabelecimentos de **qualquer município**
- ✅ Sem restrições

### **2. Gestor Municipal / Técnico Municipal**
- ✅ Vê **APENAS** estabelecimentos do **seu município**
- ✅ Pode cadastrar estabelecimentos **apenas do seu município**
- ❌ **NÃO** vê estabelecimentos de outros municípios
- 📍 **Obrigatório**: Ter `municipio_id` vinculado

### **3. Gestor Estadual / Técnico Estadual**
- ✅ Vê estabelecimentos de **competência estadual** de **qualquer município**
- ✅ Pode cadastrar estabelecimentos de **qualquer município do Tocantins**
- ✅ Estabelecimentos são filtrados pela **pactuação**
- 📋 **Regra**: Estabelecimento aparece se **PELO MENOS UMA** atividade (CNAE) for estadual

## 🏗️ Estrutura Implementada

### **1. Migration**
```sql
ALTER TABLE usuarios_internos 
ADD COLUMN municipio_id BIGINT UNSIGNED NULL,
ADD FOREIGN KEY (municipio_id) REFERENCES municipios(id);
```

### **2. Model UsuarioInterno**
```php
// Relacionamento
public function municipioRelacionado()
{
    return $this->belongsTo(Municipio::class, 'municipio_id');
}

// Métodos auxiliares
$usuario->isAdmin();      // true se Administrador
$usuario->isMunicipal();  // true se Gestor/Técnico Municipal
$usuario->isEstadual();   // true se Gestor/Técnico Estadual
```

### **3. Model Estabelecimento**
```php
// Scope para filtrar por perfil
public function scopeParaUsuario($query, $usuario)
{
    if ($usuario->isAdmin()) {
        return $query; // Vê tudo
    }
    
    if ($usuario->isMunicipal()) {
        return $query->where('municipio_id', $usuario->municipio_id);
    }
    
    if ($usuario->isEstadual()) {
        return $query; // Filtro de competência aplicado no controller
    }
}

// Verifica competência
$estabelecimento->isCompetenciaEstadual();  // true/false
$estabelecimento->isCompetenciaMunicipal(); // true/false
```

## 📊 Fluxo de Verificação

### **Para Usuários Municipais:**
```
1. Usuário loga no sistema
2. Sistema verifica: usuario->municipio_id
3. Query: WHERE municipio_id = usuario->municipio_id
4. Retorna apenas estabelecimentos do município
```

### **Para Usuários Estaduais:**
```
1. Usuário loga no sistema
2. Query: Busca TODOS os estabelecimentos
3. Para cada estabelecimento:
   a. Pega todas as atividades (CNAEs)
   b. Verifica se PELO MENOS UMA é estadual
   c. Considera exceções municipais (descentralização)
4. Retorna apenas estabelecimentos estaduais
```

## 🔄 Lógica de Competência

### **Estabelecimento é ESTADUAL se:**
```php
// Pelo menos UMA atividade é estadual
foreach ($estabelecimento->getAtividades() as $cnae) {
    if (Pactuacao::isAtividadeEstadual($cnae, $municipio)) {
        return true; // É ESTADUAL
    }
}
return false; // É MUNICIPAL
```

### **Atividade é ESTADUAL se:**
1. Existe pactuação do tipo "estadual" para o CNAE
2. **E** o município NÃO está na lista de exceções (descentralização)

### **Exemplo Prático:**
```
CNAE: 4711-3/01 (Supermercado)
Pactuação: ESTADUAL
Exceções: ["Araguaína", "Palmas"]

Estabelecimento em PALMAS:
- Atividade: 4711-3/01
- Município está nas exceções
- Resultado: MUNICIPAL ❌ (não aparece para estadual)

Estabelecimento em GURUPI:
- Atividade: 4711-3/01
- Município NÃO está nas exceções
- Resultado: ESTADUAL ✅ (aparece para estadual)
```

## 📝 Cadastro de Estabelecimentos

### **Pessoa Jurídica (API CNPJ):**
```php
// Dados vêm da API
$dadosApi = consultarCNPJ($cnpj);

// Normaliza município automaticamente
$municipioId = MunicipioHelper::normalizarEObterIdPorNome(
    $dadosApi['municipio'],
    $dadosApi['codigo_municipio_ibge']
);

$estabelecimento->municipio_id = $municipioId;
```

### **Pessoa Física (Formulário):**
```php
// Usuário preenche cidade
$cidade = $request->cidade; // Ex: "PALMAS - TO"

// Remove sufixo e normaliza
$nomeMunicipio = preg_replace('/\s*[-\/]\s*TO\s*$/i', '', $cidade);

// Obtém ID do município
$municipioId = MunicipioHelper::normalizarEObterIdPorNome($nomeMunicipio);

$estabelecimento->municipio_id = $municipioId;
```

## 🎯 Validações Importantes

### **Ao Criar Usuário Municipal:**
```php
// Validação no controller
if (in_array($nivelAcesso, ['gestor_municipal', 'tecnico_municipal'])) {
    $request->validate([
        'municipio_id' => 'required|exists:municipios,id'
    ], [
        'municipio_id.required' => 'Município é obrigatório para usuários municipais'
    ]);
}
```

### **Ao Listar Estabelecimentos:**
```php
// Usuário municipal SEM município vinculado
if ($usuario->isMunicipal() && !$usuario->municipio_id) {
    return []; // Não vê nada
}
```

## 📋 Checklist de Implementação

- [x] Migration `municipio_id` em `usuarios_internos`
- [x] Relacionamento `UsuarioInterno` → `Municipio`
- [x] Scope `paraUsuario()` no Model `Estabelecimento`
- [x] Filtro de competência estadual no Controller
- [x] Normalização automática de município no cadastro
- [ ] Atualizar formulário de usuário interno (adicionar select de município)
- [ ] Validação obrigatória de município para perfis municipais
- [ ] Testes com diferentes perfis

## 🧪 Como Testar

### **1. Criar Usuário Municipal:**
```sql
INSERT INTO usuarios_internos (nome, email, nivel_acesso, municipio_id, password)
VALUES ('João Silva', 'joao@palmas.to.gov.br', 'gestor_municipal', 90, bcrypt('senha123'));
-- municipio_id = 90 (PALMAS)
```

### **2. Logar e Verificar:**
- Deve ver apenas estabelecimentos de PALMAS
- Não deve ver estabelecimentos de GURUPI, PRAIA NORTE, etc.

### **3. Criar Usuário Estadual:**
```sql
INSERT INTO usuarios_internos (nome, email, nivel_acesso, password)
VALUES ('Maria Santos', 'maria@adapec.to.gov.br', 'gestor_estadual', bcrypt('senha123'));
```

### **4. Logar e Verificar:**
- Deve ver estabelecimentos de competência estadual de todos os municípios
- Não deve ver estabelecimentos puramente municipais

## 🚨 Importante

1. **Município é obrigatório** para Gestor/Técnico Municipal
2. **Município é opcional** para Gestor/Técnico Estadual (vê todos)
3. **Administrador** não precisa de município (vê tudo)
4. **Competência** é verificada por atividade (CNAE), não por município
5. **Exceções** (descentralização) são respeitadas na verificação

## 🔄 Próximos Passos

1. Atualizar formulário de cadastro/edição de usuário interno
2. Adicionar select de município (obrigatório para municipais)
3. Criar testes automatizados
4. Documentar no manual do usuário
