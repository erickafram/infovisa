<?php
class User
{
    private $conn;
    private $lastError;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function create($nome_completo, $cpf, $email, $telefone, $municipio, $cargo, $nivel_acesso, $senha, $tempo_vinculo, $escolaridade, $tipo_vinculo)
    {
        $stmt = $this->conn->prepare("INSERT INTO usuarios (nome_completo, cpf, email, telefone, municipio, cargo, nivel_acesso, senha, tempo_vinculo, escolaridade, tipo_vinculo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            $this->lastError = $this->conn->error;
            return false;
        }
        $stmt->bind_param("ssssssissss", $nome_completo, $cpf, $email, $telefone, $municipio, $cargo, $nivel_acesso, $senha, $tempo_vinculo, $escolaridade, $tipo_vinculo);
        if ($stmt->execute()) {
            return true;
        } else {
            $this->lastError = $stmt->error;
            return false;
        }
    }

    public function findByCPF($cpf)
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE cpf = ?");
        if (!$stmt) {
            $this->lastError = $this->conn->error;
            return false;
        }
        $stmt->bind_param("s", $cpf);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function findByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE email = ?");
        if (!$stmt) {
            $this->lastError = $this->conn->error;
            return false;
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getUsersByMunicipio($municipio)
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE municipio = ? AND nivel_acesso != 1 ORDER BY nome_completo");
        $stmt->bind_param("s", $municipio);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function resetPassword($id)
    {
        $novaSenha = password_hash('@visa@2024', PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        if (!$stmt) {
            $this->lastError = $this->conn->error;
            return false;
        }
        $stmt->bind_param("si", $novaSenha, $id);
        return $stmt->execute();
    }

    public function updateNivelAcesso($id, $nivel_acesso)
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET nivel_acesso = ? WHERE id = ?");
        $stmt->bind_param("ii", $nivel_acesso, $id);
        if ($stmt->execute()) {
            return true;
        } else {
            $this->lastError = $stmt->error;
            return false;
        }
    }


    public function getAllUsers()
    {
        $result = $this->conn->query("SELECT * FROM usuarios");
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $this->lastError = $this->conn->error;
            return false;
        }
    }

    public function activateUser($id)
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET status = 'ativo' WHERE id = ?");
        if (!$stmt) {
            $this->lastError = $this->conn->error;
            return false;
        }
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function deactivateUser($id)
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET status = 'inativo' WHERE id = ?");
        if (!$stmt) {
            $this->lastError = $this->conn->error;
            return false;
        }
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }


    public function findById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        if (!$stmt) {
            $this->lastError = $this->conn->error;
            return false;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function update($id, $nome_completo, $cpf, $email, $telefone, $municipio, $cargo, $nivel_acesso, $tempo_vinculo, $escolaridade, $tipo_vinculo)
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET nome_completo = ?, cpf = ?, email = ?, telefone = ?, municipio = ?, cargo = ?, nivel_acesso = ?, tempo_vinculo = ?, escolaridade = ?, tipo_vinculo = ? WHERE id = ?");
        if (!$stmt) {
            $this->lastError = $this->conn->error;
            return false;
        }
        $stmt->bind_param("ssssssisssi", $nome_completo, $cpf, $email, $telefone, $municipio, $cargo, $nivel_acesso, $tempo_vinculo, $escolaridade, $tipo_vinculo, $id);
        if ($stmt->execute()) {
            return true;
        } else {
            $this->lastError = $stmt->error;
            return false;
        }
    }
}
