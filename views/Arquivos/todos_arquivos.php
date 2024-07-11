<?php
session_start();

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';

$municipio = $_SESSION['user']['municipio']; // Município do usuário logado

$perPage = 10; // Número de resultados por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Consulta para obter a contagem total de registros
$countSql = "
    SELECT COUNT(*) AS total
    FROM arquivos a
    JOIN processos p ON a.processo_id = p.id
    JOIN estabelecimentos e ON p.estabelecimento_id = e.id
    WHERE 
        (p.numero_processo LIKE '%$searchTerm%' OR 
        a.tipo_documento LIKE '%$searchTerm%' OR 
        a.numero_arquivo LIKE '%$searchTerm%' OR 
        e.nome_fantasia LIKE '%$searchTerm%') AND
        e.municipio = '$municipio' AND
        a.status = 'finalizado'
";

$countResult = $conn->query($countSql);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $perPage);

// Consulta para obter todos os documentos e informações relacionadas com pesquisa e paginação
$sql = "
    SELECT 
        a.id, 
        a.tipo_documento, 
        a.data_upload, 
        a.numero_arquivo, 
        a.sigiloso, 
        p.id AS processo_id, 
        p.numero_processo, 
        e.nome_fantasia AS estabelecimento_nome,
        e.id AS estabelecimento_id
    FROM 
        arquivos a
    JOIN 
        processos p ON a.processo_id = p.id
    JOIN 
        estabelecimentos e ON p.estabelecimento_id = e.id
    WHERE 
        (p.numero_processo LIKE '%$searchTerm%' OR 
        a.tipo_documento LIKE '%$searchTerm%' OR 
        a.numero_arquivo LIKE '%$searchTerm%' OR 
        e.nome_fantasia LIKE '%$searchTerm%') AND
        e.municipio = '$municipio' AND
        a.status = 'finalizado'
    ORDER BY 
        a.data_upload DESC
    LIMIT $perPage OFFSET $offset
";

$result = $conn->query($sql);

// Debug: Verifique os dados retornados pela consulta
$debugData = [];
while ($row = $result->fetch_assoc()) {
    $debugData[] = $row;
}
?>

<?php include '../header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">Todos os Documentos</h2>
    <div class="card">
        <div class="card-body">
            <form method="GET" action="todos_arquivos.php" class="mb-4">
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" class="form-control" name="search" placeholder="Pesquisar por qualquer campo" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Pesquisar</button>
            </form>

            <?php if (empty($debugData)): ?>
                <p>Nenhum documento encontrado.</p>
            <?php else: ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tipo de Documento</th>
                            <th>Data</th>
                            <th>Número</th>
                            <th>Sigiloso</th>
                            <th>Processo</th>
                            <th>Estabelecimento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($debugData as $row) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['tipo_documento'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($row['data_upload']))); ?></td>
                                <td><?php echo htmlspecialchars($row['numero_arquivo'] . '.' . date('Y')); ?></td>
                                <td><?php echo $row['sigiloso'] ? 'Sim' : 'Não'; ?></td>
                                <td><a href="../Processo/documentos.php?processo_id=<?php echo htmlspecialchars($row['processo_id']); ?>&id=<?php echo htmlspecialchars($row['estabelecimento_id']); ?>"><?php echo htmlspecialchars($row['numero_processo'] ?? ''); ?></a></td>
                                <td><?php echo htmlspecialchars($row['estabelecimento_nome'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Paginação -->
                <nav>
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="todos_arquivos.php?page=<?php echo $i; ?>&search=<?php echo urlencode($searchTerm); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
