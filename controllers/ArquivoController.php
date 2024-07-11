<?php
require_once '../../conf/database.php';
require_once '../../models/Arquivo.php';
require_once '../../models/Estabelecimento.php';
require_once '../../models/Usuario.php';
require_once '../../models/Assinatura.php';
require_once '../../models/ResponsavelLegal.php';
require_once '../../models/ResponsavelTecnico.php';
require_once '../../models/Logomarca.php';
require_once '../../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class CustomPDF extends TCPDF
{
    private $qrCodePath;
    private $logoPath;

    public function setQrCodePath($qrCodePath)
    {
        $this->qrCodePath = $qrCodePath;
    }

    public function setLogoPath($logoPath)
    {
        $this->logoPath = $logoPath;
    }

    public function Header()
    {
        if ($this->logoPath && $this->getPage() == 1) { // Apenas na primeira página
            // Obtenha as dimensões originais da imagem
            list($originalWidth, $originalHeight) = getimagesize($this->logoPath);

            // Defina a largura e altura desejadas mantendo a proporção
            $desiredWidth = 40; // Defina a largura desejada
            $desiredHeight = ($originalHeight / $originalWidth) * $desiredWidth; // Calcula a altura proporcional

            // Insira a imagem com as novas dimensões
            $this->Image($this->logoPath, $this->getPageWidth() / 2 - ($desiredWidth / 2), 10, $desiredWidth, $desiredHeight, 'PNG');
            $this->Ln(30); // Ajustar esse valor para criar espaço suficiente para a logomarca
        }
    }


    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        if ($this->qrCodePath) {
            $this->Image($this->qrCodePath, $this->getPageWidth() - 30, $this->getPageHeight() - 30, 20, 20, 'PNG');
        }
    }
}

class ArquivoController
{
    private $arquivo;
    private $estabelecimento;
    private $usuario;
    private $assinatura;
    private $responsavelLegal;
    private $responsavelTecnico;
    private $logomarca;

    public function __construct($conn)
    {
        $this->arquivo = new Arquivo($conn);
        $this->estabelecimento = new Estabelecimento($conn);
        $this->usuario = new Usuario($conn);
        $this->assinatura = new Assinatura($conn);
        $this->responsavelLegal = new ResponsavelLegal($conn);
        $this->responsavelTecnico = new ResponsavelTecnico($conn);
        $this->logomarca = new Logomarca($conn);
    }

    private function writeLog($message)
    {
        $logFile = __DIR__ . '/arquivo_controller.log';
        $timestamp = date("Y-m-d H:i:s");
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    }

