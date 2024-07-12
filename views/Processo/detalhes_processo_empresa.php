<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificação de autenticação
if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/Processo.php';
require_once '../../models/Documento.php';
require_once '../../models/Arquivo.php';
require_once '../../models/LogVisualizacao.php';

$processoModel = new Processo($conn);
$documentoModel = new Documento($conn);
$arquivoModel = new Arquivo($conn);

if (isset($_GET['id'])) {
    $processoId = $_GET['id'];
    $dadosProcesso = $processoModel->findById($processoId);

    if (!$dadosProcesso) {
        echo "Processo não encontrado!";
        exit();
    }

    // Verificar se o usuário está vinculado ao estabelecimento e se o processo não é de denúncia
    $userId = $_SESSION['user']['id'];
    $estabelecimentos = $processoModel->getEstabelecimentosByUsuario($userId);
    $estabelecimentoIds = array_column($estabelecimentos, 'estabelecimento_id');

    if (!in_array($dadosProcesso['estabelecimento_id'], $estabelecimentoIds) || $dadosProcesso['tipo_processo'] == 'DENÚNCIA') {
        echo "Acesso negado!";
        exit();
    }
} else {
    echo "ID do processo não fornecido!";
    exit();
}

function generateUniqueFileName($dir, $filename)
{
    $path_info = pathinfo($filename);
    $basename = $path_info['filename'];
    $extension = isset($path_info['extension']) ? '.' . $path_info['extension'] : '';
    $new_filename = $filename;
    $counter = 1;

    while (file_exists($dir . $new_filename)) {
        $new_filename = $basename . '(' . $counter . ')' . $extension;
        $counter++;
    }

    return $new_filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    if ($dadosProcesso['status'] !== 'ARQUIVADO') {
        $total_files = count($_FILES['files']['name']);
        $upload_dir = "../../uploads/";

        for ($i = 0; $i < $total_files; $i++) {
            $file_name = basename($_FILES["files"]["name"][$i]);
            $file_type = mime_content_type($_FILES["files"]["tmp_name"][$i]);
            $file_size = $_FILES["files"]["size"][$i];
            $target_file = $upload_dir . generateUniqueFileName($upload_dir, $file_name);

            // Verifica se o arquivo é um PDF
            if ($file_type !== 'application/pdf') {
                echo "Erro: Apenas arquivos PDF são permitidos. Arquivo inválido: " . $file_name;
                continue; // Pula para o próximo arquivo
            }

            // Verifica se o tamanho do arquivo é menor ou igual a 5MB
            if ($file_size > 5 * 1024 * 1024) {
                echo "Erro: O arquivo " . $file_name . " excede o tamanho máximo permitido de 5MB.";
                continue; // Pula para o próximo arquivo
            }

            if (move_uploaded_file($_FILES["files"]["tmp_name"][$i], $target_file)) {
                $caminho_arquivo = 'uploads/' . basename($target_file);
                $documentoModel->createDocumento($processoId, basename($target_file), $caminho_arquivo);
                touch($target_file); // Atualiza a data de modificação do arquivo para agora
            } else {
                echo "Erro ao fazer upload do arquivo: " . $file_name;
            }
        }

        header("Location: detalhes_processo_empresa.php?id=$processoId");
        exit();
    } else {
        echo "Erro: Não é permitido fazer upload de arquivo para processos arquivados.";
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novo_arquivo_negado'])) {
    if ($dadosProcesso['status'] !== 'ARQUIVADO') {
        $documento_id = $_POST['documento_id'];
        if (isset($_FILES['novo_arquivo']) && $_FILES['novo_arquivo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = "../../uploads/";
            $file_name = basename($_FILES["novo_arquivo"]["name"]);
            $file_type = mime_content_type($_FILES["novo_arquivo"]["tmp_name"]);
            $file_size = $_FILES["novo_arquivo"]["size"];
            $target_file = $upload_dir . $file_name;

            // Verifica se o arquivo é um PDF
            if ($file_type !== 'application/pdf') {
                echo "Erro: Apenas arquivos PDF são permitidos. Arquivo inválido: " . $file_name;
            } elseif ($file_size > 5 * 1024 * 1024) {
                echo "Erro: O arquivo " . $file_name . " excede o tamanho máximo permitido de 5MB.";
            } elseif (move_uploaded_file($_FILES["novo_arquivo"]["tmp_name"], $target_file)) {
                $caminho_arquivo = 'uploads/' . $file_name;
                $documentoModel->updateDocumentoNegado($documento_id, $file_name, $caminho_arquivo);
                header("Location: detalhes_processo_empresa.php?id=$processoId");
                exit();
            } else {
                echo "Erro ao fazer upload do arquivo: " . $file_name;
            }
        } else {
            echo "Erro ao fazer upload do arquivo.";
        }
    } else {
        echo "Erro: Não é permitido fazer upload de Arquivo para processos arquivados.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_documento'])) {
    $documento_id = $_POST['documento_id'];
    $documento = $documentoModel->findById($documento_id);

    if ($documento && $documento['status'] == 'pendente') {
        $caminhoCompleto = "../../" . $documento['caminho_arquivo']; // Ajuste o caminho conforme necessário
        if ($documentoModel->deleteDocumento($documento_id)) {
            if (file_exists($caminhoCompleto)) {
                unlink($caminhoCompleto);
            }
            header("Location: detalhes_processo_empresa.php?id=$processoId");
            exit();
        } else {
            echo "Erro ao excluir o documento.";
        }
    } else {
        echo "Documento não encontrado ou não está pendente.";
    }
}

$documentos = $documentoModel->getDocumentosByProcesso($processoId);
$arquivos = $arquivoModel->getArquivosComAssinaturasCompletas($processoId); // Usar o novo método aqui
$alertas = $processoModel->getAlertasByProcesso($processoId);

$itens = array_merge(
    array_map(function ($doc) {
        $doc['tipo'] = 'documento';
        return $doc;
    }, $documentos),
    array_map(function ($arq) {
        $arq['tipo'] = 'arquivo';
        return $arq;
    }, $arquivos)
);

usort($itens, function ($a, $b) {
    return strtotime($b['data_upload']) - strtotime($a['data_upload']);
});

include '../../includes/header_empresa.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Processo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .card-title {
            font-weight: bold;
            color: #333;
            font-size: 17px;
        }

        .alerta-destaque,
        .parado-destaque {
            background-color: #ffdddd;
            border-left: 6px solid #f44336;
            padding: 10px;
            margin-bottom: 15px;
        }

        .info-list {
            display: flex;
            flex-wrap: wrap;
        }

        .info-item {
            flex: 1 1 50%;
            padding: 5px;
        }

        .info-item strong {
            display: inline-block;
            width: 150px;
        }

        .bg-danger {
            font-size: 9px !important;
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <?php
        $temAlertas = false;
        foreach ($alertas as $alerta) {
            if ($alerta['status'] !== 'finalizado') {
                $temAlertas = true;
                break;
            }
        }

        if ($temAlertas || ($dadosProcesso['status'] === 'PARADO' && !empty($dadosProcesso['motivo_parado']))) :
        ?>
            <!-- Container de Alertas e Processos Parados -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6><i class="fas fa-exclamation-triangle"></i> ATENÇÃO</h6>
                </div>
                <div class="card-body">
                    <!-- Exibição de Alertas -->
                    <?php if ($temAlertas) : ?>
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-bell"></i> Alertas</h6>
                            <?php foreach ($alertas as $alerta) : ?>
                                <?php if ($alerta['status'] !== 'finalizado') : ?>
                                    <div class="alerta-destaque">
                                        <strong>Descrição:</strong> <?php echo htmlspecialchars($alerta['descricao']); ?><br>
                                        <strong>Prazo:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($alerta['prazo']))); ?><br>
                                        <strong>Status:</strong> <?php echo htmlspecialchars($alerta['status']); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Exibição de Motivo Parado -->
                    <?php if ($dadosProcesso['status'] === 'PARADO' && !empty($dadosProcesso['motivo_parado'])) : ?>
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-stop"></i> Processo Parado</h6>
                            <div class="parado-destaque">
                                <strong>Motivo do Processo Parado:</strong> <?php echo htmlspecialchars($dadosProcesso['motivo_parado']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>


        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Informações do Processo</h5>
                <ul class="list-group info-list">
                    <li class="list-group-item info-item"><strong>Número do Processo:</strong> <?php echo htmlspecialchars($dadosProcesso['numero_processo'] ?? 'N/A'); ?></li>
                    <li class="list-group-item info-item"><strong>Nome da Empresa:</strong> <?php echo htmlspecialchars($dadosProcesso['nome_fantasia'] ?? 'N/A'); ?></li>
                    <li class="list-group-item info-item"><strong>CNPJ:</strong> <?php echo htmlspecialchars($dadosProcesso['cnpj'] ?? 'N/A'); ?></li>
                    <li class="list-group-item info-item"><strong>Telefone:</strong> <?php echo htmlspecialchars($dadosProcesso['ddd_telefone_1'] ?? 'N/A'); ?></li>
                    <li class="list-group-item info-item"><strong>Data de Abertura:</strong> <?php echo htmlspecialchars(isset($dadosProcesso['data_abertura']) ? date('d/m/Y', strtotime($dadosProcesso['data_abertura'])) : 'N/A'); ?></li>
                    <li class="list-group-item info-item"><strong>Status:</strong> <?php echo htmlspecialchars($dadosProcesso['status'] ?? 'N/A'); ?></li>
                </ul>
            </div>
        </div>

        <div class="row">
            <!-- Coluna direita: Upload de Arquivos -->
            <div class="col-md-4">
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Upload de Arquivos</h5>
                        <?php if ($dadosProcesso['status'] === 'ARQUIVADO') : ?>
                            <p>O upload de Arquivo não é permitido para processos arquivados.</p>
                        <?php else : ?>
                            <form action="detalhes_processo_empresa.php?id=<?php echo $processoId; ?>" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="files" class="form-label">Escolha os arquivos (somente PDF)</label>
                                    <input class="form-control" type="file" id="files" name="files[]" accept="application/pdf" multiple required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Enviar</button>
                            </form>
                        <?php endif; ?>
                        <hr>
                        <a href="gerar_pdf_processo.php?id=<?php echo $processoId; ?>" class="btn btn-secondary btn-sm" target="_blank">Protocolo do Processo</a>
                    </div>
                </div>
            </div>

            <!-- Coluna esquerda: Documentos e Arquivos -->
            <div class="col-md-8" style="padding-bottom:20px;">
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Documentos e Arquivos do Processo</h5>
                        <?php if (!empty($itens)) : ?>
                            <ul class="list-group">
                                <?php foreach ($itens as $item) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <a href="../../<?php echo htmlspecialchars($item['caminho_arquivo']); ?>" target="_blank" onclick="registrarVisualizacao(<?php echo htmlspecialchars($item['id']); ?>)">
                                                <?php echo htmlspecialchars($item['nome_arquivo'] ?? $item['tipo_documento'] ?? 'Documento'); ?>
                                            </a>
                                            <?php if ($item['tipo'] == 'documento') : ?>
                                                <span class="badge bg-<?php echo $item['status'] == 'aprovado' ? 'success' : ($item['status'] == 'negado' ? 'danger' : 'warning'); ?> text-light">
                                                    <?php echo ucfirst($item['status']); ?>
                                                </span>
                                                <?php if ($item['status'] == 'pendente') : ?>
                                                    <form action="detalhes_processo_empresa.php?id=<?php echo $processoId; ?>" method="POST" style="display:inline;">
                                                        <input type="hidden" name="documento_id" value="<?php echo $item['id']; ?>">
                                                    </form>
                                                <?php elseif ($item['status'] == 'negado') : ?>
                                                    <br>
                                                    <p style="margin-bottom: 0px;"><small style="color: red; font-weight:bolder;">Motivo: <?php echo htmlspecialchars($item['motivo_negacao']); ?></small></p>
                                                    <button class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#uploadModal<?php echo $item['id']; ?>">
                                                        <i class="fas fa-upload"></i> Novo Arquivo
                                                    </button>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <span class="badge bg-primary text-light">Documento</span>
                                            <?php endif; ?>
                                            <br>
                                            <small style="color: #b1b1b1; font-size:10px;">Adicionado em: <?php echo date("d/m/Y", strtotime($item['data_upload'])); ?></small>
                                        </div>
                                        <div>
                                            <?php if ($item['status'] == 'pendente') : ?>
                                                <form action="detalhes_processo_empresa.php?id=<?php echo $processoId; ?>" method="POST" style="display:inline;">
                                                    <input type="hidden" name="documento_id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" name="excluir_documento" class="btn btn-sm btn-danger mt-2" onclick="return confirm('Tem certeza que deseja excluir este documento?')">
                                                        <i class="fas fa-trash-alt"></i> Excluir
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </li>



                                    <!-- Modal para novo upload -->
                                    <?php if ($item['tipo'] == 'documento' && $item['status'] == 'negado') : ?>
                                        <div class="modal fade" id="uploadModal<?php echo $item['id']; ?>" tabindex="-1" aria-labelledby="uploadModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="uploadModalLabel<?php echo $item['id']; ?>">Novo Upload de Arquivo Negado</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="detalhes_processo_empresa.php?id=<?php echo $processoId; ?>" method="POST" enctype="multipart/form-data">
                                                            <div class="form-group">
                                                                <label for="novo_arquivo">Escolha o novo arquivo (somente PDF)</label>
                                                                <input class="form-control" type="file" id="novo_arquivo" name="novo_arquivo" accept="application/pdf" required>
                                                            </div>
                                                            <input type="hidden" name="documento_id" value="<?php echo $item['id']; ?>">
                                                            <button type="submit" class="btn btn-primary btn-sm" name="novo_arquivo_negado">Enviar</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>

                            </ul>
                        <?php else : ?>
                            <p>Nenhum documento ou arquivo encontrado para este processo.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function registrarVisualizacao(arquivoId) {
                fetch('registrar_visualizacao.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        arquivo_id: arquivoId,
                        usuario_id: <?php echo $_SESSION['user']['id']; ?>
                    })
                }).then(response => response.json()).then(data => {
                    console.log(data.message);
                }).catch(error => {
                    console.error('Erro:', error);
                });
            }
        </script>

</body>

</html>