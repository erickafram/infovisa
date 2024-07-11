<?php
session_start();
require_once '../../conf/database.php';
require_once '../../models/OrdemServico.php';
require_once '../../vendor/autoload.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php");
    exit();
}

if (!isset($_POST['ordem_id'])) {
    echo "ID da ordem de serviço não fornecido!";
    exit();
}

$ordem_id = $_POST['ordem_id'];

$ordemServico = new OrdemServico($conn);
$ordem = $ordemServico->getOrdemById($ordem_id);

if (!$ordem) {
    echo "Ordem de serviço não encontrada!";
    exit();
}

$tecnicos_ids = json_decode($ordem['tecnicos'], true);
$nomes_tecnicos = $ordemServico->getTecnicosNomes($tecnicos_ids);

// Buscar os nomes das ações executadas
$acoes_ids = json_decode($ordem['acoes_executadas'], true);
$acoes_nomes = $ordemServico->getAcoesNomes($acoes_ids);

$acoes_executadas_nomes = [];
if (is_array($acoes_ids)) {
    foreach ($acoes_ids as $acao_id) {
        $acoes_executadas_nomes[] = $acoes_nomes[$acao_id];
    }
}

$acoes_executadas_str = implode(", ", $acoes_executadas_nomes);

class OrdemServicoController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->conn->set_charset("utf8");
    }

    public function gerarPDF($data_inicio, $data_fim, $acoes_executadas, $observacao, $tecnicos_ids, $estabelecimento_id) {
        $dados_estabelecimento = $this->obterDadosEstabelecimento($estabelecimento_id);

        // Obter nomes dos técnicos a partir dos IDs
        $nomes_tecnicos = $this->obterNomesTecnicos($tecnicos_ids);

        // Formatar as datas no formato D/M/Y
        $data_inicio_formatada = date('d/m/Y', strtotime($data_inicio));
        $data_fim_formatada = date('d/m/Y', strtotime($data_fim));

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, mb_convert_encoding('Ordem de Serviço', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, mb_convert_encoding('Razão Social:', 'ISO-8859-1', 'UTF-8'), 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, mb_convert_encoding($dados_estabelecimento['razao_social'], 'ISO-8859-1', 'UTF-8'), 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, mb_convert_encoding('Nome Fantasia:', 'ISO-8859-1', 'UTF-8'), 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, mb_convert_encoding($dados_estabelecimento['nome_fantasia'], 'ISO-8859-1', 'UTF-8'), 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, mb_convert_encoding('Endereço:', 'ISO-8859-1', 'UTF-8'), 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(0, 5, mb_convert_encoding($dados_estabelecimento['endereco'], 'ISO-8859-1', 'UTF-8'), 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, mb_convert_encoding('Data Início:', 'ISO-8859-1', 'UTF-8'), 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, mb_convert_encoding($data_inicio_formatada, 'ISO-8859-1', 'UTF-8'), 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, 'Data Fim:', 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, mb_convert_encoding($data_fim_formatada, 'ISO-8859-1', 'UTF-8'), 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, mb_convert_encoding('Ações Executadas:', 'ISO-8859-1', 'UTF-8'), 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(0, 5, mb_convert_encoding($acoes_executadas, 'ISO-8859-1', 'UTF-8'), 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, mb_convert_encoding('Observações:', 'ISO-8859-1', 'UTF-8'), 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 10, mb_convert_encoding($observacao, 'ISO-8859-1', 'UTF-8'), 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, mb_convert_encoding('Técnicos:', 'ISO-8859-1', 'UTF-8'), 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 10, mb_convert_encoding($nomes_tecnicos, 'ISO-8859-1', 'UTF-8'), 1);
        $pdf->Ln();

        // Retornar o PDF gerado como string
        return $pdf->Output('S');
    }

    private function obterDadosEstabelecimento($estabelecimento_id) {
        $query = "SELECT razao_social, nome_fantasia, CONCAT(descricao_tipo_de_logradouro, ' ', logradouro, ', ', numero, ' - ', bairro, ', ', municipio, ' - ', uf, ', CEP: ', cep) AS endereco FROM estabelecimentos WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $estabelecimento_id);
        $stmt->execute();
        $stmt->bind_result($razao_social, $nome_fantasia, $endereco);
        $stmt->fetch();
        $stmt->close();

        return [
            'razao_social' => htmlspecialchars_decode($razao_social, ENT_QUOTES),
            'nome_fantasia' => htmlspecialchars_decode($nome_fantasia, ENT_QUOTES),
            'endereco' => htmlspecialchars_decode($endereco, ENT_QUOTES)
        ];
    }

    private function obterNomesTecnicos($ids_tecnicos) {
        if (empty($ids_tecnicos)) {
            return '';
        }

        $ids_tecnicos_str = implode(',', array_map('intval', $ids_tecnicos));
        $query = "SELECT nome_completo FROM usuarios WHERE id IN ($ids_tecnicos_str)";
        $result = $this->conn->query($query);

        $nomes_tecnicos = [];
        while ($row = $result->fetch_assoc()) {
            $nomes_tecnicos[] = htmlspecialchars_decode($row['nome_completo'], ENT_QUOTES);
        }

        return implode(', ', $nomes_tecnicos);
    }
}

$controller = new OrdemServicoController($conn);
$pdf_content = $controller->gerarPDF(
    $ordem['data_inicio'], 
    $ordem['data_fim'], 
    $acoes_executadas_str, 
    $ordem['observacao'], // Adicionando o campo observacao
    $tecnicos_ids, 
    $ordem['estabelecimento_id']
);

// Configurar o cabeçalho para download do PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="ordem_servico.pdf"');
echo $pdf_content;
