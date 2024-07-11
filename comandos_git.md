// ALTERAÇÕES GIT
# Verificar o status dos arquivos
git status

# Adicionar todos os arquivos modificados e novos
git add .

# Fazer commit das mudanças
git commit -m "Descrição das mudanças feitas"

# Enviar as mudanças para o repositório remoto
git push origin master


//COMANDO PARA LISTAR TODAS AS PASTAS NO PROJETO.
1 - ABRA O TERMINAL DO VISUAL CODE E COLE ISSO, LEMBRANDO QUE ESSE CODIGO EXCLUI A PASTA VENDOR
# Gera a árvore de diretórios
$tree = tree /F /A

# Divide a árvore de diretórios em linhas
$lines = $tree -split "`r`n"

# Variável para armazenar a árvore filtrada
$filteredTree = ""

# Flag para controlar se estamos dentro da pasta vendor
$inVendor = $false

foreach ($line in $lines) {
    if ($line -match "vendor") {
        $inVendor = $true
    } elseif ($inVendor -and $line -notmatch "^\|") {
        $inVendor = $false
    }

    if (-not $inVendor) {
        $filteredTree += $line + "`r`n"
    }
}

# Exibe a árvore filtrada
$filteredTree

# Salva a árvore filtrada em um arquivo de texto
$filteredTree | Out-File -FilePath .\directory_structure.txt

