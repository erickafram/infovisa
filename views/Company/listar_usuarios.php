<?php
session_start();
include '../header.php';
require_once '../../conf/database.php';
require_once '../../models/UsuarioExterno.php';
require_once '../../models/Estabelecimento.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 3])) {
    header("Location: ../../login.php"); // Redirecionar para a página de login se não for administrador ou gerente
    exit();
}

$usuarioExternoModel = new UsuarioExterno($conn);
$estabelecimentoModel = new Estabelecimento($conn);

$search = isset($_GET['search']) ? $_GET['search'] : '';
$municipio = $_SESSION['user']['municipio']; // Obtém o município do usuário logado
$nivel_acesso = $_SESSION['user']['nivel_acesso']; // Obtém o nível de acesso do usuário logado

$usuarios = $usuarioExternoModel->buscarUsuariosExternos($search, $municipio, $nivel_acesso);
$mensagem = isset($_GET['mensagem']) ? $_GET['mensagem'] : '';
$tipoMensagem = isset($_GET['tipoMensagem']) ? $_GET['tipoMensagem'] : '';

// Função para buscar estabelecimentos vinculados a um usuário
function buscarEstabelecimentosVinculados($usuarioId, $estabelecimentoModel)
{
    return $estabelecimentoModel->getEstabelecimentosByUsuarioExterno($usuarioId);
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Usuários</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        .navbar-expand-lg>.container,
        .navbar-expand-lg>.container-fluid {
            max-width: 1320px;
        }

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
        }

        .list-group-item {
            border: none;
            padding: 10px 15px;
        }

        .list-group-item:hover {
            background-color: #f8f9fa;
        }

        .welcome-message {
            background-color: #007bff;
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .welcome-message h4 {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container mt-5" style="max-width: 1320px;">
        <div class="welcome-message">
            <h4>Listar Usuários</h4>
        </div>

        <?php if ($mensagem) : ?>
            <div class="alert alert-<?php echo htmlspecialchars($tipoMensagem); ?>" role="alert">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <form method="GET" action="listar_usuarios.php" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Buscar por nome ou CPF" value="<?php echo htmlspecialchars($search); ?>">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">Buscar</button>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-users mr-2"></i>Usuários</h6>
                        <?php if (empty($usuarios)) : ?>
                            <p class="card-text">Não há usuários cadastrados.</p>
                        <?php else : ?>
                            <ul class="list-group">
                                <?php foreach ($usuarios as $usuario) : ?>
                                    <?php $estabelecimentos = buscarEstabelecimentosVinculados($usuario['id'], $estabelecimentoModel); ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Nome:</strong> <?php echo htmlspecialchars($usuario['nome_completo']); ?><br>
                                            <strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?><br>
                                            <strong>Telefone:</strong> <?php echo htmlspecialchars($usuario['telefone']); ?><br>
                                            <strong>CPF:</strong> <?php echo htmlspecialchars($usuario['cpf']); ?><br>
                                            <div style="margin-top:5px !important; font-weight:bold;font-size:12px;">Estabelecimentos Vinculados:</div>
                                            <ul style="font-size:11px;">
                                                <?php foreach ($estabelecimentos as $estabelecimento) : ?>
                                                    <li>
                                                        <a href="../Estabelecimento/detalhes_estabelecimento.php?id=<?php echo urlencode($estabelecimento['id']); ?>">
                                                         <?php echo htmlspecialchars($estabelecimento['nome_fantasia']); ?> - <?php echo htmlspecialchars($estabelecimento['cnpj']); ?>
                                                    </li>
                                                </a>
                                                <?php endforeach; ?>

                                            </ul>
                                        </div>
                                        <div>
                                            <form action="../../controllers/EmpresaController.php?action=reset_senha" method="POST" class="d-inline">
                                                <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                                <button type="submit" class="btn btn-warning">Redefinir Senha</button>
                                            </form>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <div class="mt-3">
                            <p><strong>Nota:</strong> A senha padrão será <code>@visa2024</code> ao redefinir.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $conn->close();
        ?>
    </div>
</body>

</html>