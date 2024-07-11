<?php
require_once '../../conf/database.php';
require_once '../../models/Alerta.php';
require_once '../../models/Processo.php'; // Adicione esta linha

class AlertaController
{
    private $alertaModel;
    private $processoModel;

    public function __construct($conn)
    {
        $this->alertaModel = new Alerta($conn);
        $this->processoModel = new Processo($conn);
    }

    public function getAssinaturasPendentes($usuario_id)
    {
        return $this->alertaModel->getAssinaturasPendentes($usuario_id);
    }

    public function marcarProcessoComoResolvido($processo_id, $usuario_id)
    {
        return $this->processoModel->marcarComoResolvido($processo_id, $usuario_id);
    }

    public function getAssinaturasRascunho($usuario_id)
    {
        return $this->alertaModel->getAssinaturasRascunho($usuario_id);
    }

    public function getTodosAlertas($municipioUsuario)
    {
        return $this->processoModel->getTodosAlertas($municipioUsuario);
    }

    public function getProcessosDesignadosPendentes($usuario_id)
    {
        return $this->processoModel->getProcessosDesignadosPendentes($usuario_id);
    }
}
?>
