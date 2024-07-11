<?php
session_start();
include '../../includes/header_empresa.php';
require_once '../../conf/database.php';
require_once '../../models/Estabelecimento.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$estabelecimentoModel = new Estabelecimento($conn);
$limit = 10; // Número de registros por página
$offset = isset($_GET['page']) ? ($_GET['page'] - 1) * $limit : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Obter estabelecimentos aprovados com busca e paginação
$estabelecimentosAprovados = $estabelecimentoModel->searchAprovados($user_id, $search, $limit, $offset);
$totalEstabelecimentos = $estabelecimentoModel->countAprovados($user_id, $search);

$totalPages = ceil($totalEstabelecimentos / $limit);
$currentPage = isset($_GET['page']) ? $_GET['page'] : 1;

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todos Estabelecimentos</title>
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-width: 1px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }

        .card-title {
            font-weight: bold;
            color: #333;
            font-size: 1.1rem;
        }

        .card-text {
            font-size: 0.9rem;
        }

        .list-group-item {
            border: none;
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        .card-title .fas {
            padding: 6px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-clipboard-list mr-2"></i>Todos Estabelecimentos Aprovados</h6>
                <form method="GET" action="todos_estabelecimentos.php">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="search" placeholder="Buscar estabelecimentos" value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                    </div>
                </form>
                <?php if (empty($estabelecimentosAprovados)) : ?>
                    <p class="card-text">Você não tem acesso a nenhum estabelecimento aprovado.</p>
                <?php else : ?>
                    <ul class="list-group">
                        <?php foreach ($estabelecimentosAprovados as $estabelecimento) : ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Nome:</strong> <?php echo htmlspecialchars($estabelecimento['nome_fantasia']); ?><br>
                                    <strong>CNPJ:</strong> <?php echo htmlspecialchars($estabelecimento['cnpj']); ?><br>
                                    <strong>Endereço:</strong> <?php echo htmlspecialchars($estabelecimento['logradouro'] . ', ' . $estabelecimento['numero'] . ' - ' . $estabelecimento['bairro'] . ', ' . $estabelecimento['municipio'] . ' - ' . $estabelecimento['uf'] . ', ' . $estabelecimento['cep']); ?>
                                </div>
                                <div>
                                    <a href="../Estabelecimento/detalhes_estabelecimento_empresa.php?id=<?php echo htmlspecialchars($estabelecimento['id']); ?>" class="text-primary"><i class="far fa-eye"></i></a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <nav>
                        <ul class="pagination justify-content-center mt-3">
                            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                                <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?search=<?php echo htmlspecialchars($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>