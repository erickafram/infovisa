# 📍 Sistema de Normalização de Municípios

## 🎯 Objetivo

Resolver o problema de **inconsistência de nomes de municípios** vindos de APIs externas (CNPJ, CEP) através de uma tabela normalizada com relacionamentos.

## 🏗️ Estrutura

### Tabela `municipios`
```sql
- id (PK)
- nome (ex: "PALMAS")
- codigo_ibge (ex: "1721000")
- uf (ex: "TO")
- slug (ex: "palmas")
- ativo
```

### Relacionamentos
- `estabelecimentos.municipio_id` → `municipios.id`
- `pactuacoes.municipio_id` → `municipios.id`
- `pactuacoes.municipios_excecao_ids` → Array de `municipios.id`

## 📦 Como Usar

### 1. Ao Receber Dados de API Externa

**Exemplo: Consulta CNPJ**
```php
use App\Helpers\MunicipioHelper;

// Dados da API
$dadosApi = [
    'municipio' => 'PRAIA NORTE',
    'codigo_municipio_ibge' => '1718303'
];

// Normaliza e obtém o ID do município
$municipioId = MunicipioHelper::normalizarEObterIdPorNome(
    $dadosApi['municipio'],
    $dadosApi['codigo_municipio_ibge']
);

// Salva o estabelecimento
$estabelecimento = Estabelecimento::create([
    'nome_fantasia' => $dadosApi['nome_fantasia'],
    'municipio' => $dadosApi['municipio'], // Mantém string para compatibilidade
    'municipio_id' => $municipioId,         // Relacionamento normalizado
    'codigo_municipio_ibge' => $dadosApi['codigo_municipio_ibge'],
    // ... outros campos
]);
```

### 2. Ao Criar Pactuações

**Pactuação Municipal:**
```php
use App\Helpers\MunicipioHelper;

$municipioId = MunicipioHelper::normalizarEObterIdPorNome('Araguaína');

Pactuacao::create([
    'tipo' => 'municipal',
    'municipio' => 'ARAGUAÍNA',      // String para compatibilidade
    'municipio_id' => $municipioId,  // FK normalizada
    'cnae_codigo' => '4711-3/01',
    'cnae_descricao' => 'Comércio varejista...',
    'ativo' => true
]);
```

**Pactuação Estadual com Exceções:**
```php
// Municípios descentralizados
$municipiosExcecao = ['Araguaína', 'Palmas', 'Gurupi'];
$municipiosIds = [];

foreach ($municipiosExcecao as $nome) {
    $id = MunicipioHelper::normalizarEObterIdPorNome($nome);
    if ($id) {
        $municipiosIds[] = $id;
    }
}

Pactuacao::create([
    'tipo' => 'estadual',
    'cnae_codigo' => '4645-1/01',
    'cnae_descricao' => 'Comércio atacadista...',
    'municipios_excecao' => $municipiosExcecao,      // Array de strings (compatibilidade)
    'municipios_excecao_ids' => $municipiosIds,      // Array de IDs (normalizado)
    'ativo' => true
]);
```

### 3. Verificar Competência

**Usando relacionamento normalizado:**
```php
// Verifica se estabelecimento é estadual
$estabelecimento = Estabelecimento::with('municipio')->find($id);

// Pega o nome do município do relacionamento
$nomeMunicipio = $estabelecimento->municipio->nome;

// Verifica competência
$isEstadual = Pactuacao::isAtividadeEstadual(
    $estabelecimento->cnae_fiscal,
    $nomeMunicipio
);
```

## 🔧 Comandos Úteis

### Migrar Dados Existentes
```bash
php artisan municipios:migrar
```

### Recarregar Municípios
```bash
php artisan db:seed --class=MunicipioSeeder
```

## 🎨 Helper: MunicipioHelper

### Métodos Disponíveis

```php
// Normaliza nome e retorna ID
MunicipioHelper::normalizarEObterIdPorNome('PRAIA NORTE', '1718303');

// Normaliza nome e retorna objeto Municipio
MunicipioHelper::normalizarEObterPorNome('Palmas');

// Obtém ID por código IBGE
MunicipioHelper::obterIdPorCodigoIbge('1721000');

// Obtém nome por ID
MunicipioHelper::obterNomePorId(1);

// Mapeia variações comuns
MunicipioHelper::mapearVariacoes('PARAISO DO TO'); // Retorna: "PARAÍSO DO TOCANTINS"
```

## 📊 Vantagens

✅ **Consistência**: Elimina variações de escrita  
✅ **Integridade**: FK garante dados válidos  
✅ **Performance**: Índices otimizados  
✅ **Manutenibilidade**: Centraliza lógica de normalização  
✅ **Compatibilidade**: Mantém campos antigos durante transição  
✅ **Rastreabilidade**: Código IBGE vincula com APIs oficiais  

## 🔄 Migração Gradual

O sistema foi projetado para **migração gradual**:

1. **Campos antigos mantidos**: `municipio` (string) continua funcionando
2. **Novos campos adicionados**: `municipio_id` (FK) é opcional
3. **Dupla gravação**: Salve ambos durante transição
4. **Migração automática**: Comando `municipios:migrar` atualiza dados existentes

## 🚨 Importante

- **Sempre use `MunicipioHelper`** ao processar dados de APIs externas
- **Código IBGE é prioritário** para identificação
- **Slug é usado para comparações** case-insensitive
- **Novos municípios são criados automaticamente** se não existirem

## 📝 Exemplo Completo: Salvar Estabelecimento da API

```php
use App\Helpers\MunicipioHelper;
use App\Models\Estabelecimento;

// Dados da API CNPJ
$dadosApi = json_decode($responseApi, true);

// Normaliza município
$municipioId = MunicipioHelper::normalizarEObterIdPorNome(
    $dadosApi['municipio'],
    $dadosApi['codigo_municipio_ibge']
);

// Cria estabelecimento
$estabelecimento = Estabelecimento::create([
    'tipo_pessoa' => 'juridica',
    'cnpj' => $dadosApi['cnpj'],
    'razao_social' => $dadosApi['razao_social'],
    'nome_fantasia' => $dadosApi['nome_fantasia'],
    'logradouro' => $dadosApi['logradouro'],
    'numero' => $dadosApi['numero'],
    'bairro' => $dadosApi['bairro'],
    'cidade' => $dadosApi['municipio'],
    'municipio' => $dadosApi['municipio'],           // String (compatibilidade)
    'municipio_id' => $municipioId,                  // FK (normalizado)
    'codigo_municipio_ibge' => $dadosApi['codigo_municipio_ibge'],
    'uf' => $dadosApi['uf'],
    'cep' => $dadosApi['cep'],
    'cnae_fiscal' => $dadosApi['cnae_fiscal'],
    'cnae_fiscal_descricao' => $dadosApi['cnae_fiscal_descricao'],
    'cnaes_secundarios' => $dadosApi['cnaes_secundarios'],
    // ... outros campos
]);

// Agora você pode acessar o município normalizado
$municipioNome = $estabelecimento->municipio->nome;
$municipioIbge = $estabelecimento->municipio->codigo_ibge;
```

## 🎯 Próximos Passos

1. ✅ Tabela de municípios criada
2. ✅ Relacionamentos adicionados
3. ✅ Helper de normalização implementado
4. ✅ Comando de migração criado
5. ✅ Dados migrados
6. ⏳ Atualizar controllers para usar `municipio_id`
7. ⏳ Atualizar views para exibir município normalizado
8. ⏳ Remover campos antigos após validação completa
