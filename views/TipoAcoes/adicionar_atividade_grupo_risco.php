<?php
session_start();
ob_start(); // Inicia o buffer de saída

include '../header.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';

$errors = []; // Inicializa a variável $errors

$municipio_usuario = $_SESSION['user']['municipio'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $municipio = $municipio_usuario; // Define o município com base no usuário logado
    $grupo_risco_id = $_POST['grupo_risco_id'];
    $cnaes = $_POST['cnaes'];
    $cnaes_array = explode(',', $cnaes);

    foreach ($cnaes_array as $cnae) {
        $cnae = trim($cnae);

        // Verifica se o CNAE já existe no mesmo grupo de risco e município
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM atividade_grupo_risco WHERE cnae = ? AND grupo_risco_id = ? AND municipio = ?");
        $check_stmt->bind_param("sis", $cnae, $grupo_risco_id, $municipio);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            $errors[] = "A atividade $cnae já está adicionada ao grupo de risco para o município selecionado.";
        } else {
            $stmt = $conn->prepare("INSERT INTO atividade_grupo_risco (cnae, grupo_risco_id, municipio) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $cnae, $grupo_risco_id, $municipio);

            if (!$stmt->execute()) {
                $errors[] = "Erro ao adicionar a atividade $cnae ao grupo de risco: " . $conn->error;
            }

            $stmt->close();
        }
    }

    if (empty($errors)) {
        // Limpa o buffer de saída antes de enviar o header
        ob_clean();
        header("Location: adicionar_atividade_grupo_risco.php?success=Atividades adicionadas ao grupo de risco com sucesso.");
        exit();
    }
}

// Consulta para obter os grupos de risco
$gruposRisco = $conn->query("SELECT id, descricao FROM grupo_risco");

?>

<div class="container mt-5">
    <h4 class="mb-4">Adicionar Atividades ao Grupo de Risco</h4>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <?php foreach ($errors as $error) {
                echo htmlspecialchars($error ?? '', ENT_QUOTES, 'UTF-8') . "<br>";
            } ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($_GET['success'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form action="adicionar_atividade_grupo_risco.php" method="POST">
        <div class="mb-3">
            <label for="municipio" class="form-label">Município</label>
            <input type="text" class="form-control" id="municipio" name="municipio" value="<?php echo htmlspecialchars($municipio_usuario, ENT_QUOTES, 'UTF-8'); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="grupo_risco_id" class="form-label">Grupo de Risco</label>
            <select class="form-control" id="grupo_risco_id" name="grupo_risco_id" required>
                <?php while ($grupo = $gruposRisco->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($grupo['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($grupo['descricao'] ?? '', ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="cnaes" class="form-label">CNAEs (somente número separados por vírgula)</label>
            <input type="text" class="form-control" id="cnaes" name="cnaes" required>
        </div>
        <button type="submit" class="btn btn-primary">Adicionar Atividades</button>
    </form>

    <hr class="my-5">

    <h4 class="mb-4">Lista de Atividades por Grupo de Risco</h4>
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Município</th>
                <th>Grupo de Risco</th>
                <th>CNAEs</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $atividades = $conn->query("
                SELECT 
                    agr.municipio, 
                    gr.descricao AS grupo_risco, 
                    GROUP_CONCAT(agr.cnae ORDER BY agr.cnae SEPARATOR ', ') AS cnaes
                FROM 
                    atividade_grupo_risco agr
                JOIN 
                    grupo_risco gr ON agr.grupo_risco_id = gr.id
                GROUP BY 
                    agr.municipio, gr.descricao
            ");

            if ($atividades) {
                while ($atividade = $atividades->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($atividade['municipio'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($atividade['grupo_risco'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($atividade['cnaes'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <a href="editar_atividade_grupo_risco.php?municipio=<?php echo htmlspecialchars($atividade['municipio'], ENT_QUOTES, 'UTF-8'); ?>&grupo_risco=<?php echo htmlspecialchars($atividade['grupo_risco'], ENT_QUOTES, 'UTF-8'); ?>" class="text-warning me-2"><i class="fas fa-edit"></i></a>
                            <a href="excluir_atividade_grupo_risco.php?municipio=<?php echo htmlspecialchars($atividade['municipio'], ENT_QUOTES, 'UTF-8'); ?>&grupo_risco=<?php echo htmlspecialchars($atividade['grupo_risco'], ENT_QUOTES, 'UTF-8'); ?>" class="text-danger"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                <?php endwhile;
            } else {
                echo "<tr><td colspan='4'>Nenhuma atividade encontrada.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <hr class="my-5">

   <!-- <h4 class="mb-4">Estabelecimentos Vinculados a Grupos de Risco</h4>
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Estabelecimento</th>
                <th>Município</th>
                <th>CNAE Fiscal</th>
                <th>CNAEs Secundários</th>
                <th>Grupo de Risco Fiscal</th>
                <th>Grupo de Risco Secundário</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $estabelecimentos = $conn->query("
                SELECT 
                    e.nome_fantasia AS estabelecimento,
                    e.municipio AS municipio,
                    e.cnae_fiscal AS cnae_fiscal,
                    GROUP_CONCAT(DISTINCT REPLACE(REPLACE(JSON_UNQUOTE(JSON_EXTRACT(e.cnaes_secundarios, '$[*].codigo')), '[', ''), ']', '') ORDER BY JSON_UNQUOTE(JSON_EXTRACT(e.cnaes_secundarios, '$[*].codigo')) SEPARATOR ', ') AS cnaes_secundarios,
                    GROUP_CONCAT(DISTINCT gr_fiscal.descricao ORDER BY gr_fiscal.descricao SEPARATOR ', ') AS grupo_risco_fiscal,
                    GROUP_CONCAT(DISTINCT gr_secundario.descricao ORDER BY gr_secundario.descricao SEPARATOR ', ') AS grupo_risco_secundario
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
                        JSON_TABLE(e.cnaes_secundarios, '$[*]' COLUMNS (codigo VARCHAR(255) PATH '$.codigo')) AS cnaes
                    ) AS cnae_secundario ON e.id = cnae_secundario.estabelecimento_id
                LEFT JOIN 
                    atividade_grupo_risco agr_secundario ON cnae_secundario.cnae_secundario = agr_secundario.cnae AND e.municipio = agr_secundario.municipio
                LEFT JOIN 
                    grupo_risco gr_secundario ON agr_secundario.grupo_risco_id = gr_secundario.id
                GROUP BY 
                    e.id, e.nome_fantasia, e.municipio, e.cnae_fiscal
                ORDER BY 
                    e.nome_fantasia
            ");

            if ($estabelecimentos) {
                while ($estabelecimento = $estabelecimentos->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($estabelecimento['estabelecimento'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($estabelecimento['municipio'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($estabelecimento['cnae_fiscal'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($estabelecimento['cnaes_secundarios'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($estabelecimento['grupo_risco_fiscal'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($estabelecimento['grupo_risco_secundario'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endwhile;
            } else {
                echo "<tr><td colspan='6'>Nenhum estabelecimento encontrado.</td></tr>";
            }
            ?>
        </tbody>
    </table> -->
</div>

<?php include '../footer.php'; ?>
<?php
ob_end_flush(); // Libera o buffer de saída
?>
