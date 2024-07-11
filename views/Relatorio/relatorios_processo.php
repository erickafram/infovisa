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
$tipo_processo = isset($_GET['tipo_processo']) ? $_GET['tipo_processo'] : ''; // Tipo de processo selecionado pelo usuário
$situacao = isset($_GET['situacao']) ? $_GET['situacao'] : 'sem_processo'; // Situação selecionada pelo usuário

// Construção da cláusula WHERE para a situação do processo
$whereSituacao = "";
if ($situacao === 'com_processo') {
    $whereSituacao = "p.tipo_processo IS NOT NULL";
} else {
    $whereSituacao = "p.tipo_processo IS NULL";
}

// Consulta para obter estabelecimentos com e sem o tipo de processo especificado
$sql = "
    SELECT e.*, 
           CASE WHEN p.tipo_processo IS NULL THEN 'Sem Processo' ELSE 'Com Processo' END AS situacao_processo
    FROM estabelecimentos e
    LEFT JOIN (
        SELECT *
        FROM processos 
        WHERE tipo_processo = ?
        AND YEAR(data_abertura) = ?
    ) p ON e.id = p.estabelecimento_id
    WHERE $whereSituacao
    AND e.municipio = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $tipo_processo, $ano, $municipio);
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
    <title>Relatório de Estabelecimentos com e sem Tipo de Processo Específico</title>
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
        <h4>Relatório de Estabelecimentos com e sem Tipo de Processo Específico</h4>
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
                <label for="tipo_processo">Selecione o Tipo de Processo:</label>
                <select class="form-control" id="tipo_processo" name="tipo_processo" required>
                    <option value="ADMINISTRATIVO" <?php echo $tipo_processo == 'ADMINISTRATIVO' ? 'selected' : ''; ?>>Administrativo</option>
                    <option value="DENÚNCIA" <?php echo $tipo_processo == 'DENÚNCIA' ? 'selected' : ''; ?>>Denúncia</option>
                    <option value="LICENCIAMENTO" <?php echo $tipo_processo == 'LICENCIAMENTO' ? 'selected' : ''; ?>>Licenciamento</option>
                    <option value="PROJETO ARQUITETÔNICO" <?php echo $tipo_processo == 'PROJETO ARQUITETÔNICO' ? 'selected' : ''; ?>>Projeto Arquitetônico</option>

                </select>
            </div>
            <div class="form-group">
                <label for="situacao">Selecione a Situação:</label>
                <select class="form-control" id="situacao" name="situacao" required>
                    <option value="sem_processo" <?php echo $situacao == 'sem_processo' ? 'selected' : ''; ?>>Sem Processo</option>
                    <option value="com_processo" <?php echo $situacao == 'com_processo' ? 'selected' : ''; ?>>Com Processo</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form>

        <?php if (!empty($estabelecimentos)) : ?>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Estabelecimentos no município: <?php echo htmlspecialchars($municipio); ?> - Situação: <?php echo htmlspecialchars($situacao == 'sem_processo' ? 'Sem Processo' : 'Com Processo'); ?></h5>
                    <table class="table table-bordered mt-3" id="relatorioTable">
                        <thead>
                            <tr>
                                <th>CNPJ</th>
                                <th>Nome Fantasia</th>
                                <th>Razão Social</th>
                                <th>Endereço</th>
                                <th>Telefone</th>
                                <th>Situação Processo</th>
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
                                    <td><?php echo htmlspecialchars($estabelecimento['situacao_processo']); ?></td>
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
            <p>Nenhum estabelecimento encontrado com a situação e tipo de processo selecionados no município.</p>
        <?php endif; ?>
    </div>

    <!-- Adicionando jsPDF e jsPDF-AutoTable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
    <script>
        function generatePDF() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF();

            doc.autoTable({
                head: [
                    ['CNPJ', 'Nome Fantasia', 'Razão Social', 'Endereço', 'Telefone', 'Situação Processo']
                ],
                body: Array.from(document.querySelectorAll("#relatorioTable tbody tr")).map(row =>
                    Array.from(row.cells).map(cell => cell.textContent)
                ),
                foot: [
                    [{
                        content: 'Total de Estabelecimentos: ' + <?php echo count($estabelecimentos); ?>,
                        colSpan: 6,
                        styles: {
                            halign: 'right'
                        }
                    }]
                ],
                theme: 'striped',
                headStyles: {
                    fillColor: [52, 152, 219]
                },
                margin: {
                    top: 20
                }
            });

            doc.save('relatorio_processo.pdf');
        }
    </script>
</body>

</html>