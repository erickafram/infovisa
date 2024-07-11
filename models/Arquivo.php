<?php
class Arquivo
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getConnection()
    {
        return $this->conn;
    }
    public function createArquivo($processo_id, $tipo_documento, $caminho_arquivo, $codigo_verificador, $conteudo, $sigiloso)
    {
        // Primeira inserção sem o número do arquivo
        $query = "INSERT INTO arquivos (processo_id, tipo_documento, caminho_arquivo, codigo_verificador, data_upload, numero_arquivo, conteudo, status, sigiloso) VALUES (?, ?, ?, ?, NOW(), 0, ?, 'rascunho', ?)";
        $stmt = $this->conn->prepare($query);

        if ($stmt === false) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("issssi", $processo_id, $tipo_documento, $caminho_arquivo, $codigo_verificador, $conteudo, $sigiloso);
        error_log("Query: $query");
        error_log("Parameters: " . json_encode([$processo_id, $tipo_documento, $caminho_arquivo, $codigo_verificador, $conteudo]));

        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }

        $arquivo_id = $stmt->insert_id;

        // Atualiza o numero_arquivo para ser igual ao id do arquivo
        $update_query = "UPDATE arquivos SET numero_arquivo = ? WHERE id = ?";
        $update_stmt = $this->conn->prepare($update_query);

        if ($update_stmt === false) {
            error_log("Prepare failed (update): " . $this->conn->error);
            return false;
        }

        $update_stmt->bind_param("ii", $arquivo_id, $arquivo_id);

        if (!$update_stmt->execute()) {
            error_log("Execute failed (update): " . $update_stmt->error);
            return false;
        }

        return $arquivo_id;
    }

    public function createDraftArquivo($processo_id, $tipo_documento, $conteudo, $sigiloso)
    {
        $stmt = $this->conn->prepare("INSERT INTO arquivos (processo_id, tipo_documento, caminho_arquivo, codigo_verificador, data_upload, numero_arquivo, conteudo, status, sigiloso) VALUES (?, ?, '', '', NOW(), 0, ?, 'rascunho', ?)");
        $stmt->bind_param("issi", $processo_id, $tipo_documento, $conteudo, $sigiloso);
        $stmt->execute();
        return $stmt->insert_id;
    }

    public function getArquivosByProcessoRascunho($processoId)
    {
        $sql = "SELECT * FROM arquivos WHERE processo_id = ? AND status != 'rascunho' AND sigiloso = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $processoId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }


    public function getVisualizacoes($arquivoId)
    {
        $sql = "SELECT MAX(lv.data_visualizacao) as data_visualizacao, u.nome_completo 
                FROM log_visualizacoes lv 
                JOIN usuarios_externos u ON lv.usuario_id = u.id 
                WHERE lv.arquivo_id = ?
                GROUP BY lv.usuario_id, u.nome_completo";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $arquivoId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                return $result->fetch_all(MYSQLI_ASSOC);
            } else {
                error_log("Erro ao obter resultado: " . $this->conn->error);
            }
        } else {
            error_log("Erro ao preparar consulta: " . $this->conn->error);
        }
        return [];
    }

    public function getArquivosNaoVisualizados($usuarioId)
    {
        $sql = "
            SELECT d.id, d.tipo_documento AS nome_arquivo, d.caminho_arquivo, p.numero_processo, e.nome_fantasia, p.id as processo_id
            FROM arquivos d
            JOIN processos p ON d.processo_id = p.id
            JOIN estabelecimentos e ON p.estabelecimento_id = e.id
            JOIN usuarios_estabelecimentos ue ON e.id = ue.estabelecimento_id
            LEFT JOIN log_visualizacoes lv ON d.id = lv.arquivo_id AND lv.usuario_id = ?
            WHERE lv.id IS NULL 
              AND d.sigiloso = 0 
              AND ue.usuario_id = ? 
              AND d.status = 'assinado' 
              AND NOT EXISTS (
                  SELECT 1
                  FROM assinaturas a
                  WHERE a.arquivo_id = d.id
                  AND a.status != 'assinado'
              )
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $usuarioId, $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }



    public function isVisualizadoPorUsuarioExterno($arquivoId)
    {
        $sql = "SELECT COUNT(*) AS count FROM log_visualizacoes WHERE arquivo_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $arquivoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }


    public function getArquivoByCodigo($codigo_verificador)
    {
        $stmt = $this->conn->prepare("SELECT * FROM arquivos WHERE codigo_verificador = ?");
        $stmt->bind_param("s", $codigo_verificador);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getArquivosByProcesso($processo_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM arquivos WHERE processo_id = ? ORDER BY data_upload DESC");
        $stmt->bind_param("i", $processo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function todasAssinaturasConcluidas($arquivo_id)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as pendentes FROM assinaturas WHERE arquivo_id = ? AND status = 'pendente'");
        $stmt->bind_param("i", $arquivo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['pendentes'] == 0;
    }

    public function getArquivosComAssinaturasCompletas($processoId)
    {
        $query = "
        SELECT a.*
        FROM arquivos a
        LEFT JOIN assinaturas ass ON a.id = ass.arquivo_id
        WHERE a.processo_id = ? AND ass.status = 'assinado' AND a.sigiloso = 0 AND a.status != 'rascunho'
        GROUP BY a.id
        HAVING COUNT(ass.id) = (
            SELECT COUNT(*)
            FROM assinaturas
            WHERE arquivo_id = a.id
        )
    ";


        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $processoId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function todasAssinaturasPendentes($arquivo_id)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM assinaturas WHERE arquivo_id = ? AND status = 'pendente'");
        $stmt->bind_param("i", $arquivo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'] > 0;
    }

    public function arquivoFinalizadoComAssinaturasPendentes($arquivo_id)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM assinaturas WHERE arquivo_id = ? AND status = 'pendente'");
        $stmt->bind_param("i", $arquivo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt = $this->conn->prepare("SELECT status FROM arquivos WHERE id = ?");
        $stmt->bind_param("i", $arquivo_id);
        $stmt->execute();
        $result_status = $stmt->get_result();
        $row_status = $result_status->fetch_assoc();

        return $row_status['status'] == 'finalizado' && $row['total'] > 0;
    }



    public function getArquivoById($arquivo_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM arquivos WHERE id = ?");
        $stmt->bind_param("i", $arquivo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    public function getProcessoIdByArquivoId($arquivo_id)
    {
        $stmt = $this->conn->prepare("SELECT processo_id FROM arquivos WHERE id = ?");
        $stmt->bind_param("i", $arquivo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['processo_id'];
    }

    public function getArquivosNaoSigilososByProcesso($processoId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM arquivos WHERE processo_id = ? AND sigiloso = 0");
        $stmt->bind_param("i", $processoId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function updateArquivo($arquivo_id, $tipo_documento, $conteudo, $sigiloso)
    {
        $stmt = $this->conn->prepare("UPDATE arquivos SET tipo_documento = ?, conteudo = ?, sigiloso = ? WHERE id = ?");
        $stmt->bind_param("ssii", $tipo_documento, $conteudo, $sigiloso, $arquivo_id);
        return $stmt->execute();
    }

    public function updateArquivoPathAndCodigo($arquivo_id, $caminho_arquivo, $codigo_verificador)
    {
        $stmt = $this->conn->prepare("UPDATE arquivos SET caminho_arquivo = ?, codigo_verificador = ? WHERE id = ?");
        $stmt->bind_param("ssi", $caminho_arquivo, $codigo_verificador, $arquivo_id);
        $stmt->execute();
    }

    public function atualizarStatusAssinado($arquivo_id)
    {
        $sql = "UPDATE arquivos SET status = 'assinado' WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $arquivo_id);
        $stmt->execute();
        $stmt->close();
    }
    


    public function deleteArquivo($arquivo_id)
    {
        // Primeiro, obtenha o caminho do arquivo para deletar o arquivo físico
        $stmt = $this->conn->prepare("SELECT caminho_arquivo FROM arquivos WHERE id = ?");
        $stmt->bind_param("i", $arquivo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $arquivo = $result->fetch_assoc();
        if ($arquivo) {
            $caminho_absoluto = realpath(__DIR__ . '/../../' . $arquivo['caminho_arquivo']);
            if (file_exists($caminho_absoluto)) {
                unlink($caminho_absoluto);
            }
            // Depois, delete o registro do banco de dados
            $stmt = $this->conn->prepare("DELETE FROM arquivos WHERE id = ?");
            $stmt->bind_param("i", $arquivo_id);
            return $stmt->execute();
        }
        return false;
    }

    public function getArquivosRascunho($limit = 10, $offset = 0, $search = '')
    {
        $query = "
        SELECT a.*, p.numero_processo
        FROM arquivos a
        JOIN processos p ON a.processo_id = p.id
        WHERE a.status = 'rascunho'
        AND (a.tipo_documento LIKE ? OR p.numero_processo LIKE ?)
        LIMIT ? OFFSET ?
    ";
        $stmt = $this->conn->prepare($query);
        $searchParam = "%" . $search . "%";
        $stmt->bind_param("ssii", $searchParam, $searchParam, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function countArquivosRascunho($search = '')
    {
        $query = "
        SELECT COUNT(*) as total
        FROM arquivos a
        JOIN processos p ON a.processo_id = p.id
        WHERE a.status = 'rascunho'
        AND (a.tipo_documento LIKE ? OR p.numero_processo LIKE ?)
    ";
        $stmt = $this->conn->prepare($query);
        $searchParam = "%" . $search . "%";
        $stmt->bind_param("ss", $searchParam, $searchParam);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }


    public function getNumeroProcesso($processo_id)
    {
        $stmt = $this->conn->prepare("SELECT numero_processo FROM processos WHERE id = ?");
        $stmt->bind_param("i", $processo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $processo = $result->fetch_assoc();
        return $processo ? $processo['numero_processo'] : null;
    }

    public function getDocumentosParaFinalizar($search, $limit, $offset)
    {
        $searchParam = "%" . $search . "%";
        $sql = "SELECT a.*, p.numero_processo, e.id AS estabelecimento_id
                FROM arquivos a
                JOIN processos p ON a.processo_id = p.id
                JOIN estabelecimentos e ON p.estabelecimento_id = e.id
                WHERE a.caminho_arquivo = '' AND (a.tipo_documento LIKE ? OR p.numero_processo LIKE ?)
                LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssii", $searchParam, $searchParam, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getTotalDocumentosParaFinalizar($search)
    {
        $searchParam = "%" . $search . "%";
        $sql = "SELECT COUNT(*) AS total
                FROM arquivos a
                JOIN processos p ON a.processo_id = p.id
                WHERE a.caminho_arquivo = '' AND (a.tipo_documento LIKE ? OR p.numero_processo LIKE ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $searchParam, $searchParam);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function getProcessoInfo($processo_id)
    {
        $stmt = $this->conn->prepare("SELECT estabelecimento_id, numero_processo, tipo_processo FROM processos WHERE id = ?");
        $stmt->bind_param("i", $processo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
}
