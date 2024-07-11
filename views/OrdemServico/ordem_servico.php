<?php
session_start();
include '../header.php';

// Verificação de autenticação e nível de acesso 
// 1 Administrador, 2 Suporte, 3 Gerente, 4 Fiscal
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1,3])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';

// Obter o município do usuário logado
$usuario_municipio = $_SESSION['user']['municipio'];

// Obter usuários técnicos com nível de acesso 3 e 4 do mesmo município
$query = "SELECT id, nome_completo FROM usuarios WHERE (nivel_acesso = 3 OR nivel_acesso = 4) AND municipio = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $usuario_municipio);
$stmt->execute();
$result = $stmt->get_result();
$tecnicos = $result->fetch_all(MYSQLI_ASSOC);

// Obter tipos de ações
$query_tipos_acoes = "SELECT id, descricao FROM tipos_acoes_executadas";
$result_tipos_acoes = $conn->query($query_tipos_acoes);
$tipos_acoes = $result_tipos_acoes->fetch_all(MYSQLI_ASSOC);

$estabelecimento_id = $_GET['id'];
$processo_id = $_GET['processo_id'];
?>

<div class="container mt-5">
    <h4>Criar Ordem de Serviço</h4>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger" role="alert">
            Erro ao criar ordem de serviço: <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <form action="../../controllers/OrdemServicoController.php?action=criar" method="POST">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="data_inicio" class="form-label">Data Início</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" required>
            </div>
            <div class="col-md-6">
                <label for="data_fim" class="form-label">Data Fim</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" required>
            </div>
        </div>

        <div class="mb-3">
    <label for="acoes_executadas" class="form-label">Ações Executadas</label>
    <select multiple class="form-control" id="acoes_executadas" name="acoes_executadas[]" required>
        <?php foreach ($tipos_acoes as $tipo_acao): ?>
            <option value="<?php echo $tipo_acao['id']; ?>"><?php echo htmlspecialchars($tipo_acao['descricao']); ?></option>
        <?php endforeach; ?>
    </select>
</div>


        <div class="mb-3">
            <label for="tecnicos" class="form-label">Técnicos</label>
            <select multiple class="form-control" id="tecnicos" name="tecnicos[]" required>
                <?php foreach ($tecnicos as $tecnico): ?>
                    <option value="<?php echo $tecnico['id']; ?>"><?php echo htmlspecialchars($tecnico['nome_completo']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
    <label for="observacao_check" class="form-label">
        <input type="checkbox" id="observacao_check" onclick="toggleObservacao()"> Adicionar Observação
    </label>
</div>

<div class="mb-3" id="observacao_container" style="display: none;">
    <label for="observacao" class="form-label">Observação</label>
    <textarea class="form-control" id="observacao" name="observacao"></textarea>
</div>

        <input type="hidden" name="estabelecimento_id" value="<?php echo $estabelecimento_id; ?>">
        <input type="hidden" name="processo_id" value="<?php echo $processo_id; ?>">

        <button type="submit" class="btn btn-primary">Criar Ordem de Serviço</button>
    </form>
</div>

<script>
    function toggleObservacao() {
    const observacaoContainer = document.getElementById('observacao_container');
    observacaoContainer.style.display = observacaoContainer.style.display === 'none' ? 'block' : 'none';
}


document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const dataInicio = document.getElementById('data_inicio');
    const dataFim = document.getElementById('data_fim');

    form.addEventListener('submit', function (event) {
        if (dataFim.value < dataInicio.value) {
            event.preventDefault();
            alert('A data de fim não pode ser anterior à data de início.');
        }
    });
});
</script>


<?php include '../footer.php'; ?>