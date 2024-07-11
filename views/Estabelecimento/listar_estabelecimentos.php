<?php
session_start();
include '../header.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php"); // Redirecionar para a página de login se não estiver autenticado ou não for administrador
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/Estabelecimento.php';

$estabelecimento = new Estabelecimento($conn);

$search = isset($_GET['search']) ? $_GET['search'] : '';

// Configurações de paginação
$limit = 10; // Número de registros por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$userMunicipio = $_SESSION['user']['municipio'];
$nivel_acesso = $_SESSION['user']['nivel_acesso'];

// Obter estabelecimentos filtrados pelo município do usuário
$totalEstabelecimentos = $estabelecimento->countEstabelecimentos($search, $userMunicipio, $nivel_acesso);
$totalPages = ceil($totalEstabelecimentos / $limit);

$estabelecimentos = $estabelecimento->searchEstabelecimentos($search, $limit, $offset, $userMunicipio, $nivel_acesso);

?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Lista de Estabelecimentos</h2>
        <?php if ($_SESSION['user']['nivel_acesso'] == 1 || $_SESSION['user']['nivel_acesso'] == 3) : ?>
            <a href="../Estabelecimento/cadastro_estabelecimento.php" class="btn btn-success btn-sm">Cadastrar Estabelecimento</a>
        <?php endif; ?>
    </div>
    <form class="mb-3" method="GET" action="listar_estabelecimentos.php">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Buscar por CNPJ, Razão Social, Nome Fantasia ou Município" value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
    </form>
    <table class="table table-hover table-bordered" style="font-size:15px;">
        <thead>
            <tr>
                <th>CNPJ</th>
                <th>Razão Social</th>
                <th>Nome Fantasia</th>
                <th>Município</th>
                <th>Situação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($estabelecimentos) : ?>
                <?php foreach ($estabelecimentos as $estab) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($estab['cnpj']); ?></td>
                        <td><?php echo htmlspecialchars($estab['razao_social']); ?></td>
                        <td><?php echo htmlspecialchars($estab['nome_fantasia']); ?></td>
                        <td><?php echo htmlspecialchars($estab['municipio']); ?></td>
                        <td>
                        <span style="
                        display: inline-block;
                        padding: 0px 10px;
                        color: white;
                        background-color: <?php
                            $situacao = htmlspecialchars($estab['descricao_situacao_cadastral']);
                            if ($situacao == 'ATIVA') {
                                echo 'green';
                            } elseif (in_array($situacao, ['SUSPENSA', 'BAIXADA'])) {
                                echo 'red';
                            } else {
                                echo 'orange';
                            }
                            ?>;
                         border-radius: 5px;">
                                <?php echo $situacao; ?>
                            </span>
                        </td>

                        <td>
                            <div class="dropdown">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    Detalhes
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <li><a class="dropdown-item" href="detalhes_estabelecimento.php?id=<?php echo $estab['id']; ?>">Ver Detalhes</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5" class="text-center">Nenhum estabelecimento encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Paginação -->
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                    <a class="page-link" href="listar_estabelecimentos.php?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php
$conn->close();
include '../footer.php';
?>