    public function Header()
    {
        if ($this->logoPath) {
            $this->Image($this->logoPath, $this->getPageWidth() / 2 - 20, 10, 40, 20, 'PNG');
            $this->Ln(30); // Adjust this value to create enough space for the logo
        }
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['processo_id']) && isset($_POST['conteudo']) && isset($_POST['tipo_documento'])) {
            $processo_id = $_POST['processo_id'];
            $sigiloso = isset($_POST['sigiloso']) ? intval($_POST['sigiloso']) : 0;
            $conteudo = $_POST['conteudo'];
            $tipo_documento = $_POST['tipo_documento'];
            $ano = date('Y');
            $upload_dir = __DIR__ . '/../uploads';

            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $estabelecimento_id = $_POST['estabelecimento_id'];
            $codigo_verificador = md5(uniqid(rand(), true));
            $arquivo_id = $this->arquivo->createArquivo($processo_id, $tipo_documento, 'uploads/temp', $codigo_verificador, $conteudo, $sigiloso);

            if ($arquivo_id === false) {
                header("Location: ../Processo/documentos.php?processo_id=$processo_id&id=$estabelecimento_id&error=" . urlencode('Erro ao registrar o arquivo no banco de dados.'));
                exit();
            }

            $numero_arquivo = $arquivo_id;
            $nome_arquivo = "{$tipo_documento}_{$numero_arquivo}_{$ano}";
            $caminho_arquivo = $upload_dir . DIRECTORY_SEPARATOR . $nome_arquivo;
            $this->arquivo->updateArquivoPathAndCodigo($arquivo_id, 'uploads/' . $nome_arquivo, $codigo_verificador);

            $assinantes = isset($_POST['assinantes']) ? $_POST['assinantes'] : [];
            foreach ($assinantes as $assinante_id) {
                $this->assinatura->createAssinatura($arquivo_id, $assinante_id);
            }

            header("Location: ../Processo/documentos.php?processo_id=$processo_id&id=$estabelecimento_id");
            exit();
        }
    }

    public function gerarPdf($arquivo_id)
    {
        $this->writeLog("Iniciando a geração do PDF para o arquivo ID: $arquivo_id");

        $arquivo = $this->arquivo->getArquivoById($arquivo_id);
        if (!$arquivo || !isset($arquivo['status']) || $arquivo['status'] != 'assinado') {
            $this->writeLog("Arquivo não encontrado ou não assinado para o ID: $arquivo_id");
            return false;
        }

        $processo_id = $arquivo['processo_id'];
        $tipo_documento = $arquivo['tipo_documento'];
        $ano = date('Y');
        $upload_dir = __DIR__ . '/../uploads';
        $nome_arquivo = "{$tipo_documento}_{$arquivo_id}_{$ano}.pdf";
        $caminho_arquivo = $upload_dir . DIRECTORY_SEPARATOR . $nome_arquivo;
        $codigo_verificador = $arquivo['codigo_verificador'];

        // Obter o ID do estabelecimento a partir do processo
        $processo = $this->arquivo->getProcessoInfo($processo_id);
        if (!$processo) {
            $this->writeLog("Processo não encontrado para ID: $processo_id");
            return false;
        }

        $this->writeLog("Processo encontrado: " . json_encode($processo));

        if (!isset($processo['estabelecimento_id']) || empty($processo['estabelecimento_id'])) {
            $this->writeLog("Processo sem estabelecimento associado para ID: $processo_id");
            return false;
        }

        $estabelecimento_id = $processo['estabelecimento_id'];
        $this->writeLog("Estabelecimento ID: $estabelecimento_id associado ao processo ID: $processo_id");

        $estabelecimento = $this->estabelecimento->findById($estabelecimento_id);
        if (!$estabelecimento) {
            $this->writeLog("Estabelecimento não encontrado para ID: $estabelecimento_id");
            return false;
        }

        $this->writeLog("Estabelecimento encontrado: " . json_encode($estabelecimento));

        // Obter informações do responsável legal
        $responsavel_legal = $this->responsavelLegal->findByEstabelecimentoId($estabelecimento_id);
        if ($responsavel_legal) {
            $responsavel_legal['cpf'] = $this->mask($responsavel_legal['cpf'], '###.###.###-##');
        } else {
            $responsavel_legal = ['nome' => 'N/A', 'cpf' => 'N/A'];
        }

        // Obter informações do responsável técnico
        $responsavel_tecnico = $this->responsavelTecnico->findByEstabelecimentoId($estabelecimento_id);
        if ($responsavel_tecnico) {
            $responsavel_tecnico['cpf'] = $this->mask($responsavel_tecnico['cpf'], '###.###.###-##');
        } else {
            $responsavel_tecnico = ['nome' => 'N/A', 'cpf' => 'N/A'];
        }

        $processo_info = $this->arquivo->getProcessoInfo($processo_id);
        if (!$processo_info) {
            $this->writeLog("Processo não encontrado para ID: $processo_id");
            return false;
        }

        $numero_processo = str_replace("", "", $processo_info['numero_processo']);
        $tipo_processo = $processo_info['tipo_processo'];
        $link_verificacao = "https://vigilancia-to.com.br/visamunicipal/views/Arquivos/verificar.php";

        // Desativar avisos de depreciação temporariamente
        error_reporting(E_ALL & ~E_DEPRECATED);

        // Código que gera o QR Code
        $qrCode = new QrCode($link_verificacao);
        $qrCode->setSize(150);
        $writer = new PngWriter();
        $qrCodePath = $upload_dir . DIRECTORY_SEPARATOR . 'qrcode_' . time() . '.png';
        $writer->writeFile($qrCode, $qrCodePath);

        // Restaurar a configuração original de relatórios de erro
        error_reporting(E_ALL);

        // Obter a logomarca do município do estabelecimento
        $logomarca_info = $this->logomarca->getLogomarcaByMunicipio($estabelecimento['municipio']);
        $logoPath = $logomarca_info ? $logomarca_info['caminho_logomarca'] : null;
        $espacamento = $logomarca_info ? $logomarca_info['espacamento'] : 40;

        $numero_arquivo = $arquivo_id;
        $nome_arquivo_header = "{$tipo_documento}: {$numero_arquivo}.{$ano}";

        // Create PDF
        $pdf = new CustomPDF();
        $pdf->setQrCodePath($qrCodePath);
        $pdf->setLogoPath($logoPath);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 8);

        // Add header
        $pdf->Ln($espacamento); // ESPAÇO ENTRE LOGOMARCA E TEXTO
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->Cell(0, 0, "{$nome_arquivo_header}", 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 5, "{$tipo_processo}: {$numero_processo}", 0, 1, 'C');
        $pdf->Ln(10);

        // Styles
        $styles = "
        <style>
            h2 {
                font-size: 10pt;
                font-weight: 100;
            }
            table {
                width: 100%;
                font-size: 10pt;
                border-collapse: collapse;
            }
            td {
                padding: 5px;
                border: 1px solid #808080;
            }
            .header {
                font-size: 16pt;
                text-align: center;
                color: #808080;
            }
            .centered {
                text-align: center;
                color: #808080;
                font-size: 10pt;
            }
        </style>
    ";

        // Add content
        $informacoes_estabelecimento = "
        {$styles}
        <table>
            <tr>
                <td><strong>NOME FANTASIA: </strong> {$estabelecimento['nome_fantasia']}</td>
                <td><strong>RAZÃO SOCIAL:</strong> {$estabelecimento['razao_social']}</td>
            </tr>
            <tr>
                <td><strong>CNPJ:</strong> {$estabelecimento['cnpj']}</td>
                <td><strong>ENDEREÇO:</strong> {$estabelecimento['logradouro']}, {$estabelecimento['numero']}, {$estabelecimento['bairro']}, {$estabelecimento['municipio']}-{$estabelecimento['uf']}</td>
            </tr>
            <tr>
                <td><strong>CEP:</strong> {$estabelecimento['cep']}</td>
                <td><strong>TELEFONE:</strong> {$estabelecimento['ddd_telefone_1']} / {$estabelecimento['ddd_telefone_2']}</td>
            </tr>
        </table>
        <table>
            <tr>
                <td><strong>RESPONSÁVEL LEGAL:</strong> {$responsavel_legal['nome']}</td>
                <td><strong>CPF:</strong> {$responsavel_legal['cpf']}</td>
            </tr>
            <tr>
                <td><strong>RESPONSÁVEL TÉCNICO:</strong> {$responsavel_tecnico['nome']}</td>
                <td><strong>CPF:</strong> {$responsavel_tecnico['cpf']}</td>
            </tr>
        </table>
    ";

        $conteudo_completo = $informacoes_estabelecimento . $arquivo['conteudo'];
        $pdf->writeHTML($conteudo_completo);

        // Add signatures
        $assinaturas = [];
        $assinantes = $this->assinatura->getAssinaturasPorArquivo($arquivo_id);
        foreach ($assinantes as $assinante) {
            if ($assinante['status'] == 'assinado') {
                $data_assinatura = date('d/m/Y H:i:s', strtotime($assinante['data_assinatura']));
                $assinaturas[] = "Documento assinado eletronicamente por {$assinante['nome_completo']} em {$data_assinatura}";
            }
        }

        $assinaturas_html = implode('<br>', $assinaturas);
        $rodape = "
        <div style='font-size: 8pt; text-align: center;'>
            <p>{$assinaturas_html}</p>
            <p>A autenticidade do documento pode ser conferida no link: <a href='{$link_verificacao}'>{$link_verificacao}</a> informando o código verificador {$codigo_verificador}</p>
        </div>
    ";

        $pdf->writeHTML($rodape, true, false, true, false, '');

        $pdf->Output($caminho_arquivo, 'F');

        // Verificar se o arquivo foi criado
        if (file_exists($caminho_arquivo)) {
            $this->writeLog("PDF gerado com sucesso: $caminho_arquivo");

            // Update arquivo path and status
            $this->arquivo->updateArquivoPathAndCodigo($arquivo_id, 'uploads/' . $nome_arquivo, $codigo_verificador);
            return true;
        } else {
            $this->writeLog("Falha ao gerar o PDF: $caminho_arquivo");
            return false;
        }
    }



    public function getModeloDocumento($tipo_documento)
    {
        $stmt = $this->conn->prepare("SELECT conteudo FROM modelos_documentos WHERE tipo_documento = ?");
        $stmt->bind_param("s", $tipo_documento);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['conteudo'];
        }
        return "";
    }

    public function createDraft()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['processo_id']) && isset($_POST['conteudo']) && isset($_POST['tipo_documento'])) {
            $processo_id = $_POST['processo_id'];
            $sigiloso = isset($_POST['sigiloso']) ? intval($_POST['sigiloso']) : 0;
            $conteudo = $_POST['conteudo'];
            $tipo_documento = $_POST['tipo_documento'];
            $ano = date('Y');

            $estabelecimento_id = $_POST['estabelecimento_id'];
            $assinantes = isset($_POST['assinantes']) ? $_POST['assinantes'] : [];

            $arquivo_id = $this->arquivo->createDraftArquivo($processo_id, $tipo_documento, $conteudo, $sigiloso);
            foreach ($assinantes as $assinante_id) {
                $this->assinatura->createAssinatura($arquivo_id, $assinante_id);
            }

            header("Location: ../Processo/documentos.php?processo_id=$processo_id&id=" . $estabelecimento_id);
            exit();
        }
    }


    private function mask($val, $mask)
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i < strlen($mask); $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) {
                    $maskared .= $val[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }
        return $maskared;
    }
}

$arquivoController = new ArquivoController($conn);

if (isset($_GET['acao'])) {
    $acao = $_GET['acao'];
    if ($acao == 'create') {
        $arquivoController->create();
    } elseif ($acao == 'previsualizar') {
        //$arquivoController->previsualizar();
    }
}
