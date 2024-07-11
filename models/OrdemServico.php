<?php
class OrdemServico
{
    private $conn;
    private $lastError;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function create($estabelecimento_id, $processo_id, $data_inicio, $data_fim, $acoes_executadas, $tecnicos, $pdf_path, $municipio, $status = 'ativa', $observacao = null)
    {
        $acoes_executadas_json = json_encode($acoes_executadas, JSON_UNESCAPED_UNICODE);
        $stmt = $this->conn->prepare(
            "INSERT INTO ordem_servico (estabelecimento_id, processo_id, data_inicio, data_fim, acoes_executadas, tecnicos, pdf_path, status, observacao) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
    
        if (!$stmt) {
            $this->lastError = $this->conn->error;
            return false;
        }
    
        $stmt->bind_param(
            "iisssssss",
            $estabelecimento_id,
            $processo_id,
            $data_inicio,
            $data_fim,
            $acoes_executadas_json,
            $tecnicos,
            $pdf_path,
            $status,
            $observacao
        );
    
        // Set charset to utf8mb4
        if (!$this->conn->set_charset("utf8mb4")) {
            printf("Error loading character set utf8mb4: %s\n", $this->conn->error);
        }
    
        if ($stmt->execute()) {
            return true;
        } else {
            $this->lastError = $stmt->error;
            return false;
        }
    }
    


    public function getPontuacaoMensal($tecnico_id, $mes, $ano)
    {
        $query = "
            SELECT SUM(pontuacao) AS pontuacao_total
            FROM pontuacao_tecnicos
            WHERE tecnico_id = ? AND MONTH(data) = ? AND YEAR(data) = ?
        ";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            // Exibe a mensagem de erro se a preparação da consulta falhar
            die("Erro na preparação da consulta: " . $this->conn->error);
        }

        $stmt->bind_param("iii", $tecnico_id, $mes, $ano);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) {
            // Exibe a mensagem de erro se a execução da consulta falhar
            die("Erro na execução da consulta: " . $this->conn->error);
        }

