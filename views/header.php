<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define o tempo limite de inatividade (em segundos)
$tempoLimiteInatividade = 1800; // 30 minutos

// Verifica a última atividade do usuário
if (isset($_SESSION['ultima_atividade']) && (time() - $_SESSION['ultima_atividade']) > $tempoLimiteInatividade) {
    // Destrói a sessão
    session_unset();
    session_destroy();
    // Redireciona para a página de login
    header("Location: ../../login.php");
    exit();
}

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php");
    exit();
}

// Atualiza o tempo da última atividade
$_SESSION['ultima_atividade'] = time();

require_once '../../conf/database.php';
require_once '../../controllers/AlertaController.php';

$alertaController = new AlertaController($conn);
$usuario_id = $_SESSION['user']['id'];
$assinaturasPendentes = $alertaController->getAssinaturasPendentes($usuario_id);
$assinaturasRascunho = $alertaController->getAssinaturasRascunho($usuario_id);
$processosDesignadosPendentes = $alertaController->getProcessosDesignadosPendentes($usuario_id);

// Contar processos com documentação pendente
$query = "
    SELECT COUNT(*) AS total
    FROM processos p
    JOIN documentos d ON p.id = d.processo_id
    JOIN estabelecimentos e ON p.estabelecimento_id = e.id
    WHERE d.status = 'pendente' AND e.municipio = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $_SESSION['user']['municipio']);
$stmt->execute();
$result = $stmt->get_result();
$pendentes = $result->fetch_assoc()['total'];
$stmt->close();

// Contagem total de alertas
$totalAlertas = count($assinaturasPendentes) + count($assinaturasRascunho) + count($processosDesignadosPendentes) + $pendentes;

$ultimosAlertas = array_merge(
    array_slice($assinaturasPendentes, 0, 5),
    array_slice($assinaturasRascunho, 0, 5),
    array_slice($processosDesignadosPendentes, 0, 5)
);
$ultimosAlertas = array_slice($ultimosAlertas, 0, 5); // Garantir que sejam no máximo 5 alertas

if ($pendentes > 0) {
    $ultimosAlertas[] = array('tipo' => 'Processos com Documentação Pendente', 'total' => $pendentes);
}

