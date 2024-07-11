<?php
class LogVisualizacao
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function registrarVisualizacao($usuario_id, $arquivo_id)
    {
        $stmt = $this->conn->prepare("INSERT INTO log_visualizacoes (usuario_id, arquivo_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $usuario_id, $arquivo_id);
        return $stmt->execute();
    }
}
?>
