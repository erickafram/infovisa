<?php
session_start();

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 3])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/OrdemServico.php';

$ordemServico = new OrdemServico($conn);

// Verifique se o município do usuário está na sessão
if (!isset($_SESSION['user']['municipio'])) {
    echo "Município do usuário não encontrado!";
    exit();
}

$municipio_usuario = $_SESSION['user']['municipio'];

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

// Verificação se a ordem está finalizada
if ($ordem['status'] == 'finalizada') {
    include '../header.php';
    echo '<div class="container mt-5">
            <div class="alert alert-danger" role="alert">
                Não é possível vincular uma ordem de serviço finalizada.
            </div>
          </div>';
    include '../footer.php';
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $estabelecimento_id = $_POST['estabelecimento_id'];
    $processo_id = $_POST['processo_id'];

    // Obter as ações executadas atuais
    $acoes_executadas = json_decode($ordem['acoes_executadas'], true);

    if ($ordemServico->update($id, $ordem['data_inicio'], $ordem['data_fim'], $acoes_executadas, $ordem['tecnicos'], $ordem['pdf_path'], $estabelecimento_id, $processo_id)) {
        header("Location: detalhes_ordem.php?id=$id&success=Ordem de Serviço vinculada com sucesso.");
        exit();
    } else {
        $error = "Erro ao vincular a ordem de serviço: " . $ordemServico->getLastError();
    }
}

// Obter lista de estabelecimentos do mesmo município do usuário
$query = "SELECT id, nome_fantasia FROM estabelecimentos WHERE municipio = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $municipio_usuario);
$stmt->execute();
$result = $stmt->get_result();
$estabelecimentos = $result->fetch_all(MYSQLI_ASSOC);

?>

<?php include '../header.php'; ?>

<div class="container mt-5">
    <h4>Vincular Ordem de Serviço</h4>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="vincular_ordem.php?id=<?php echo htmlspecialchars($id); ?>" method="POST">
        <div class="mb-3">
            <label for="estabelecimento_id" class="form-label">Estabelecimento</label>
            <select class="form-control" id="estabelecimento_id" name="estabelecimento_id" required>
                <option value="">Selecione um estabelecimento</option>
                <?php foreach ($estabelecimentos as $estabelecimento) : ?>
                    <option value="<?php echo htmlspecialchars($estabelecimento['id']); ?>">
                        <?php echo htmlspecialchars($estabelecimento['nome_fantasia']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="processo_id" class="form-label">Processo</label>
            <select class="form-control" id="processo_id" name="processo_id" required>
                <option value="">Selecione um processo</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="listar_ordens.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include '../footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#estabelecimento_id').change(function() {
            var estabelecimento_id = $(this).val();
            if (estabelecimento_id) {
                $.ajax({
                    url: 'get_processos.php',
                    type: 'GET',
                    data: {
                        estabelecimento_id: estabelecimento_id
                    },
                    success: function(data) {
                        $('#processo_id').html(data);
                    }
                });
            } else {
                $('#processo_id').html('<option value="">Selecione um processo</option>');
            }
        });
    });
</script>