// Buscar todas as chaves de API
$sql = "SELECT nome_api, chave_api FROM configuracoes_apis";
$result = $conn->query($sql);
$chavesApi = [];
while ($row = $result->fetch_assoc()) {
    $chavesApi[$row['nome_api']] = $row['chave_api'];
}'';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="/visamunicipal/assets/css/style.css" media="screen" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>INFOVISA</title>
    <style>
        .navbar-light .navbar-nav .nav-link {
            color: rgba(0, 0, 0, .5);
            font-size: 13px;
        }

        .dropdown-item {
            font-size: 12px;
        }

        .badge {
            display: inline-block;
            padding: 0.25em 0.4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.375rem;
            margin: 0 1px !important;
        }

        .nav-link .badge-counter {
            position: absolute;
            transform: scale(.7);
            transform-origin: top right;
            right: 0.15rem;
            margin-top: -0.25rem;
        }

        .bg-danger {
            font-size: 10px !important;
        }

        .badge-danger {
            color: #fff !important;
            background-color: #dc3545 !important;
        }

        .text-light {
            color: #f8f9fa !important;
        }

        .dropdown-menu-alert {
            max-width: 400px;
            max-height: 300px;
            overflow-y: auto;
        }

        .nav-link {
            display: block;
            padding: .5rem 1rem;
        }

        @media (max-width: 991.98px) {
            .nav-link .badge-counter {
                position: absolute;
                transform: scale(.7);
                transform-origin: top right;
                right: 5.00rem;
                margin-top: -0.25rem;
            }

            .menu-alerta {
                display: none;
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: rotate(0deg);
            }

            25% {
                transform: rotate(-5deg);
            }

            75% {
                transform: rotate(5deg);
            }
        }

        .shake {
            animation: shake 0.5s infinite;
        }
    </style>

     <!-- Adicione os scripts das APIs configuradas -->
    <?php if (isset($chavesApi['tiny'])) : ?>
        <script src="https://cdn.tiny.cloud/1/<?php echo htmlspecialchars($chavesApi['tiny']); ?>/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <?php endif; ?>
    <?php if (isset($chavesApi['google_maps'])) : ?>
        <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($chavesApi['google_maps']); ?>"></script>
    <?php endif; ?>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="/visamunicipal/assets/img/logo.png" alt="Logomarca" width="100" height="30" class="d-inline-block align-top">
            </a>
            <div class="d-flex">
                <a class="nav-link d-lg-none" href="#" id="alertDropdownMobile" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:20px !important;">
                    <i class="fas fa-bell <?php echo ($totalAlertas > 0) ? 'shake' : ''; ?>"></i>
                    <?php if ($totalAlertas > 0) : ?>
                        <span class="badge badge-danger badge-counter"><?php echo $totalAlertas; ?></span>
                    <?php endif; ?>
                </a>

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-alert" aria-labelledby="alertDropdownMobile">
                    <li class="dropdown-header">Centro de Alertas</li>
                    <?php if (empty($ultimosAlertas)) : ?>
                        <li><a class="dropdown-item" href="#">Nenhum alerta</a></li>
                    <?php else : ?>
                        <?php foreach ($ultimosAlertas as $alerta) : ?>
                            <li><a class="dropdown-item" href="../Alertas/alertas_usuario_logado.php">
                                    <?php
                                    if (isset($alerta['status']) && $alerta['status'] == 'rascunho') {
                                        echo 'Arquivos Rascunho a Finalizar: ' . htmlspecialchars($alerta['tipo_documento']);
                                    } elseif (isset($alerta['tipo_documento'])) {
                                        echo 'Assinaturas Pendentes: ' . htmlspecialchars($alerta['tipo_documento']);
                                    } elseif (isset($alerta['descricao'])) {
                                        echo 'Processos Alerta Pendente: ' . htmlspecialchars($alerta['descricao']);
                                    } elseif (isset($alerta['tipo']) && $alerta['tipo'] == 'Processos com Documentação Pendente') {
                                        echo 'Processos com Documentação Pendente: ' . htmlspecialchars($alerta['total']);
                                    } else {
                                        echo 'Processos Designados Pendentes: ' . htmlspecialchars($alerta['nome_fantasia']);
                                    }
                                    ?>
                                    <br> <?php echo htmlspecialchars($alerta['nome_fantasia'] ?? ''); ?>
                                </a></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-center" href="../Alertas/alertas_usuario_logado.php">Ver todos os alertas</a></li>
                </ul>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../Dashboard/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="estabelecimentoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Estabelecimentos
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="estabelecimentoDropdown">
                            <li><a class="dropdown-item" href="../Estabelecimento/listar_estabelecimentos.php">Lista</a></li>
                            <li><a class="dropdown-item" href="../Estabelecimento/listar_estabelecimentos_rejeitados.php">Negado</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="estabelecimentoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Processos
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="estabelecimentoDropdown">
                            <li><a class="dropdown-item" href="/visamunicipal/views/Processo/listar_processos.php">Lista</a></li>
                            <li><a class="dropdown-item" href="/visamunicipal/views/Processo/listar_processos.php?search=&pendentes=1">Documentação Pendente <?php if ($pendentes > 0) : ?> <span class="badge bg-danger text-light"><?php echo $pendentes; ?></span> <?php endif; ?></a></li>
                            <?php if ($_SESSION['user']['nivel_acesso'] == 1 || $_SESSION['user']['nivel_acesso'] == 3) : ?>
                                <li><a class="dropdown-item" href="/visamunicipal/views/Processo/listar_processos_designados.php">Processos Designados</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="ordemServicoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Ordens de Serviço
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="ordemServicoDropdown">
                            <li><a class="dropdown-item" href="../OrdemServico/listar_ordens.php">Lista</a></li>
                        </ul>
                    </li>
                    <?php if ($_SESSION['user']['nivel_acesso'] == 1) : ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Usuários
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="../Admin/cadastro.php">Cadastrar</a></li>
                                <li><a class="dropdown-item" href="../Admin/listar_usuarios.php">Lista</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="ordemServicoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Documentos
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="ordemServicoDropdown">
                            <li><a class="dropdown-item" href="../Arquivos/todos_arquivos.php">Lista</a></li>
                            <li><a class="dropdown-item" href="../Arquivos/documentos_para_finalizar.php">Documentos para Finalizar</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="ordemServicoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Assinaturas
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="ordemServicoDropdown">
                            <li><a class="dropdown-item" href="../Assinatura/assinaturas_pendentes_usuario.php">Minhas Assinaturas Pendente</a></li>
                            <li><a class="dropdown-item" href="../Assinatura/assinaturas_realizadas_usuario.php">Documentos Assinados</a></li>
                        </ul>
                    </li>

                    <?php if ($_SESSION['user']['nivel_acesso'] == 1 || $_SESSION['user']['nivel_acesso'] == 3) : ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Gerenciar
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="../ModeloDocumentos/listar_modelos.php">Modelos de Documentos</a></li>
                                <hr>
                                <li><a class="dropdown-item" href="../TipoAcoes/listar_tipos.php">Adcionar Tipo Ação</a></li>
                                <li><a class="dropdown-item" href="../TipoAcoes/adicionar_pontuacao.php">Adcionar Pontuação</a></li>
                                <?php if ($_SESSION['user']['nivel_acesso'] == 1) : ?>
                                    <li><a class="dropdown-item" href="../TipoAcoes/adicionar_grupo_risco.php">Adicionar Tipo Grupo de Risco</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="../TipoAcoes/adicionar_atividade_grupo_risco.php">Adicionar Atividade/Grupo de Risco</a></li>
                                <hr>
                                <li><a class="dropdown-item" href="../Logomarcas/cadastrar_logomarca.php">Cadastrar Logomarca</a></li>
                                <li><a class="dropdown-item" href="../Logomarcas/listar_logomarcas.php">Lista Logomarca</a></li>
                                <hr>
                                <li><a class="dropdown-item" href="../Admin/listar_usuarios.php">Usuários Interno</a></li>
                                <li><a class="dropdown-item" href="../Company/listar_usuarios.php">Usuários Externos</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Relatórios
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="../Relatorio/relatorio_estabelecimentos.php">Estabelecimentos</a></li>
                            <li><a class="dropdown-item" href="../Relatorio/relatorio_atividades.php">Atividades</a></li>
                            <li><a class="dropdown-item" href="../Relatorio/relatorio_documentos.php">Documentos</a></li>
                            <li><a class="dropdown-item" href="../Relatorio/relatorio_grupoderisco.php">Grupo de Risco</a></li>
                            <li><a class="dropdown-item" href="../Relatorio/relatorio_alvara.php">Alvará Sanitário</a></li>
                            <li><a class="dropdown-item" href="../Relatorio/relatorios_processo.php">Processos</a></li>
                            <?php if ($_SESSION['user']['nivel_acesso'] == 1) : ?>
                                <li><a class="dropdown-item" href="../Relatorio/relatorio_usuarios_municipio.php">Usuários por Município</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <?php if ($_SESSION['user']['nivel_acesso'] == 1) : ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Admin
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="../Admin/apitiny.php">Api</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown menu-alerta">
                        <a class="nav-link" href="#" id="alertDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:18px !important;">
                            <i class="fas fa-bell <?php echo ($totalAlertas > 0) ? 'shake' : ''; ?>"></i>
                            <?php if ($totalAlertas > 0) : ?>
                                <span class="badge badge-danger badge-counter"><?php echo $totalAlertas; ?></span>
                            <?php endif; ?>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-alert" aria-labelledby="alertDropdown">
                            <li class="dropdown-header">Centro de Alertas</li>
                            <?php if (empty($ultimosAlertas)) : ?>
                                <li><a class="dropdown-item" href="#">Nenhum alerta</a></li>
                            <?php else : ?>
                                <?php foreach ($ultimosAlertas as $alerta) : ?>
                                    <li><a class="dropdown-item" href="../Alertas/alertas_usuario_logado.php">
                                            <?php
                                            if (isset($alerta['status']) && $alerta['status'] == 'rascunho') {
                                                echo 'Arquivos Rascunho a Finalizar: ' . htmlspecialchars($alerta['tipo_documento']);
                                            } elseif (isset($alerta['tipo_documento'])) {
                                                echo 'Assinaturas Pendentes: ' . htmlspecialchars($alerta['tipo_documento']);
                                            } elseif (isset($alerta['descricao'])) {
                                                echo 'Processos Alerta Pendente: ' . htmlspecialchars($alerta['descricao']);
                                            } elseif (isset($alerta['tipo']) && $alerta['tipo'] == 'Processos com Documentação Pendente') {
                                                echo 'Processos com Documentação Pendente: ' . htmlspecialchars($alerta['total']);
                                            } else {
                                                echo 'Processos Designados Pendentes: ' . htmlspecialchars($alerta['nome_fantasia']);
                                            }
                                            ?>
                                            <br> <?php echo htmlspecialchars($alerta['nome_fantasia'] ?? ''); ?>
                                        </a></li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-center" href="../Alertas/alertas_usuario_logado.php">Ver todos os alertas</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Minha Conta
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                            <li><a class="dropdown-item" href="../Admin/editar_cadastro_usuario.php">Editar Cadastro</a></li>
                            <li><a class="dropdown-item" href="../Usuario/alterar_senha.php">Alterar Senha</a></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- Adicione a biblioteca jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Adicione o Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <!-- Adicione a biblioteca de máscara -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
</body>

</html>