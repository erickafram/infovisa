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
if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

// Atualiza o tempo da última atividade
$_SESSION['ultima_atividade'] = time();

require_once '../../conf/database.php';
require_once '../../models/Processo.php';
require_once '../../models/Estabelecimento.php';
require_once '../../models/Arquivo.php';

// Obter o ID do usuário logado
$userId = $_SESSION['user']['id'];

// Instanciar o objeto Processo e obter a contagem de alertas e processos parados
$processo = new Processo($conn);
$alertasCount = $processo->getAlertasCountByUsuario($userId);
$processosParadosCount = $processo->getProcessosParadosCountByUsuario($userId);

$estabelecimento = new Estabelecimento($conn);
$documentosNegadosCount = count($estabelecimento->getDocumentosNegadosByUsuario($userId));

$arquivoModel = new Arquivo($conn);
$arquivosNaoVisualizadosCount = count($arquivoModel->getArquivosNaoVisualizados($userId));

// Combinar as contagens de alertas, processos parados, documentos negados e arquivos não visualizados
$totalNotificacoes = $alertasCount + $processosParadosCount + $documentosNegadosCount + $arquivosNaoVisualizadosCount;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="/visamunicipal/assets/css/style.css" media="screen" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>INFOVISA - Empresa</title>
    <style>
        .badge-alerta {
            background-color: #f44336;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 12px;
            margin-left: 5px;
        }

        .badge-parado {
            background-color: #ff9800;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 12px;
            margin-left: 5px;
        }

        .nav-link .badge-counter {
            position: absolute;
            transform: scale(.7);
            transform-origin: top right;
            margin-top: -0.25rem;
            margin: -4px -14px;
        }

        .nav-link .fa {
            font-size: 20px;
        }

        @media (max-width: 768px) {
            .nav-link {
                color: rgba(0, 0, 0, 0.5) !important;
                margin: 0 20px;
                font-size: 20px;
            }
        }

        .bg-danger {
            font-size: 13px !important;
        }

        .badge-danger {
            color: #fff !important;
            background-color: #dc3545 !important;
            font-size: 11px !important;
        }

        .navbar-toggler-icon {
            position: relative;
        }

        .navbar-toggler-custom {
            display: flex;
            align-items: center;
        }

        .navbar-toggler-custom .fa-bell {
            margin-right: 10px;
        }

        .d-lg-none {
            display: flex;
            align-items: center;
        }

        @keyframes shake {
            0%, 100% {
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
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="/visamunicipal/assets/img/logo.png" alt="Logomarca" width="100" height="30" class="d-inline-block align-top">
            </a>
            <div class="d-lg-none">
                <a class="nav-link" href="../Company/alertas_empresas.php" id="notificationsLink">
                    <i class="fa fa-bell <?php echo ($totalNotificacoes > 0) ? 'shake' : ''; ?>"></i>
                    <span class="badge badge-danger badge-counter"><?php echo $totalNotificacoes; ?></span>
                </a>
                <button class="navbar-toggler navbar-toggler-custom" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../Company/dashboard_empresa.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../Company/todos_estabelecimentos.php">Estabelecimentos</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <!-- Sino de notificações visível em telas grandes -->
                    <li class="nav-item d-none d-lg-block">
                        <a class="nav-link" href="../Company/alertas_empresas.php" id="notificationsLink">
                            <i class="fa fa-bell <?php echo ($totalNotificacoes > 0) ? 'shake' : ''; ?>"></i>
                            <span class="badge badge-danger badge-counter"><?php echo $totalNotificacoes; ?></span>
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Minha Conta
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                            <li><a class="dropdown-item" href="../Company/alterar_senha_empresa.php">Alterar Senha</a></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

</html>
