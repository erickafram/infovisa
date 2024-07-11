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
require_once '../../models/Estabelecimento.php';
require_once '../../models/ResponsavelLegal.php';
require_once '../../models/ResponsavelTecnico.php';

$estabelecimento = new Estabelecimento($conn);
$responsavelLegal = new ResponsavelLegal($conn);
$responsavelTecnico = new ResponsavelTecnico($conn);

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $dadosEstabelecimento = $estabelecimento->findById($id);

    if (!$dadosEstabelecimento) {
        echo "Estabelecimento não encontrado!";
        exit();
    }

    $responsaveisLegais = $responsavelLegal->getByEstabelecimento($id);
    $responsaveisTecnicos = $responsavelTecnico->getByEstabelecimento($id);
} else {
    echo "ID do estabelecimento não fornecido!";
    exit();
}

$qsa = json_decode($dadosEstabelecimento['qsa'], true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_legal'])) {
        $cpf = $_POST['cpf'];
        $responsavelExistente = $responsavelLegal->findByCpf($cpf);

        if ($responsavelExistente) {
            $responsavelLegal->create($id, $responsavelExistente['nome'], $responsavelExistente['cpf'], $responsavelExistente['email'], $responsavelExistente['telefone'], $responsavelExistente['documento_identificacao']);
        } else {
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $telefone = $_POST['telefone'];
            $documento_identificacao = $_FILES['documento_identificacao']['name'];
            $target_dir = "../../uploads/";
            $target_file = $target_dir . basename($_FILES["documento_identificacao"]["name"]);
            move_uploaded_file($_FILES["documento_identificacao"]["tmp_name"], $target_file);

            $responsavelLegal->create($id, $nome, $cpf, $email, $telefone, $documento_identificacao);
        }
        header("Location: responsaveis.php?id=$id");
        exit();
    } elseif (isset($_POST['add_tecnico'])) {
        $cpf = $_POST['cpf'];
        $responsavelExistente = $responsavelTecnico->findByCpf($cpf);

        if ($responsavelExistente) {
            $responsavelTecnico->create($id, $responsavelExistente['nome'], $responsavelExistente['cpf'], $responsavelExistente['email'], $responsavelExistente['telefone'], $responsavelExistente['conselho'], $responsavelExistente['numero_registro_conselho'], $responsavelExistente['carteirinha_conselho']);
        } else {
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $telefone = $_POST['telefone'];
            $conselho = $_POST['conselho'];
            $numero_registro_conselho = $_POST['numero_registro_conselho'];
            $carteirinha_conselho = $_FILES['carteirinha_conselho']['name'];
            $target_dir = "../../uploads/";
            $target_file = $target_dir . basename($_FILES["carteirinha_conselho"]["name"]);
            move_uploaded_file($_FILES["carteirinha_conselho"]["tmp_name"], $target_file);

            $responsavelTecnico->create($id, $nome, $cpf, $email, $telefone, $conselho, $numero_registro_conselho, $carteirinha_conselho);
        }
        header("Location: responsaveis.php?id=$id");
        exit();
    } elseif (isset($_POST['edit_legal'])) {
        $responsavel_id = $_POST['responsavel_id'];
        $nome = $_POST['nome'];
        $cpf = $_POST['cpf'];
        $email = $_POST['email'];
        $telefone = $_POST['telefone'];
        $documento_identificacao = $_FILES['documento_identificacao']['name'];
        if ($documento_identificacao) {
            $target_dir = "../../uploads/";
            $target_file = $target_dir . basename($_FILES["documento_identificacao"]["name"]);
            move_uploaded_file($_FILES["documento_identificacao"]["tmp_name"], $target_file);
        } else {
            $documento_identificacao = $_POST['old_documento_identificacao'];
        }

        $responsavelLegal->update($responsavel_id, $nome, $cpf, $email, $telefone, $documento_identificacao);
        header("Location: responsaveis.php?id=$id");
        exit();
    } elseif (isset($_POST['edit_tecnico'])) {
        $responsavel_id = $_POST['responsavel_id'];
        $nome = $_POST['nome'];
        $cpf = $_POST['cpf'];
        $email = $_POST['email'];
        $telefone = $_POST['telefone'];
        $conselho = $_POST['conselho'];
        $numero_registro_conselho = $_POST['numero_registro_conselho'];
        $carteirinha_conselho = $_FILES['carteirinha_conselho']['name'];
        if ($carteirinha_conselho) {
            $target_dir = "../../uploads/";
            $target_file = $target_dir . basename($_FILES["carteirinha_conselho"]["name"]);
            move_uploaded_file($_FILES["carteirinha_conselho"]["tmp_name"], $target_file);
        } else {
            $carteirinha_conselho = $_POST['old_carteirinha_conselho'];
        }

        $responsavelTecnico->update($responsavel_id, $nome, $cpf, $email, $telefone, $conselho, $numero_registro_conselho, $carteirinha_conselho);
        header("Location: responsaveis.php?id=$id");
        exit();
    }
}

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'delete_legal') {
        $responsavel_id = $_GET['responsavel_id'];
        $responsavelLegal->delete($responsavel_id);
        header("Location: responsaveis.php?id=$id");
        exit();
    } elseif ($_GET['action'] == 'delete_tecnico') {
        $responsavel_id = $_GET['responsavel_id'];
        $responsavelTecnico->delete($responsavel_id);
        header("Location: responsaveis.php?id=$id");
        exit();
    }
}
?>