        $row = $result->fetch_assoc();
        return $row['pontuacao_total'] ?: 0;
    }


    public function calcularPontuacao($estabelecimento_id, $acoes_executadas, $municipio)
    {
        if ($estabelecimento_id) {
            $query = "
            SELECT DISTINCT gr.id AS grupo_risco_id
            FROM estabelecimentos e
            LEFT JOIN atividade_grupo_risco agr_fiscal ON e.cnae_fiscal = agr_fiscal.cnae AND e.municipio = agr_fiscal.municipio
            LEFT JOIN grupo_risco gr_fiscal ON agr_fiscal.grupo_risco_id = gr_fiscal.id
            LEFT JOIN (
                SELECT 
                    e.id AS estabelecimento_id, 
                    JSON_UNQUOTE(JSON_EXTRACT(cnaes.codigo, '$')) AS cnae_secundario
                FROM 
                    estabelecimentos e,
                    JSON_TABLE(e.cnaes_secundarios, '$[*]' COLUMNS (codigo VARCHAR(20) PATH '$.codigo')) cnaes
            ) cnae_secundario ON e.id = cnae_secundario.estabelecimento_id
            LEFT JOIN atividade_grupo_risco agr_secundario ON cnae_secundario.cnae_secundario = agr_secundario.cnae AND e.municipio = agr_secundario.municipio
            LEFT JOIN grupo_risco gr_secundario ON agr_secundario.grupo_risco_id = gr_secundario.id
            LEFT JOIN grupo_risco gr ON gr_fiscal.id = gr.id OR gr_secundario.id = gr.id
            WHERE e.id = ? AND (agr_fiscal.municipio = ? OR agr_secundario.municipio = ?)
        ";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("iss", $estabelecimento_id, $municipio, $municipio);
        } else {
            $query = "
            SELECT DISTINCT gr.id AS grupo_risco_id
            FROM grupo_risco gr
            LEFT JOIN atividade_grupo_risco agr ON gr.id = agr.grupo_risco_id
            WHERE agr.municipio = ?
        ";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $municipio);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $pontuacao_total = 0;
        $grupos_risco_ids = [];

        while ($row = $result->fetch_assoc()) {
            $grupo_risco_id = $row['grupo_risco_id'];
            $grupos_risco_ids[] = $grupo_risco_id;
            error_log("Grupo de risco ID: $grupo_risco_id");
        }

        if (!empty($grupos_risco_ids) && is_array($acoes_executadas)) {
            foreach ($grupos_risco_ids as $grupo_risco_id) {
                foreach ($acoes_executadas as $acao_id) {
                    error_log("Processando Ação ID: $acao_id, Grupo de Risco ID: $grupo_risco_id");
                    $acaoQuery = $this->conn->prepare("SELECT pontuacao FROM acoes_pontuacao WHERE acao_id = ? AND grupo_risco_id = ? AND municipio = ?");
                    $acaoQuery->bind_param("iis", $acao_id, $grupo_risco_id, $municipio);
                    $acaoQuery->execute();
                    $acaoResult = $acaoQuery->get_result();

                    if ($acaoRow = $acaoResult->fetch_assoc()) {
                        $pontuacao_total += $acaoRow["pontuacao"];
                        error_log("Ação ID: $acao_id, Grupo de Risco ID: $grupo_risco_id, Pontuação: " . $acaoRow["pontuacao"]);
                    } else {
                        error_log("Nenhuma pontuação encontrada para Ação ID: $acao_id, Grupo de Risco ID: $grupo_risco_id");
                    }

                    $acaoQuery->close();
                }
            }
        } else {
            error_log("Nenhum grupo de risco encontrado ou ações executadas não são um array.");
        }

        $stmt->close();
        error_log("Pontuação total calculada: $pontuacao_total");
        return $pontuacao_total;
    }


    public function deleteOrdem($id)
    {
        // Obter informações da ordem de serviço antes de excluir
        $ordem = $this->getOrdemById($id);
        if ($ordem) {
            $tecnicos_ids = json_decode($ordem['tecnicos'], true);

            // Remover a pontuação dos técnicos associados à ordem de serviço
            if (is_array($tecnicos_ids)) {
                foreach ($tecnicos_ids as $tecnico_id) {
                    $this->removerPontuacaoTecnicoPorOrdem($tecnico_id, $id);
                }
            }

            // Excluir a ordem de serviço
            $query = "DELETE FROM ordem_servico WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                return true;
            } else {
                $this->lastError = $stmt->error;
                return false;
            }
        } else {
            $this->lastError = "Ordem de serviço não encontrada.";
            return false;
        }
    }



    public function salvarPontuacaoTecnico($tecnico_id, $pontuacao, $ordem_id)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO pontuacao_tecnicos (tecnico_id, pontuacao, data, ordem_id) VALUES (?, ?, NOW(), ?)"
        );
        if ($stmt === false) {
            $this->lastError = $this->conn->error;
            return false;
        }
        $stmt->bind_param("iii", $tecnico_id, $pontuacao, $ordem_id);
        if ($stmt->execute()) {
            return true;
        } else {
            $this->lastError = $stmt->error;
            return false;
        }
    }

    public function reiniciarOrdem($id)
    {
        // Obter informações da ordem de serviço antes de reiniciar
        $ordem = $this->getOrdemById($id);
        if ($ordem) {
            $tecnicos_ids = json_decode($ordem['tecnicos'], true);

            // Remover a pontuação dos técnicos associados à ordem de serviço
            if (is_array($tecnicos_ids)) {
                foreach ($tecnicos_ids as $tecnico_id) {
                    $this->removerPontuacaoTecnicoPorOrdem($tecnico_id, $id);
                }
            }

            // Atualizar o status da ordem de serviço para 'ativa' e limpar a descrição de encerramento
            $query = "UPDATE ordem_servico SET status = 'ativa', descricao_encerramento = NULL WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                return true;
            } else {
                $this->lastError = $stmt->error;
                return false;
            }
        } else {
            $this->lastError = "Ordem de serviço não encontrada.";
            return false;
        }
    }



    public function removerPontuacaoTecnicoPorOrdem($tecnico_id, $ordem_id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM pontuacao_tecnicos WHERE tecnico_id = ? AND ordem_id = ?"
        );
        if ($stmt === false) {
            $this->lastError = $this->conn->error;
            return false;
        }
        $stmt->bind_param("ii", $tecnico_id, $ordem_id);
        if ($stmt->execute()) {
            return true;
        } else {
            $this->lastError = $stmt->error;
            return false;
        }
    }

    public function removerPontuacaoTecnico($tecnico_id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM pontuacao_tecnicos WHERE tecnico_id = ?"
        );
        if ($stmt === false) {
            $this->lastError = $this->conn->error;
            return false;
        }
        $stmt->bind_param("i", $tecnico_id);
        if ($stmt->execute()) {
            return true;
        } else {
            $this->lastError = $stmt->error;
            return false;
        }
    }




    public function getOrdensByProcesso($processo_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM ordem_servico WHERE processo_id = ?");
        $stmt->bind_param("i", $processo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getOrdensByTecnico($tecnico_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT os.*, e.razao_social, e.nome_fantasia 
             FROM ordem_servico os 
             JOIN estabelecimentos e ON os.estabelecimento_id = e.id 
             WHERE JSON_CONTAINS(os.tecnicos, JSON_QUOTE(?), '$') AND os.status = 'ativa'"
        );
        $stmt->bind_param("s", $tecnico_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllOrdens($search = '', $limit = 10, $offset = 0)
    {
        $search_query = '';
        if (!empty($search)) {
            $search_query = "WHERE os.id LIKE ? OR e.razao_social LIKE ? OR e.nome_fantasia LIKE ?";
            $search_param = '%' . $search . '%';
        }

        $query = "SELECT os.*, e.razao_social, e.nome_fantasia 
                  FROM ordem_servico os 
                  LEFT JOIN estabelecimentos e ON os.estabelecimento_id = e.id 
                  $search_query
                  LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $stmt->bind_param("sssii", $search_param, $search_param, $search_param, $limit, $offset);
        } else {
            $stmt->bind_param("ii", $limit, $offset);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $ordens = $result->fetch_all(MYSQLI_ASSOC);

        foreach ($ordens as &$ordem) {
            $tecnicos_ids = json_decode($ordem['tecnicos']);
            $ordem['tecnicos_nomes'] = $this->getTecnicosNomes($tecnicos_ids);
        }

        return $ordens;
    }

    public function getOrdensCount($search = '')
    {
        $search_query = '';
        if (!empty($search)) {
            $search_query = "WHERE os.id LIKE ? OR e.razao_social LIKE ? OR e.nome_fantasia LIKE ?";
            $search_param = '%' . $search . '%';
        }

        $query = "SELECT COUNT(*) AS total 
                  FROM ordem_servico os 
                  LEFT JOIN estabelecimentos e ON os.estabelecimento_id = e.id 
                  $search_query";
        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $stmt->bind_param("sss", $search_param, $search_param, $search_param);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function getOrdensByMunicipio($municipio, $search = '', $limit = 10, $offset = 0)
    {
        $search_query = '';
        $params = [];
        $types = '';
    
        if (!empty($search)) {
            $search_query = "AND (os.id LIKE ? OR e.razao_social LIKE ? OR e.nome_fantasia LIKE ?)";
            $search_param = '%' . $search . '%';
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= 'sss';
        }
    
        $params = array_merge([$municipio], $params, [$limit, $offset]);
        $types = 's' . $types . 'ii';
    
        $query = "SELECT os.*, e.razao_social, e.nome_fantasia 
                  FROM ordem_servico os 
                  LEFT JOIN estabelecimentos e ON os.estabelecimento_id = e.id 
                  WHERE (e.municipio = ? OR os.estabelecimento_id IS NULL) $search_query
                  LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
    
        $stmt->bind_param($types, ...$params);
    
        $stmt->execute();
        $result = $stmt->get_result();
        $ordens = $result->fetch_all(MYSQLI_ASSOC);
    
        foreach ($ordens as &$ordem) {
            $tecnicos_ids = json_decode($ordem['tecnicos']);
            $ordem['tecnicos_nomes'] = $this->getTecnicosNomes($tecnicos_ids);
        }
    
        return $ordens;
    }
    

    public function getOrdensCountByMunicipio($municipio, $search = '')
    {
        $search_query = '';
        $params = [];
        $types = 's';
    
        if (!empty($search)) {
            $search_query = "AND (os.id LIKE ? OR e.razao_social LIKE ? OR e.nome_fantasia LIKE ?)";
            $search_param = '%' . $search . '%';
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= 'sss';
        }
    
        $params = array_merge([$municipio], $params);
    
        $query = "SELECT COUNT(*) AS total 
                  FROM ordem_servico os 
                  LEFT JOIN estabelecimentos e ON os.estabelecimento_id = e.id 
                  WHERE e.municipio = ? $search_query";
        $stmt = $this->conn->prepare($query);
    
        $stmt->bind_param($types, ...$params);
    
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    
        return $row['total'];
    }
    

    public function getDescricoesAcoesExecutadas($ids_acoes)
    {
        if (empty($ids_acoes)) {
            return [];
        }

        $ids_acoes_str = implode(',', array_map('intval', $ids_acoes));
        $query = "SELECT descricao FROM tipos_acoes_executadas WHERE id IN ($ids_acoes_str)";
        $result = $this->conn->query($query);

        $descricoes_acoes = [];
        while ($row = $result->fetch_assoc()) {
            $descricoes_acoes[] = $row['descricao'];
        }

        return $descricoes_acoes;
    }

    public function getOrdemById($id)
    {
        $query = "SELECT os.*, 
                         e.razao_social, 
                         e.nome_fantasia, 
                         p.numero_processo, 
                         CONCAT(e.descricao_tipo_de_logradouro, ' ', e.logradouro, ', ', e.numero, ' ', e.complemento, ' - ', e.bairro, ', ', e.municipio, ' - ', e.uf, ', CEP: ', e.cep) AS endereco 
                  FROM ordem_servico os 
                  LEFT JOIN estabelecimentos e ON os.estabelecimento_id = e.id 
                  LEFT JOIN processos p ON os.processo_id = p.id
                  WHERE os.id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            $this->lastError = $this->conn->error;
            return false;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $ordem = $result->fetch_assoc();

        if (!$ordem) {
            $this->lastError = "Ordem de serviço não encontrada.";
            return false;
        }

        // Obter descrições das ações executadas
        $ids_acoes = json_decode($ordem['acoes_executadas'], true); // Decodifica como array associativo
        if (is_array($ids_acoes)) {
            $descricoes_acoes = $this->getDescricoesAcoesExecutadas($ids_acoes);
            $ordem['acoes_executadas_descricao'] = implode(", ", $descricoes_acoes);
        } else {
            $ordem['acoes_executadas_descricao'] = '';
        }

        // Obter nomes dos técnicos
        $tecnicos_ids = json_decode($ordem['tecnicos'], true);
        if (is_array($tecnicos_ids)) {
            $ordem['tecnicos_nomes'] = $this->getTecnicosNomes($tecnicos_ids);
        } else {
            $ordem['tecnicos_nomes'] = [];
        }

        return $ordem;
    }


    public function getOrdensByTecnicoIncludingNoEstabelecimento($tecnico_id)
    {
        // Consulta para buscar ordens de serviço com ou sem estabelecimento
        $query = "
            SELECT os.*, e.nome_fantasia 
            FROM ordem_servico os 
            LEFT JOIN estabelecimentos e ON os.estabelecimento_id = e.id 
            WHERE JSON_CONTAINS(os.tecnicos, JSON_QUOTE(?), '$')
            OR os.estabelecimento_id IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $tecnico_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $ordens = $result->fetch_all(MYSQLI_ASSOC);

        // Transformar os IDs das ações executadas em nomes
        foreach ($ordens as &$ordem) {
            $ids_acoes = json_decode($ordem['acoes_executadas'], true);
            if (is_array($ids_acoes)) {
                $descricoes_acoes = $this->getDescricoesAcoesExecutadas($ids_acoes);
                $ordem['acoes_executadas_nomes'] = implode(", ", $descricoes_acoes);
            } else {
                $ordem['acoes_executadas_nomes'] = '';
            }
        }

        return $ordens;
    }


    public function update($ordem_id, $data_inicio, $data_fim, $acoes_executadas, $tecnicos, $pdf_path, $estabelecimento_id = null, $processo_id = null, $observacao = null)
    {
        if (is_null($acoes_executadas)) {
            $query = "SELECT acoes_executadas FROM ordem_servico WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $ordem_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $acoes_executadas = json_decode($row['acoes_executadas'], true);
        }
    
        $acoes_executadas_json = json_encode($acoes_executadas, JSON_UNESCAPED_UNICODE);
    
        $query = "UPDATE ordem_servico SET data_inicio = ?, data_fim = ?, acoes_executadas = ?, tecnicos = ?, pdf_path = ?, estabelecimento_id = ?, processo_id = ?, observacao = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssssssi", $data_inicio, $data_fim, $acoes_executadas_json, $tecnicos, $pdf_path, $estabelecimento_id, $processo_id, $observacao, $ordem_id);
    
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $this->lastError = $stmt->error;
            $stmt->close();
            return false;
        }
    }
    
    


    public function getAcoesNomes($acoes_ids)
    {
        if (empty($acoes_ids)) {
            return [];
        }

        $ids_acoes_str = implode(',', array_map('intval', $acoes_ids));
        $query = "SELECT id, descricao FROM tipos_acoes_executadas WHERE id IN ($ids_acoes_str)";
        $result = $this->conn->query($query);

        $descricoes_acoes = [];
        while ($row = $result->fetch_assoc()) {
            $descricoes_acoes[$row['id']] = $row['descricao'];
        }

        return $descricoes_acoes;
    }


    public function finalizarOrdem($id, $descricao_encerramento)
    {
        $query = "UPDATE ordem_servico SET status = 'finalizada', descricao_encerramento = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $descricao_encerramento, $id);
        if ($stmt->execute()) {
            $ordem = $this->getOrdemById($id);
            if ($ordem) {
                $estabelecimento_id = $ordem['estabelecimento_id'];
                $acoes_executadas = json_decode($ordem['acoes_executadas'], true);
                if (!is_array($acoes_executadas)) {
                    $acoes_executadas = [];
                }
                $municipio = $_SESSION['user']['municipio'];
                $pontuacao_total = $this->calcularPontuacao($estabelecimento_id, $acoes_executadas, $municipio);
                $tecnicos_ids = json_decode($ordem['tecnicos'], true);
                if (is_array($tecnicos_ids)) {
                    foreach ($tecnicos_ids as $tecnico_id) {
                        $this->salvarPontuacaoTecnico($tecnico_id, $pontuacao_total, $id);
                    }
                }
            }
            return true;
        } else {
            $this->lastError = $stmt->error;
            return false;
        }
    }
    

    public function updateStatus($id, $status)
    {
        $stmt = $this->conn->prepare("UPDATE ordem_servico SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM ordem_servico WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function getTecnicosNomes($ids_tecnicos)
    {
        if (empty($ids_tecnicos)) {
            return [];
        }

        $ids_tecnicos_str = implode(',', array_map('intval', $ids_tecnicos));
        $query = "SELECT nome_completo FROM usuarios WHERE id IN ($ids_tecnicos_str)";
        $result = $this->conn->query($query);

        $nomes_tecnicos = [];
        while ($row = $result->fetch_assoc()) {
            $nomes_tecnicos[] = $row['nome_completo'];
        }

        return $nomes_tecnicos;
    }

    public function getTiposAcoesExecutadas()
    {
        $query = "SELECT * FROM tipos_acoes_executadas";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
