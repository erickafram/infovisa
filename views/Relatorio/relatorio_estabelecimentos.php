<?php
session_start();
require_once '../../conf/database.php';
require_once '../../models/Relatorios.php';
require '../../vendor/autoload.php';

use Dompdf\Dompdf;

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

$nivel_acesso = $_SESSION['user']['nivel_acesso'];
$municipioUsuario = $_SESSION['user']['municipio'];
$relatoriosModel = new Relatorios($conn);
$municipios = $relatoriosModel->getMunicipios($nivel_acesso, $municipioUsuario);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $municipioSelecionado = $_POST['municipio'];
    $estabelecimentos = $relatoriosModel->getEstabelecimentosByMunicipio($municipioSelecionado);
    gerarPDF($estabelecimentos, $municipioSelecionado);
    exit(); // Certifique-se de sair após gerar o PDF
}

function gerarPDF($estabelecimentos, $municipio) {
    $dompdf = new Dompdf();

    $html = '
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                }
                h1 {
                    text-align: center;
                    color: #333;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                th, td {
                    padding: 10px;
                    text-align: left;
                    border-bottom: 1px solid #ddd;
                }
                th {
                    background-color: #f2f2f2;
                    color: #333;
                }
                tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .total-registros {
                    text-align: right;
                    margin-top: 20px;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <h1>Relatório de Estabelecimentos - ' . htmlspecialchars($municipio) . '</h1>
            <table>
                <thead>
                    <tr>
                        <th>CNPJ</th>
                        <th>Nome Fantasia</th>
                        <th>Razão Social</th>
                        <th>Telefone</th>
                    </tr>
                </thead>
                <tbody>';

    foreach ($estabelecimentos as $estabelecimento) {
        $html .= '
            <tr>
                <td>' . htmlspecialchars($estabelecimento['cnpj']) . '</td>
                <td>' . htmlspecialchars($estabelecimento['nome_fantasia']) . '</td>
                <td>' . htmlspecialchars($estabelecimento['razao_social']) . '</td>
                <td>' . htmlspecialchars($estabelecimento['ddd_telefone_1']) . '</td>
            </tr>';
    }

    $html .= '
                </tbody>
            </table>
            <div class="total-registros">Total de Registros: ' . count($estabelecimentos) . '</div>
        </body>
        </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape'); // Define a orientação do papel como horizontal
    $dompdf->render();
    $dompdf->stream('Relatorio_Estabelecimentos_' . htmlspecialchars($municipio) . '.pdf', array("Attachment" => 1));
}

include '../header.php';
?>

<div class="container mt-5">
    <h4 class="mb-4">Relatório de Estabelecimentos por Município</h4>
    <form method="POST" action="relatorio_estabelecimentos.php" class="mb-4">
        <div class="form-group">
            <label for="municipio">Escolha o Município:</label>
            <select id="municipio" name="municipio" class="form-control" required>
                <?php foreach ($municipios as $municipio): ?>
                    <option value="<?php echo htmlspecialchars($municipio['municipio']); ?>"><?php echo htmlspecialchars($municipio['municipio']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:8px !important;">Gerar PDF</button>
    </form>
</div>

<?php include '../footer.php'; ?>
