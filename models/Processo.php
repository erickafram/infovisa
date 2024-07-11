<?php
class Processo
{
    private $conn;
    private $lastError;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function createProcesso($estabelecimento_id, $tipo_processo)
    {
        $anoAtual = date('Y');
        
        // Busca o maior número de processo existente para o ano atual
        $stmt = $this->conn->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(numero_processo, '/', -1) AS UNSIGNED)) as max_numero FROM processos WHERE YEAR(data_abertura) = ?");
        $stmt->bind_param("i", $anoAtual);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        // Incrementa o maior número de processo existente
        $maxNumero = $row['max_numero'] ? $row['max_numero'] : 0;
        $proximoNumero = $maxNumero + 1;
    
        // Formata o número do processo com o ano e o próximo número
        $numero_processo = sprintf("%s/%05d", $anoAtual, $proximoNumero);
    
        // Insere o novo processo no banco de dados
        $stmt = $this->conn->prepare("INSERT INTO processos (estabelecimento_id, tipo_processo, data_abertura, numero_processo, status) VALUES (?, ?, NOW(), ?, 'ATIVO')");
        $stmt->bind_param("iss", $estabelecimento_id, $tipo_processo, $numero_processo);
        return $stmt->execute();
    }
    

    public function getAlertasCountByUsuario($usuario_id)
    {
        $sql = "
            SELECT COUNT(*) as count
            FROM alertas_processo a
            JOIN processos p ON a.processo_id = p.id
            JOIN estabelecimentos e ON p.estabelecimento_id = e.id
            JOIN usuarios_estabelecimentos ue ON e.id = ue.estabelecimento_id
            WHERE ue.usuario_id = ? AND a.status != 'FINALIZADO'
        ";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die('Erro na preparação da consulta: ' . $this->conn->error);
        }
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }


    public function getAlertasByUsuario($usuario_id)
    {
        $sql = "
            SELECT a.*, p.numero_processo, p.tipo_processo, e.nome_fantasia AS empresa_nome
            FROM alertas_processo a
            JOIN processos p ON a.processo_id = p.id
            JOIN estabelecimentos e ON p.estabelecimento_id = e.id
            JOIN usuarios_estabelecimentos ue ON e.id = ue.estabelecimento_id
            WHERE ue.usuario_id = ? AND a.status != 'FINALIZADO'
            ORDER BY a.prazo ASC
        ";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die('Erro na preparação da consulta: ' . $this->conn->error);
        }
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getProcessosParadosByUsuario($usuario_id)
    {
        $sql = "
            SELECT p.*, e.nome_fantasia AS empresa_nome
            FROM processos p
            JOIN estabelecimentos e ON p.estabelecimento_id = e.id
            JOIN usuarios_estabelecimentos ue ON e.id = ue.estabelecimento_id
            WHERE ue.usuario_id = ? AND p.status = 'PARADO' AND p.status != 'FINALIZADO'
        ";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die('Erro na preparação da consulta: ' . $this->conn->error);
        }
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProcessosParadosCountByUsuario($usuario_id)
    {
        $sql = "
            SELECT COUNT(*) as count
            FROM processos p
            JOIN estabelecimentos e ON p.estabelecimento_id = e.id
            JOIN usuarios_estabelecimentos ue ON e.id = ue.estabelecimento_id
            WHERE ue.usuario_id = ? AND p.status = 'PARADO'
        ";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die('Erro na preparação da consulta: ' . $this->conn->error);
        }
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function getProcessosComDocumentacaoPendente($municipioUsuario)
    {
        $query = "
        SELECT p.id AS processo_id, p.numero_processo, e.id AS estabelecimento_id, e.nome_fantasia, d.status
        FROM processos p
        JOIN documentos d ON p.id = d.processo_id
        JOIN estabelecimentos e ON p.estabelecimento_id = e.id
        WHERE d.status = 'pendente' AND e.municipio = ?
        GROUP BY p.id, p.numero_processo, e.id, e.nome_fantasia
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $municipioUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function createProcessoLicenciamento($estabelecimento_id)
    {
        $anoAtual = date('Y');
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM processos WHERE YEAR(data_abertura) = ?");
        $stmt->bind_param("i", $anoAtual);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total = $row['total'] + 1;

        $numero_processo = sprintf("%s/%05d", $anoAtual, $total);

        $stmt = $this->conn->prepare("INSERT INTO processos (estabelecimento_id, tipo_processo, data_abertura, numero_processo, status) VALUES (?, 'LICENCIAMENTO', NOW(), ?, 'ATIVO')");
        $stmt->bind_param("is", $estabelecimento_id, $numero_processo);
        return $stmt->execute();
    }


    public function createProcessoProjetoArquitetonico($estabelecimento_id)
    {
        $anoAtual = date('Y');
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM processos WHERE YEAR(data_abertura) = ?");
        $stmt->bind_param("i", $anoAtual);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total = $row['total'] + 1;

        $numero_processo = sprintf("%s/%05d", $anoAtual, $total);

        $stmt = $this->conn->prepare("INSERT INTO processos (estabelecimento_id, tipo_processo, data_abertura, numero_processo, status) VALUES (?, 'PROJETO ARQUITETÔNICO', NOW(), ?, 'ATIVO')");
        $stmt->bind_param("is", $estabelecimento_id, $numero_processo);
        return $stmt->execute();
    }



    public function checkProcessoExistente($estabelecimento_id, $anoAtual)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM processos WHERE estabelecimento_id = ? AND YEAR(data_abertura) = ? AND tipo_processo = 'LICENCIAMENTO'");
        $stmt->bind_param("ii", $estabelecimento_id, $anoAtual);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'] > 0;
    }

    public function searchProcessosPorMunicipio($search, $municipio, $isAdmin)
    {
        if ($isAdmin) {
            $query = "SELECT p.*, e.nome_fantasia, e.cnpj 
                      FROM processos p 
                      JOIN estabelecimentos e ON p.estabelecimento_id = e.id 
                      WHERE p.numero_processo LIKE ? OR e.nome_fantasia LIKE ? OR e.cnpj LIKE ?";

            $stmt = $this->conn->prepare($query);
            $searchParam = "%" . $search . "%";
            $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
        } else {
            $query = "SELECT p.*, e.nome_fantasia, e.cnpj 
                      FROM processos p 
                      JOIN estabelecimentos e ON p.estabelecimento_id = e.id 
                      WHERE e.municipio = ? AND (p.numero_processo LIKE ? OR e.nome_fantasia LIKE ? OR e.cnpj LIKE ?)";

            $stmt = $this->conn->prepare($query);
            $searchParam = "%" . $search . "%";
            $stmt->bind_param("ssss", $municipio, $searchParam, $searchParam, $searchParam);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $processos = [];
        while ($row = $result->fetch_assoc()) {
            $processos[] = $row;
        }

        return $processos;
    }



    public function getProcessosByEstabelecimento($estabelecimento_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM processos WHERE estabelecimento_id = ?");
        $stmt->bind_param("i", $estabelecimento_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProcessosAcompanhados($usuario_id)
    {
        $sql = "
        SELECT p.*, e.nome_fantasia, e.cnpj
        FROM processos_acompanhados pa
        JOIN processos p ON pa.processo_id = p.id
        JOIN estabelecimentos e ON p.estabelecimento_id = e.id
        WHERE pa.usuario_id = ?
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProcessosResponsaveisPorUsuario($usuario_id)
    {
        $sql = "SELECT p.id, p.numero_processo, e.nome_fantasia, pr.descricao, pr.status, p.estabelecimento_id
            FROM processos_responsaveis pr
            JOIN processos p ON pr.processo_id = p.id
            JOIN estabelecimentos e ON p.estabelecimento_id = e.id
            WHERE pr.usuario_id = ? AND pr.status = 'pendente'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllProcessos()
    {
        $stmt = $this->conn->prepare("
            SELECT p.*, e.nome_fantasia 
            FROM processos p
            JOIN estabelecimentos e ON p.estabelecimento_id = e.id
            ORDER BY p.data_abertura DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProcessosParados($municipio)
    {
        $stmt = $this->conn->prepare("
            SELECT p.*, e.nome_fantasia 
            FROM processos p
            JOIN estabelecimentos e ON p.estabelecimento_id = e.id
            WHERE p.status = 'PARADO' AND e.municipio = ?
        ");
        $stmt->bind_param("s", $municipio);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->conn->prepare("
            SELECT p.*, e.nome_fantasia, e.cnpj, e.logradouro, e.numero, e.complemento, e.bairro, e.ddd_telefone_1, e.ddd_telefone_2, e.municipio 
            FROM processos p
            JOIN estabelecimentos e ON p.estabelecimento_id = e.id
            WHERE p.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }


    public function deleteProcesso($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM processos WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function archiveProcesso($id)
    {
        $stmt = $this->conn->prepare("UPDATE processos SET status = 'ARQUIVADO' WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function searchProcessosPorMunicipioPaginacao($search, $municipio, $isAdmin, $limit, $offset, $pendentes = false, $status = '')
    {
        $searchParam = "%" . $search . "%";

        $query = "SELECT p.*, e.nome_fantasia, e.cnpj, 
                  (SELECT COUNT(*) FROM documentos d WHERE d.processo_id = p.id AND d.status = 'pendente') AS documentos_pendentes
                  FROM processos p 
                  JOIN estabelecimentos e ON p.estabelecimento_id = e.id 
                  WHERE (p.numero_processo LIKE ? OR e.nome_fantasia LIKE ? OR e.cnpj LIKE ?)";

        if (!$isAdmin) {
            $query .= " AND e.municipio = ?";
        }

        if (!empty($status)) {
            $query .= " AND p.status = ?";
        }

        if ($pendentes) {
            $query .= " HAVING documentos_pendentes > 0";
        }

        $query .= " ORDER BY p.numero_processo DESC LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($query);

        if (!$isAdmin) {
            if (!empty($status)) {
                $stmt->bind_param("sssssss", $searchParam, $searchParam, $searchParam, $municipio, $status, $limit, $offset);
            } else {
                $stmt->bind_param("ssssss", $searchParam, $searchParam, $searchParam, $municipio, $limit, $offset);
            }
        } else {
            if (!empty($status)) {
                $stmt->bind_param("ssssss", $searchParam, $searchParam, $searchParam, $status, $limit, $offset);
            } else {
                $stmt->bind_param("sssss", $searchParam, $searchParam, $searchParam, $limit, $offset);
            }
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $processos = [];
        while ($row = $result->fetch_assoc()) {
            $processos[] = $row;
        }

        return $processos;
    }


    public function countProcessosPorMunicipio($search, $municipio, $isAdmin)
    {
        if ($isAdmin) {
            $query = "SELECT COUNT(*) as total 
                      FROM processos p 
                      JOIN estabelecimentos e ON p.estabelecimento_id = e.id 
                      WHERE p.numero_processo LIKE ? OR e.nome_fantasia LIKE ? OR e.cnpj LIKE ?";

            $stmt = $this->conn->prepare($query);
            $searchParam = "%" . $search . "%";
            $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
        } else {
            $query = "SELECT COUNT(*) as total 
                      FROM processos p 
                      JOIN estabelecimentos e ON p.estabelecimento_id = e.id 
                      WHERE e.municipio = ? AND (p.numero_processo LIKE ? OR e.nome_fantasia LIKE ? OR e.cnpj LIKE ?)";

            $stmt = $this->conn->prepare($query);
            $searchParam = "%" . $search . "%";
            $stmt->bind_param("ssss", $municipio, $searchParam, $searchParam, $searchParam);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }



    public function unarchiveProcesso($id)
    {
        $stmt = $this->conn->prepare("UPDATE processos SET status = 'ATIVO' WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function stopProcesso($id, $motivo)
    {
        $stmt = $this->conn->prepare("UPDATE processos SET status = 'PARADO', motivo_parado = ? WHERE id = ?");
        $stmt->bind_param("si", $motivo, $id);
        return $stmt->execute();
    }

    public function getEstabelecimentoIdByProcessoId($processo_id)
    {
        $stmt = $this->conn->prepare("SELECT estabelecimento_id FROM processos WHERE id = ?");
        $stmt->bind_param("i", $processo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['estabelecimento_id'] : null;
    }


    public function restartProcesso($id)
    {
        $stmt = $this->conn->prepare("UPDATE processos SET status = 'ATIVO', motivo_parado = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function searchProcessos($search)
    {
        $search = "%$search%";
        $stmt = $this->conn->prepare("
            SELECT p.*, e.nome_fantasia, e.cnpj 
            FROM processos p
            JOIN estabelecimentos e ON p.estabelecimento_id = e.id
            WHERE p.numero_processo LIKE ? OR e.nome_fantasia LIKE ? OR e.cnpj LIKE ?
            ORDER BY p.data_abertura DESC
        ");
        $stmt->bind_param("sss", $search, $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProcessoByNumero($numeroProcesso)
    {
        $stmt = $this->conn->prepare("
            SELECT p.*, e.nome_fantasia, e.cnpj 
            FROM processos p
            JOIN estabelecimentos e ON p.estabelecimento_id = e.id
            WHERE p.numero_processo = ?
        ");
        $stmt->bind_param("s", $numeroProcesso);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getProcessoByCnpj($cnpj)
    {
        $stmt = $this->conn->prepare("
            SELECT p.*, e.nome_fantasia, e.cnpj 
            FROM processos p
            JOIN estabelecimentos e ON p.estabelecimento_id = e.id
            WHERE e.cnpj = ?
        ");
        $stmt->bind_param("s", $cnpj);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function createAlerta($processo_id, $descricao, $prazo)
    {
        $sql = "INSERT INTO alertas_processo (processo_id, descricao, prazo) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $processo_id, $descricao, $prazo);
        return $stmt->execute();
    }

    public function getAlertasByProcesso($processo_id)
    {
        $sql = "SELECT * FROM alertas_processo WHERE processo_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $processo_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAlertasVencidos($municipioUsuario)
    {
        $sql = "
            SELECT a.*, p.numero_processo, e.nome_fantasia
            FROM alertas_processo a
            JOIN processos p ON a.processo_id = p.id
            JOIN estabelecimentos e ON p.estabelecimento_id = e.id
            WHERE a.prazo < NOW() AND a.status != 'finalizado' AND e.municipio = ?
        ";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Erro na preparação da consulta: ' . $this->conn->error);
        }

        $stmt->bind_param('s', $municipioUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getAlertasProximosAVencer($municipioUsuario)
    {
        $sql = "
            SELECT a.*, p.numero_processo, e.nome_fantasia, e.id AS estabelecimento_id, DATEDIFF(a.prazo, NOW()) AS dias_restantes
            FROM alertas_processo a
            JOIN processos p ON a.processo_id = p.id
            JOIN estabelecimentos e ON p.estabelecimento_id = e.id
            WHERE a.prazo BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND DATE_ADD(NOW(), INTERVAL 5 DAY) 
            AND a.status != 'finalizado' AND e.municipio = ?
        ";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Erro na preparação da consulta: ' . $this->conn->error);
        }

        $stmt->bind_param('s', $municipioUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProcessosDesignadosPendentes($usuario_id)
    {
        $sql = "
        SELECT pr.*, p.numero_processo, e.nome_fantasia, e.id as estabelecimento_id
        FROM processos_responsaveis pr
        JOIN processos p ON pr.processo_id = p.id
        JOIN estabelecimentos e ON p.estabelecimento_id = e.id
        WHERE pr.usuario_id = ? AND pr.status = 'pendente'
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function marcarComoResolvido($processo_id, $usuario_id)
    {
        $sql = "UPDATE processos_responsaveis SET status = 'resolvido' WHERE processo_id = ? AND usuario_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $processo_id, $usuario_id);
        return $stmt->execute();
    }



    public function getTodosAlertas($municipioUsuario)
    {
        $sql = "
            SELECT a.*, p.numero_processo, e.nome_fantasia, e.id AS estabelecimento_id
            FROM alertas_processo a
            JOIN processos p ON a.processo_id = p.id
            JOIN estabelecimentos e ON p.estabelecimento_id = e.id
            WHERE e.municipio = ? AND a.status = 'ativo'
        ";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Erro na preparação da consulta: ' . $this->conn->error);
        }

        $stmt->bind_param('s', $municipioUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }


    public function getAlertaById($id)
    {
        $sql = "SELECT a.*, p.numero_processo, p.tipo_processo, e.nome_fantasia, e.id AS estabelecimento_id
                FROM alertas_processo a
                JOIN processos p ON a.processo_id = p.id
                JOIN estabelecimentos e ON p.estabelecimento_id = e.id
                WHERE a.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateAlerta($id, $descricao = null, $prazo = null, $status = null)
    {
        $fields = [];
        $params = [];
        $types = '';

        if ($descricao !== null) {
            $fields[] = 'descricao = ?';
            $params[] = $descricao;
            $types .= 's';
        }

        if ($prazo !== null) {
            $fields[] = 'prazo = ?';
            $params[] = $prazo;
            $types .= 's';
        }

        if ($status !== null) {
            $fields[] = 'status = ?';
            $params[] = $status;
            $types .= 's';
        }

        if (empty($fields)) {
            return false; // Nothing to update
        }

        $params[] = $id;
        $types .= 'i';

        $sql = 'UPDATE alertas_processo SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
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


    public function getProcessoById($id)
    {
        $stmt = $this->conn->prepare("
            SELECT p.*, e.nome_fantasia, e.cnpj, e.logradouro, e.numero, e.complemento, e.bairro, e.ddd_telefone_1, e.ddd_telefone_2, e.municipio 
            FROM processos p
            JOIN estabelecimentos e ON p.estabelecimento_id = e.id
            WHERE p.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getDocumentosByProcesso($processoId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM documentos WHERE processo_id = ?");
        $stmt->bind_param("i", $processoId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    public function getEstabelecimentosByUsuario($usuarioId)
    {
        $query = "
        SELECT ue.estabelecimento_id
        FROM usuarios_estabelecimentos ue
        WHERE ue.usuario_id = ?
    ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    public function deleteAlerta($id)
    {
        $sql = "DELETE FROM alertas_processo WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
