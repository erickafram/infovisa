<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../../conf/database.php';
    require_once '../../models/LogVisualizacao.php';

    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['arquivo_id']) && isset($data['usuario_id'])) {
        $logVisualizacaoModel = new LogVisualizacao($conn);
        $logVisualizacaoModel->registrarVisualizacao($data['usuario_id'], $data['arquivo_id']);
        echo json_encode(['message' => 'Visualização registrada com sucesso.']);
    } else {
        echo json_encode(['message' => 'Dados inválidos.']);
    }
} else {
    echo json_encode(['message' => 'Método não permitido.']);
}
?>
