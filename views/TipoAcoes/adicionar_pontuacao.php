<?php
session_start();
ob_start();
include '../header.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['nivel_acesso'] != 3) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['acao']) && $_POST['acao'] == 'deletar') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM acoes_pontuacao WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: adicionar_pontuacao.php?success=Pontuação deletada com sucesso.");
            exit();
        } else {
            $error = "Erro ao deletar a pontuação: " . $conn->error;
        }

        $stmt->close();
    } else {
        $acao_id = $_POST['acao_id'];
        $grupo_risco_id = $_POST['grupo_risco_id'];
        $municipio = $_SESSION['user']['municipio'];
        $pontuacao = $_POST['pontuacao'];

        $stmt = $conn->prepare("INSERT INTO acoes_pontuacao (acao_id, grupo_risco_id, municipio, pontuacao) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE pontuacao = ?");
        $stmt->bind_param("iissi", $acao_id, $grupo_risco_id, $municipio, $pontuacao, $pontuacao);

        if ($stmt->execute()) {
            header("Location: adicionar_pontuacao.php?success=Pontuação adicionada com sucesso.");
            exit();
        } else {
            $error = "Erro ao adicionar a pontuação: " . $conn->error;
        }

        $stmt->close();
    }
    $conn->close();
}

// Variáveis de paginação
$results_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

// Variável de pesquisa
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Consulta para as ações e grupos de risco
$acoes = $conn->query("SELECT id, descricao FROM tipos_acoes_executadas");
$gruposRisco = $conn->query("SELECT id, descricao FROM grupo_risco");

// Consulta para contagem total de registros
$total_query = "SELECT COUNT(*) FROM acoes_pontuacao ap 
                JOIN tipos_acoes_executadas ta ON ap.acao_id = ta.id 
                JOIN grupo_risco gr ON ap.grupo_risco_id = gr.id 
                WHERE ta.descricao LIKE '%$search%' OR gr.descricao LIKE '%$search%' OR ap.municipio LIKE '%$search%'";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_row();
$total_records = $total_row[0];
$total_pages = ceil($total_records / $results_per_page);

// Consulta com limite para paginação
$pontuacoes_query = "
    SELECT ap.id, ta.descricao AS acao, gr.descricao AS grupo_risco, ap.municipio, ap.pontuacao
    FROM acoes_pontuacao ap
    JOIN tipos_acoes_executadas ta ON ap.acao_id = ta.id
    JOIN grupo_risco gr ON ap.grupo_risco_id = gr.id
    WHERE ta.descricao LIKE '%$search%' OR gr.descricao LIKE '%$search%' OR ap.municipio LIKE '%$search%'
    LIMIT $start_from, $results_per_page
";
$pontuacoes = $conn->query($pontuacoes_query);
?>

<div class="container mt-5">
    <h4>Adicionar Pontuação para Ação</h4>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>

    <form action="adicionar_pontuacao.php" method="POST">
        <div class="mb-3">
            <label for="acao_id" class="form-label">Ação</label>
            <select class="form-control" id="acao_id" name="acao_id" required>
                <?php while ($acao = $acoes->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($acao['id']); ?>"><?php echo htmlspecialchars($acao['descricao']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="grupo_risco_id" class="form-label">Grupo de Risco</label>
            <select class="form-control" id="grupo_risco_id" name="grupo_risco_id" required>
                <?php while ($grupo = $gruposRisco->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($grupo['id']); ?>"><?php echo htmlspecialchars($grupo['descricao']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="pontuacao" class="form-label">Pontuação</label>
            <input type="number" class="form-control" id="pontuacao" name="pontuacao" required>
        </div>
        <button type="submit" class="btn btn-primary">Adicionar Pontuação</button>
    </form>

    <h4 class="mt-5">Pontuações das Ações por Grupo de Risco e Município</h4>

    <form class="d-flex mb-3" method="GET" action="">
        <input class="form-control me-2" type="search" name="search" placeholder="Pesquisar" aria-label="Pesquisar" value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-outline-success" type="submit">Pesquisar</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Ação</th>
                <th>Grupo de Risco</th>
                <th>Município</th>
                <th>Pontuação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($pontuacao = $pontuacoes->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($pontuacao['acao']); ?></td>
                    <td><?php echo htmlspecialchars($pontuacao['grupo_risco']); ?></td>
                    <td><?php echo htmlspecialchars($pontuacao['municipio']); ?></td>
                    <td><?php echo htmlspecialchars($pontuacao['pontuacao']); ?></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $pontuacao['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                Ações
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $pontuacao['id']; ?>">
                                <li><a class="dropdown-item" href="editar_pontuacao.php?id=<?php echo htmlspecialchars($pontuacao['id']); ?>">Editar</a></li>
                                <li>
                                    <form action="adicionar_pontuacao.php" method="POST" onsubmit="return confirm('Tem certeza que deseja deletar esta pontuação?');">
                                        <input type="hidden" name="acao" value="deletar">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($pontuacao['id']); ?>">
                                        <button type="submit" class="dropdown-item">Deletar</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="adicionar_pontuacao.php?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php include '../footer.php'; ?>
<?php
ob_end_flush();
?>
