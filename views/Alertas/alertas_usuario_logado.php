<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';
require_once '../../controllers/AlertaController.php';
require_once '../../models/Processo.php';
require_once '../../models/LogVisualizacao.php'; // Adicionado

$municipioUsuario = $_SESSION['user']['municipio'];
$processoModel = new Processo($conn);
$alertas = $processoModel->getTodosAlertas($municipioUsuario);

$usuario_id = $_SESSION['user']['id'];
$alertaController = new AlertaController($conn);
$assinaturasPendentes = $alertaController->getAssinaturasPendentes($usuario_id);
$assinaturasRascunho = $alertaController->getAssinaturasRascunho($usuario_id);
$processosDesignadosPendentes = $alertaController->getProcessosDesignadosPendentes($usuario_id);
$processosPendentes = $processoModel->getProcessosComDocumentacaoPendente($municipioUsuario);

$logVisualizacaoModel = new LogVisualizacao($conn); // Adicionado

// Processar a marcação como resolvido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marcar_resolvido'])) {
    $processo_id = $_POST['processo_id'];
    $alertaController->marcarProcessoComoResolvido($processo_id, $usuario_id);
    // Recarregar a página para refletir a atualização
    header("Location: alertas_usuario_logado.php");
    exit();
}

include '../header.php';

// Combinar todos os alertas em uma única lista
$todosAlertas = array_merge(
    array_map(function($assinatura) {
        return [
            'tipo' => 'Assinatura Pendente',
            'descricao' => $assinatura['tipo_documento'],
            'data' => $assinatura['data_upload'],
            'processo_id' => $assinatura['processo_id'],
            'estabelecimento_id' => $assinatura['estabelecimento_id'],
            'arquivo_id' => $assinatura['arquivo_id'],
            'acao' => 'Assinar Documento'
        ];
    }, $assinaturasPendentes),
    array_map(function($assinatura) {
        return [
            'tipo' => 'Documento Rascunho a Finalizar',
            'descricao' => $assinatura['tipo_documento'],
            'data' => $assinatura['data_upload'],
            'processo_id' => $assinatura['processo_id'],
            'estabelecimento_id' => $assinatura['estabelecimento_id'],
            'acao' => 'Finalizar Documento'
        ];
    }, $assinaturasRascunho),
    array_map(function($processo) {
        return [
            'tipo' => 'Processo com Documentação Pendente',
            'descricao' => 'Processo #'.$processo['numero_processo'].' - '.$processo['nome_fantasia'],
            'data' => null,
            'processo_id' => $processo['processo_id'],
            'estabelecimento_id' => $processo['estabelecimento_id'],
            'acao' => 'Ver Processo'
        ];
    }, $processosPendentes),
    array_map(function($alerta) {
        return [
            'tipo' => 'Alerta Pendente',
            'descricao' => $alerta['descricao'],
            'data' => $alerta['prazo'],
            'processo_id' => $alerta['processo_id'],
            'estabelecimento_id' => $alerta['estabelecimento_id'],
            'acao' => 'Ver Processo'
        ];
    }, $alertas),
    array_map(function($processo) {
        return [
            'tipo' => 'Processo Designado Pendente',
            'descricao' => $processo['descricao'],
            'data' => null,
            'processo_id' => $processo['processo_id'],
            'estabelecimento_id' => $processo['estabelecimento_id'],
            'acao' => 'Ver Processo'
        ];
    }, $processosDesignadosPendentes)
);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas</title>
    <style>
        .content {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            background-color: #f9f9f9;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <!-- Lista de Alertas -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Alertas</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($todosAlertas)) : ?>
                    <ul class="list-group">
                        <?php foreach ($todosAlertas as $alerta) : ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($alerta['tipo']); ?>: <?php echo htmlspecialchars($alerta['descricao']); ?></strong>
                                    <br>
                                    <?php if ($alerta['data']) : ?>
                                        <small>Data: <?php echo date("d/m/Y", strtotime($alerta['data'])); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <a href="../Processo/documentos.php?processo_id=<?php echo $alerta['processo_id']; ?>&id=<?php echo $alerta['estabelecimento_id']; ?>" class="btn btn-primary btn-sm"><?php echo $alerta['acao']; ?></a>
                                    <?php if ($alerta['tipo'] == 'Processo Designado Pendente') : ?>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="processo_id" value="<?php echo $alerta['processo_id']; ?>">
                                            <button type="submit" name="marcar_resolvido" class="btn btn-success btn-sm">Finalizar</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p>Nenhum alerta disponível.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
