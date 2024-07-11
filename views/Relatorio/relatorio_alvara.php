<?php
session_start();
include '../header.php';
require_once '../../conf/database.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php");
    exit();
}

$municipio = $_SESSION['user']['municipio']; // Município do usuário logado
$ano = isset($_GET['ano']) ? $_GET['ano'] : date('Y'); // Ano selecionado pelo usuário
$situacao = isset($_GET['situacao']) ? $_GET['situacao'] : 'sem_alvara'; // Situação selecionada pelo usuário

// Construção da cláusula WHERE para a situação do alvará
$whereSituacao = "";
if ($situacao === 'com_alvara') {
    $whereSituacao = "a.processo_id IS NOT NULL";
} else {
    $whereSituacao = "a.processo_id IS NULL";
}

// Consulta para obter estabelecimentos com e sem alvará sanitário
$sql = "
    SELECT e.*, 
           CASE WHEN a.processo_id IS NULL THEN 'Sem Alvará Sanitário' ELSE 'Com Alvará Sanitário' END AS situacao_alvara
    FROM estabelecimentos e
    JOIN processos p ON e.id = p.estabelecimento_id
    LEFT JOIN (
        SELECT DISTINCT processo_id 
        FROM arquivos 
        WHERE tipo_documento = 'ALVARÁ SANITÁRIO'
        AND YEAR(data_upload) = ?
    ) a ON p.id = a.processo_id
    WHERE $whereSituacao
    AND e.municipio = ?
    AND p.tipo_processo = 'LICENCIAMENTO'
    AND YEAR(p.data_abertura) = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $ano, $municipio, $ano);
$stmt->execute();
$result = $stmt->get_result();
$estabelecimentos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include '../footer.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Estabelecimentos com e sem Alvará Sanitário</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        .navbar-expand-lg>.container,
        .navbar-expand-lg>.container-fluid {
            max-width: 1320px;
        }

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
    </style>
</head>
<body>
    <div class="container mt-5" style="max-width: 1320px;">
            <h4>Relatório de Estabelecimentos com e sem Alvará Sanitário</h4>
        <form method="GET" action="">
            <div class="form-group">
                <label for="ano">Selecione o Ano:</label>
                <select class="form-control" id="ano" name="ano" required>
                    <?php
                    $currentYear = date('Y');
                    for ($year = $currentYear; $year >= 2024; $year--) {
                        echo "<option value=\"$year\">$year</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="situacao">Selecione a Situação:</label>
                <select class="form-control" id="situacao" name="situacao" required>
                    <option value="sem_alvara" <?php echo $situacao == 'sem_alvara' ? 'selected' : ''; ?>>Sem Alvará Sanitário</option>
                    <option value="com_alvara" <?php echo $situacao == 'com_alvara' ? 'selected' : ''; ?>>Com Alvará Sanitário</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form>

        <?php if (!empty($estabelecimentos)) : ?>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Estabelecimentos no município: <?php echo htmlspecialchars($municipio); ?> - Situação: <?php echo htmlspecialchars($situacao == 'sem_alvara' ? 'Sem Alvará Sanitário' : 'Com Alvará Sanitário'); ?></h5>
                    <table class="table table-bordered mt-3" id="relatorioTable">
                        <thead>
                            <tr>
                                <th>CNPJ</th>
                                <th>Nome Fantasia</th>
                                <th>Razão Social</th>
                                <th>Endereço</th>
                                <th>Telefone</th>
                                <th>Situação Alvará</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estabelecimentos as $estabelecimento) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($estabelecimento['cnpj']); ?></td>
                                    <td><?php echo htmlspecialchars($estabelecimento['nome_fantasia']); ?></td>
                                    <td><?php echo htmlspecialchars($estabelecimento['razao_social']); ?></td>
                                    <td><?php echo htmlspecialchars($estabelecimento['logradouro'] . ', ' . $estabelecimento['numero'] . ' - ' . $estabelecimento['bairro'] . ', ' . $estabelecimento['municipio'] . ' - ' . $estabelecimento['uf'] . ', ' . $estabelecimento['cep']); ?></td>
                                    <td><?php echo htmlspecialchars($estabelecimento['ddd_telefone_1']); ?></td>
                                    <td><?php echo htmlspecialchars($estabelecimento['situacao_alvara']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5"><strong>Total de Estabelecimentos:</strong></td>
                                <td><?php echo count($estabelecimentos); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    <button onclick="generatePDF()" class="btn btn-danger mt-3">Gerar PDF</button>
                </div>
            </div>
        <?php else : ?>
            <p>Nenhum estabelecimento encontrado com a situação selecionada no município.</p>
        <?php endif; ?>
    </div>

    <!-- Adicionando jsPDF e jsPDF-AutoTable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
    <script>
        function generatePDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            doc.autoTable({
                head: [['CNPJ', 'Nome Fantasia', 'Razão Social', 'Endereço', 'Telefone', 'Situação Alvará']],
                body: Array.from(document.querySelectorAll("#relatorioTable tbody tr")).map(row =>
                    Array.from(row.cells).map(cell => cell.textContent)
                ),
                foot: [[{content: 'Total de Estabelecimentos: ' + <?php echo count($estabelecimentos); ?>, colSpan: 6, styles: {halign: 'right'}}]],
                theme: 'striped',
                headStyles: { fillColor: [52, 152, 219] },
                margin: { top: 20 }
            });

            doc.save('relatorio_alvara.pdf');
        }
    </script>
</body>
</html>
