<?php
session_start();
include '../header.php';

// Verificação de autenticação e nível de acesso
// 1 Administrador, 2 Suporte, 3 Gerente, 4 Fiscal
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php"); // Redirecionar para a página de login se não estiver autenticado ou não for administrador
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/Estabelecimento.php';
require_once '../../models/Processo.php';

$estabelecimento = new Estabelecimento($conn);
$processo = new Processo($conn);

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $dadosEstabelecimento = $estabelecimento->findById($id);

    if (!$dadosEstabelecimento) {
        echo "Estabelecimento não encontrado!";
        exit();
    }

    // Verificar se o usuário tem permissão para acessar o estabelecimento
    $usuarioMunicipio = $_SESSION['user']['municipio'];
    $nivel_acesso = $_SESSION['user']['nivel_acesso'];

    if ($nivel_acesso != 1 && $dadosEstabelecimento['municipio'] !== $usuarioMunicipio) {
        header("Location: listar_estabelecimentos.php?error=" . urlencode("Você não tem permissão para acessar este estabelecimento."));
        exit();
    }

    $processos = $processo->getProcessosByEstabelecimento($id);

    // Nova consulta para obter grupos de risco específicos do município do estabelecimento
    $queryGruposRisco = "
        SELECT 
            gr_fiscal.descricao AS grupo_risco_fiscal,
            gr_secundario.descricao AS grupo_risco_secundario
        FROM 
            estabelecimentos e
        LEFT JOIN 
            atividade_grupo_risco agr_fiscal ON e.cnae_fiscal = agr_fiscal.cnae AND e.municipio = agr_fiscal.municipio
        LEFT JOIN 
            grupo_risco gr_fiscal ON agr_fiscal.grupo_risco_id = gr_fiscal.id
        LEFT JOIN (
            SELECT 
                e.id AS estabelecimento_id, 
                JSON_UNQUOTE(JSON_EXTRACT(cnaes.codigo, '$')) AS cnae_secundario
            FROM 
                estabelecimentos e,
                JSON_TABLE(e.cnaes_secundarios, '$[*]' COLUMNS (codigo VARCHAR(20) PATH '$.codigo')) cnaes
        ) cnae_secundario ON e.id = cnae_secundario.estabelecimento_id
        LEFT JOIN 
            atividade_grupo_risco agr_secundario ON cnae_secundario.cnae_secundario = agr_secundario.cnae AND e.municipio = agr_secundario.municipio
        LEFT JOIN 
            grupo_risco gr_secundario ON agr_secundario.grupo_risco_id = gr_secundario.id
        WHERE 
            e.id = ?
    ";

    $stmt = $conn->prepare($queryGruposRisco);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultGruposRisco = $stmt->get_result();
    $gruposRisco = $resultGruposRisco->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    // Processar os grupos de risco para evitar duplicação
    $gruposRiscoUnicos = [];
    foreach ($gruposRisco as $grupo) {
        if (!empty($grupo['grupo_risco_fiscal'])) {
            $gruposRiscoUnicos[] = $grupo['grupo_risco_fiscal'];
        }
        if (!empty($grupo['grupo_risco_secundario'])) {
            $gruposRiscoUnicos[] = $grupo['grupo_risco_secundario'];
        }
    }
    $gruposRiscoUnicos = array_unique($gruposRiscoUnicos);
} else {
    echo "ID do estabelecimento não fornecido!";
    exit();
}

$qsa = json_decode($dadosEstabelecimento['qsa'], true);
$cnaes_secundarios = json_decode($dadosEstabelecimento['cnaes_secundarios'], true);

function getAnoProcesso($data)
{
    $date = new DateTime($data);
    return $date->format('Y');
}
?>

<style>
    h5 {
        font-size: 16px;
    }

    p {
        margin-top: 0;
        margin-bottom: 8px;
    }

    .row .col-md-6 {
        font-size: 14px;
    }
</style>

