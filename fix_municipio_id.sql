-- Script para corrigir o municipio_id dos estabelecimentos
-- O problema: estabelecimentos estão com municipio_id = código IBGE ao invés do ID da tabela municipios

-- 1. Verificar o ID correto de Palmas na tabela municipios
SELECT id, nome, codigo_ibge FROM municipios WHERE nome = 'PALMAS' AND uf = 'TO';
-- Resultado esperado: id = 90, codigo_ibge = 1721000

-- 2. Atualizar estabelecimentos de Palmas que estão com código IBGE ao invés do ID
UPDATE estabelecimentos 
SET municipio_id = 90 
WHERE municipio_id = 1721000 
  AND cidade = 'PALMAS' 
  AND estado = 'TO';

-- 3. Verificar se a correção foi aplicada
SELECT id, nome_fantasia, cidade, municipio_id 
FROM estabelecimentos 
WHERE cidade = 'PALMAS' 
  AND estado = 'TO';

-- 4. Atualizar TODOS os estabelecimentos que possam ter o mesmo problema
-- (usando o código IBGE ao invés do ID da tabela)
UPDATE estabelecimentos e
INNER JOIN municipios m ON e.municipio_id = m.codigo_ibge
SET e.municipio_id = m.id
WHERE e.municipio_id > 1000; -- IDs válidos da tabela municipios são menores que 1000

-- 5. Verificar estabelecimentos que ainda não têm municipio_id preenchido
SELECT id, nome_fantasia, cidade, estado, municipio_id 
FROM estabelecimentos 
WHERE municipio_id IS NULL;
