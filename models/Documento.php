<?php
class Documento
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function createDocumento($processo_id, $nome_arquivo, $caminho_arquivo)
    {
        $stmt = $this->conn->prepare("INSERT INTO documentos (processo_id, nome_arquivo, caminho_arquivo, data_upload, status) VALUES (?, ?, ?, NOW(), 'pendente')");
        $stmt->bind_param("iss", $processo_id, $nome_arquivo, $caminho_arquivo);
        return $stmt->execute();
    }

    public function approveDocumento($documento_id)
    {
        $query = "UPDATE documentos SET status = 'aprovado' WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $documento_id);
        return $stmt->execute();
    }

    public function denyDocumento($documento_id, $motivo)
    {
        $query = "UPDATE documentos SET status = 'negado', motivo_negacao = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('si', $motivo, $documento_id);
        return $stmt->execute();
    }
    public function updateDocumentoNegado($documento_id, $nome_arquivo, $caminho_arquivo)
    {
        $stmt = $this->conn->prepare("UPDATE documentos SET nome_arquivo = ?, caminho_arquivo = ?, status = 'pendente', motivo_negacao = NULL, data_upload = NOW() WHERE id = ?");
        $stmt->bind_param("ssi", $nome_arquivo, $caminho_arquivo, $documento_id);
        return $stmt->execute();
    }

    public function getDocumentosByProcesso($processo_id)
    {
        $query = "SELECT * FROM documentos WHERE processo_id = ? ORDER BY data_upload DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $processo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function updateNomeDocumento($documento_id, $novo_nome)
    {
        $stmt = $this->conn->prepare("UPDATE documentos SET nome_arquivo = ? WHERE id = ?");
        $stmt->bind_param("si", $novo_nome, $documento_id);
        return $stmt->execute();
    }

    public function deleteDocumento($documento_id)
    {
        // Primeiro, obtenha o caminho do arquivo para deletar o arquivo físico
        $stmt = $this->conn->prepare("SELECT caminho_arquivo FROM documentos WHERE id = ?");
        $stmt->bind_param("i", $documento_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $doc = $result->fetch_assoc();
        if ($doc) {
            $caminhoCompleto = "../../" . $doc['caminho_arquivo']; // Ajuste o caminho conforme necessário
            if (file_exists($caminhoCompleto)) {
                unlink($caminhoCompleto);
            }
            // Depois, delete o registro do banco de dados
            $stmt = $this->conn->prepare("DELETE FROM documentos WHERE id = ?");
            $stmt->bind_param("i", $documento_id);
            return $stmt->execute();
        }
        return false;
    }

    public function deleteDocumentosByProcesso($processo_id)
    {
        $stmt = $this->conn->prepare("SELECT caminho_arquivo FROM documentos WHERE processo_id = ?");
        $stmt->bind_param("i", $processo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($doc = $result->fetch_assoc()) {
            $caminhoCompleto = "../../" . $doc['caminho_arquivo']; // Ajuste o caminho conforme necessário
            if (file_exists($caminhoCompleto)) {
                unlink($caminhoCompleto);
            }
        }
        $stmt = $this->conn->prepare("DELETE FROM documentos WHERE processo_id = ?");
        $stmt->bind_param("i", $processo_id);
        return $stmt->execute();
    }

    public function findById($documento_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM documentos WHERE id = ?");
        $stmt->bind_param("i", $documento_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
