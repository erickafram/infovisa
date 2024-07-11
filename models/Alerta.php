<?php
class Alerta
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAssinaturasPendentes($usuario_id)
    {
        $stmt = $this->conn->prepare("
            SELECT a.id, a.arquivo_id, ar.tipo_documento, ar.processo_id, ar.data_upload, ar.caminho_arquivo, p.estabelecimento_id
            FROM assinaturas a
            JOIN arquivos ar ON a.arquivo_id = ar.id
            JOIN processos p ON ar.processo_id = p.id
            WHERE a.usuario_id = ? AND a.status = 'pendente'
        ");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAssinaturasRascunho($usuario_id)
    {
        $stmt = $this->conn->prepare("
            SELECT a.id, a.arquivo_id, ar.tipo_documento, ar.processo_id, ar.data_upload, ar.caminho_arquivo, p.estabelecimento_id, ar.status
            FROM assinaturas a
            JOIN arquivos ar ON a.arquivo_id = ar.id
            JOIN processos p ON ar.processo_id = p.id
            WHERE a.usuario_id = ? AND a.status AND ar.caminho_arquivo = ''
        ");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
}
?>
