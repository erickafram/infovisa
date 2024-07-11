<?php
session_start();
include '../header.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php"); // Redirecionar para a página de login se não estiver autenticado ou não for administrador
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/Estabelecimento.php';


$estabelecimento = new Estabelecimento($conn);

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $dadosEstabelecimento = $estabelecimento->findById($id);

    if (!$dadosEstabelecimento) {
        echo "Estabelecimento não encontrado!";
        exit();
    }
} else {
    echo "ID do estabelecimento não fornecido!";
    exit();
}

$cnaes_secundarios = json_decode($dadosEstabelecimento['cnaes_secundarios'], true);

function formatCNAE($cnae)
{
    if (strlen($cnae) === 7) {
        return substr($cnae, 0, 4) . '-' . substr($cnae, 4, 1) . '/' . substr($cnae, 5, 2);
    }
    return $cnae;
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
                <h4>Atividades do Estabelecimento</h4>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">CNAE Principal</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Código CNAE Fiscal:</strong></label>
                                <input type="text" class="form-control form-control-sm" value="<?php echo formatCNAE(htmlspecialchars($dadosEstabelecimento['cnae_fiscal'])); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Descrição CNAE Fiscal:</strong></label>
                                <input type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($dadosEstabelecimento['cnae_fiscal_descricao']); ?>" disabled>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">CNAEs Secundários</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cnaes_secundarios as $cnae) : ?>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Código:</strong></label>
                                    <input type="text" class="form-control form-control-sm" value="<?php echo formatCNAE(htmlspecialchars($cnae['codigo'])); ?>" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Descrição:</strong></label>
                                    <input type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($cnae['descricao']); ?>" disabled>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include '../footer.php';
?>