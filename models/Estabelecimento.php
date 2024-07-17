<?php
class Estabelecimento
{
    private $conn;
    private $lastError;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function create($data)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO estabelecimentos (
                cnpj, descricao_identificador_matriz_filial, nome_fantasia, 
                descricao_situacao_cadastral, data_situacao_cadastral, data_inicio_atividade, 
                cnae_fiscal, cnae_fiscal_descricao, descricao_tipo_de_logradouro, 
                logradouro, numero, complemento, bairro, cep, uf, municipio, 
                ddd_telefone_1, ddd_telefone_2, razao_social, natureza_juridica, 
                qsa, cnaes_secundarios, nome_socio_1, qualificacao_socio_1, 
                nome_socio_2, qualificacao_socio_2, nome_socio_3, qualificacao_socio_3, status, usuario_externo_id, data_cadastro
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )"
        );

        if (!$stmt) {
            $this->lastError = $this->conn->error;
            return false;
        }

        $qsa_json = json_encode($data['qsa']);
        $cnaes_secundarios_json = json_encode($data['cnaes_secundarios']);

        // Extrair até três sócios
        $nome_socio_1 = isset($data['qsa'][0]['nome_socio']) ? $data['qsa'][0]['nome_socio'] : null;
        $qualificacao_socio_1 = isset($data['qsa'][0]['qualificacao_socio']) ? $data['qsa'][0]['qualificacao_socio'] : null;

        $nome_socio_2 = isset($data['qsa'][1]['nome_socio']) ? $data['qsa'][1]['nome_socio'] : null;
        $qualificacao_socio_2 = isset($data['qsa'][1]['qualificacao_socio']) ? $data['qsa'][1]['qualificacao_socio'] : null;

        $nome_socio_3 = isset($data['qsa'][2]['nome_socio']) ? $data['qsa'][2]['nome_socio'] : null;
        $qualificacao_socio_3 = isset($data['qsa'][2]['qualificacao_socio']) ? $data['qsa'][2]['qualificacao_socio'] : null;

        $stmt->bind_param(
            "sssssssssssssssssssssssssssssi",
            $data['cnpj'],
            $data['descricao_identificador_matriz_filial'],
            $data['nome_fantasia'],
            $data['descricao_situacao_cadastral'],
            $data['data_situacao_cadastral'],
            $data['data_inicio_atividade'],
            $data['cnae_fiscal'],
            $data['cnae_fiscal_descricao'],
            $data['descricao_tipo_de_logradouro'],
            $data['logradouro'],
            $data['numero'],
            $data['complemento'],
            $data['bairro'],
            $data['cep'],
            $data['uf'],
            $data['municipio'],
            $data['ddd_telefone_1'],
            $data['ddd_telefone_2'],
            $data['razao_social'],
            $data['natureza_juridica'],
            $qsa_json,
            $cnaes_secundarios_json,
            $nome_socio_1,
            $qualificacao_socio_1,
            $nome_socio_2,
            $qualificacao_socio_2,
            $nome_socio_3,
            $qualificacao_socio_3,
            $data['status'],
            $data['usuario_externo_id']
        );

        if ($stmt->execute()) {
            return $stmt->insert_id; // Retorna o ID do estabelecimento criado
        } else {
            $this->lastError = $stmt->error;
            return false;
        }
    }

    public function delete($id)
    {
        $query = "DELETE FROM estabelecimentos WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function vincularUsuarioEstabelecimento($usuarioId, $estabelecimentoId, $tipoVinculo)
    {
        $query = "INSERT INTO usuarios_estabelecimentos (usuario_id, estabelecimento_id, tipo_vinculo) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iis", $usuarioId, $estabelecimentoId, $tipoVinculo);
        return $stmt->execute();
    }

    public function searchAprovados($usuarioId, $search = '', $limit = 10, $offset = 0)
    {
        $query = "
            SELECT e.*
            FROM estabelecimentos e
            JOIN usuarios_estabelecimentos ue ON e.id = ue.estabelecimento_id
            WHERE ue.usuario_id = ? AND e.status = 'aprovado'
        ";

        if ($search) {
            $query .= " AND (e.nome_fantasia LIKE ? OR e.cnpj LIKE ?)";
            $search = "%$search%";
        }

        $query .= " LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);

        if ($search) {
            $stmt->bind_param("issii", $usuarioId, $search, $search, $limit, $offset);
        } else {
            $stmt->bind_param("iii", $usuarioId, $limit, $offset);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countAprovados($usuarioId, $search = '')
    {
        $query = "
            SELECT COUNT(*) as total
            FROM estabelecimentos e
            JOIN usuarios_estabelecimentos ue ON e.id = ue.estabelecimento_id
            WHERE ue.usuario_id = ? AND e.status = 'aprovado'
        ";

        if ($search) {
            $query .= " AND (e.nome_fantasia LIKE ? OR e.cnpj LIKE ?)";
            $search = "%$search%";
        }

        $stmt = $this->conn->prepare($query);

        if ($search) {
            $stmt->bind_param("iss", $usuarioId, $search, $search);
        } else {
            $stmt->bind_param("i", $usuarioId);
        }

        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }


    public function update($id, $data)
    {
        $stmt = $this->conn->prepare(
            "UPDATE estabelecimentos SET 
                descricao_identificador_matriz_filial = ?, 
                nome_fantasia = ?, 
                descricao_situacao_cadastral = ?, 
                data_situacao_cadastral = ?, 
                data_inicio_atividade = ?, 
                descricao_tipo_de_logradouro = ?, 
                logradouro = ?, 
                numero = ?, 
                complemento = ?, 
                bairro = ?, 
                cep = ?, 
                uf = ?, 
                municipio = ?, 
                ddd_telefone_1 = ?, 
                ddd_telefone_2 = ?, 
                razao_social = ?, 
                natureza_juridica = ?
            WHERE id = ?"
        );

        if (!$stmt) {
            $this->lastError = $this->conn->error;
            return false;
        }

        $stmt->bind_param(
            "sssssssssssssssssi",
            $data['descricao_identificador_matriz_filial'],
            $data['nome_fantasia'],
            $data['descricao_situacao_cadastral'],
            $data['data_situacao_cadastral'],
            $data['data_inicio_atividade'],
            $data['descricao_tipo_de_logradouro'],
            $data['logradouro'],
            $data['numero'],
            $data['complemento'],
            $data['bairro'],
            $data['cep'],
            $data['uf'],
            $data['municipio'],
            $data['ddd_telefone_1'],
            $data['ddd_telefone_2'],
            $data['razao_social'],
            $data['natureza_juridica'],
            $id
        );

        if ($stmt->execute()) {
            return true;
        } else {
            $this->lastError = $stmt->error;
            return false;
        }
    }

    public function approve($id)
    {
        $stmt = $this->conn->prepare("UPDATE estabelecimentos SET status = 'aprovado' WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getEstabelecimentosByUsuarioExterno($usuarioExternoId)
    {
        $sql = "
        SELECT e.id, e.nome_fantasia, e.cnpj 
        FROM estabelecimentos e
        JOIN usuarios_estabelecimentos ue ON e.id = ue.estabelecimento_id
        WHERE ue.usuario_id = ?
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $usuarioExternoId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }


    public function findById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM estabelecimentos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getEstabelecimentosPendentes($municipio)
    {
        $stmt = $this->conn->prepare("SELECT * FROM estabelecimentos WHERE status = 'pendente' AND municipio = ?");
        $stmt->bind_param("s", $municipio);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllEstabelecimentos()
    {
        $result = $this->conn->query("SELECT * FROM estabelecimentos");
        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }

    public function searchEstabelecimentos($search, $limit, $offset, $municipio, $nivel_acesso)
    {
        $sql = "SELECT * FROM estabelecimentos WHERE status = 'aprovado'";
        $params = [];
        $types = "";

        // Adicionar filtro de município para não administradores
        if ($nivel_acesso != 1) {
            $sql .= " AND municipio = ?";
            $params[] = $municipio;
            $types .= "s";
        }

        if ($search) {
            $sql .= " AND (cnpj LIKE ? OR razao_social LIKE ? OR nome_fantasia LIKE ? OR municipio LIKE ?)";
            $search = "%$search%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "ssss";
        }

        $sql .= " ORDER BY razao_social ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->conn->prepare($sql);

        if ($types) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }


    public function getEstabelecimentosByUsuario($usuarioId)
    {
        $query = "
            SELECT e.*
            FROM estabelecimentos e
            JOIN usuarios_estabelecimentos ue ON e.id = ue.estabelecimento_id
            WHERE ue.usuario_id = ? AND e.status = 'aprovado'
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getEstabelecimentosRejeitadosByUsuario($usuarioId)
    {
        $query = "
            SELECT e.*
            FROM estabelecimentos e
            JOIN usuarios_estabelecimentos ue ON e.id = ue.estabelecimento_id
            WHERE ue.usuario_id = ? AND e.status = 'rejeitado'
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }



    public function reject($id, $motivo)
    {
        $stmt = $this->conn->prepare("UPDATE estabelecimentos SET status = 'rejeitado', motivo_negacao = ? WHERE id = ?");
        $stmt->bind_param("si", $motivo, $id);
        return $stmt->execute();
    }

    public function getEstabelecimentosPendentesByUsuario($usuarioId)
    {
        $query = "
            SELECT e.*
            FROM estabelecimentos e
            JOIN usuarios_estabelecimentos ue ON e.id = ue.estabelecimento_id
            WHERE ue.usuario_id = ? AND e.status = 'pendente'
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getProcessosByEstabelecimento($estabelecimentoId)
    {
        $query = "
            SELECT p.*
            FROM processos p
            WHERE p.estabelecimento_id = ? AND p.tipo_processo != 'DENÚNCIA'
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $estabelecimentoId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    public function getDocumentosNegadosByUsuario($userId)
    {
        $query = "SELECT d.*, p.numero_processo, e.nome_fantasia
                  FROM documentos d
                  JOIN processos p ON d.processo_id = p.id
                  JOIN estabelecimentos e ON p.estabelecimento_id = e.id
                  JOIN usuarios_estabelecimentos ue ON e.id = ue.estabelecimento_id
                  WHERE ue.usuario_id = ? AND d.status = 'negado'";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $documentosNegados = [];
        while ($row = $result->fetch_assoc()) {
            $documentosNegados[] = $row;
        }

        return $documentosNegados;
    }

    public function getDocumentosPendentesByUsuario($userId)
    {
        $query = "SELECT d.*, p.numero_processo, e.nome_fantasia
                  FROM documentos d
                  JOIN processos p ON d.processo_id = p.id
                  JOIN estabelecimentos e ON p.estabelecimento_id = e.id
                  JOIN usuarios_estabelecimentos ue ON e.id = ue.estabelecimento_id
                  WHERE ue.usuario_id = ? AND d.status = 'pendente'";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $documentosPendentes = [];
        while ($row = $result->fetch_assoc()) {
            $documentosPendentes[] = $row;
        }

        return $documentosPendentes;
    }



    public function countEstabelecimentos($search, $municipio, $nivel_acesso)
    {
        $sql = "SELECT COUNT(*) as total FROM estabelecimentos WHERE status = 'aprovado'";
        $params = [];
        $types = "";

        // Adicionar filtro de município para não administradores
        if ($nivel_acesso != 1) {
            $sql .= " AND municipio = ?";
            $params[] = $municipio;
            $types .= "s";
        }

        if ($search) {
            $sql .= " AND (cnpj LIKE ? OR nome_fantasia LIKE ? OR municipio LIKE ?)";
            $search = "%$search%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "sss";
        }

        $stmt = $this->conn->prepare($sql);

        if ($types) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }


    public function searchEstabelecimentosRejeitados($search, $limit, $offset, $municipio, $nivel_acesso)
    {
        $sql = "SELECT * FROM estabelecimentos WHERE status = 'rejeitado'";
        $params = [];
        $types = "";

        // Adicionar filtro de município para não administradores
        if ($nivel_acesso != 1) {
            $sql .= " AND municipio = ?";
            $params[] = $municipio;
            $types .= "s";
        }

        if ($search) {
            $sql .= " AND (cnpj LIKE ? OR nome_fantasia LIKE ? OR municipio LIKE ?)";
            $search = "%$search%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "sss";
        }

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->conn->prepare($sql);

        if ($types) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function reiniciarEstabelecimento($id)
    {
        $stmt = $this->conn->prepare("UPDATE estabelecimentos SET status = 'pendente', motivo_negacao = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function countEstabelecimentosRejeitados($search, $municipio, $nivel_acesso)
    {
        $sql = "SELECT COUNT(*) as total FROM estabelecimentos WHERE status = 'rejeitado'";
        $params = [];
        $types = "";

        // Adicionar filtro de município para não administradores
        if ($nivel_acesso != 1) {
            $sql .= " AND municipio = ?";
            $params[] = $municipio;
            $types .= "s";
        }

        if ($search) {
            $sql .= " AND (cnpj LIKE ? OR nome_fantasia LIKE ? OR municipio LIKE ?)";
            $search = "%$search%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "sss";
        }

        $stmt = $this->conn->prepare($sql);

        if ($types) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function getMunicipioByCnpj($cnpj)
    {
        $stmt = $this->conn->prepare("SELECT municipio FROM estabelecimentos WHERE cnpj = ?");
        $stmt->bind_param("s", $cnpj);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['municipio'];
    }



    public function checkCnpjExists($cnpj)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM estabelecimentos WHERE cnpj = ?");
        $stmt->bind_param("s", $cnpj);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function searchEstabelecimento($searchTerm)
    {
        $query = "SELECT * FROM estabelecimentos WHERE nome_fantasia LIKE ? OR razao_social LIKE ? OR cnpj LIKE ?";
        $stmt = $this->conn->prepare($query);
        $searchTerm = '%' . $searchTerm . '%';
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function searchByNameOrRazaoSocial($search, $userId)
    {
        $search = "%$search%";
        $stmt = $this->conn->prepare("
            SELECT e.*
            FROM estabelecimentos e
            JOIN usuarios_estabelecimentos ue ON e.id = ue.estabelecimento_id
            WHERE ue.usuario_id = ? AND (e.nome_fantasia LIKE ? OR e.razao_social LIKE ?)
        ");
        $stmt->bind_param("iss", $userId, $search, $search);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function findByCnpj($cnpj)
    {
        $stmt = $this->conn->prepare("SELECT * FROM estabelecimentos WHERE cnpj = ?");
        $stmt->bind_param("s", $cnpj);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findByCnpjAndUsuario($cnpj, $usuarioId)
    {
        $stmt = $this->conn->prepare("
        SELECT e.*
        FROM estabelecimentos e
        JOIN usuarios_estabelecimentos ue ON e.id = ue.estabelecimento_id
        WHERE e.cnpj = ? AND ue.usuario_id = ?
    ");
        $stmt->bind_param("si", $cnpj, $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function searchByNameAndUsuario($name, $usuarioId)
    {
        $name = "%$name%";
        $stmt = $this->conn->prepare("
        SELECT e.*
        FROM estabelecimentos e
        JOIN usuarios_estabelecimentos ue ON e.id = ue.estabelecimento_id
        WHERE e.nome_fantasia LIKE ? AND ue.usuario_id = ?
    ");
        $stmt->bind_param("si", $name, $usuarioId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
