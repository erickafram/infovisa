# CNAEs Completos para Adicionar ao Seeder

## ✅ JÁ IMPORTADOS (Exemplos de cada tabela)

### Tabela I - 12 CNAEs
### Tabela II - 12 CNAEs  
### Tabela III - 7 CNAEs
### Tabela IV - 6 CNAEs
### Tabela V - 5 CNAEs

**TOTAL IMPORTADO: 42 CNAEs**

---

## 📋 FALTAM ADICIONAR

### TABELA I - Atividades Municipais (faltam ~80 CNAEs)

Adicione no método `seedTabelaI()` do PactuacaoSeeder.php:

```php
['1091-1/02', 'Fabricação de produtos de padaria e confeitaria com predominância de produção própria'],
['3250-7/06', 'Serviços de prótese dentária'],
['3702-9/00', 'Atividades relacionadas a esgoto, exceto a gestão de redes'],
['3811-4/00', 'Coleta de resíduos não-perigosos'],
['3812-2/00', 'Coleta de resíduos perigosos'],
['3821-1/00', 'Tratamento e disposição de resíduos não-perigosos'],
['3822-0/00', 'Tratamento e disposição de resíduos perigosos'],
// ... adicione todos os outros da Tabela I do documento
```

### TABELA II - Atividades Estaduais Exclusivas (faltam ~10 CNAEs)

```php
['1099-6/99', 'Fabricação de outros produtos alimentícios não especificados anteriormente'],
['1122-4/04', 'Fabricação de bebidas isotônicas'],
['1122-4/99', 'Fabricação de outras bebidas não-alcoólicas não especificadas anteriormente'],
['1742-7/02', 'Fabricação de absorventes higiênicos'],
['2062-2/00', 'Fabricação de produtos de limpeza e polimento'],
// ... adicione todos os outros da Tabela II
```

### TABELA III - Atividades Alto Risco Pactuadas (faltam ~15 CNAEs)

```php
['1061-9/02', 'Fabricação de produtos do arroz', ['Palmas']],
['1062-7/00', 'Moagem de trigo e fabricação de derivados', ['Palmas']],
['1065-1/03', 'Fabricação de óleo de milho refinado', ['Palmas']],
['1072-4/01', 'Fabricação de açúcar de cana refinado', ['Palmas']],
['1072-4/02', 'Fabricação de açúcar de cereais (dextrose) e de beterraba', ['Palmas']],
['1082-1/00', 'Fabricação de produtos à base de café', ['Palmas']],
['1091-1/01', 'Fabricação de produtos de panificação industrial', ['Palmas']],
['1099-6/02', 'Fabricação de pós alimentícios', ['Palmas']],
['1742-7/01', 'Fabricação de fraldas descartáveis', ['Palmas']],
['4645-1/01', 'Comércio atacadista de instrumentos e materiais para uso médico, cirúrgico, hospitalar e de laboratórios', ['Araguaína', 'Augustinópolis', 'Colinas do TO', 'Dianópolis', 'Guaraí', 'Gurupi', 'Palmas', 'Porto Nacional', 'Paraíso do TO', 'Tocantinópolis']],
// ... adicione todos os outros da Tabela III
```

### TABELA IV - Atividades com Questionário (faltam ~10 CNAEs)

```php
['1032-5/99', 'Fabricação de conservas de legumes e outros vegetais, exceto palmito', 'O resultado do exercício da atividade será diferente de produto artesanal?', ['Araguaína', 'Augustinópolis', 'Colinas do TO', 'Dianópolis', 'Guaraí', 'Gurupi', 'Palmas', 'Porto Nacional', 'Paraíso do TO', 'Tocantinópolis']],
['1064-3/00', 'Fabricação de farinha de milho e derivados, exceto óleos de milho', 'O resultado do exercício da atividade será diferente de produto artesanal?', ['Araguaína', 'Augustinópolis', 'Colinas do TO', 'Dianópolis', 'Guaraí', 'Gurupi', 'Palmas', 'Porto Nacional', 'Paraíso do TO', 'Tocantinópolis']],
['1065-1/01', 'Fabricação de amidos e féculas de vegetais', 'O polvilho, resultado do exercício da atividade econômica, será diferente de produto artesanal?', ['Araguaína', 'Augustinópolis', 'Colinas do TO', 'Dianópolis', 'Guaraí', 'Gurupi', 'Palmas', 'Porto Nacional', 'Paraíso do TO', 'Tocantinópolis']],
// ... adicione todos os outros da Tabela IV
```

### TABELA V - Atividades que dependem de questionário (faltam ~15 CNAEs)

```php
['1731-1/00', 'Fabricação de embalagens de papel', 'O produto se destina a entrar em contato com alimento ou será usado para embalar produto a ser esterilizado?'],
['1732-0/00', 'Fabricação de embalagens de cartolina e papel-cartão', 'O produto se destina a entrar em contato com alimento ou produto para saúde?'],
['1733-8/00', 'Fabricação de chapas e de embalagens de papelão ondulado', 'O produto se destina a entrar em contato com alimento ou produto para saúde?'],
['2019-3/99', 'Fabricação de outros produtos químicos inorgânicos não especificados anteriormente', 'O resultado do exercício da atividade será produto de uso ou aplicação como aditivo de alimentos?'],
// ... adicione todos os outros da Tabela V
```

---

## 🚀 COMO ADICIONAR MAIS CNAEs

1. Abra `database/seeders/PactuacaoSeeder.php`
2. Localize o método da tabela correspondente (seedTabelaI, seedTabelaII, etc.)
3. Adicione os CNAEs no array `$dados`
4. Rode: `php artisan db:seed --class=PactuacaoSeeder`

---

## 📊 RESUMO

- **Tabela I**: ~92 CNAEs (12 importados, faltam ~80)
- **Tabela II**: ~22 CNAEs (12 importados, faltam ~10)
- **Tabela III**: ~22 CNAEs (7 importados, faltam ~15)
- **Tabela IV**: ~16 CNAEs (6 importados, faltam ~10)
- **Tabela V**: ~20 CNAEs (5 importados, faltam ~15)

**TOTAL ESTIMADO**: ~172 CNAEs
**JÁ IMPORTADOS**: 42 CNAEs (24%)
**FALTAM**: ~130 CNAEs (76%)

Os CNAEs mais importantes já estão importados como exemplo de cada tabela!
