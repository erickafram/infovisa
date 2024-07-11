<?php
session_start();


require_once '../../conf/database.php';
require_once '../../models/Estabelecimento.php';
require_once '../../models/Processo.php';
require_once '../../models/ResponsavelLegal.php';
require_once '../../models/ResponsavelTecnico.php';

$estabelecimentoModel = new Estabelecimento($conn);
$processoModel = new Processo($conn);
$responsavelLegalModel = new ResponsavelLegal($conn);
$responsavelTecnicoModel = new ResponsavelTecnico($conn);

$userId = $_SESSION['user']['id'];
$vinculosEstabelecimentos = $estabelecimentoModel->getEstabelecimentosByUsuario($userId);

if (isset($_GET['id'])) {
    $estabelecimentoId = $_GET['id'];
    $dadosEstabelecimento = $estabelecimentoModel->findById($estabelecimentoId);

    if (!$dadosEstabelecimento || !in_array($estabelecimentoId, array_column($vinculosEstabelecimentos, 'id'))) {
        echo "Estabelecimento não encontrado ou acesso negado!";
        exit();
    }

    // Buscar processos vinculados ao estabelecimento
    $processos = $estabelecimentoModel->getProcessosByEstabelecimento($estabelecimentoId);

    // Buscar responsáveis vinculados ao estabelecimento
    $responsaveisLegais = $responsavelLegalModel->getByEstabelecimento($estabelecimentoId);
    $responsaveisTecnicos = $responsavelTecnicoModel->getByEstabelecimento($estabelecimentoId);

    // Verificação de criação de novo processo
    // Verificação de criação de novo processo
    if (isset($_POST['criar_processo'])) {
        $tipoProcesso = $_POST['tipo_processo'];

        if (empty($responsaveisLegais)) {
            $mensagemErro = "Por favor, cadastre um responsável legal antes de criar um novo processo.";
        } else {
            $anoAtual = date('Y');
            $processosAnoAtual = array_filter($processos, function ($processo) use ($anoAtual, $tipoProcesso) {
                return date('Y', strtotime($processo['data_abertura'])) == $anoAtual && $processo['tipo_processo'] == $tipoProcesso;
            });

            if (!empty($processosAnoAtual)) {
                $mensagemErro = "Já existe um processo de $tipoProcesso criado para este ano.";
            } else {
                if ($tipoProcesso == 'LICENCIAMENTO') {
                    $processoModel->createProcessoLicenciamento($estabelecimentoId);
                } elseif ($tipoProcesso == 'PROJETO ARQUITETÔNICO') {
                    $processoModel->createProcessoProjetoArquitetonico($estabelecimentoId);
                }
                header("Location: detalhes_estabelecimento_empresa.php?id=$estabelecimentoId");
                exit();
            }
        }
    }



    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['add_legal'])) {
            $cpf = $_POST['cpf'];
            $responsavelExistente = $responsavelLegalModel->findByCpf($cpf);

            if ($responsavelExistente) {
                $responsavelLegalModel->create($estabelecimentoId, $responsavelExistente['nome'], $responsavelExistente['cpf'], $responsavelExistente['email'], $responsavelExistente['telefone'], $responsavelExistente['documento_identificacao']);
            } else {
                $nome = $_POST['nome'];
                $email = $_POST['email'];
                $telefone = $_POST['telefone'];
                $documento_identificacao = $_FILES['documento_identificacao']['name'];
                $target_dir = "../../uploads/";
                $target_file = $target_dir . basename($_FILES["documento_identificacao"]["name"]);
                move_uploaded_file($_FILES["documento_identificacao"]["tmp_name"], $target_file);

                $responsavelLegalModel->create($estabelecimentoId, $nome, $cpf, $email, $telefone, $documento_identificacao);
            }
            header("Location: detalhes_estabelecimento_empresa.php?id=$estabelecimentoId");
            exit();
        } elseif (isset($_POST['add_tecnico'])) {
            $cpf = $_POST['cpf'];
            $responsavelExistente = $responsavelTecnicoModel->findByCpf($cpf);

            if ($responsavelExistente) {
                $responsavelTecnicoModel->create($estabelecimentoId, $responsavelExistente['nome'], $responsavelExistente['cpf'], $responsavelExistente['email'], $responsavelExistente['telefone'], $responsavelExistente['conselho'], $responsavelExistente['numero_registro_conselho'], $responsavelExistente['carteirinha_conselho']);
            } else {
                $nome = $_POST['nome'];
                $email = $_POST['email'];
                $telefone = $_POST['telefone'];
                $conselho = $_POST['conselho'];
                $numero_registro_conselho = $_POST['numero_registro_conselho'];
                $carteirinha_conselho = $_FILES['carteirinha_conselho']['name'];
                $target_dir = "../../uploads/";
                $target_file = $target_dir . basename($_FILES["carteirinha_conselho"]["name"]);
                move_uploaded_file($_FILES["carteirinha_conselho"]["tmp_name"], $target_file);

                $responsavelTecnicoModel->create($estabelecimentoId, $nome, $cpf, $email, $telefone, $conselho, $numero_registro_conselho, $carteirinha_conselho);
            }
            header("Location: detalhes_estabelecimento_empresa.php?id=$estabelecimentoId");
            exit();
        }
    }
} else {
    echo "ID do estabelecimento não fornecido!";
    exit();
}

