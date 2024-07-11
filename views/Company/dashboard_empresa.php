<?php
session_start();
include '../../includes/header_empresa.php';
require_once '../../conf/database.php';
require_once '../../models/Estabelecimento.php';
require_once '../../models/Arquivo.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php"); // Redirecionar para a página de login se não estiver autenticado
    exit();
}

$nome_completo = $_SESSION['user']['nome_completo'];
$user_id = $_SESSION['user']['id'];

$estabelecimentoModel = new Estabelecimento($conn);
$arquivoModel = new Arquivo($conn);
$estabelecimentosAprovados = $estabelecimentoModel->getEstabelecimentosByUsuario($user_id);
$estabelecimentosPendentes = $estabelecimentoModel->getEstabelecimentosPendentesByUsuario($user_id);
$documentosNegados = $estabelecimentoModel->getDocumentosNegadosByUsuario($user_id);
$documentosPendentes = $estabelecimentoModel->getDocumentosPendentesByUsuario($user_id);
$estabelecimentosRejeitados = $estabelecimentoModel->getEstabelecimentosRejeitadosByUsuario($user_id);
$arquivosNaoVisualizados = $arquivoModel->getArquivosNaoVisualizados($user_id);


?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Empresa</title>
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

        .card-cadastrar {
            border-color: #007bff;
        }

        .card-aprovados {
            border-color: #28a745;
        }

        .card-pendentes {
            border-color: #ffc107;
        }

        .card-rejeitados {
            border-color: #dc3545;
        }

        .card-documentos-negados {
            border-color: #dc3545;
        }

        .card-documentos-pendentes {
            border-color: #ffc107;
        }

        .card-title .fas {
            padding: 6px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4>Dashboard</h4>
                <p>Bem-vindo, <?php echo htmlspecialchars($nome_completo); ?>!</p>
            </div>
            <div>
                <a href="cadastro_estabelecimento_empresa.php" class="btn btn-primary btn-sm">Cadastrar Estabelecimento</a>
            </div>
        </div>
        <div class="row">

            <!-- Card para Estabelecimentos Aprovados -->
            <div class="col-md-6">
                <div class="card mb-4 card-aprovados">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-clipboard-list mr-2"></i>Meus Estabelecimentos Aprovados</h6>
                        <?php if (empty($estabelecimentosAprovados)) : ?>
                            <p class="card-text">Você não tem acesso a nenhum estabelecimento aprovado.</p>
                        <?php else : ?>
                            <ul class="list-group">
                                <?php
                                $estabelecimentosExibidos = array_slice($estabelecimentosAprovados, 0, 3);
                                foreach ($estabelecimentosExibidos as $estabelecimento) : ?>
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
                            <?php if (count($estabelecimentosAprovados) > 3) : ?>
                                <a href="todos_estabelecimentos.php" class="btn btn-secondary btn-sm mt-3">Ver Todos Estabelecimentos</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>


            <!-- Card para Cadastrar Estabelecimento -->
            <div class="col-md-6">
                <div class="card mb-4 card-aprovados">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-file-alt mr-2"></i>Documentos Emitidos pela Vigilância Sanitária</h6>
                        <?php if (empty($arquivosNaoVisualizados)) : ?>
                            <p class="card-text">Você não tem documentos não visualizados.</p>
                        <?php else : ?>
                            <ul class="list-group">
                                <?php foreach ($arquivosNaoVisualizados as $arquivo) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Documento:</strong> <?php echo htmlspecialchars($arquivo['nome_arquivo']); ?><br>
                                            <strong>Processo:</strong> <?php echo htmlspecialchars($arquivo['numero_processo']); ?> - <?php echo htmlspecialchars($arquivo['nome_fantasia']); ?>
                                        </div>
                                        <div>
                                            <a href="#" class="text-primary visualizar-arquivo" data-arquivo-id="<?php echo $arquivo['id']; ?>" data-arquivo-url="../../<?php echo htmlspecialchars($arquivo['caminho_arquivo']); ?>" target="_blank">Ver Documento</a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Card para Estabelecimentos Pendentes -->
            <div class="col-md-6">
                <div class="card mb-4 card-pendentes">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-hourglass-half mr-2"></i>Estabelecimentos Pendentes</h6>
                        <?php if (empty($estabelecimentosPendentes)) : ?>
                            <p class="card-text">Você não tem estabelecimentos pendentes.</p>
                        <?php else : ?>
                            <ul class="list-group">
                                <?php foreach ($estabelecimentosPendentes as $estabelecimento) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Nome:</strong> <?php echo htmlspecialchars($estabelecimento['nome_fantasia']); ?><br>
                                            <strong>CNPJ:</strong> <?php echo htmlspecialchars($estabelecimento['cnpj']); ?><br>
                                            <strong>Endereço:</strong> <?php echo htmlspecialchars($estabelecimento['logradouro'] . ', ' . $estabelecimento['numero'] . ' - ' . $estabelecimento['bairro'] . ', ' . $estabelecimento['municipio'] . ' - ' . $estabelecimento['uf'] . ', ' . $estabelecimento['cep']); ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Card para Documentos Pendentes -->
                <div class="card mb-4 card-documentos-pendentes">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-hourglass-half mr-2"></i>Documentos Pendentes de aprovação</h6>
                        <?php if (empty($documentosPendentes)) : ?>
                            <p class="card-text">Você não tem documentos pendentes de aprovação.</p>
                        <?php else : ?>
                            <ul class="list-group">
                                <?php foreach ($documentosPendentes as $doc) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Documento:</strong> <?php echo htmlspecialchars($doc['nome_arquivo']); ?><br>
                                            <strong>Processo:</strong> <?php echo htmlspecialchars($doc['numero_processo']); ?> - <?php echo htmlspecialchars($doc['nome_fantasia']); ?>
                                        </div>
                                        <div>
                                            <a href="../Processo/detalhes_processo_empresa.php?id=<?php echo htmlspecialchars($doc['processo_id']); ?>" class="text-primary">Ver Processo</a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Card para Estabelecimentos Rejeitados -->
            <div class="col-md-6">
                <div class="card mb-4 card-rejeitados">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-times-circle mr-2"></i>Estabelecimentos Rejeitados</h6>
                        <?php if (empty($estabelecimentosRejeitados)) : ?>
                            <p class="card-text">Você não tem estabelecimentos rejeitados.</p>
                        <?php else : ?>
                            <ul class="list-group">
                                <?php foreach ($estabelecimentosRejeitados as $estabelecimento) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Nome:</strong> <?php echo htmlspecialchars($estabelecimento['nome_fantasia']); ?><br>
                                            <strong>CNPJ:</strong> <?php echo htmlspecialchars($estabelecimento['cnpj']); ?><br>
                                            <strong>Motivo da Rejeição:</strong> <?php echo htmlspecialchars($estabelecimento['motivo_negacao']); ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Card para Documentos Negados -->
                <div class="card mb-4 card-documentos-negados">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>Documentos Negados</h6>
                        <?php if (empty($documentosNegados)) : ?>
                            <p class="card-text">Você não tem documentos negados.</p>
                        <?php else : ?>
                            <ul class="list-group">
                                <?php foreach ($documentosNegados as $doc) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Documento:</strong> <?php echo htmlspecialchars($doc['nome_arquivo']); ?><br>
                                            <strong>Motivo:</strong> <?php echo htmlspecialchars($doc['motivo_negacao']); ?><br>
                                            <strong>Processo:</strong> <?php echo htmlspecialchars($doc['numero_processo']); ?> - <?php echo htmlspecialchars($doc['nome_fantasia']); ?>
                                        </div>
                                        <div>
                                            <a href="../Processo/detalhes_processo_empresa.php?id=<?php echo htmlspecialchars($doc['processo_id']); ?>" class="text-primary">Ver Processo</a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Outros cards podem ser adicionados aqui -->
        </div>

        <?php
        $conn->close();
        ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('.visualizar-arquivo');
            links.forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    const arquivoId = this.getAttribute('data-arquivo-id');
                    const arquivoUrl = this.getAttribute('data-arquivo-url');

                    // Enviar requisição AJAX para registrar visualização
                    fetch('registrar_visualizacao.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'arquivo_id=' + arquivoId
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                // Abrir o documento em uma nova aba
                                window.open(arquivoUrl, '_blank');
                                // Remover o item da lista
                                this.closest('li').remove();
                            } else {
                                alert('Erro ao registrar visualização: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                        });
                });
            });
        });
    </script>
</body>

</html>