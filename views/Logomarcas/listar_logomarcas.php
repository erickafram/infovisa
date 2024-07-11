<?php
session_start();
include '../header.php';
require_once '../../conf/database.php'; // Inclua o arquivo de configuração do banco de dados
require_once '../../models/Logomarca.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

$municipio = $_SESSION['user']['municipio'];
$logomarcaModel = new Logomarca($conn);
$logomarcas = $logomarcaModel->getLogomarcaByUserMunicipio($municipio);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Logomarcas</title>
</head>
<body>

<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Logomarcas Cadastradas</h6>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Município</th>
                        <th>Logomarca</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logomarcas as $logomarca) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($logomarca['municipio']); ?></td>
                            <td><img src="<?php echo htmlspecialchars($logomarca['caminho_logomarca']); ?>" alt="Logomarca" width="100"></td>
                            <td>
                                <a href="editar_logomarca.php?municipio=<?php echo $logomarca['municipio']; ?>" class="btn btn-warning">Editar</a>
                                <a href="excluir_logomarca.php?municipio=<?php echo $logomarca['municipio']; ?>" class="btn btn-danger">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
