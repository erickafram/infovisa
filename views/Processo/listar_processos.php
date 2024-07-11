<?php
session_start();
include '../header.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/Processo.php';

$processo = new Processo($conn);

$search = isset($_GET['search']) ? $_GET['search'] : '';
$municipioUsuario = $_SESSION['user']['municipio']; // Obtendo o município do usuário logado
$isAdmin = $_SESSION['user']['nivel_acesso'] == 1; // Verificando se o usuário é administrador
$pendentes = isset($_GET['pendentes']) && $_GET['pendentes'] == '1'; // Verifica se o filtro de pendentes está ativado
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Variáveis de paginação
$limit = 10; // Número de processos por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Buscar processos com base na busca, no município do usuário (se não for administrador) e no filtro de pendentes
$processos = $processo->searchProcessosPorMunicipioPaginacao($search, $municipioUsuario, $isAdmin, $limit, $offset, $pendentes, $status);

// Obter o total de processos para a paginação
$totalProcessos = $processo->countProcessosPorMunicipio($search, $municipioUsuario, $isAdmin, $pendentes);


function formatDate($date)
{
    $dateTime = new DateTime($date);
    return $dateTime->format('d/m/Y');
}
?>

<style>
    th {
        font-size: 13px;
    }

    td {
        font-size: 14px;
    }
</style>

<div class="container mt-5">

    <form class="mb-3" method="GET" action="listar_processos.php">
        <div class="row mb-3">
            <div class="col">
                <input type="text" class="form-control" name="search" placeholder="Buscar por Número do Processo, Nome do Estabelecimento ou CNPJ" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col">
                <select class="form-select" name="status">
                    <option value="">Todos os Status</option>
                    <option value="ATIVO" <?php echo (isset($_GET['status']) && $_GET['status'] == 'ATIVO') ? 'selected' : ''; ?>>ATIVO</option>
                    <option value="ARQUIVADO" <?php echo (isset($_GET['status']) && $_GET['status'] == 'ARQUIVADO') ? 'selected' : ''; ?>>ARQUIVADO</option>
                    <option value="PARADO" <?php echo (isset($_GET['status']) && $_GET['status'] == 'PARADO') ? 'selected' : ''; ?>>PARADO</option>
                </select>
            </div>
            <div class="col">
                <button class="btn btn-primary" type="submit">Buscar</button>
            </div>
        </div>
        <button class="btn btn-warning btn-sm" type="submit" name="pendentes" value="1">Processos com documentação pendente</button>
    </form>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Lista de Processos</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>N° do Processo</th>
                        <th>Tipo</th>
                        <th>Data de Abertura</th>
                        <th>Estabelecimento</th>
                        <th>CNPJ</th>
                        <th>Status</th>
                        <th>Arquivos Pendentes</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($processos as $proc) : ?>
                        <tr onclick="window.location='documentos.php?processo_id=<?php echo $proc['id']; ?>&id=<?php echo $proc['estabelecimento_id']; ?>';" style="cursor:pointer;">
                            <td><?php echo htmlspecialchars($proc['numero_processo']); ?></td>
                            <td><?php echo htmlspecialchars($proc['tipo_processo']); ?></td>
                            <td><?php echo htmlspecialchars(formatDate($proc['data_abertura'])); ?></td>
                            <td><?php echo htmlspecialchars($proc['nome_fantasia']); ?></td>
                            <td><?php echo htmlspecialchars($proc['cnpj']); ?></td>
                            <td><?php echo htmlspecialchars($proc['status']); ?></td>
                            <td>
                                <?php if ($proc['documentos_pendentes'] > 0) : ?>
                                    <span class="badge bg-warning text-light">Pendentes</span>
                                <?php else : ?>
                                    <span class="badge bg-success text-light">Nenhum Pendente</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Links de paginação -->
            <?php
            $totalPages = ceil($totalProcessos / $limit);
            if ($totalPages > 1) : ?>
                <nav>
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                            <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>&pendentes=<?php echo $pendentes ? '1' : '0'; ?>&status=<?php echo isset($_GET['status']) ? htmlspecialchars($_GET['status']) : ''; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$conn->close();
include '../footer.php';
?>