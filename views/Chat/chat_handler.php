<?php
require_once '../../conf/database.php';
require_once '../../models/Processo.php';
require_once '../../models/Estabelecimento.php';

session_start();

$data = json_decode(file_get_contents('php://input'), true);
$message = $data['message'];
$response = ['reply' => ''];

if (!isset($_SESSION['chat_state'])) {
    $_SESSION['chat_state'] = 'initial';
}

$processoModel = new Processo($conn);
$estabelecimentoModel = new Estabelecimento($conn);

switch ($_SESSION['chat_state']) {
    case 'initial':
        if ($message === '1') {
            $_SESSION['chat_state'] = 'consulta';
            $response['reply'] = 'Por favor, digite o CNPJ ou nome do estabelecimento:';
        } elseif ($message === '2') {
            $_SESSION['chat_state'] = 'licenciamento';
            $response['reply'] = 'Para mais informações sobre os documentos necessários para o processo de licenciamento sanitário, visite: [link para o sistema]';
        } else {
            $response['reply'] = 'Opção inválida. Por favor, escolha uma opção:\n1. Consultar estabelecimento e andamento do processo\n2. Saber quais documentos são necessários para o processo de licenciamento sanitário.';
        }
        break;

    case 'consulta':
        if (preg_match('/^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$/', $message)) {
            // Se a mensagem for um CNPJ
            $estabelecimento = $estabelecimentoModel->findByCnpjAndUsuario($message, $_SESSION['user']['id']);
            if ($estabelecimento) {
                $processos = $processoModel->getProcessosByEstabelecimento($estabelecimento['id']);
                if ($processos) {
                    $response['reply'] = "Processos encontrados para o estabelecimento {$estabelecimento['nome_fantasia']}:\n";
                    foreach ($processos as $processo) {
                        $response['reply'] .= "- Estabelecimento: {$estabelecimento['nome_fantasia']}, Número do Processo: {$processo['numero_processo']}, Status: {$processo['status']}\n";
                    }
                } else {
                    $response['reply'] = "Nenhum processo encontrado para o CNPJ $message.";
                }
            } else {
                $response['reply'] = "Nenhum estabelecimento encontrado para o CNPJ $message.";
            }
        } else {
            // Se a mensagem for um nome do estabelecimento
            $estabelecimentos = $estabelecimentoModel->searchByNameAndUsuario($message, $_SESSION['user']['id']);
            if ($estabelecimentos) {
                foreach ($estabelecimentos as $estabelecimento) {
                    $processos = $processoModel->getProcessosByEstabelecimento($estabelecimento['id']);
                    if ($processos) {
                        $response['reply'] .= "Processos encontrados para o estabelecimento {$estabelecimento['nome_fantasia']}:\n";
                        foreach ($processos as $processo) {
                            $response['reply'] .= "- Estabelecimento: {$estabelecimento['nome_fantasia']}, Número do Processo: {$processo['numero_processo']}, Status: {$processo['status']}\n";
                        }
                    } else {
                        $response['reply'] .= "Nenhum processo encontrado para o estabelecimento {$estabelecimento['nome_fantasia']}.\n";
                    }
                }
            } else {
                $response['reply'] = "Nenhum estabelecimento encontrado com o nome $message.";
            }
        }
        $_SESSION['chat_state'] = 'initial';
        $response['reply'] .= "\nPor favor, escolha uma opção:\n1. Consultar estabelecimento e andamento do processo\n2. Saber quais documentos são necessários para o processo de licenciamento sanitário.";
        break;

    case 'licenciamento':
        $response['reply'] = 'Para mais informações sobre os documentos necessários para o processo de licenciamento sanitário, visite: [link para o sistema]';
        $_SESSION['chat_state'] = 'initial';
        break;
}

echo json_encode($response);
?>
