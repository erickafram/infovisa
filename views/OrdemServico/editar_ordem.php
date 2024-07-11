<?php
session_start();
ob_start();
include '../header.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 3])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/OrdemServico.php';

$ordemServico = new OrdemServico($conn);

if (!isset($_GET['id'])) {
    echo "ID da ordem de serviço não fornecido!";
    exit();
}

$id = $_GET['id'];
$ordem = $ordemServico->getOrdemById($id);

if (!$ordem) {
    echo "Ordem de serviço não encontrada!";
    exit();
}

// Verifica se a ordem de serviço está finalizada
if (isset($ordem['status']) && $ordem['status'] == 'finalizada') {
    header("Location: detalhes_ordem.php?id=$id&error=Não é possível editar uma ordem de serviço finalizada.");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $acoes_executadas = $_POST['acoes_executadas'];
    $tecnicos_ids = $_POST['tecnicos'];
    $tecnicos = json_encode($tecnicos_ids);
    $status = $ordem['status']; // Usar o status atual, pois o campo está desabilitado
    $estabelecimento_id = $_POST['estabelecimento_id'];
    $processo_id = $_POST['processo_id'];
    $observacao = $_POST['observacao']; // Adicionando o campo observacao

    // Atualizar o PDF path se necessário
    $pdf_path = $ordem['pdf_path']; // Assumindo que o caminho do PDF não muda

    // Adição de depuração
    error_log("Debug: Data Início - $data_inicio, Data Fim - $data_fim, Ações Executadas - " . json_encode($acoes_executadas) . ", Técnicos - $tecnicos, PDF Path - $pdf_path, Estabelecimento ID - $estabelecimento_id, Processo ID - $processo_id, Observação - $observacao");

    if ($ordemServico->update($id, $data_inicio, $data_fim, $acoes_executadas, $tecnicos, $pdf_path, $estabelecimento_id, $processo_id, $observacao)) {
        header("Location: detalhes_ordem.php?id=$id&success=Ordem de serviço atualizada com sucesso.");
        exit();
    } else {
        $error = "Erro ao atualizar a ordem de serviço: " . $ordemServico->getLastError();
    }
}


// Obter usuários técnicos do mesmo município e com nível de acesso 3 ou 4
$municipio_usuario = $_SESSION['user']['municipio'];
$query = "SELECT id, nome_completo FROM usuarios WHERE nivel_acesso IN (3, 4) AND municipio = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $municipio_usuario);
$stmt->execute();
$result = $stmt->get_result();
$tecnicos = $result->fetch_all(MYSQLI_ASSOC);

// Obter tipos de ações executadas
$tipos_acoes_executadas = $ordemServico->getTiposAcoesExecutadas();

// Decodifica tecnicos e garante que seja um array
$ordem_tecnicos = json_decode($ordem['tecnicos']);
if (!is_array($ordem_tecnicos)) {
    $ordem_tecnicos = [];
}

// Decodifica acoes_executadas e garante que seja um array
$ordem_acoes_executadas = json_decode($ordem['acoes_executadas'], true);
if (!is_array($ordem_acoes_executadas)) {
    $ordem_acoes_executadas = [];
}
?>

<div class="container mt-5">
    <h4>Editar Ordem de Serviço</h4>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="editar_ordem.php?id=<?php echo htmlspecialchars($id); ?>" method="POST">
        <input type="hidden" name="ordem_id" value="<?php echo htmlspecialchars($id); ?>">
        <input type="hidden" name="estabelecimento_id" value="<?php echo htmlspecialchars($ordem['estabelecimento_id']); ?>">
        <input type="hidden" name="processo_id" value="<?php echo htmlspecialchars($ordem['processo_id']); ?>">

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="data_inicio" class="form-label">Data Início</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo htmlspecialchars($ordem['data_inicio']); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="data_fim" class="form-label">Data Fim</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo htmlspecialchars($ordem['data_fim']); ?>" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="acoes_executadas" class="form-label">Ações Executadas</label>
            <select class="form-control" id="acoes_executadas" name="acoes_executadas[]" multiple required>
                <?php foreach ($tipos_acoes_executadas as $tipo) : ?>
                    <option value="<?php echo htmlspecialchars($tipo['id']); ?>" <?php echo in_array($tipo['id'], $ordem_acoes_executadas) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($tipo['descricao']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="tecnicos" class="form-label">Técnicos</label>
            <select class="form-control" id="tecnicos" name="tecnicos[]" multiple required>
                <?php foreach ($tecnicos as $tecnico) : ?>
                    <option value="<?php echo htmlspecialchars($tecnico['id']); ?>" <?php echo in_array($tecnico['id'], $ordem_tecnicos) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($tecnico['nome_completo']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
    <label for="observacao_check" class="form-label">
        <input type="checkbox" id="observacao_check" onclick="toggleObservacao()" <?php echo !empty($ordem['observacao']) ? 'checked' : ''; ?>> Adicionar Observação
    </label>
</div>

<div class="mb-3" id="observacao_container" style="display: <?php echo !empty($ordem['observacao']) ? 'block' : 'none'; ?>;">
    <label for="observacao" class="form-label">Observação</label>
    <textarea class="form-control" id="observacao" name="observacao"><?php echo htmlspecialchars($ordem['observacao']); ?></textarea>
</div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-control" id="status" name="status" required disabled>
                <option value="ativa" <?php echo (isset($ordem['status']) && $ordem['status'] == 'ativa') ? 'selected' : ''; ?>>Ativa</option>
                <option value="finalizada" <?php echo (isset($ordem['status']) && $ordem['status'] == 'finalizada') ? 'selected' : ''; ?>>Finalizada</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="listar_ordens.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
    function toggleObservacao() {
    const observacaoContainer = document.getElementById('observacao_container');
    observacaoContainer.style.display = observacaoContainer.style.display === 'none' ? 'block' : 'none';
}
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const dataInicio = document.getElementById('data_inicio');
        const dataFim = document.getElementById('data_fim');

        form.addEventListener('submit', function(event) {
            if (dataFim.value < dataInicio.value) {
                event.preventDefault();
                alert('A data de fim não pode ser anterior à data de início.');
            }
        });
    });
</script>

<?php include '../footer.php'; ?>
