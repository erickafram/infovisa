<?php
class Usuario
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getUsuariosByMunicipio($municipio)
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE municipio = ?");
        $stmt->bind_param("s", $municipio);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getUsuariosPorMunicipio($municipio)
    {
        $sql = "SELECT id, nome_completo FROM usuarios WHERE municipio = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $municipio);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuarios = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $usuarios;
    }


    public function findById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
