<?php
session_start();
ob_start();
include '../header.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';

if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM modelos_documentos WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

$municipio = $_SESSION['user']['municipio'];
$modelos = $conn->query("SELECT * FROM modelos_documentos WHERE municipio = '$municipio'");

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Modelos de Documentos</title>
</head>

<body>

    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Modelos de Documentos</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tipo de Documento</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($modelo = $modelos->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($modelo['tipo_documento'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <a href="editar_modelo.php?id=<?php echo $modelo['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                                    <a href="listar_modelos.php?delete=true&id=<?php echo $modelo['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este modelo?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="inserir_modelo.php" class="btn btn-success">Inserir Novo Modelo</a>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>

</html>
