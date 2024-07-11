<?php
session_start();
include '../header.php';
require_once '../../conf/database.php';
require_once '../../models/Processo.php';
require_once '../../models/OrdemServico.php';
require_once '../../models/Estabelecimento.php';
require_once '../../models/User.php'; // Adicionando o modelo User
require_once '../../models/Assinatura.php'; // Adicionando o modelo Assinatura
require_once '../../models/Arquivo.php'; // Adicionando o modelo Arquivo

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php"); // Redirecionar para a página de login se não estiver autenticado
    exit();
}

$user_id = $_SESSION['user']['id'];
$municipioUsuario = $_SESSION['user']['municipio']; // Obtendo o município do usuário logado
$ordemServicoModel = new OrdemServico($conn);
$ordensServico = $ordemServicoModel->getOrdensByTecnicoIncludingNoEstabelecimento($user_id);

// Filtrar ordens de serviço para excluir as finalizadas
$ordensServico = array_filter($ordensServico, function ($ordem) {
    return $ordem['status'] !== 'finalizada';
});

// Buscar processos parados
$processoModel = new Processo($conn);
$processosParados = $processoModel->getProcessosParados($municipioUsuario); // Passando o município como parâmetro

// Buscar alertas próximos de vencer
$alertasProximosAVencer = $processoModel->getAlertasProximosAVencer($municipioUsuario); // Passando o município como parâmetro

// Buscar estabelecimentos pendentes de aprovação
$estabelecimentoModel = new Estabelecimento($conn);
$estabelecimentosPendentes = $estabelecimentoModel->getEstabelecimentosPendentes($municipioUsuario); // Passando o município como parâmetro

// Calcular a pontuação mensal
$mesAtual = date('m');
$anoAtual = date('Y');
$pontuacaoMensal = $ordemServicoModel->getPontuacaoMensal($user_id, $mesAtual, $anoAtual);

// Buscar processos com documentação pendente
$processosPendentes = $processoModel->getProcessosComDocumentacaoPendente($municipioUsuario);
$processosAcompanhados = $processoModel->getProcessosAcompanhados($user_id);
$responsabilidades = $processoModel->getProcessosResponsaveisPorUsuario($user_id);

// Filtrar responsabilidades para excluir as resolvidas e mostrar apenas pendentes
$responsabilidades = array_filter($responsabilidades, function ($responsavel) {
    return isset($responsavel['status']) && $responsavel['status'] === 'pendente';
});

$userModel = new User($conn);
$usuarioLogado = $userModel->findById($user_id);

if ($usuarioLogado['nivel_acesso'] != 1 && (is_null($usuarioLogado['tempo_vinculo']) || is_null($usuarioLogado['escolaridade']) || is_null($usuarioLogado['tipo_vinculo']))) {
    $camposIncompletos = true;
} else {
    $camposIncompletos = false;
}

