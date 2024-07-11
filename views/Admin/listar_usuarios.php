<?php
session_start();
include '../header.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 3])) {
    header("Location: ../../login.php"); // Redirecionar para a página de login se não estiver autenticado ou não for autorizado
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/User.php';

// Função auxiliar para converter o nível de acesso para o nome do cargo
function getNomeCargo($nivel_acesso)
{
    switch ($nivel_acesso) {
        case 1:
            return "Administrador";
        case 2:
            return "Suporte";
        case 3:
            return "Gerente";
        case 4:
            return "Fiscal";
        default:
            return "Desconhecido";
    }
}

$user = new User($conn);
$usuarioLogado = $_SESSION['user'];

if ($usuarioLogado['nivel_acesso'] == 1) {
    $usuarios = $user->getAllUsers();
} elseif ($usuarioLogado['nivel_acesso'] == 3) {
    $municipioUsuario = $usuarioLogado['municipio'];
    $usuarios = $user->getUsersByMunicipio($municipioUsuario);
}
?>

<div class="container mt-5">
    <h2 class="d-flex justify-content-between align-items-center">
        Lista de Usuários
        <a href="cadastro.php" class="btn btn-success btn-sm">Cadastrar Usuário</a>
    </h2>
    <?php if (isset($_GET['success'])) : ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nome Completo</th>
                <th>CPF</th>
                <th>Email</th>
                <th>Município</th>
                <th>Cargo</th>
                <th>Nível de Acesso</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($usuarios) : ?>
                <?php foreach ($usuarios as $usuario) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['nome_completo']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['cpf']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['municipio']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['cargo']); ?></td>
                        <td><?php echo htmlspecialchars(getNomeCargo($usuario['nivel_acesso'])); ?></td>
                        <td><?php echo htmlspecialchars($usuario['status']); ?></td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    Ações
                                </button>
                                <ul class="dropdown-menu">
                                    <?php if ($_SESSION['user']['nivel_acesso'] == 1) : ?>
                                        <li><a class="dropdown-item" href="editar_usuario.php?id=<?php echo $usuario['id']; ?>">Editar</a></li>
                                    <?php endif; ?>
                                    <?php if ($_SESSION['user']['nivel_acesso'] == 1 || $_SESSION['user']['nivel_acesso'] == 3) : ?>
                                        <?php if ($usuario['status'] == 'ativo') : ?>
                                            <li><a class="dropdown-item" href="../../controllers/UserController.php?action=deactivate&id=<?php echo $usuario['id']; ?>">Desativar</a></li>
                                        <?php else : ?>
                                            <li><a class="dropdown-item" href="../../controllers/UserController.php?action=activate&id=<?php echo $usuario['id']; ?>">Ativar</a></li>
                                        <?php endif; ?>
                                        <li><a class="dropdown-item" href="../../controllers/UserController.php?action=reset_password&id=<?php echo $usuario['id']; ?>">Redefinir Senha</a></li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#alterarNivelAcessoModal" data-id="<?php echo $usuario['id']; ?>" data-nivel="<?php echo $usuario['nivel_acesso']; ?>">Alterar Nível de Acesso</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="8" class="text-center">Nenhum usuário encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="modal fade" id="alterarNivelAcessoModal" tabindex="-1" aria-labelledby="alterarNivelAcessoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="../../controllers/UserController.php?action=alterar_nivel_acesso" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="alterarNivelAcessoModalLabel">Alterar Nível de Acesso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="usuarioId">
                    <div class="mb-3">
                        <label for="nivelAcesso" class="form-label">Nível de Acesso</label>
                        <select class="form-select" name="nivel_acesso" id="nivelAcesso">
                            <option value="3">Gerente</option>
                            <option value="4">Fiscal</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>

<script>
    var alterarNivelAcessoModal = document.getElementById('alterarNivelAcessoModal');
    alterarNivelAcessoModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var userId = button.getAttribute('data-id');
        var nivelAcesso = button.getAttribute('data-nivel');

        var modal = this;
        modal.querySelector('.modal-body #usuarioId').value = userId;
        modal.querySelector('.modal-body #nivelAcesso').value = nivelAcesso;
    });
</script>

<?php
$conn->close();
include '../footer.php';
?>
