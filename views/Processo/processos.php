<?php
session_start();
ob_start(); // Inicia o buffer de saída

include '../header.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/Processo.php';

$processo = new Processo($conn);

if (isset($_GET['id'])) {
    $estabelecimento_id = $_GET['id'];
} else {
    echo "ID do estabelecimento não fornecido!";
    exit();
}

$mensagemErro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tipo_processo'])) {
        $tipo_processo = $_POST['tipo_processo'];
        $anoAtual = date('Y');

        if ($tipo_processo == 'LICENCIAMENTO' && $processo->checkProcessoExistente($estabelecimento_id, $anoAtual)) {
            $mensagemErro = "Já existe um processo de LICENCIAMENTO para o ano vigente.";
        } else {
            if ($processo->createProcesso($estabelecimento_id, $tipo_processo)) {
                header("Location: processos.php?id=$estabelecimento_id");
                exit();
            } else {
                $mensagemErro = "Erro ao criar processo: " . $conn->error;
            }
        }
    } elseif (isset($_POST['archive_processo_id'])) {
        $archive_processo_id = $_POST['archive_processo_id'];
        if ($processo->archiveProcesso($archive_processo_id)) {
            header("Location: processos.php?id=$estabelecimento_id");
            exit();
        } else {
            $mensagemErro = "Erro ao arquivar processo: " . $conn->error;
        }
    } elseif (isset($_POST['unarchive_processo_id'])) {
        $unarchive_processo_id = $_POST['unarchive_processo_id'];
        if ($processo->unarchiveProcesso($unarchive_processo_id)) {
            header("Location: processos.php?id=$estabelecimento_id");
            exit();
        } else {
            $mensagemErro = "Erro ao desarquivar processo: " . $conn->error;
        }
    }
}

$processos = $processo->getProcessosByEstabelecimento($estabelecimento_id);
?>

<div class="container">
    <div class="row">
        <div class="col-md-3">
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Menu</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="../Estabelecimento/detalhes_estabelecimento.php?id=<?php echo $estabelecimento_id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-info-circle me-2"></i>Detalhes
                    </a>
                    <a href="../Estabelecimento/editar_estabelecimento.php?id=<?php echo $estabelecimento_id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-edit me-2"></i>Editar
                    </a>
                    <a href="../Estabelecimento/atividades.php?id=<?php echo $estabelecimento_id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-tasks me-2"></i>Atividades
                    </a>
                    <a href="../Estabelecimento/responsaveis.php?id=<?php echo $estabelecimento_id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i>Responsáveis
                    </a>
                    <a href="../Estabelecimento/acesso_empresa.php?id=<?php echo $estabelecimento_id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i>Acesso Empresa
                    </a>
                    <a href="../Processo/processos.php?id=<?php echo $estabelecimento_id; ?>" class="list-group-item list-group-item-action active">
                        <i class="fas fa-folder-open me-2"></i>Processos
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Processos do Estabelecimento</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($mensagemErro)) : ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $mensagemErro; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#criarProcessoModal">
                            <i class="fas fa-plus me-2"></i>Criar Novo Processo
                        </button>
                    </div>

                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                        <?php foreach ($processos as $proc) : ?>
                            <div class="col">
                                <div class="card h-100">
                                    <div class="card-body" style="font-size:12px;">
                                        <h6 class="card-title text-center"><?php echo htmlspecialchars($proc['numero_processo']); ?></h6>
                                        <p class="card-text mb-1"><strong>Tipo Processo:</strong> <?php echo htmlspecialchars($proc['tipo_processo']); ?></p>
                                        <p class="card-text"><strong>Data Autuação:</strong> <?php echo htmlspecialchars((new DateTime($proc['data_abertura']))->format('d/m/Y')); ?></p>
                                        <div class="d-grid gap-2">
                                            <a href="documentos.php?processo_id=<?php echo $proc['id']; ?>&id=<?php echo $estabelecimento_id; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-file-alt me-2"></i>Ver Processo
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Criar Processo -->
<div class="modal fade" id="criarProcessoModal" tabindex="-1" aria-labelledby="criarProcessoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="criarProcessoModalLabel">Criar Novo Processo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="processos.php?id=<?php echo $estabelecimento_id; ?>" method="POST">
                    <div class="mb-3">
                        <label for="tipo_processo" class="form-label">Tipo de Processo</label>
                        <select id="tipo_processo" name="tipo_processo" class="form-select" required>
                            <option value="ADMINISTRATIVO">ADMINISTRATIVO</option>
                            <option value="DENÚNCIA">DENÚNCIA</option>
                            <option value="LICENCIAMENTO">LICENCIAMENTO</option>
                            <option value="PROJETO ARQUITETÔNICO">PROJETO ARQUITETÔNICO</option>
                        </select>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Criar Processo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include '../footer.php';
?>