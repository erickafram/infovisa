<?php
session_start();


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
// Configurações de paginação
$limit = 10; // Número de registros por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$userMunicipio = $_SESSION['user']['municipio'];
$nivel_acesso = $_SESSION['user']['nivel_acesso'];

// Obter estabelecimentos rejeitados filtrados pelo município do usuário
$totalEstabelecimentos = $estabelecimento->countEstabelecimentosRejeitados($search, $userMunicipio, $nivel_acesso);
$totalPages = ceil($totalEstabelecimentos / $limit);

$estabelecimentos = $estabelecimento->searchEstabelecimentosRejeitados($search, $limit, $offset, $userMunicipio, $nivel_acesso);

// Reiniciar estabelecimento para status pendente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reiniciar_estabelecimento_id'])) {
    $estabelecimentoId = $_POST['reiniciar_estabelecimento_id'];
    if ($estabelecimento->reiniciarEstabelecimento($estabelecimentoId)) {
        header("Location: listar_estabelecimentos_rejeitados.php?success=1");
        exit();
    } else {
        header("Location: listar_estabelecimentos_rejeitados.php?error=1");
        exit();
    }
}
include '../header.php';

?>

<div class="container mt-5">
    <h2>Lista de Estabelecimentos Negados</h2>
    <form class="mb-3" method="GET" action="listar_estabelecimentos_rejeitados.php">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Buscar por CNPJ, Nome Fantasia ou Município" value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
    </form>
    <table class="table table-hover table-bordered" style="font-size:15px;">
        <thead>
        <tr>
            <th>ID</th>
            <th>CNPJ</th>
            <th>Nome Fantasia</th>
            <th>Município</th>
            <th>Motivo da Rejeição</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($estabelecimentos): ?>
            <?php foreach ($estabelecimentos as $estab): ?>
                <tr>
                    <td><?php echo htmlspecialchars($estab['id']); ?></td>
                    <td><?php echo htmlspecialchars($estab['cnpj']); ?></td>
                    <td><?php echo htmlspecialchars($estab['nome_fantasia']); ?></td>
                    <td><?php echo htmlspecialchars($estab['municipio']); ?></td>
                    <td><?php echo htmlspecialchars($estab['motivo_negacao']); ?></td>
                    <td>
                        <form method="POST" action="listar_estabelecimentos_rejeitados.php" style="display:inline;">
                            <input type="hidden" name="reiniciar_estabelecimento_id" value="<?php echo htmlspecialchars($estab['id']); ?>">
                            <button type="submit" class="btn btn-warning btn-sm">Reiniciar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">Nenhum estabelecimento negado encontrado.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Paginação -->
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                    <a class="page-link" href="listar_estabelecimentos_rejeitados.php?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php
$conn->close();
include '../footer.php';
?>