<div class="container">
    <div class="row">
        <div class="col-md-3">
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Menu</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="detalhes_estabelecimento.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-info-circle me-2"></i>Detalhes
                    </a>
                    <a href="editar_estabelecimento.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-edit me-2"></i>Editar
                    </a>
                    <a href="atividades.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-tasks me-2"></i>Atividades
                    </a>
                    <a href="responsaveis.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i>Responsáveis
                    </a>
                    <a href="acesso_empresa.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-building me-2"></i>Acesso Empresa
                    </a>
                    <a href="../Processo/processos.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-folder-open me-2"></i>Processos
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="container mt-5">
                <h4>Responsáveis pelo Estabelecimento</h4>
                <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#modalEscolherResponsavel">Adicionar Responsável</button>

                <!-- Lista de Responsáveis Legais -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Responsáveis Legais</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($responsaveisLegais as $responsavel) : ?>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Nome:</strong></label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($responsavel['nome']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>CPF:</strong></label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($responsavel['cpf']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Email:</strong></label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($responsavel['email']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Telefone:</strong></label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($responsavel['telefone']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Documento de Identificação:</strong></label>
                                    <a href="../../uploads/<?php echo htmlspecialchars($responsavel['documento_identificacao']); ?>" target="_blank"><?php echo htmlspecialchars($responsavel['documento_identificacao']); ?></a>
                                </div>
                                <div class="col-md-12">
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditLegal<?php echo $responsavel['id']; ?>">Editar</button>
                                    <a href="responsaveis.php?id=<?php echo $id; ?>&action=delete_legal&responsavel_id=<?php echo $responsavel['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este responsável legal?')">Excluir</a>
                                </div>
                            </div>

                            <!-- Modal para editar Responsável Legal -->
                            <div class="modal fade" id="modalEditLegal<?php echo $responsavel['id']; ?>" tabindex="-1" aria-labelledby="modalEditLegalLabel<?php echo $responsavel['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalEditLegalLabel<?php echo $responsavel['id']; ?>">Editar Responsável Legal</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="responsaveis.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                                                <div class="mb-3">
                                                    <label for="nome" class="form-label">Nome</label>
                                                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($responsavel['nome']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="cpf" class="form-label">CPF</label>
                                                    <input type="text" class="form-control" id="cpf" name="cpf" value="<?php echo htmlspecialchars($responsavel['cpf']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($responsavel['email']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="telefone" class="form-label">Telefone</label>
                                                    <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo htmlspecialchars($responsavel['telefone']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="documento_identificacao" class="form-label">Documento de Identificação</label>
                                                    <input type="file" class="form-control" id="documento_identificacao" name="documento_identificacao">
                                                    <input type="hidden" name="old_documento_identificacao" value="<?php echo htmlspecialchars($responsavel['documento_identificacao']); ?>">
                                                </div>
                                                <input type="hidden" name="responsavel_id" value="<?php echo $responsavel['id']; ?>">
                                                <input type="hidden" name="edit_legal" value="1">
                                                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Lista de Responsáveis Técnicos -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Responsáveis Técnicos</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($responsaveisTecnicos as $responsavel) : ?>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Nome:</strong></label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($responsavel['nome']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>CPF:</strong></label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($responsavel['cpf']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Email:</strong></label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($responsavel['email']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Telefone:</strong></label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($responsavel['telefone']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Conselho:</strong></label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($responsavel['conselho']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Número do Registro do Conselho:</strong></label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($responsavel['numero_registro_conselho']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Carteirinha do Conselho:</strong></label>
                                    <a href="../../uploads/<?php echo htmlspecialchars($responsavel['carteirinha_conselho']); ?>" target="_blank"><?php echo htmlspecialchars($responsavel['carteirinha_conselho']); ?></a>
                                </div>
                                <div class="col-md-12">
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditTecnico<?php echo $responsavel['id']; ?>">Editar</button>
                                    <a href="responsaveis.php?id=<?php echo $id; ?>&action=delete_tecnico&responsavel_id=<?php echo $responsavel['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este responsável técnico?')">Excluir</a>
                                </div>
                            </div>

                            <!-- Modal para editar Responsável Técnico -->
                            <div class="modal fade" id="modalEditTecnico<?php echo $responsavel['id']; ?>" tabindex="-1" aria-labelledby="modalEditTecnicoLabel<?php echo $responsavel['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalEditTecnicoLabel<?php echo $responsavel['id']; ?>">Editar Responsável Técnico</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="responsaveis.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                                                <div class="mb-3">
                                                    <label for="nome" class="form-label">Nome</label>
                                                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($responsavel['nome']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="cpf" class="form-label">CPF</label>
                                                    <input type="text" class="form-control" id="cpf" name="cpf" value="<?php echo htmlspecialchars($responsavel['cpf']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($responsavel['email']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="telefone" class="form-label">Telefone</label>
                                                    <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo htmlspecialchars($responsavel['telefone']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="conselho" class="form-label">Conselho</label>
                                                    <select class="form-control" id="conselho" name="conselho" required>
                                                        <option value="CRM" <?php echo ($responsavel['conselho'] == 'CRM') ? 'selected' : ''; ?>>CRM</option>
                                                        <option value="CRF" <?php echo ($responsavel['conselho'] == 'CRF') ? 'selected' : ''; ?>>CRF</option>
                                                        <option value="CRO" <?php echo ($responsavel['conselho'] == 'CRO') ? 'selected' : ''; ?>>CRO</option>
                                                        <option value="CREFITO" <?php echo ($responsavel['conselho'] == 'CREFITO') ? 'selected' : ''; ?>>CREFITO</option>
                                                        <option value="COREN" <?php echo ($responsavel['conselho'] == 'COREN') ? 'selected' : ''; ?>>COREN</option>
                                                        <option value="CRP" <?php echo ($responsavel['conselho'] == 'CRP') ? 'selected' : ''; ?>>CRP</option>
                                                        <option value="CRMV" <?php echo ($responsavel['conselho'] == 'CRMV') ? 'selected' : ''; ?>>CRMV</option>
                                                        <option value="CREFONO" <?php echo ($responsavel['conselho'] == 'CREFONO') ? 'selected' : ''; ?>>CREFONO</option>
                                                        <option value="CRN" <?php echo ($responsavel['conselho'] == 'CRN') ? 'selected' : ''; ?>>CRN</option>
                                                        <option value="CREF" <?php echo ($responsavel['conselho'] == 'CREF') ? 'selected' : ''; ?>>CREF</option>
                                                        <option value="CRAS" <?php echo ($responsavel['conselho'] == 'CRAS') ? 'selected' : ''; ?>>CRAS</option>
                                                        <option value="CRT" <?php echo ($responsavel['conselho'] == 'CRT') ? 'selected' : ''; ?>>CRT</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="numero_registro_conselho" class="form-label">Número do Registro do Conselho</label>
                                                    <input type="text" class="form-control" id="numero_registro_conselho" name="numero_registro_conselho" value="<?php echo htmlspecialchars($responsavel['numero_registro_conselho']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="carteirinha_conselho" class="form-label">Carteirinha do Conselho</label>
                                                    <input type="file" class="form-control" id="carteirinha_conselho" name="carteirinha_conselho">
                                                    <input type="hidden" name="old_carteirinha_conselho" value="<?php echo htmlspecialchars($responsavel['carteirinha_conselho']); ?>">
                                                </div>
                                                <input type="hidden" name="responsavel_id" value="<?php echo $responsavel['id']; ?>">
                                                <input type="hidden" name="edit_tecnico" value="1">
                                                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="container mt-5">
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Sociedade</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($qsa as $socio) : ?>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Nome do Sócio:</strong></label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($socio['nome_socio']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Qualificação:</strong></label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($socio['qualificacao_socio']); ?>" readonly>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para escolher tipo de Responsável -->
<div class="modal fade" id="modalEscolherResponsavel" tabindex="-1" aria-labelledby="modalEscolherResponsavelLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEscolherResponsavelLabel">Adicionar Responsável</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <button class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#modalAddLegal" data-bs-dismiss="modal">Responsável Legal</button>
                <button class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#modalAddTecnico" data-bs-dismiss="modal">Responsável Técnico</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para adicionar Responsável Legal -->
<div class="modal fade" id="modalAddLegal" tabindex="-1" aria-labelledby="modalAddLegalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddLegalLabel">Adicionar Responsável Legal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAddLegal" action="responsaveis.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="cpf" class="form-label">CPF</label>
                        <input type="text" class="form-control" id="cpfLegal" name="cpf" required maxlength="11">
                        <small class="form-text text-muted">Digite apenas números, sem ponto ou hífen.</small>
                        <button type="button" class="btn btn-secondary mt-2" id="buscarCpfLegal">Buscar CPF</button>
                    </div>
                    <div id="alertLegal" class="alert alert-info" style="display:none;">
                        Responsável Legal já cadastrado e vinculado ao estabelecimento.
                    </div>
                    <div id="legalFields" style="display: none;">
                        <div class="mb-3">
                            <label for="nomeLegal" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nomeLegal" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="emailLegal" class="form-label">Email</label>
                            <input type="email" class="form-control" id="emailLegal" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefoneLegal" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefoneLegal" name="telefone" required>
                        </div>
                        <div class="mb-3">
                            <label for="documento_identificacaoLegal" class="form-label">Documento de Identificação</label>
                            <input type="file" class="form-control" id="documento_identificacaoLegal" name="documento_identificacao" required>
                        </div>
                    </div>
                    <input type="hidden" name="add_legal" value="1">
                    <button type="submit" class="btn btn-primary" id="btnAddLegal" style="display: none;">Adicionar Responsável</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal para adicionar Responsável Técnico -->
<div class="modal fade" id="modalAddTecnico" tabindex="-1" aria-labelledby="modalAddTecnicoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddTecnicoLabel">Adicionar Responsável Técnico</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAddTecnico" action="responsaveis.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="cpfTecnico" class="form-label">CPF</label>
                        <input type="text" class="form-control" id="cpfTecnico" name="cpf" required maxlength="11">
                        <small class="form-text text-muted">Digite apenas números, sem ponto ou hífen.</small>
                        <button type="button" class="btn btn-secondary mt-2" id="buscarCpfTecnico">Buscar CPF</button>
                    </div>
                    <div id="alertTecnico" class="alert alert-info" style="display:none;">
                        Responsável Técnico já cadastrado e vinculado ao estabelecimento.
                    </div>
                    <div id="tecnicoFields" style="display: none;">
                        <div class="mb-3">
                            <label for="nomeTecnico" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nomeTecnico" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="emailTecnico" class="form-label">Email</label>
                            <input type="email" class="form-control" id="emailTecnico" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefoneTecnico" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefoneTecnico" name="telefone" required>
                        </div>
                        <div class="mb-3">
                            <label for="conselho" class="form-label">Conselho</label>
                            <select class="form-control" id="conselho" name="conselho" required>
                                <option value="CRM">CRM</option>
                                <option value="CRF">CRF</option>
                                <option value="CRO">CRO</option>
                                <option value="CREFITO">CREFITO</option>
                                <option value="COREN">COREN</option>
                                <option value="CRP">CRP</option>
                                <option value="CRMV">CRMV</option>
                                <option value="CREFONO">CREFONO</option>
                                <option value="CRN">CRN</option>
                                <option value="CREF">CREF</option>
                                <option value="CRAS">CRAS</option>
                                <option value="CRT">CRT</option>
                                <!-- Adicione outros conselhos conforme necessário -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="numero_registro_conselho" class="form-label">Número do Registro do Conselho</label>
                            <input type="text" class="form-control" id="numero_registro_conselho" name="numero_registro_conselho" required>
                        </div>
                        <div class="mb-3">
                            <label for="carteirinha_conselho" class="form-label">Carteirinha do Conselho</label>
                            <input type="file" class="form-control" id="carteirinha_conselho" name="carteirinha_conselho" required>
                        </div>
                    </div>
                    <input type="hidden" name="add_tecnico" value="1">
                    <button type="submit" class="btn btn-primary" id="btnAddTecnico" style="display: none;">Adcionar Responsável</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    document.getElementById('cpfLegal').addEventListener('input', function(event) {
        var value = event.target.value;
        event.target.value = value.replace(/[^0-9]/g, ''); // Remove caracteres não numéricos
    });

    document.getElementById('cpfTecnico').addEventListener('input', function(event) {
        var value = event.target.value;
        event.target.value = value.replace(/[^0-9]/g, ''); // Remove caracteres não numéricos
    });

    // Função para buscar CPF e exibir campos adicionais para Responsável Legal
    document.getElementById('buscarCpfLegal').addEventListener('click', function() {
        var cpf = document.getElementById('cpfLegal').value;
        fetch('verificar_cpf.php?cpf=' + cpf + '&tipo=legal')
            .then(response => response.json())
            .then(data => {
                if (data.existe) {
                    document.getElementById('nomeLegal').value = data.nome;
                    document.getElementById('emailLegal').value = data.email;
                    document.getElementById('telefoneLegal').value = data.telefone;
                    document.getElementById('documento_identificacaoLegal').required = false;
                    document.getElementById('legalFields').style.display = 'none';
                    document.getElementById('alertLegal').style.display = 'block';
                } else {
                    document.getElementById('legalFields').style.display = 'block';
                    document.getElementById('documento_identificacaoLegal').required = true;
                    document.getElementById('alertLegal').style.display = 'none';
                }
                toggleRequiredFields('legalFields', !data.existe);
                document.getElementById('btnAddLegal').style.display = 'block';
            });
    });

    // Função para buscar CPF e exibir campos adicionais para Responsável Técnico
    document.getElementById('buscarCpfTecnico').addEventListener('click', function() {
        var cpf = document.getElementById('cpfTecnico').value;
        fetch('verificar_cpf.php?cpf=' + cpf + '&tipo=tecnico')
            .then(response => response.json())
            .then(data => {
                if (data.existe) {
                    document.getElementById('nomeTecnico').value = data.nome;
                    document.getElementById('emailTecnico').value = data.email;
                    document.getElementById('telefoneTecnico').value = data.telefone;
                    document.getElementById('conselho').value = data.conselho;
                    document.getElementById('numero_registro_conselho').value = data.numero_registro_conselho;
                    document.getElementById('carteirinha_conselho').required = false;
                    document.getElementById('tecnicoFields').style.display = 'none';
                    document.getElementById('alertTecnico').style.display = 'block';
                } else {
                    document.getElementById('tecnicoFields').style.display = 'block';
                    document.getElementById('carteirinha_conselho').required = true;
                    document.getElementById('alertTecnico').style.display = 'none';
                }
                toggleRequiredFields('tecnicoFields', !data.existe);
                document.getElementById('btnAddTecnico').style.display = 'block';
            });
    });

    // Função para alternar os atributos "required" dos campos
    function toggleRequiredFields(containerId, isRequired) {
        var container = document.getElementById(containerId);
        var fields = container.querySelectorAll('input, select');
        fields.forEach(function(field) {
            if (isRequired) {
                field.setAttribute('required', 'required');
            } else {
                field.removeAttribute('required');
            }
        });
    }
</script>


<?php
$conn->close();
include '../footer.php';
?>