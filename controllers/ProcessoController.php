<?php
require_once '../conf/database.php';
require_once '../models/Processo.php';

$log_file = '../logs/processo_controller.log'; // Defina o caminho para o arquivo de log

// Verificar se o diretório de logs existe, se não existir, criá-lo
if (!file_exists(dirname($log_file))) {
    mkdir(dirname($log_file), 0777, true);
}

function log_message($message) {
    global $log_file;
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, $log_file);
}

if (isset($_GET['action']) && $_GET['action'] === 'finalize') {
    if (isset($_GET['id'])) {
        $processo_id = $_GET['id'];
        log_message("Tentando finalizar o processo com ID $processo_id");

        // Atualizar status para 'resolvido' na tabela processos_responsaveis
        $stmt = $conn->prepare("UPDATE processos_responsaveis SET status = 'resolvido' WHERE processo_id = ?");
        if ($stmt === false) {
            log_message("Erro ao preparar a consulta: " . $conn->error);
            header("Location: ../views/Dashboard/dashboard.php?error=Erro ao preparar a consulta.");
            exit();
        }

        $stmt->bind_param('i', $processo_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                log_message("Processo_responsavel com processo_id $processo_id finalizado com sucesso.");
                header("Location: ../views/Dashboard/dashboard.php?success=Processo finalizado com sucesso");
                exit();
            } else {
                log_message("Nenhuma linha foi afetada ao finalizar o processo_responsavel com processo_id $processo_id.");
                header("Location: ../views/Dashboard/dashboard.php?error=Nenhuma linha foi afetada. Verifique o ID do processo.");
                exit();
            }
        } else {
            $error = $stmt->error;
            log_message("Erro ao finalizar o processo_responsavel com processo_id $processo_id: $error");
            header("Location: ../views/Dashboard/dashboard.php?error=Erro ao finalizar o processo: " . urlencode($error));
            exit();
        }
    } else {
        log_message("ID do processo não fornecido.");
        header("Location: ../views/Dashboard/dashboard.php?error=ID do processo não fornecido.");
        exit();
    }
} else {
    log_message("Ação não reconhecida.");
    header("Location: ../views/Dashboard/dashboard.php?error=Ação não reconhecida.");
    exit();
}
?>
