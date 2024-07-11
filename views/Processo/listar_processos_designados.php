<?php
session_start();

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1,3])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/ProcessoResponsavel.php';

$processoResponsavel = new ProcessoResponsavel($conn);

$searchUser = isset($_GET['search_user']) ? $_GET['search_user'] : '';
$searchStatus = isset($_GET['search_status']) ? $_GET['search_status'] : '';

$processosDesignados = $processoResponsavel->getProcessosDesignados($searchUser, $searchStatus);

include '../header.php';

function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pendente':
            return 'badge bg-warning';
        case 'resolvido':
            return 'badge bg-success text-light';
        default:
            return 'badge bg-secondary';
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Processos Designados</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="listar_processos_designados.php" class="mb-4">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="search_user" placeholder="Pesquisar por usuário" value="<?php echo htmlspecialchars($searchUser); ?>">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <select class="form-control" name="search_status">
                                        <option value="">Todos os Status</option>
                                        <option value="pendente" <?php echo $searchStatus == 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                        <option value="resolvido" <?php echo $searchStatus == 'resolvido' ? 'selected' : ''; ?>>Resolvido</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-block">Pesquisar</button>
                            </div>
                        </div>
                    </form>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Número do Processo</th>
                                <th>Usuário</th>
                                <th>Descrição</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($processosDesignados as $processo) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($processo['numero_processo']); ?></td>
                                    <td><?php echo htmlspecialchars($processo['nome_completo']); ?></td>
                                    <td><?php echo htmlspecialchars($processo['descricao']); ?></td>
                                    <td><span class="badge <?php echo getStatusBadgeClass($processo['status']); ?>"><?php echo htmlspecialchars($processo['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>