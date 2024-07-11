<?php
session_start();
include '../header.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/Arquivo.php';
require_once '../../models/Processo.php';
require_once '../../models/Estabelecimento.php';

$arquivoModel = new Arquivo($conn);
$processoModel = new Processo($conn);
$estabelecimentoModel = new Estabelecimento($conn);

$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$documentosParaFinalizar = $arquivoModel->getDocumentosParaFinalizar($search, $limit, $offset);
$totalDocumentos = $arquivoModel->getTotalDocumentosParaFinalizar($search);
$totalPages = ceil($totalDocumentos / $limit);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos para Finalizar</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Documentos para Finalizar</h6>
                <form class="d-flex" action="" method="get">
                    <input class="form-control me-2" type="search" name="search" placeholder="Pesquisar" value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-success" type="submit">Pesquisar</button>
                </form>
            </div>
            <div class="card-body">
                <?php if (!empty($documentosParaFinalizar)) : ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tipo Documento</th>
                                <th>Data Upload</th>
                                <th>Numero Processo</th>
                                <th>Ver Processo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documentosParaFinalizar as $doc) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($doc['tipo_documento']); ?></td>
                                    <td><?php echo date("d/m/Y H:i:s", strtotime($doc['data_upload'])); ?></td>
                                    <td><?php echo htmlspecialchars($doc['numero_processo']); ?></td>
                                    <td>
                                        <a href="../Processo/documentos.php?processo_id=<?php echo $doc['processo_id']; ?>&id=<?php echo $doc['estabelecimento_id']; ?>" class="btn btn-primary btn-sm">Ver Processo</a>
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
                    <p>Nenhum documento para finalizar encontrado.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
