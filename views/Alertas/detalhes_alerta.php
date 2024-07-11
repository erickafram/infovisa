<?php
session_start();
include '../header.php';
require_once '../../conf/database.php';
require_once '../../models/Processo.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php"); // Redirecionar para a página de login se não estiver autenticado
    exit();
}

if (!isset($_GET['alerta_id'])) {
    echo "ID do alerta não fornecido.";
    exit();
}

$alerta_id = $_GET['alerta_id'];
$processoModel = new Processo($conn);

// Lógica para finalizar o alerta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['finalizar_alerta'])) {
    $processoModel->updateAlerta($alerta_id, null, null, 'finalizado');
    header("Location: ../Dashboard/dashboard.php");
    exit();
}

$alerta = $processoModel->getAlertaById($alerta_id);

if (!$alerta) {
    echo "Alerta não encontrado.";
    exit();
}
?>

<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <h4>Detalhes do Alerta</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nome do Estabelecimento:</strong> <?php echo htmlspecialchars($alerta['nome_fantasia']); ?></p>
                    <p><strong>Número do Processo:</strong> <?php echo htmlspecialchars($alerta['numero_processo']); ?></p>
                    <p><strong>Tipo de Processo:</strong> <?php echo htmlspecialchars($alerta['tipo_processo']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Descrição:</strong> <?php echo htmlspecialchars($alerta['descricao']); ?></p>
                    <p><strong>Prazo:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($alerta['prazo']))); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($alerta['status']); ?></p>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <form method="POST" class="d-inline">
                <button type="submit" name="finalizar_alerta" class="btn btn-success">Finalizar Alerta</button>
            </form>
            <a href="../Processo/documentos.php?processo_id=<?php echo $alerta['processo_id']; ?>&id=<?php echo $alerta['estabelecimento_id']; ?>" class="btn btn-primary">Ver Processo</a>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>