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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $acoes_executadas = $_POST['acoes_executadas'];
    $tecnicos_ids = $_POST['tecnicos'];
    $tecnicos = json_encode($tecnicos_ids);
    $estabelecimento_id = $_POST['estabelecimento_id'];
    $processo_id = $_POST['processo_id'];
    $observacao = $_POST['observacao']; // Novo campo de observação
    $pdf_path = ''; // Defina o caminho do PDF após gerar
    $municipio = $_SESSION['user']['municipio'];

    $data_inicio_dt = new DateTime($data_inicio);
    $data_fim_dt = new DateTime($data_fim);

    if ($data_fim_dt < $data_inicio_dt) {
        $error = "A data de fim não pode ser menor que a data de início.";
    } else {
        if ($ordemServico->create($estabelecimento_id, $processo_id, $data_inicio, $data_fim, $acoes_executadas, $tecnicos, $pdf_path, $municipio, 'ativa', $observacao)) { // Adicionar a observação
            header("Location: listar_ordens.php?success=Ordem de Serviço criada com sucesso.");
            exit();
        } else {
            $error = "Erro ao criar a ordem de serviço: " . $ordemServico->getLastError();
        }
    }
}

// Obter tipos de ações executadas
$tipos_acoes_executadas = $ordemServico->getTiposAcoesExecutadas();

// Obter usuários técnicos do mesmo município e com nível de acesso 3 ou 4
$municipio_usuario = $_SESSION['user']['municipio'];
$query = "SELECT id, nome_completo FROM usuarios WHERE nivel_acesso IN (3, 4) AND municipio = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $municipio_usuario);
$stmt->execute();
$result = $stmt->get_result();
$tecnicos = $result->fetch_all(MYSQLI_ASSOC);

?>

<div class="container mt-5">
    <h4>Nova Ordem de Serviço</h4>

    <div class="alert alert-warning" role="alert">
        A criação de ordem de serviço neste lugar não vinculará ao estabelecimento. Após a inspeção, o técnico deverá encerrar a OS e vincular a ordem de serviço ao estabelecimento ou encerrar a ordem de serviço sem vincular ao estabelecimento.
    </div>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="nova_ordem_servico.php" method="POST">
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
            <select class="form-control" id="acoes_executadas" name="acoes_executadas[]" multiple required>
                <?php foreach ($tipos_acoes_executadas as $tipo) : ?>
                    <option value="<?php echo htmlspecialchars($tipo['id']); ?>">
                        <?php echo htmlspecialchars($tipo['descricao']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="tecnicos" class="form-label">Técnicos</label>
            <select class="form-control" id="tecnicos" name="tecnicos[]" multiple required>
                <?php foreach ($tecnicos as $tecnico) : ?>
                    <option value="<?php echo htmlspecialchars($tecnico['id']); ?>">
                        <?php echo htmlspecialchars($tecnico['nome_completo']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="observacao" class="form-label">Observação</label>
            <textarea class="form-control" id="observacao" name="observacao" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="listar_ordens.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include '../footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('submit', function(event) {
            const dataInicio = document.getElementById('data_inicio').value;
            const dataFim = document.getElementById('data_fim').value;

            if (new Date(dataFim) < new Date(dataInicio)) {
                event.preventDefault();
                alert('A data de fim não pode ser menor que a data de início.');
            }
        });
    });
</script>
