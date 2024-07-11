<?php
session_start();
require_once '../../conf/database.php';
require_once '../../models/OrdemServico.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php");
    exit();
}

// Finalizar a Ordem de Serviço
if (isset($_POST['finalizar']) && isset($_POST['descricao_encerramento']) && isset($_GET['id'])) {
    $ordemServico = new OrdemServico($conn);
    $descricao_encerramento = $_POST['descricao_encerramento'];
    if ($ordemServico->finalizarOrdem($_GET['id'], $descricao_encerramento)) {
        header("Location: detalhes_ordem_sem_estabelecimento.php?id=" . $_GET['id'] . "&success=Ordem de serviço finalizada com sucesso.");
        exit();
    } else {
        $error = "Erro ao finalizar a ordem de serviço: " . $ordemServico->getLastError();
    }
}

// Reiniciar a Ordem de Serviço
if (isset($_POST['reiniciar']) && isset($_GET['id'])) {
    $ordemServico = new OrdemServico($conn);
    if ($ordemServico->reiniciarOrdem($_GET['id'])) {
        header("Location: detalhes_ordem_sem_estabelecimento.php?id=" . $_GET['id'] . "&success=Ordem de serviço reiniciada com sucesso.");
        exit();
    } else {
        $error = "Erro ao reiniciar a ordem de serviço: " . $ordemServico->getLastError();
    }
}

include '../header.php';

if (!isset($_GET['id'])) {
    header("Location: listar_ordens.php");
    exit();
}

$ordemServico = new OrdemServico($conn);
$ordem = $ordemServico->getOrdemById($_GET['id']);

if (!$ordem) {
    header("Location: listar_ordens.php");
    exit();
}

$acoes_ids = json_decode($ordem['acoes_executadas'], true);
$acoes_nomes = $ordemServico->getAcoesNomes($acoes_ids);

function formatDate($date)
{
    $dateTime = new DateTime($date);
    return $dateTime->format('d/m/Y');
}
?>

<div class="container mt-5">
    <h4 class="mb-4">Detalhes da Ordem de Serviço (Sem Estabelecimento)</h4>

    <?php if (isset($_GET['success'])) : ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <td><?php echo isset($ordem['id']) ? htmlspecialchars($ordem['id']) : 'N/A'; ?></td>
        </tr>
        <tr>
            <th>Data Início</th>
            <td><?php echo isset($ordem['data_inicio']) ? htmlspecialchars(formatDate($ordem['data_inicio'])) : 'N/A'; ?></td>
        </tr>
        <tr>
            <th>Data Fim</th>
            <td><?php echo isset($ordem['data_fim']) ? htmlspecialchars(formatDate($ordem['data_fim'])) : 'N/A'; ?></td>
        </tr>
        <tr>
            <th>Técnicos</th>
            <td><?php echo isset($ordem['tecnicos_nomes']) ? htmlspecialchars(implode(', ', $ordem['tecnicos_nomes'])) : 'N/A'; ?></td>
        </tr>
        <tr>
            <th>Ações Executadas</th>
            <td>
                <?php
                $acoes_executadas_nomes = [];
                foreach ($acoes_ids as $acao_id) {
                    $acoes_executadas_nomes[] = $acoes_nomes[$acao_id];
                }
                echo htmlspecialchars(implode(', ', $acoes_executadas_nomes));
                ?>
            </td>
            <tr>
    <th>Observação</th>
    <td><?php echo htmlspecialchars($ordem['observacao']); ?></td>
</tr>
        </tr>

        <tr>
            <th>Status</th>
            <td>
                <?php if ($ordem['status'] == 'ativa') : ?>
                    <span class="badge bg-success">Ativa</span>
                <?php elseif ($ordem['status'] == 'finalizada') : ?>
                    <span class="badge bg-danger">Finalizada</span>
                <?php else : ?>
                    <?php echo htmlspecialchars($ordem['status']); ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php if (!empty($ordem['descricao_encerramento'])) : ?>
            <tr>
                <th>Descrição do Encerramento</th>
                <td><?php echo htmlspecialchars($ordem['descricao_encerramento']); ?></td>
            </tr>
        <?php endif; ?>
        <?php if (is_null($ordem['estabelecimento_id']) || is_null($ordem['processo_id'])) : ?>
            <?php if ($ordem['status'] != 'finalizada') : ?>
                <tr>
                    <th>Vincular Estabelecimento</th>
                    <td>
                        <a href="vincular_ordem.php?id=<?php echo htmlspecialchars($ordem['id']); ?>" class="btn btn-warning btn-sm">
                            Vincular Estabelecimento
                        </a>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Adicionar botões de Editar e Excluir -->
        <?php if ($ordem['status'] != 'finalizada') : ?>
            <tr>
                <th>Ações</th>
                <td>
                    <a href="excluir_ordem.php?id=<?php echo htmlspecialchars($ordem['id']); ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta ordem de serviço?')">Excluir</a>
                </td>
            </tr>
        <?php endif; ?>
    </table>

    <form method="POST">
        <?php if ($ordem['status'] != 'finalizada') : ?>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#finalizarModal">Finalizar</button>
        <?php endif; ?>
        <?php if ($ordem['status'] == 'finalizada') : ?>
            <button type="submit" name="reiniciar" class="btn btn-warning">Reiniciar</button>
        <?php endif; ?>
        <a href="listar_ordens.php" class="btn btn-primary">Voltar</a>
    </form>
</div>

<!-- Modal -->
<div class="modal fade" id="finalizarModal" tabindex="-1" aria-labelledby="finalizarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="finalizarModalLabel">Descrição do Encerramento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="descricao_encerramento" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao_encerramento" name="descricao_encerramento" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="finalizar" class="btn btn-danger">Finalizar Ordem de Serviço</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