include '../../includes/header_empresa.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Estabelecimento</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card-title {
            font-weight: bold;
            color: #333;
            font-size: 15px;
        }

        .form-control {
            font-size: 13px;
        }

        strong {
            font-size: 13px;
        }

        .info-list {
            display: flex;
            flex-wrap: wrap;
            list-style-type: none;
            padding-left: 0;
            font-size: 14px;
        }

        .info-item {
            flex: 1 1 50%;
            padding: 5px;
        }

        .info-item strong {
            display: inline-block;
            width: 150px;
        }

        p {
            margin-top: 0;
            margin-bottom: 0rem;
        }
    </style>
</head>

<body>

    <div class="container mt-4">
        <div class="row">
            <!-- Coluna esquerda -->
            <div class="col-md-8">
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Informações do Estabelecimento</h5>
                        <ul class="list-group info-list">
                            <li class="list-group-item info-item"><strong>Nome Fantasia:</strong> <?php echo htmlspecialchars($dadosEstabelecimento['nome_fantasia']); ?></li>
                            <li class="list-group-item info-item"><strong>Razão Social:</strong> <?php echo htmlspecialchars($dadosEstabelecimento['razao_social']); ?></li>
                            <li class="list-group-item info-item"><strong>CNPJ:</strong> <?php echo htmlspecialchars($dadosEstabelecimento['cnpj']); ?></li>
                            <li class="list-group-item info-item"><strong>Endereço:</strong> <?php echo htmlspecialchars($dadosEstabelecimento['logradouro']) . ', ' . htmlspecialchars($dadosEstabelecimento['numero']) . ', ' . htmlspecialchars($dadosEstabelecimento['bairro']) . ', ' . htmlspecialchars($dadosEstabelecimento['municipio']) . ' - ' . htmlspecialchars($dadosEstabelecimento['uf']) . ', ' . htmlspecialchars($dadosEstabelecimento['cep']); ?></li>
                            <li class="list-group-item info-item"><strong>Telefone:</strong> <?php echo htmlspecialchars($dadosEstabelecimento['ddd_telefone_1']); ?></li>
                            <li class="list-group-item info-item"><strong>Situação Cadastral:</strong> <?php echo htmlspecialchars($dadosEstabelecimento['descricao_situacao_cadastral']); ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Coluna direita -->
            <div class="col-md-4">
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title">Criar Novo Processo</h6>
                        <?php if (isset($mensagemErro)) : ?>
                            <div class="alert alert-danger"><?php echo $mensagemErro; ?></div>
                        <?php endif; ?>
                        <form action="detalhes_estabelecimento_empresa.php?id=<?php echo $estabelecimentoId; ?>" method="POST">
                            <div class="mb-3">
                                <select class="form-select" id="tipo_processo" name="tipo_processo" required>
                                    <option value="" selected disabled>SELECIONE O TIPO DE PROCESSO</option>
                                    <option value="LICENCIAMENTO">LICENCIAMENTO</option>
                                    <option value="PROJETO ARQUITETÔNICO">PROJETO ARQUITETÔNICO</option>
                                </select>
                            </div>
                            <button type="submit" name="criar_processo" class="btn btn-primary btn-sm">Criar Processo</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Seção para exibir processos vinculados -->
            <div class="container mt-4 border p-4">
                <h6 class="card-title" style="margin-bottom:15px;">Processos do Estabelecimento</h6>
                <?php if (!empty($processos)) : ?>
                    <div class="row">
                        <?php foreach ($processos as $processo) : ?>
                            <div class="col-md-3 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <center>
                                            <h6 class="card-title" style="font-size:13px; color: #0d6efd;"><?php echo htmlspecialchars($processo['tipo_processo']); ?></h6>
                                        </center>
                                        <p class="card-text small"><strong>N° Processo:</strong> <?php echo htmlspecialchars($processo['numero_processo']); ?></p>
                                        <p class="card-text small"><strong>Data de Abertura:</strong> <?php echo date('d/m/Y', strtotime($processo['data_abertura'])); ?></p>
                                        <p class="card-text small"><strong>Status:</strong>
                                            <?php if ($processo['status'] === 'PARADO') : ?>
                                                <span style="
                                        display: inline-block;
                                        padding: 2px 8px;
                                        color: white;
                                        background-color: red;
                                        border-radius: 5px;">
                                                    PARADO
                                                </span>
                                            <?php elseif ($processo['status'] === 'ATIVO') : ?>
                                                <span style="
                                        display: inline-block;
                                        padding: 2px 8px;
                                        color: white;
                                        background-color: green;
                                        border-radius: 5px;">
                                                    EM ANDAMENTO
                                                </span>
                                            <?php elseif ($processo['status'] === 'ARQUIVADO') : ?>
                                                <span style="
                                        display: inline-block;
                                        padding: 2px 8px;
                                        color: white;
                                        background-color: orange;
                                        border-radius: 5px;">
                                                    ARQUIVADO
                                                </span>
                                            <?php else : ?>
                                                <?php echo htmlspecialchars($processo['status']); ?>
                                            <?php endif; ?>
                                        </p>
                                        <center><a href="../Processo/detalhes_processo_empresa.php?id=<?php echo htmlspecialchars($processo['id']); ?>" class="btn btn-primary btn-sm" style="margin-top:15px;">Ver Processo</a></center>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p>Nenhum processo encontrado para este estabelecimento.</p>
                <?php endif; ?>
            </div>


            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title">Responsáveis pelo Estabelecimento</h6>
                    <button class="btn btn-primary btn-sm mb-4" data-bs-toggle="modal" data-bs-target="#modalEscolherResponsavel">Adicionar Responsável</button>

                    <!-- Lista de Responsáveis Legais -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">Responsáveis Legais</h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($responsaveisLegais as $responsavel) : ?>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>Nome:</strong></label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($responsavel['nome']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>CPF:</strong></label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($responsavel['cpf']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>Email:</strong></label>
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($responsavel['email']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>Telefone:</strong></label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($responsavel['telefone']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>Documento de Identificação:</strong></label>
                                        <?php if (!empty($responsavel['documento_identificacao'])) : ?>
                                            <a href="../../uploads/<?php echo htmlspecialchars($responsavel['documento_identificacao']); ?>" target="_blank"><?php echo htmlspecialchars($responsavel['documento_identificacao']); ?></a>
                                        <?php else : ?>
                                            <span>Nenhum documento</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-6" style="padding-top:10px;">
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditarResponsavelLegal" data-id="<?php echo $responsavel['id']; ?>" data-nome="<?php echo $responsavel['nome']; ?>" data-cpf="<?php echo $responsavel['cpf']; ?>" data-email="<?php echo $responsavel['email']; ?>" data-telefone="<?php echo $responsavel['telefone']; ?>" data-documento="<?php echo $responsavel['documento_identificacao']; ?>">Editar</button>
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalExcluirResponsavelLegal" data-id="<?php echo $responsavel['id']; ?>">Excluir</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Lista de Responsáveis Técnicos -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">Responsáveis Técnicos</h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($responsaveisTecnicos as $responsavel) : ?>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>Nome:</strong></label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($responsavel['nome']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>CPF:</strong></label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($responsavel['cpf']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>Email:</strong></label>
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($responsavel['email']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>Telefone:</strong></label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($responsavel['telefone']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>Conselho:</strong></label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($responsavel['conselho']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>Número do Registro do Conselho:</strong></label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($responsavel['numero_registro_conselho']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>Carteirinha do Conselho:</strong></label>
                                        <a href="../../uploads/<?php echo htmlspecialchars($responsavel['carteirinha_conselho']); ?>" target="_blank"><?php echo htmlspecialchars($responsavel['carteirinha_conselho']); ?></a>
                                    </div>
                                    <div class="col-md-6" style="margin-top:10px;">
                                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditarResponsavelTecnico" data-id="<?php echo $responsavel['id']; ?>" data-nome="<?php echo $responsavel['nome']; ?>" data-cpf="<?php echo $responsavel['cpf']; ?>" data-email="<?php echo $responsavel['email']; ?>" data-telefone="<?php echo $responsavel['telefone']; ?>" data-conselho="<?php echo $responsavel['conselho']; ?>" data-numero-registro="<?php echo $responsavel['numero_registro_conselho']; ?>" data-carteirinha="<?php echo $responsavel['carteirinha_conselho']; ?>">Editar</button>
                                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalExcluirResponsavelTecnico" data-id="<?php echo $responsavel['id']; ?>">Excluir</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- Modal para escolher tipo de Responsável -->
                    <div class="modal fade" id="modalEscolherResponsavel" tabindex="-1" aria-labelledby="modalEscolherResponsavelLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalEscolherResponsavelLabel">Adicionar Responsável</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <button class="btn btn-primary btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#modalAddLegal" data-bs-dismiss="modal">Responsável Legal</button>
                                    <button class="btn btn-primary btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#modalAddTecnico" data-bs-dismiss="modal">Responsável Técnico</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal para adicionar Responsável Legal -->
                    <div class="modal fade" id="modalAddLegal" tabindex="-1" aria-labelledby="modalAddLegalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalAddLegalLabel">Adicionar Responsável Legal</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="formAddLegal" action="detalhes_estabelecimento_empresa.php?id=<?php echo $estabelecimentoId; ?>" method="POST" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="cpf" class="form-label">CPF</label>
                                            <input type="text" class="form-control" id="cpfLegal" name="cpf" required maxlength="11">
                                            <small class="form-text text-muted">Digite apenas números, sem ponto ou hífen.</small>
                                            <button type="button" class="btn btn-secondary mt-2" id="buscarCpfLegal">Buscar CPF</button>
                                        </div>

                                        <div id="alertLegal" class="alert alert-info" style="display:none;">
                                            Responsável Legal já cadastrado e vinculado ao estabelecimento.
                                        </div>
                                        <div id="legalFields" style="display: none;">
                                            <div class="mb-3">
                                                <label for="nomeLegal" class="form-label">Nome</label>
                                                <input type="text" class="form-control" id="nomeLegal" name="nome" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="emailLegal" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="emailLegal" name="email" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="telefoneLegal" class="form-label">Telefone</label>
                                                <input type="text" class="form-control" id="telefoneLegal" name="telefone" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="documento_identificacaoLegal" class="form-label">Documento de Identificação</label>
                                                <input type="file" class="form-control" id="documento_identificacaoLegal" name="documento_identificacao" required>
                                            </div>
                                        </div>
                                        <input type="hidden" name="add_legal" value="1">
                                        <button type="submit" class="btn btn-primary btn-sm" id="btnAddLegal" style="display: none;">Adicionar Responsável</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal para adicionar Responsável Técnico -->
                    <div class="modal fade" id="modalAddTecnico" tabindex="-1" aria-labelledby="modalAddTecnicoLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalAddTecnicoLabel">Adicionar Responsável Técnico</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="formAddTecnico" action="detalhes_estabelecimento_empresa.php?id=<?php echo $estabelecimentoId; ?>" method="POST" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="cpfTecnico" class="form-label">CPF</label>
                                            <input type="text" class="form-control" id="cpfTecnico" name="cpf" required maxlength="11">
                                            <small class="form-text text-muted">Digite apenas números, sem ponto ou hífen.</small>
                                            <button type="button" class="btn btn-secondary mt-2" id="buscarCpfTecnico">Buscar CPF</button>
                                        </div>
                                        <div id="alertTecnico" class="alert alert-info" style="display:none;">
                                            Responsável Técnico já cadastrado e vinculado ao estabelecimento.
                                        </div>
                                        <div id="tecnicoFields" style="display: none;">
                                            <div class="mb-3">
                                                <label for="nomeTecnico" class="form-label">Nome</label>
                                                <input type="text" class="form-control" id="nomeTecnico" name="nome" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="emailTecnico" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="emailTecnico" name="email" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="telefoneTecnico" class="form-label">Telefone</label>
                                                <input type="text" class="form-control" id="telefoneTecnico" name="telefone" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="conselho" class="form-label">Conselho</label>
                                                <select class="form-control" id="conselho" name="conselho" required>
                                                    <option value="CRM">CRM</option>
                                                    <option value="CRF">CRF</option>
                                                    <!-- Adicione outros conselhos conforme necessário -->
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="numero_registro_conselho" class="form-label">Número do Registro do Conselho</label>
                                                <input type="text" class="form-control" id="numero_registro_conselho" name="numero_registro_conselho" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="carteirinha_conselho" class="form-label">Carteirinha do Conselho</label>
                                                <input type="file" class="form-control" id="carteirinha_conselho" name="carteirinha_conselho" required>
                                            </div>
                                        </div>
                                        <input type="hidden" name="add_tecnico" value="1">
                                        <button type="submit" class="btn btn-primary btn-sm" id="btnAddTecnico" style="display: none;">Adicionar Responsável</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal para editar Responsável Legal -->
                    <div class="modal fade" id="modalEditarResponsavelLegal" tabindex="-1" aria-labelledby="modalEditarResponsavelLegalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalEditarResponsavelLegalLabel">Editar Responsável Legal</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="formEditarLegal" action="editar_responsavel_legal.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" id="editLegalId">
                                        <input type="hidden" name="estabelecimento_id" value="<?php echo $estabelecimentoId; ?>">
                                        <input type="hidden" name="documento_atual" id="editLegalDocumentoAtual">
                                        <div class="mb-3">
                                            <label for="editLegalNome" class="form-label">Nome</label>
                                            <input type="text" class="form-control" id="editLegalNome" name="nome" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editLegalCpf" class="form-label">CPF</label>
                                            <input type="text" class="form-control" id="editLegalCpf" name="cpf" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editLegalEmail" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="editLegalEmail" name="email" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editLegalTelefone" class="form-label">Telefone</label>
                                            <input type="text" class="form-control" id="editLegalTelefone" name="telefone" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editLegalDocumento" class="form-label">Documento de Identificação</label>
                                            <input type="file" class="form-control" id="editLegalDocumento" name="documento_identificacao">
                                            <small class="form-text text-muted" id="documentoAtualText"></small>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">Salvar Alterações</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- Modal para excluir Responsável Legal -->
                    <div class="modal fade" id="modalExcluirResponsavelLegal" tabindex="-1" aria-labelledby="modalExcluirResponsavelLegalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalExcluirResponsavelLegalLabel">Excluir Responsável Legal</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="formExcluirLegal" action="excluir_responsavel_legal.php" method="POST">
                                        <input type="hidden" name="id" id="deleteLegalId">
                                        <input type="hidden" name="estabelecimento_id" value="<?php echo $estabelecimentoId; ?>">
                                        <p>Tem certeza de que deseja excluir este responsável legal?</p>
                                        <button type="submit" class="btn btn-danger">Excluir</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Modal para editar Responsável Técnico -->
                    <div class="modal fade" id="modalEditarResponsavelTecnico" tabindex="-1" aria-labelledby="modalEditarResponsavelTecnicoLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalEditarResponsavelTecnicoLabel">Editar Responsável Técnico</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="formEditarTecnico" action="editar_responsavel_tecnico.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" id="editTecnicoId">
                                        <input type="hidden" name="estabelecimento_id" value="<?php echo $estabelecimentoId; ?>">
                                        <input type="hidden" name="carteirinha_atual" id="editTecnicoCarteirinhaAtual">
                                        <div class="mb-3">
                                            <label for="editTecnicoNome" class="form-label">Nome</label>
                                            <input type="text" class="form-control" id="editTecnicoNome" name="nome" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editTecnicoCpf" class="form-label">CPF</label>
                                            <input type="text" class="form-control" id="editTecnicoCpf" name="cpf" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editTecnicoEmail" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="editTecnicoEmail" name="email" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editTecnicoTelefone" class="form-label">Telefone</label>
                                            <input type="text" class="form-control" id="editTecnicoTelefone" name="telefone" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editTecnicoConselho" class="form-label">Conselho</label>
                                            <input type="text" class="form-control" id="editTecnicoConselho" name="conselho" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editTecnicoNumeroRegistro" class="form-label">Número do Registro do Conselho</label>
                                            <input type="text" class="form-control" id="editTecnicoNumeroRegistro" name="numero_registro_conselho" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editTecnicoCarteirinha" class="form-label">Carteirinha do Conselho</label>
                                            <input type="file" class="form-control" id="editTecnicoCarteirinha" name="carteirinha_conselho">
                                            <small class="form-text text-muted" id="carteirinhaAtualText"></small>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">Salvar Alterações</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- Modal para excluir Responsável Técnico -->
                    <div class="modal fade" id="modalExcluirResponsavelTecnico" tabindex="-1" aria-labelledby="modalExcluirResponsavelTecnicoLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalExcluirResponsavelTecnicoLabel">Excluir Responsável Técnico</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="formExcluirTecnico" action="excluir_responsavel_tecnico.php" method="POST">
                                        <input type="hidden" name="id" id="deleteTecnicoId">
                                        <input type="hidden" name="estabelecimento_id" value="<?php echo $estabelecimentoId; ?>">
                                        <p>Tem certeza de que deseja excluir este responsável técnico?</p>
                                        <button type="submit" class="btn btn-danger">Excluir</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>


                    <script>
                        document.getElementById('cpfLegal').addEventListener('input', function(event) {
                            var value = event.target.value;
                            event.target.value = value.replace(/[^0-9]/g, ''); // Remove caracteres não numéricos
                        });

                        document.getElementById('cpfTecnico').addEventListener('input', function(event) {
                            var value = event.target.value;
                            event.target.value = value.replace(/[^0-9]/g, ''); // Remove caracteres não numéricos
                        });

                        // Função para buscar CPF e exibir campos adicionais para Responsável Legal
                        document.getElementById('buscarCpfLegal').addEventListener('click', function() {
                            var cpf = document.getElementById('cpfLegal').value;
                            fetch('verificar_cpf.php?cpf=' + cpf + '&tipo=legal')
                                .then(response => response.json())
                                .then(data => {
                                    if (data.existe) {
                                        document.getElementById('nomeLegal').value = data.nome;
                                        document.getElementById('emailLegal').value = data.email;
                                        document.getElementById('telefoneLegal').value = data.telefone;
                                        document.getElementById('documento_identificacaoLegal').required = false;
                                        document.getElementById('legalFields').style.display = 'none';
                                        document.getElementById('alertLegal').style.display = 'block';
                                    } else {
                                        document.getElementById('legalFields').style.display = 'block';
                                        document.getElementById('documento_identificacaoLegal').required = true;
                                        document.getElementById('alertLegal').style.display = 'none';
                                    }
                                    toggleRequiredFields('legalFields', !data.existe);
                                    document.getElementById('btnAddLegal').style.display = 'block';
                                });
                        });

                        // Função para buscar CPF e exibir campos adicionais para Responsável Técnico
                        document.getElementById('buscarCpfTecnico').addEventListener('click', function() {
                            var cpf = document.getElementById('cpfTecnico').value;
                            fetch('verificar_cpf.php?cpf=' + cpf + '&tipo=tecnico')
                                .then(response => response.json())
                                .then(data => {
                                    if (data.existe) {
                                        document.getElementById('nomeTecnico').value = data.nome;
                                        document.getElementById('emailTecnico').value = data.email;
                                        document.getElementById('telefoneTecnico').value = data.telefone;
                                        document.getElementById('conselho').value = data.conselho;
                                        document.getElementById('numero_registro_conselho').value = data.numero_registro_conselho;
                                        document.getElementById('carteirinha_conselho').required = false;
                                        document.getElementById('tecnicoFields').style.display = 'none';
                                        document.getElementById('alertTecnico').style.display = 'block';
                                    } else {
                                        document.getElementById('tecnicoFields').style.display = 'block';
                                        document.getElementById('carteirinha_conselho').required = true;
                                        document.getElementById('alertTecnico').style.display = 'none';
                                    }
                                    toggleRequiredFields('tecnicoFields', !data.existe);
                                    document.getElementById('btnAddTecnico').style.display = 'block';
                                });
                        });

                        // Função para alternar os atributos "required" dos campos
                        function toggleRequiredFields(containerId, isRequired) {
                            var container = document.getElementById(containerId);
                            var fields = container.querySelectorAll('input, select');
                            fields.forEach(function(field) {
                                if (isRequired) {
                                    field.setAttribute('required', 'required');
                                } else {
                                    field.removeAttribute('required');
                                }
                            });
                        }

                        // Passar dados para o modal de edição e exclusão
                        $('#modalEditarResponsavelLegal').on('show.bs.modal', function(event) {
                            var button = $(event.relatedTarget);
                            var id = button.data('id');
                            var nome = button.data('nome');
                            var cpf = button.data('cpf');
                            var email = button.data('email');
                            var telefone = button.data('telefone');
                            var documento = button.data('documento');

                            var modal = $(this);
                            modal.find('#editLegalId').val(id);
                            modal.find('#editLegalNome').val(nome);
                            modal.find('#editLegalCpf').val(cpf);
                            modal.find('#editLegalEmail').val(email);
                            modal.find('#editLegalTelefone').val(telefone);
                            modal.find('#editLegalDocumentoAtual').val(documento);
                            if (documento) {
                                modal.find('#documentoAtualText').text('Documento Atual: ' + documento);
                            }
                        });

                        $('#modalExcluirResponsavelLegal').on('show.bs.modal', function(event) {
                            var button = $(event.relatedTarget);
                            var id = button.data('id');

                            var modal = $(this);
                            modal.find('#deleteLegalId').val(id);
                        });

                        $('#modalEditarResponsavelTecnico').on('show.bs.modal', function(event) {
                            var button = $(event.relatedTarget);
                            var id = button.data('id');
                            var nome = button.data('nome');
                            var cpf = button.data('cpf');
                            var email = button.data('email');
                            var telefone = button.data('telefone');
                            var conselho = button.data('conselho');
                            var numeroRegistro = button.data('numero-registro');
                            var carteirinha = button.data('carteirinha');

                            var modal = $(this);
                            modal.find('#editTecnicoId').val(id);
                            modal.find('#editTecnicoNome').val(nome);
                            modal.find('#editTecnicoCpf').val(cpf);
                            modal.find('#editTecnicoEmail').val(email);
                            modal.find('#editTecnicoTelefone').val(telefone);
                            modal.find('#editTecnicoConselho').val(conselho);
                            modal.find('#editTecnicoNumeroRegistro').val(numeroRegistro);
                            modal.find('#editTecnicoCarteirinhaAtual').val(carteirinha);
                            if (carteirinha) {
                                modal.find('#carteirinhaAtualText').text('Carteirinha Atual: ' + carteirinha);
                            }
                        });

                        $('#modalExcluirResponsavelTecnico').on('show.bs.modal', function(event) {
                            var button = $(event.relatedTarget);
                            var id = button.data('id');

                            var modal = $(this);
                            modal.find('#deleteTecnicoId').val(id);
                        });
                    </script>

                    <?php include '../footer.php'; ?>
</body>

</html>