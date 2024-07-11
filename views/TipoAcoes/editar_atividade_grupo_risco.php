<?php
session_start();
ob_start(); // Inicia o buffer de saída

include '../header.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';

$errors = [];
$municipio = $_GET['municipio'];
$grupo_risco = $_GET['grupo_risco'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $grupo_risco_id = $_POST['grupo_risco_id'];
    $cnaes = $_POST['cnae'];
    $cnaes_array = explode(',', $cnaes);

    // Primeiro, removemos todas as atividades antigas do grupo de risco e município
    $delete_stmt = $conn->prepare("DELETE FROM atividade_grupo_risco WHERE grupo_risco_id = ? AND municipio = ?");
    $delete_stmt->bind_param("is", $grupo_risco_id, $municipio);
    $delete_stmt->execute();
    $delete_stmt->close();

    // Depois, adicionamos as novas atividades
    foreach ($cnaes_array as $cnae) {
        $cnae = trim($cnae);

        $stmt = $conn->prepare("INSERT INTO atividade_grupo_risco (cnae, grupo_risco_id, municipio) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $cnae, $grupo_risco_id, $municipio);

        if (!$stmt->execute()) {
            $errors[] = "Erro ao atualizar a atividade $cnae: " . $conn->error;
        }

        $stmt->close();
    }

    if (empty($errors)) {
        ob_clean();
        header("Location: adicionar_atividade_grupo_risco.php?success=Atividade atualizada com sucesso.");
        exit();
    }
}

// Obtenha as atividades do grupo de risco e município
$atividadesExistentes = $conn->query("SELECT cnae FROM atividade_grupo_risco WHERE grupo_risco_id = (SELECT id FROM grupo_risco WHERE descricao = '$grupo_risco') AND municipio = '$municipio'");
$cnaes = [];
while ($row = $atividadesExistentes->fetch_assoc()) {
    $cnaes[] = $row['cnae'];
}
$cnaes_str = implode(', ', $cnaes);

$gruposRisco = $conn->query("SELECT id, descricao FROM grupo_risco");
?>

<div class="container mt-5">
    <h4>Editar Atividade</h4>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <?php foreach ($errors as $error) {
                echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "<br>";
            } ?>
        </div>
    <?php endif; ?>

    <form action="editar_atividade_grupo_risco.php?municipio=<?php echo $municipio; ?>&grupo_risco=<?php echo $grupo_risco; ?>" method="POST">
        <div class="mb-3">
            <label for="grupo_risco_id" class="form-label">Grupo de Risco</label>
            <select class="form-control" id="grupo_risco_id" name="grupo_risco_id" required>
                <?php while ($grupo = $gruposRisco->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($grupo['id'], ENT_QUOTES, 'UTF-8'); ?>" <?php if ($grupo['descricao'] == $grupo_risco) echo 'selected'; ?>><?php echo htmlspecialchars($grupo['descricao'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="cnae" class="form-label">CNAEs (separados por vírgula)</label>
            <input type="text" class="form-control" id="cnae" name="cnae" value="<?php echo htmlspecialchars($cnaes_str, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Atualizar Atividade</button>
    </form>
</div>

<?php include '../footer.php'; ?>
<?php
ob_end_flush(); // Libera o buffer de saída
?>
