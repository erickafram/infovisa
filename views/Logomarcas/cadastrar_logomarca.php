<?php
session_start();
require_once '../../conf/database.php'; // Inclua o arquivo de configuração do banco de dados
require_once '../../controllers/LogomarcaController.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

$controller = new LogomarcaController($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['acao'] == 'cadastrar') {
        // Verificar se o município enviado no formulário corresponde ao município do usuário logado
        if ($_POST['municipio'] !== $_SESSION['user']['municipio']) {
            header("Location: cadastrar_logomarca.php?error=" . urlencode("Você só pode cadastrar logomarca para o seu município."));
            exit();
        }
        $controller->create();
    } elseif ($_POST['acao'] == 'atualizar') {
        // Verificar se o município enviado no formulário corresponde ao município do usuário logado
        if ($_POST['municipio'] !== $_SESSION['user']['municipio']) {
            header("Location: cadastrar_logomarca.php?error=" . urlencode("Você só pode atualizar logomarca para o seu município."));
            exit();
        }
        $controller->update();
    }
}
include '../header.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Logomarca</title>
</head>
<body>

<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Cadastrar Logomarca</h6>
        </div>
        <div class="card-body">
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php elseif (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>
            <form action="cadastrar_logomarca.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="municipio" class="form-label">Município</label>
                    <input type="text" class="form-control" id="municipio" name="municipio" value="<?php echo $_SESSION['user']['municipio']; ?>" readonly required>
                </div>
                <div class="mb-3">
                    <label for="logomarca" class="form-label">Logomarca</label>
                    <input type="file" class="form-control" id="logomarca" name="logomarca" required>
                </div>
                <div class="mb-3">
                    <label for="espacamento" class="form-label">Espaçamento abaixo da logomarca (em pixels)</label>
                    <input type="number" class="form-control" id="espacamento" name="espacamento" value="40" required>
                </div>
                <input type="hidden" name="acao" value="cadastrar">
                <button type="submit" class="btn btn-primary">Cadastrar</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
