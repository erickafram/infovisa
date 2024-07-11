<?php
session_start();
require_once '../../conf/database.php';
require_once '../../models/Estabelecimento.php';

// Verificação de autenticação
if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

// Verificação do nível de acesso
if ($_SESSION['user']['nivel_acesso'] < 2) {
    header("Location: ../dashboard.php");
    exit();
}

$estabelecimentoModel = new Estabelecimento($conn);

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($estabelecimentoModel->approve($id)) {
        header("Location: ../Dashboard/dashboard.php?success=Estabelecimento aprovado com sucesso.");
    } else {
        header("Location: ../Dashboard/dashboard.php?error=Erro ao aprovar estabelecimento.");
    }
} else {
    header("Location: ../Dashboard/dashboard.php?error=ID do estabelecimento não fornecido.");
}
?>
