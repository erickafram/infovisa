<?php
require_once '../../conf/database.php';
require_once '../../models/Processo.php';
require_once '../../models/Estabelecimento.php';

session_start();

// Inclua o arquivo FAQ
include 'faq.php';

$data = json_decode(file_get_contents('php://input'), true);
$message = $data['message'];
$response = ['reply' => ''];

if (!isset($_SESSION['chat_state'])) {
    $_SESSION['chat_state'] = 'initial';
}

$processoModel = new Processo($conn);
$estabelecimentoModel = new Estabelecimento($conn);

// Função para buscar respostas no FAQ com base em palavras-chave
function searchFaq($message, $faq) {
    $message = strtolower($message);
    $keywords = explode(' ', $message);
    foreach ($faq as $question => $answer) {
        foreach ($keywords as $keyword) {
            if (stripos($question, $keyword) !== false) {
                return $answer;
            }
        }
    }
    return null;
}

switch ($_SESSION['chat_state']) {
    case 'initial':
        if ($message === '1') {
            $_SESSION['chat_state'] = 'consulta';
            $response['reply'] = 'Por favor, digite o CNPJ ou nome do estabelecimento:';
        } elseif ($message === '2') {
            $_SESSION['chat_state'] = 'licenciamento';
            $response['reply'] = 'Para mais informações sobre os documentos necessários para o processo de licenciamento sanitário, visite: [link para o sistema]';
            $response['reply'] .= "\nDeseja realizar uma nova consulta ou finalizar o atendimento?\n1. Nova consulta\n2. Finalizar atendimento\n3. Iniciar nova conversa";
            $_SESSION['chat_state'] = 'after_consulta';
        } elseif ($message === '3') {
            $_SESSION['chat_state'] = 'faq';
            $response['reply'] = 'Digite sua dúvida ou palavras-chave relacionadas, e eu tentarei encontrar a resposta no FAQ:';
        } else {
            $response['reply'] = "Opção inválida. Por favor, escolha uma das opções abaixo para continuar:\n1. Consultar estabelecimento e saber andamento do processo\n2. Saber quais documentos são necessários para o processo de licenciamento sanitário\n3. Tire suas dúvidas.";
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
        $response['reply'] .= "\nDeseja realizar uma nova consulta ou finalizar o atendimento?\n1. Nova consulta\n2. Finalizar atendimento\n3. Iniciar nova conversa";
        $_SESSION['chat_state'] = 'after_consulta';
        break;

    case 'after_consulta':
        if ($message === '1') {
            $_SESSION['chat_state'] = 'consulta';
            $response['reply'] = 'Por favor, digite o CNPJ ou nome do estabelecimento:';
        } elseif ($message === '3') {
            $_SESSION['chat_state'] = 'initial';
            $response['reply'] = "Por favor, escolha uma das opções abaixo para continuar:\n1. Consultar estabelecimento e saber andamento do processo\n2. Saber quais documentos são necessários para o processo de licenciamento sanitário\n3. Tire suas dúvidas.";
        } else {
            $response['reply'] = 'Atendimento finalizado. Obrigado por usar o serviço de chat.';
            $_SESSION['chat_state'] = 'initial';
        }
        break;

    case 'licenciamento':
        $response['reply'] = 'Para mais informações sobre os documentos necessários para o processo de licenciamento sanitário, visite: [link para o sistema]';
        $response['reply'] .= "\nDeseja realizar uma nova consulta ou finalizar o atendimento?\n1. Nova consulta\n2. Finalizar atendimento\n3. Iniciar nova conversa";
        $_SESSION['chat_state'] = 'after_consulta';
        break;

    case 'faq':
        $faqResponse = searchFaq($message, $faq);
        if ($faqResponse) {
            $response['reply'] = $faqResponse;
        } else {
            $response['reply'] = "Desculpe, não encontrei uma resposta para sua dúvida. Aqui estão as principais dúvidas do sistema:\n";
            foreach ($faq as $question => $answer) {
                $response['reply'] .= "- $question\n";
            }
            $response['reply'] .= "\nPara mais dúvidas, visite: [link para arquivo de perguntas e respostas]";
            $_SESSION['chat_state'] = 'initial';
        }
        break;

    default:
        $response['reply'] = 'Desculpe, não entendi sua pergunta. Tente novamente ou escolha uma das opções disponíveis.';
        break;
}

echo json_encode($response);
?>
