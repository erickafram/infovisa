<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../../includes/header_empresa.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/Processo.php';
require_once '../../models/Estabelecimento.php';
require_once '../../models/Arquivo.php';

$processoModel = new Processo($conn);
$estabelecimentoModel = new Estabelecimento($conn);
$arquivoModel = new Arquivo($conn);

$userId = $_SESSION['user']['id'];

// Obter todos os alertas, processos parados, documentos negados e arquivos não visualizados das empresas vinculadas ao usuário
$alertas = $processoModel->getAlertasByUsuario($userId);
$processosParados = $processoModel->getProcessosParadosByUsuario($userId);
$documentosNegados = $estabelecimentoModel->getDocumentosNegadosByUsuario($userId);
$arquivosNaoVisualizados = $arquivoModel->getArquivosNaoVisualizados($userId);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas das Empresas</title>
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }

        .card-title {
            font-weight: bold;
            color: #333;
            font-size: 1rem;
        }

        .alerta-item {
            background-color: #ffdddd;
            border-left: 6px solid #f44336;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <h5>Alertas das Empresas</h5>
        <?php if (!empty($alertas) || !empty($processosParados) || !empty($documentosNegados) || !empty($arquivosNaoVisualizados)) : ?>
            <div class="card mt-4">
                <div class="card-body">
                    <?php if (!empty($alertas)) : ?>
                        <h5 class="card-title">Alertas</h5>
                        <?php foreach ($alertas as $alerta) : ?>
                            <?php if ($alerta['status'] != 'FINALIZADO') : ?>
                                <div class="alerta-item">
                                    <strong>Empresa:</strong> <?php echo htmlspecialchars($alerta['empresa_nome']); ?><br>
                                    <strong>Descrição:</strong> <?php echo htmlspecialchars($alerta['descricao']); ?><br>
                                    <strong>Prazo:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($alerta['prazo']))); ?><br>
                                    <strong>Status:</strong> <?php echo htmlspecialchars($alerta['status']); ?><br>
                                    <strong>Processo:</strong> <a href="../Processo/detalhes_processo_empresa.php?id=<?php echo htmlspecialchars($alerta['processo_id']); ?>"><?php echo htmlspecialchars($alerta['numero_processo']); ?></a> - <?php echo htmlspecialchars($alerta['tipo_processo']); ?>
                                    <?php if ($alerta['status'] == 'PARADO') : ?>
                                        <br><strong>Motivo:</strong> <?php echo htmlspecialchars($alerta['motivo_parado']); ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($processosParados)) : ?>
                        <h5 class="card-title mt-4">Processos Parados</h5>
                        <?php foreach ($processosParados as $processo) : ?>
                            <div class="alerta-item">
                                <strong>Empresa:</strong> <?php echo htmlspecialchars(isset($processo['empresa_nome']) ? $processo['empresa_nome'] : 'N/A'); ?><br>
                                <strong>Processo:</strong> <a href="../Processo/detalhes_processo_empresa.php?id=<?php echo htmlspecialchars($processo['id']); ?>"><?php echo htmlspecialchars($processo['numero_processo']); ?></a> - <?php echo htmlspecialchars($processo['tipo_processo']); ?><br>
                                <strong>Status:</strong> <?php echo htmlspecialchars($processo['status']); ?><br>
                                <strong>Motivo:</strong> <?php echo htmlspecialchars(isset($processo['motivo_parado']) ? $processo['motivo_parado'] : 'N/A'); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($arquivosNaoVisualizados)) : ?>
                        <h5 class="card-title mt-4">Documentos Emitidos pela Vigilância Sanitária</h5>
                        <?php foreach ($arquivosNaoVisualizados as $arquivo) : ?>
                            <div class="alerta-item">
                                <strong>Empresa:</strong> <?php echo htmlspecialchars($arquivo['nome_fantasia']); ?><br>
                                <strong>Documento:</strong> <?php echo htmlspecialchars($arquivo['nome_arquivo']); ?><br>
                                <strong>Processo:</strong> <a href="../Processo/detalhes_processo_empresa.php?id=<?php echo htmlspecialchars($arquivo['processo_id']); ?>"><?php echo htmlspecialchars($arquivo['numero_processo']); ?></a><br>
                                <a href="../../<?php echo htmlspecialchars($arquivo['caminho_arquivo']); ?>" target="_blank" class="btn btn-primary btn-sm" style="margin-top:5px;" onclick="registrarVisualizacao(<?php echo $arquivo['id']; ?>)">Ver Documento</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($documentosNegados)) : ?>
                        <h5 class="card-title mt-4">Documentos Negados</h5>
                        <?php foreach ($documentosNegados as $documento) : ?>
                            <div class="alerta-item">
                                <strong>Empresa:</strong> <?php echo htmlspecialchars($documento['nome_fantasia']); ?><br>
                                <strong>Documento:</strong> <?php echo htmlspecialchars($documento['nome_arquivo']); ?><br>
                                <strong>Motivo da Negação:</strong> <?php echo htmlspecialchars($documento['motivo_negacao']); ?><br>
                                <strong>Data de Upload:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($documento['data_upload']))); ?><br>
                                <strong>Processo:</strong> <a href="../Processo/detalhes_processo_empresa.php?id=<?php echo htmlspecialchars($documento['processo_id']); ?>"><?php echo htmlspecialchars($documento['numero_processo']); ?></a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php else : ?>
            <p>Nenhum Alerta encontrado.</p>
        <?php endif; ?>
    </div>

    <script>
        function registrarVisualizacao(arquivoId) {
            $.post("../Company/registrar_visualizacao.php", {
                arquivo_id: arquivoId
            }, function(data) {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert('Erro ao registrar visualização.');
                }
            }, 'json');
        }
    </script>
</body>

</html>