$assinaturaModel = new Assinatura($conn); // Instanciar o modelo Assinatura
$assinaturasPendentes = $assinaturaModel->getAssinaturasPendentes($user_id);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <style>
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
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .welcome-message::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 10px;
            border: 2px solid transparent;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background: linear-gradient(90deg, #ff6a00, #ee0979, #007bff, #0056b3, #00c6ff);
            background-size: 300% 300%;
            animation: borderAnimation 6s infinite;
            z-index: 1;
        }

        @keyframes borderAnimation {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .welcome-message h4,
        .welcome-message p {
            position: relative;
            z-index: 2;
        }


        .welcome-message h4 {
            margin-bottom: 10px;
        }

        .mr-2,
        .mx-2 {
            margin-right: .5rem !important;
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <div class="welcome-message">
            <h4>Dashboard</h4>
            <p>Bem-vindo, <?php echo htmlspecialchars($_SESSION['user']['nome_completo'] ?? ''); ?>!</p>
        </div>

        <div class="row">
            <!-- Card para Aprovação de Estabelecimentos -->
            <div class="col-md-6 col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-check-circle mr-2"></i>Aprovação de Estabelecimentos</h6>
                        <?php if (empty($estabelecimentosPendentes)) : ?>
                            <p class="card-text">Não há estabelecimentos pendentes de aprovação.</p>
                        <?php else : ?>
                            <ul class="list-group">
                                <?php foreach ($estabelecimentosPendentes as $estabelecimento) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div style="font-size:12px;">
                                            <?php echo htmlspecialchars($estabelecimento['nome_fantasia'] ?? ''); ?>
                                        </div>
                                        <div>
                                            <a href="../Estabelecimento/detalhes_estabelecimento.php?id=<?php echo htmlspecialchars($estabelecimento['id'] ?? ''); ?>" class="text-primary"><i class="far fa-eye"></i></a>
                                            <a href="../Estabelecimento/approve_estabelecimento.php?id=<?php echo htmlspecialchars($estabelecimento['id'] ?? ''); ?>" class="text-success"><i class="fas fa-check"></i></a>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal" data-id="<?php echo htmlspecialchars($estabelecimento['id']); ?>"><i class="fas fa-times"></i></button>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Card para Processos para Resolver -->
            <div class="col-md-6 col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-tasks mr-2"></i>Processos Designados</h6>
                        <?php if (empty($responsabilidades)) : ?>
                            <p class="card-text">Não há processos designados para você resolver no momento.</p>
                        <?php else : ?>
                            <ul class="list-group">
                                <?php foreach ($responsabilidades as $resp) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div style="font-size:12px;">
                                            Processo #<?php echo htmlspecialchars($resp['numero_processo'] ?? ''); ?> - <?php echo htmlspecialchars($resp['nome_fantasia'] ?? ''); ?>
                                            <br>
                                            <small>Descrição: <?php echo htmlspecialchars($resp['descricao'] ?? ''); ?></small>
                                        </div>
                                        <div>
                                            <a href="../Processo/documentos.php?processo_id=<?php echo htmlspecialchars($resp['id'] ?? ''); ?>&id=<?php echo htmlspecialchars($resp['estabelecimento_id'] ?? ''); ?>" class="text-primary"><i class="far fa-eye"></i></a>
                                            <a href="#" onclick="confirmFinalize('<?php echo htmlspecialchars($resp['id'] ?? ''); ?>')" class="text-success"><i class="fas fa-check"></i></a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Adicione um novo card para Processos Acompanhados -->
            <div class="col-md-6 col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-eye mr-2"></i>Meus Processos Acompanhados</h6>
                        <?php if (empty($processosAcompanhados)) : ?>
                            <p class="card-text">Você não está acompanhando nenhum processo no momento.</p>
                        <?php else : ?>
                            <ul class="list-group">
                                <?php foreach ($processosAcompanhados as $processo) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div style="font-size:12px;">
                                            Processo #<?php echo htmlspecialchars($processo['numero_processo'] ?? ''); ?> - <?php echo htmlspecialchars($processo['nome_fantasia'] ?? ''); ?>
                                        </div>
                                        <div>
                                            <a href="../Processo/documentos.php?processo_id=<?php echo htmlspecialchars($processo['id'] ?? ''); ?>&id=<?php echo htmlspecialchars($processo['estabelecimento_id'] ?? ''); ?>" class="text-primary"><i class="far fa-eye"></i></a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Card para Processos com Documentação Pendente -->
            <div class="col-md-6 col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-file-alt mr-2"></i>Processos com Documentação Pendente</h6>
                        <?php if (empty($processosPendentes)) : ?>
                            <p class="card-text">Não há processos com documentação pendente no momento.</p>
                        <?php else : ?>
                            <ul class="list-group">
                                <?php foreach ($processosPendentes as $processo) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div style="font-size:12px;">
                                            Processo #<?php echo htmlspecialchars($processo['numero_processo'] ?? ''); ?> - <?php echo htmlspecialchars($processo['nome_fantasia'] ?? ''); ?>
                                        </div>
                                        <div>
                                            <a href="../Processo/documentos.php?processo_id=<?php echo htmlspecialchars($processo['processo_id'] ?? ''); ?>&id=<?php echo htmlspecialchars($processo['estabelecimento_id'] ?? ''); ?>" class="text-primary"><i class="far fa-eye"></i></a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Card para Processos Parados -->
            <div class="col-md-6 col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-pause-circle mr-2"></i>Processos Parados</h6>
                        <?php if (empty($processosParados)) : ?>
                            <p class="card-text">Não há processos parados no momento.</p>
                        <?php else : ?>
                            <ul class="list-group">
                                <div style="font-size:12px;">
                                    <?php foreach ($processosParados as $processo) : ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                Processo #<?php echo htmlspecialchars($processo['numero_processo'] ?? ''); ?> - <?php echo htmlspecialchars($processo['nome_fantasia'] ?? ''); ?>
                                            </div>
                                            <div>
                                                <a href="../Processo/documentos.php?processo_id=<?php echo htmlspecialchars($processo['id'] ?? ''); ?>&id=<?php echo htmlspecialchars($processo['estabelecimento_id'] ?? ''); ?>" class="text-primary"><i class="far fa-eye"></i></a>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </div>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Card para Alertas Próximos a Vencer -->
            <div class="col-md-6 col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>Alertas Próximos a Vencer</h6>
                        <?php if (empty($alertasProximosAVencer)) : ?>
                            <p class="card-text">Não há alertas próximos a vencer no momento.</p>
                        <?php else : ?>
                            <ul class="list-group">
                                <?php foreach ($alertasProximosAVencer as $alerta) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div style="font-size:12px;">
                                            <strong>Nome Estabelecimento:</strong> <?php echo htmlspecialchars($alerta['nome_fantasia'] ?? ''); ?><br>
                                            <strong>Prazo:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($alerta['prazo']))); ?><br>
                                            <strong>Vencimento:</strong>
                                            <?php
                                            $diasRestantes = $alerta['dias_restantes'] ?? 0;
                                            if ($diasRestantes > 0) {
                                                echo "Faltam $diasRestantes dias para o vencimento.";
                                            } elseif ($diasRestantes == 0) {
                                                echo "Alerta vence hoje!";
                                            } else {
                                                echo "Vencido há " . abs($diasRestantes) . " dias.";
                                            }
                                            ?>
                                        </div>
                                        <div>
                                            <a href="../Alertas/detalhes_alerta.php?alerta_id=<?php echo htmlspecialchars($alerta['id'] ?? ''); ?>" class="text-warning"><i class="far fa-eye"></i></a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Card para Ordens de Serviço Ativas -->
            <div class="col-md-6 col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-tools mr-2"></i>Minhas Ordens de Serviço em Andamento</h6>
                        <?php if (empty($ordensServico)) : ?>
                            <p class="card-text">Você não está designado como técnico em nenhuma ordem de serviço ativa.</p>
                        <?php else : ?>
                            <ul class="list-group">
                                <?php foreach ($ordensServico as $ordem) : ?>
                                    <div style="font-size:12px;">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <?php echo htmlspecialchars($ordem['nome_fantasia'] ?? 'Sem Estabelecimento'); ?>
                                            </div>
                                            <div>
                                                <?php if ($ordem['estabelecimento_id']) { ?>
                                                    <a href="../OrdemServico/detalhes_ordem.php?id=<?php echo htmlspecialchars($ordem['id'] ?? ''); ?>" class="text-info"><i class="fas fa-info-circle"></i></a>
                                                <?php } else { ?>
                                                    <a href="../OrdemServico/detalhes_ordem_sem_estabelecimento.php?id=<?php echo htmlspecialchars($ordem['id'] ?? ''); ?>" class="text-info"><i class="fas fa-info-circle"></i></a>
                                                <?php } ?>
                                            </div>
                                        </li>
                                    </div>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Card para Pontuação Mensal -->
            <div class="col-md-6 col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-star mr-2"></i>Minha Pontuação Mensal</h6>
                        <p class="card-text">Pontuação acumulada no mês atual: <?php echo htmlspecialchars($pontuacaoMensal ?? 0); ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-signature mr-2"></i>Assinaturas Pendentes</h6>
                        <?php if (empty($assinaturasPendentes)) : ?>
                            <p class="card-text">Você não tem assinaturas pendentes.</p>
                        <?php else : ?>
                            <ul class="list-group">
                                <?php foreach ($assinaturasPendentes as $assinatura) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            Documento:
                                            <?php echo htmlspecialchars($assinatura['tipo_documento'] ?? 'Tipo de documento não disponível'); ?>
                                        </div>
                                        <div>
                                            <a href="../Processo/pre_visualizar_arquivo.php?arquivo_id=<?php echo htmlspecialchars($assinatura['arquivo_id'] ?? ''); ?>&processo_id=<?php echo htmlspecialchars($assinatura['processo_id'] ?? ''); ?>&estabelecimento_id=<?php echo htmlspecialchars($assinatura['estabelecimento_id'] ?? ''); ?>" class="text-primary"><i class="far fa-eye"></i> Visualizar</a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>


                        <?php endif; ?>
                    </div>
                </div>
            </div>


            <!-- Modal para Inserir Motivo de Rejeição -->
            <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel">Negar Estabelecimento</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="../../controllers/EstabelecimentoController.php?action=rejectEstabelecimento" method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="id" id="rejectEstabelecimentoId">
                                <div class="form-group">
                                    <label for="motivo">Motivo da Rejeição</label>
                                    <textarea class="form-control" id="motivo" name="motivo" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger">Negar Estabelecimento</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($camposIncompletos) : ?>
        <div class="modal fade" id="incompleteProfileModal" tabindex="-1" aria-labelledby="incompleteProfileModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="incompleteProfileModalLabel">Informações Incompletas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Você precisa atualizar suas informações cadastrais. Por favor, complete os campos do cadastro do usuário.</p>
                    </div>
                    <div class="modal-footer">
                        <a href="../Admin/editar_cadastro_usuario.php" class="btn btn-primary">Atualizar Agora</a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                $('#incompleteProfileModal').modal('show');
            });
        </script>
    <?php endif; ?>


    <script>
        var rejectModal = document.getElementById('rejectModal');
        rejectModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget; // Botão que acionou o modal
            var estabelecimentoId = button.getAttribute('data-id'); // Extrair informação dos atributos data-*

            // Atualizar os dados no modal
            var modalBodyInput = rejectModal.querySelector('.modal-body input#rejectEstabelecimentoId');
            modalBodyInput.value = estabelecimentoId;
        });

        function confirmFinalize(processoId) {
            if (confirm("Tem certeza que você resolveu as pendências neste processo?")) {
                window.location.href = '../../controllers/ProcessoController.php?action=finalize&id=' + processoId;
            }
        }
    </script>


    <?php $conn->close(); ?>

</body>

</html>