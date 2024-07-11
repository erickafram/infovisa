<?php
class ProcessoResponsavel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getProcessosDesignados($searchUser = '', $searchStatus = '')
    {
        $sql = "
            SELECT pr.id, p.numero_processo, u.nome_completo, pr.descricao, pr.status
            FROM processos_responsaveis pr
            JOIN processos p ON pr.processo_id = p.id
            JOIN usuarios u ON pr.usuario_id = u.id
            WHERE (u.nome_completo LIKE ? OR ? = '')
            AND (pr.status LIKE ? OR ? = '')
            ORDER BY p.numero_processo
        ";
        $stmt = $this->conn->prepare($sql);
        $searchUserParam = '%' . $searchUser . '%';
        $searchStatusParam = '%' . $searchStatus . '%';
        $stmt->bind_param('ssss', $searchUserParam, $searchUser, $searchStatusParam, $searchStatus);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