<div class="container">
    <div class="row">

        <div class="col-md-3">
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Menu</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="detalhes_estabelecimento.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-info-circle me-2"></i>Detalhes
                    </a>
                    <a href="editar_estabelecimento.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-edit me-2"></i>Editar
                    </a>
                    <a href="atividades.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-tasks me-2"></i>Atividades
                    </a>
                    <a href="responsaveis.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i>Responsáveis
                    </a>
                    <a href="acesso_empresa.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-building me-2"></i>Acesso Empresa
                    </a>
                    <a href="../Processo/processos.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-folder-open me-2"></i>Processos
                    </a>
                </div>
            </div>
            <div style="padding-top:5px;">
                <!-- EXLCUIR ESTABELECIMENTO -->
                <?php if ($_SESSION['user']['nivel_acesso'] == 1) : // Apenas administradores podem excluir 
                ?>
                    <form method="POST" action="excluir_estabelecimento.php" onsubmit="return confirm('Você tem certeza que deseja excluir este estabelecimento?');">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <button type="submit" class="btn btn-danger">Excluir Estabelecimento</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Dados do Estabelecimento</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>CNPJ:</strong> <?php echo htmlspecialchars($dadosEstabelecimento['cnpj']); ?></p>
                            <p><strong>Nome Fantasia:</strong> <?php echo htmlspecialchars($dadosEstabelecimento['nome_fantasia']); ?></p>
                            <p><strong>Razão Social:</strong> <?php echo htmlspecialchars($dadosEstabelecimento['razao_social']); ?></p>
                            <p><strong>Data Início Atividade:</strong> <?php echo htmlspecialchars((new DateTime($dadosEstabelecimento['data_inicio_atividade']))->format('d/m/Y')); ?></p>
                            <p><strong>Situação Cadastral:</strong>
                                <span style="
                             display: inline-block;
                             padding: 2px 8px;
                             color: white;
                             background-color: <?php
                                                $situacao = htmlspecialchars($dadosEstabelecimento['descricao_situacao_cadastral']);
                                                if ($situacao == 'ATIVA') {
                                                    echo 'green';
                                                } elseif (in_array($situacao, ['SUSPENSA', 'BAIXADA'])) {
                                                    echo 'red';
                                                } else {
                                                    echo 'orange';
                                                }
                                                ?>;
                            border-radius: 5px;">
                                    <?php echo $situacao; ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Endereço:</strong> <?php echo htmlspecialchars($dadosEstabelecimento['logradouro'] . ', ' . $dadosEstabelecimento['numero'] . ' - ' . $dadosEstabelecimento['bairro'] . ', ' . $dadosEstabelecimento['municipio'] . ' - ' . $dadosEstabelecimento['uf'] . ', ' . $dadosEstabelecimento['cep']); ?></p>
                            <p><strong>Telefone 1:</strong> <?php echo htmlspecialchars($dadosEstabelecimento['ddd_telefone_1'] === 'Não Informado' ? $dadosEstabelecimento['ddd_telefone_1'] : sprintf("(%s) %s-%s", substr($dadosEstabelecimento['ddd_telefone_1'], 0, 2), substr($dadosEstabelecimento['ddd_telefone_1'], 2, 4), substr($dadosEstabelecimento['ddd_telefone_1'], 6))); ?></p>
                            <p><strong>Telefone 2:</strong> <?php echo htmlspecialchars($dadosEstabelecimento['ddd_telefone_2'] === 'Não Informado' ? $dadosEstabelecimento['ddd_telefone_2'] : sprintf("(%s) %s-%s", substr($dadosEstabelecimento['ddd_telefone_2'], 0, 2), substr($dadosEstabelecimento['ddd_telefone_2'], 2, 4), substr($dadosEstabelecimento['ddd_telefone_2'], 6))); ?></p>
                            <p><strong>Natureza Jurídica:</strong> <?php echo htmlspecialchars($dadosEstabelecimento['natureza_juridica']); ?></p>
                        </div>
                    </div>
                </div>
            </div>


            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Informações Infovisa</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Data de cadastro:</strong> <?php echo htmlspecialchars((new DateTime($dadosEstabelecimento['data_cadastro']))->format('d/m/Y')); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($gruposRiscoUnicos)) : ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Grupos de Risco do Estabelecimento</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($gruposRiscoUnicos as $grupo) : ?>
                                <li class="list-group-item"><?php echo htmlspecialchars($grupo); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Processos do Estabelecimento</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($processos)) : ?>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                            <?php foreach ($processos as $proc) : ?>
                                <div class="col">
                                    <div class="card h-100">
                                        <div class="card-body" style="font-size:12px;">
                                            <h6 class="card-title text-center"><?php echo htmlspecialchars($proc['numero_processo']); ?></h6>
                                            <p class="card-text mb-1"><strong>Tipo Processo:</strong> <?php echo htmlspecialchars($proc['tipo_processo']); ?></p>
                                            <p class="card-text"><strong>Data Autuação:</strong> <?php echo htmlspecialchars((new DateTime($proc['data_abertura']))->format('d/m/Y')); ?></p>
                                            <div class="d-grid gap-2">
                                                <a href="../Processo/documentos.php?processo_id=<?php echo $proc['id']; ?>&id=<?php echo $id; ?>" class="btn btn-primary">Ver Processo</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p>Nenhum processo encontrado para este estabelecimento.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>