<?php
session_start();
include '../header.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';

// Variáveis de paginação
$results_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

// Variável de pesquisa
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Consulta para contagem total de registros
$total_query = "SELECT COUNT(*) FROM tipos_acoes_executadas WHERE descricao LIKE '%$search%'";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_row();
$total_records = $total_row[0];
$total_pages = ceil($total_records / $results_per_page);

// Consulta com limite para paginação
$query = "SELECT * FROM tipos_acoes_executadas WHERE descricao LIKE '%$search%' LIMIT $start_from, $results_per_page";
$result = $conn->query($query);
?>

<div class="container mt-5">
    <h4>Lista de Tipos de Ações</h4>

    <?php if (isset($_GET['success'])) : ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>

    <form class="d-flex mb-3" method="GET" action="">
        <input class="form-control me-2" type="search" name="search" placeholder="Pesquisar" aria-label="Pesquisar" value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-outline-success" type="submit">Pesquisar</button>
    </form>

    <div class="d-flex justify-content-between mb-3">
        <div></div>
        <a href="adicionar_tipo.php" class="btn btn-primary">Adicionar Tipo de Ação</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Descrição</th>
                <th>Código Procedimento</th>
                <th>Atividade do SIA?</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                    <td><?php echo htmlspecialchars($row['codigo_procedimento']); ?></td>
                    <td><?php echo $row['atividade_sia'] ? 'Sim' : 'Não'; ?></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $row['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                Ações
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $row['id']; ?>">
                                <li><a class="dropdown-item" href="editar_tipo.php?id=<?php echo htmlspecialchars($row['id']); ?>">Editar</a></li>
                                <li><a class="dropdown-item" href="deletar_tipo.php?id=<?php echo htmlspecialchars($row['id']); ?>" onclick="return confirm('Tem certeza que deseja deletar este tipo de ação?');">Deletar</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="listar_tipos.php?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php include '../footer.php'; ?>
