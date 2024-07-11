<?php
session_start();
include '../header.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/Assinatura.php';
require_once '../../models/Arquivo.php';

$assinaturaModel = new Assinatura($conn);
$usuario_id = $_SESSION['user']['id'];

$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$assinaturasRealizadas = $assinaturaModel->getAssinaturasRealizadas($usuario_id, $search, $limit, $offset);
$totalAssinaturas = $assinaturaModel->getTotalAssinaturasRealizadas($usuario_id, $search);
$totalPages = ceil($totalAssinaturas / $limit);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Assinaturas Realizadas</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Minhas Assinaturas Realizadas</h6>
                <form class="d-flex" action="" method="get">
                    <input class="form-control me-2" type="search" name="search" placeholder="Pesquisar" value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-success" type="submit">Pesquisar</button>
                </form>
            </div>
            <div class="card-body">
                <?php if (!empty($assinaturasRealizadas)) : ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tipo de Documento</th>
                                <th>Data da Assinatura</th>
                                <th>Número do Processo</th>
                                <th>Visualizar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assinaturasRealizadas as $assinatura) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($assinatura['tipo_documento']); ?></td>
                                    <td><?php echo date("d/m/Y H:i:s", strtotime($assinatura['data_assinatura'])); ?></td>
                                    <td><?php echo htmlspecialchars($assinatura['numero_processo']); ?></td>
                                    <td>
                                        <a href="../../<?php echo htmlspecialchars($assinatura['caminho_arquivo']); ?>" target="_blank" class="btn btn-primary btn-sm">Visualizar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-3">
                            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php else : ?>
                    <p>Nenhuma assinatura realizada.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.min.js"></script>
</body>

</html>
