<?php
session_start();
include '../header.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/OrdemServico.php';

$ordemServico = new OrdemServico($conn);

$usuarioLogado = $_SESSION['user'];
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$total_ordens = $ordemServico->getOrdensCountByMunicipio($usuarioLogado['municipio'], $search);
$total_pages = ceil($total_ordens / $limit);

$ordens = $ordemServico->getOrdensByMunicipio($usuarioLogado['municipio'], $search, $limit, $offset);


function formatDate($date) {
    $dateTime = new DateTime($date);
    return $dateTime->format('d/m/Y');
}
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Lista de Ordens de Serviço</h4>
        <?php if ($_SESSION['user']['nivel_acesso'] == 1 || $_SESSION['user']['nivel_acesso'] == 3) : ?>
        <a href="../OrdemServico/nova_ordem_servico.php" class="btn btn-success btn-sm">Criar ordem de Serviço sem Estabelecimento</a>
        <?php endif; ?>
    </div>
    <form method="GET" action="listar_ordens.php" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Buscar ordens de serviço..." value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
    </form>
    <table class="table table-hover table-bordered" style="font-size:15px;">
        <thead>
            <tr>
                <th>Razão Social</th>
                <th>Nome Fantasia</th>
                <th>Data Início</th>
                <th>Data Fim</th>
                <th>Técnicos</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ordens as $ordem): ?>
                <tr>
                    <td><?php echo isset($ordem['razao_social']) ? htmlspecialchars($ordem['razao_social']) : 'N/A'; ?></td>
                    <td><?php echo isset($ordem['nome_fantasia']) ? htmlspecialchars($ordem['nome_fantasia']) : 'N/A'; ?></td>
                    <td><?php echo htmlspecialchars(formatDate($ordem['data_inicio'])); ?></td>
                    <td><?php echo htmlspecialchars(formatDate($ordem['data_fim'])); ?></td>
                    <td><?php echo htmlspecialchars(implode(', ', $ordem['tecnicos_nomes'])); ?></td>
                    <td><?php echo htmlspecialchars($ordem['status']); ?></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $ordem['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                Ações
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $ordem['id']; ?>">
                                <li>
                                    <a class="dropdown-item" href="<?php echo is_null($ordem['estabelecimento_id']) || is_null($ordem['processo_id']) ? 'detalhes_ordem_sem_estabelecimento.php' : 'detalhes_ordem.php'; ?>?id=<?php echo htmlspecialchars($ordem['id']); ?>">Detalhes</a>
                                </li>
                                <?php if (is_null($ordem['estabelecimento_id']) || is_null($ordem['processo_id'])): ?>
                                    <li>
                                        <a class="dropdown-item" href="vincular_ordem.php?id=<?php echo htmlspecialchars($ordem['id']); ?>">Vincular</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Paginação -->
    <nav>
        <ul class="pagination">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>">Anterior</a>
                </li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>">Próximo</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<?php include '../footer.php'; ?>
