<?php
session_start();
require_once 'conf/database.php';
require_once 'models/Estabelecimento.php';
require_once 'models/Processo.php';
require_once 'models/Arquivo.php';

$estabelecimentoModel = new Estabelecimento($conn);
$processoModel = new Processo($conn);
$arquivoModel = new Arquivo($conn);

$searchTerm = '';
$processoInfo = [];
$alvaraSanitario = null;
$erroVerificacao = '';
$arquivoVerificado = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['searchTerm'])) {
        $searchTerm = $_POST['searchTerm'];

        if (is_numeric(str_replace(['.', '/', '-'], '', $searchTerm))) {
            // Buscar pelo CNPJ
            $processoInfo = $processoModel->getProcessoByCnpj($searchTerm);

            if (!empty($processoInfo)) {
                $processo_id = $processoInfo['id'];
                $arquivos = $arquivoModel->getArquivosByProcesso($processo_id);

                // Procurar por Alvará Sanitário nos arquivos do processo
                foreach ($arquivos as $arquivo) {
                    if (strpos($arquivo['tipo_documento'], 'ALVARÁ SANITÁRIO') !== false) {
                        $alvaraSanitario = $arquivo;
                        break;
                    }
                }
            }
        } else {
            // Nenhum processo encontrado
            $processoInfo = null;
        }
    } elseif (isset($_POST['codigo_verificador'])) {
        $codigo_verificador = $_POST['codigo_verificador'] ?? '';

        if (!empty($codigo_verificador)) {
            $arquivoVerificado = $arquivoModel->getArquivoByCodigo($codigo_verificador);

            if (!$arquivoVerificado) {
                $erroVerificacao = "Código verificador inválido.";
            } else {
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . $arquivoVerificado['tipo_documento'] . '"');
                readfile(__DIR__ . '/' . $arquivoVerificado['caminho_arquivo']);
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infovisa - Sistema de Informações de Vigilância Sanitária</title>
    <link rel="stylesheet" type="text/css" href="/visamunicipal/assets/css/style.css" media="screen" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .main-container {
            margin-top: 60px;
        }
        .section-container {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        footer {
            background: #f8f9fa;
            padding: 1rem 0;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light" style="border:1px solid #eee;">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <img src="/visamunicipal/assets/img/logo.png" alt="Logomarca" width="100" height="30" class="d-inline-block align-top">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="btn btn-primary" href="views/login.php">
                                <i class="fas fa-user"></i> Login
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="main-container container">
        <div class="section-container">
            <section class="about">
                <h2>Sobre o Projeto</h2>
                <p>O Infovisa é um sistema desenvolvido para gerenciar e monitorar processos de vigilância sanitária, facilitando o acesso às informações e a transparência dos procedimentos.</p>
            </section>
        </div>

        <div class="section-container">
            <section class="consulta-processo" id="consulta-processo">
                <h4>Consultar Andamento do Processo</h4>
                <form method="POST" action="">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="searchTerm" name="searchTerm" placeholder="Digite o CNPJ" required>
                        <button class="btn btn-primary" type="submit">Buscar</button>
                    </div>
                </form>

                <?php if (!empty($processoInfo)): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Informações do Processo</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Nome do Estabelecimento:</strong> <?php echo htmlspecialchars($processoInfo['nome_fantasia']); ?></p>
                            <p><strong>CNPJ:</strong> <?php echo htmlspecialchars($processoInfo['cnpj']); ?></p>
                            <p><strong>Tipo do Processo:</strong> <?php echo htmlspecialchars($processoInfo['tipo_processo']); ?></p>
                            <p><strong>Número do Processo:</strong> <?php echo htmlspecialchars($processoInfo['numero_processo']); ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($processoInfo['status'] === 'ATIVO' ? 'ANDAMENTO' : $processoInfo['status']); ?></p>
                            <?php if (!empty($processoInfo['motivo_parado'])): ?>
                                <p><strong>Motivo do Parado:</strong> <?php echo htmlspecialchars($processoInfo['motivo_parado']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($alvaraSanitario)): ?>
                                <p><strong>ALVARÁ SANITÁRIO:</strong> <a href="<?php echo htmlspecialchars($alvaraSanitario['caminho_arquivo']); ?>" target="_blank">Visualizar</a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['searchTerm'])): ?>
                    <div class="alert alert-danger" role="alert">
                        Nenhum processo encontrado para o CNPJ fornecido.
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <div class="section-container">
            <section class="verificar-documento" id="verificar-documento">
                <h4>Verificar Documento</h4>
                <?php if (!empty($erroVerificacao)): ?>
                    <div class="alert alert-danger"><?php echo $erroVerificacao; ?></div>
                <?php endif; ?>
                <form method="POST" action="" target="_blank">
                    <div class="form-group">
                        <label for="codigo_verificador">Código Verificador</label>
                        <input type="text" class="form-control" id="codigo_verificador" name="codigo_verificador" required>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2" >Verificar</button>
                </form>
            </section>
        </div>
    </main>

    <footer class="text-center">
        <div class="container">
            <p class="mb-0">&copy; 2024 Infovisa. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- Adicione a biblioteca jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Adicione o Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <!-- Adicione a biblioteca de máscara -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://unpkg.com/imask"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var searchTermInput = document.getElementById('searchTerm');
            var maskOptions = {
                mask: '00.000.000/0000-00'
            };
            var mask = IMask(searchTermInput, maskOptions);
        });
    </script>
</body>
</html>
