<?php
session_start();
require_once '../../conf/database.php';

// Verificação de autenticação
if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
    exit();
}

$user_id = $_SESSION['user']['id'];
$arquivo_id = isset($_POST['arquivo_id']) ? intval($_POST['arquivo_id']) : 0;

if ($arquivo_id > 0) {
    $sql = "INSERT INTO log_visualizacoes (usuario_id, arquivo_id, data_visualizacao) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $user_id, $arquivo_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao registrar visualização.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID do arquivo inválido.']);
}
?>
