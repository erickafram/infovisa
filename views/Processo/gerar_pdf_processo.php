<?php
require_once '../../vendor/autoload.php';
require_once '../../conf/database.php';
require_once '../../models/Processo.php';
require_once '../../models/Estabelecimento.php';

ob_start(); // Inicia o buffer de saída

if (!isset($_GET['id'])) {
    echo "ID do processo não fornecido!";
    exit();
}

$processoId = $_GET['id'];

$processoModel = new Processo($conn);
$dadosProcesso = $processoModel->findById($processoId);

if (!$dadosProcesso) {
    echo "Processo não encontrado!";
    exit();
}

$estabelecimentoModel = new Estabelecimento($conn);
$dadosEstabelecimento = $estabelecimentoModel->findById($dadosProcesso['estabelecimento_id']);

// Criação do PDF
$pdf = new TCPDF();

// Configurações do PDF
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Seu Nome');
$pdf->SetTitle('Informações do Processo');
$pdf->SetSubject('Detalhes do Processo e Estabelecimento');
$pdf->SetKeywords('TCPDF, PDF, processo, estabelecimento');

// Adiciona uma página
$pdf->AddPage();

// Define o título do documento
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Informações do Processo', 0, 1, 'C');

// Define a fonte do conteúdo
$pdf->SetFont('helvetica', '', 12);

// Informações do Processo
$pdf->Ln(10);
$pdf->Cell(0, 10, 'Informações do Processo', 0, 1, 'L');
$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 12);
$pdf->writeHTML('<ul>
    <li><strong>Número do Processo:</strong> ' . htmlspecialchars($dadosProcesso['numero_processo']) . '</li>
    <li><strong>Tipo de Processo:</strong> ' . htmlspecialchars($dadosProcesso['tipo_processo']) . '</li>
    <li><strong>Nome da Empresa:</strong> ' . htmlspecialchars($dadosProcesso['nome_fantasia']) . '</li>
    <li><strong>CNPJ:</strong> ' . htmlspecialchars($dadosProcesso['cnpj']) . '</li>
    <li><strong>Telefone:</strong> ' . htmlspecialchars($dadosProcesso['ddd_telefone_1']) . '</li>
    <li><strong>Data de Abertura:</strong> ' . htmlspecialchars(date('d/m/Y', strtotime($dadosProcesso['data_abertura']))) . '</li>
    <li><strong>Status:</strong> ' . htmlspecialchars($dadosProcesso['status']) . '</li>
</ul>');

// Informações do Estabelecimento
$pdf->Ln(10);
$pdf->Cell(0, 10, 'Dados do Estabelecimento', 0, 1, 'L');
$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 12);
$pdf->writeHTML('<ul>
    <li><strong>Nome Fantasia:</strong> ' . htmlspecialchars($dadosEstabelecimento['nome_fantasia']) . '</li>
    <li><strong>Razão Social:</strong> ' . htmlspecialchars($dadosEstabelecimento['razao_social']) . '</li>
    <li><strong>CNPJ:</strong> ' . htmlspecialchars($dadosEstabelecimento['cnpj']) . '</li>
    <li><strong>Endereço:</strong> ' . htmlspecialchars($dadosEstabelecimento['logradouro']) . ', ' . htmlspecialchars($dadosEstabelecimento['numero']) . ', ' . htmlspecialchars($dadosEstabelecimento['bairro']) . ', ' . htmlspecialchars($dadosEstabelecimento['municipio']) . ' - ' . htmlspecialchars($dadosEstabelecimento['uf']) . ', ' . htmlspecialchars($dadosEstabelecimento['cep']) . '</li>
    <li><strong>Telefone:</strong> ' . htmlspecialchars($dadosEstabelecimento['ddd_telefone_1']) . '</li>
    <li><strong>Situação Cadastral:</strong> ' . htmlspecialchars($dadosEstabelecimento['descricao_situacao_cadastral']) . '</li>
</ul>');

// Aviso no final do documento
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Ln(10); // Adiciona uma linha em branco antes do texto
$pdf->MultiCell(0, 10, 'Para consultar a autenticidade do processo, por favor entre no site https://vigilancia-to.com.br/visamunicipal no "Consultar Andamento do Processo", insira o CNPJ da empresa para verificar o andamento deste processo e sua autenticidade.', 0, 'L', false);

// Fecha e gera o PDF
$pdf->Output('informacoes_processo.pdf', 'I');

ob_end_flush(); // Envia o conteúdo do buffer de saída e desativa o buffer
?>
