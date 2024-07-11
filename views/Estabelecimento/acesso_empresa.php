<?php
session_start();

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/UsuarioExterno.php';
require_once '../../models/Estabelecimento.php';

$usuarioExterno = new UsuarioExterno($conn);
$estabelecimento = new Estabelecimento($conn);

if (isset($_GET['id'])) {
    $estabelecimentoId = $_GET['id'];
    $dadosEstabelecimento = $estabelecimento->findById($estabelecimentoId);

    if (!$dadosEstabelecimento) {
        echo "Estabelecimento não encontrado!";
        exit();
    }

    $usuarios = $usuarioExterno->getUsuariosByEstabelecimento($estabelecimentoId);
}

$usuariosDisponiveis = [];
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $usuariosDisponiveis = $usuarioExterno->searchUsuarios($searchTerm);
} else {
    $usuariosDisponiveis = $usuarioExterno->getAllUsuarios();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioId = $_POST['usuario_id'];
    $tipoVinculo = $_POST['tipo_vinculo'];

    if ($usuarioExterno->vincularUsuarioEstabelecimento($usuarioId, $estabelecimentoId, $tipoVinculo)) {
        header("Location: acesso_empresa.php?id=$estabelecimentoId&success=1");
    } else {
        header("Location: acesso_empresa.php?id=$estabelecimentoId&error=O usuário já está vinculado a este estabelecimento.");
    }
    exit();
}

if (isset($_GET['delete']) && isset($_GET['usuario_id'])) {
    $usuarioId = $_GET['usuario_id'];
    if ($usuarioExterno->desvincularUsuarioEstabelecimento($usuarioId, $estabelecimentoId)) {
        header("Location: acesso_empresa.php?id=$estabelecimentoId&success=2");
    } else {
        header("Location: acesso_empresa.php?id=$estabelecimentoId&error=Erro ao desvincular o usuário.");
    }
    exit();
}


include '../header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Acesso Empresa - Estabelecimento: <?php echo htmlspecialchars($dadosEstabelecimento['nome_fantasia']); ?></h2>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Pesquisar e Vincular Usuário</h5>
            <form method="GET" action="acesso_empresa.php" class="mb-4">
                <input type="hidden" name="id" value="<?php echo $estabelecimentoId; ?>">
                <div class="input-group">
                    <input type="text" class="form-control" id="search" name="search" placeholder="Pesquisar usuário por nome ou CPF" value="<?php echo isset($searchTerm) ? htmlspecialchars($searchTerm) : ''; ?>">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">Pesquisar</button>
                    </div>
                </div>
            </form>

            <?php if (!empty($usuariosDisponiveis)) : ?>
                <form method="POST" class="mb-4">
                    <div class="form-group">
                        <label for="usuario_id">Selecionar Usuário</label>
                        <select class="form-control" id="usuario_id" name="usuario_id" required>
                            <?php foreach ($usuariosDisponiveis as $usuario) : ?>
                                <option value="<?php echo htmlspecialchars($usuario['id']); ?>">
                                    <?php echo htmlspecialchars($usuario['nome_completo']); ?> (<?php echo htmlspecialchars($usuario['cpf']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tipo_vinculo">Tipo de Vínculo</label>
                        <select class="form-control" id="tipo_vinculo" name="tipo_vinculo" required>
                            <option value="CONTADOR">CONTADOR</option>
                            <option value="RESPONSÁVEL LEGAL">RESPONSÁVEL LEGAL</option>
                            <option value="RESPONSÁVEL TÉCNICO">RESPONSÁVEL TÉCNICO</option>
                            <option value="FUNCIONÁRIO">FUNCIONÁRIO</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success mt-3">Vincular Usuário</button>
                </form>
            <?php else : ?>
                <p class="text-muted">Nenhum usuário encontrado. Tente uma pesquisa diferente.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">Usuários Vinculados</h5>
        <?php if (!empty($usuarios)) : ?>
            <ul class="list-group">
                <?php foreach ($usuarios as $usuario) : ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo htmlspecialchars($usuario['nome_completo']); ?></strong>
                            <span class="text-muted">(<?php echo htmlspecialchars($usuario['cpf']); ?>)</span>
                        </div>
                        <div>
                            <span class="badge badge-primary badge-pill"><?php echo htmlspecialchars($usuario['tipo_vinculo']); ?></span>
                            <a href="acesso_empresa.php?id=<?php echo $estabelecimentoId; ?>&delete=true&usuario_id=<?php echo $usuario['id']; ?>" class="btn btn-danger btn-sm ml-2" onclick="return confirm('Tem certeza que deseja desvincular este usuário?')">Excluir</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p class="text-muted">Nenhum usuário vinculado a este estabelecimento.</p>
        <?php endif; ?>
    </div>
</div>

</div>

<?php include '../footer.php'; ?>
