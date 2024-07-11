<?php
include_once '../conf/database.php';
include_once '../models/UsuarioExterno.php';

class UsuarioExternoController {
    private $db;
    private $usuarioExterno;

    public function __construct($conn) {
        $this->db = $conn;
        $this->usuarioExterno = new UsuarioExterno($this->db);
    }

  // Função para validar número de telefone usando a API da numverify e verificações adicionais
  function validarTelefone($telefone) {
    // Verificação de padrões repetitivos
    $padroesInvalidos = [
        '/(\d)\1{3,}/', // Sequência de 4 ou mais dígitos repetidos
        '/(\d{2,})\1+/', // Sequência de dois ou mais dígitos repetidos
    ];

    foreach ($padroesInvalidos as $padrao) {
        if (preg_match($padrao, preg_replace('/\D/', '', $telefone))) {
            return false;
        }
    }

    $access_key = '126254f53c5ea8ce0af6ed347ad1df76'; // Substitua com sua chave de API da numverify
    $country_code = 'BR'; // Defina o código do país, por exemplo, 'BR' para Brasil
    $url = 'http://apilayer.net/api/validate?access_key=' . $access_key . '&number=' . urlencode($telefone) . '&country_code=' . $country_code . '&format=1';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $validationResult = json_decode($response, true);
    return isset($validationResult['valid']) && $validationResult['valid'] && isset($validationResult['line_type']) && $validationResult['line_type'] == 'mobile';
}

public function create() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $this->usuarioExterno->nome_completo = $_POST['nome_completo'] ?? '';
        $this->usuarioExterno->cpf = $_POST['cpf'] ?? '';
        $this->usuarioExterno->telefone = $_POST['telefone'] ?? '';
        $this->usuarioExterno->email = $_POST['email'] ?? '';
        $this->usuarioExterno->vinculo_estabelecimento = $_POST['vinculo_estabelecimento'] ?? '';
        $this->usuarioExterno->senha = $_POST['senha'] ?? '';

        // Verifica se a senha e a confirmação de senha coincidem
        if ($_POST['senha'] !== $_POST['senha_confirmacao']) {
            $_SESSION['error_message'] = "Erro: As senhas não coincidem.";
            header("Location: ../views/Company/register.php");
            return;
        }

        // Verifica se o usuário já existe pelo e-mail ou CPF
        if ($this->usuarioExterno->findByEmail($this->usuarioExterno->email)) {
            $_SESSION['error_message'] = "Erro: Já existe um usuário com este e-mail.";
            header("Location: ../views/Company/register.php");
            return;
        }

        if ($this->usuarioExterno->findByCPF($this->usuarioExterno->cpf)) {
            $_SESSION['error_message'] = "Erro: Já existe um usuário com este CPF.";
            header("Location: ../views/Company/register.php");
            return;
        }

        if ($this->usuarioExterno->findByTelefone($this->usuarioExterno->telefone)) {
            $_SESSION['error_message'] = "Erro: Já existe um usuário com este telefone.";
            header("Location: ../views/Company/register.php");
            return;
        }

        // Validação do número de telefone
        if (!$this->validarTelefone($this->usuarioExterno->telefone)) {
            $_SESSION['error_message'] = "Erro: Número de telefone inválido.";
            header("Location: ../views/Company/register.php");
            return;
        }

        if ($this->usuarioExterno->create()) {
            $_SESSION['success_message'] = "Usuário cadastrado com sucesso!";
            header("Location: ../views/Company/register.php");
        } else {
            $_SESSION['error_message'] = "Erro ao cadastrar usuário.";
            header("Location: ../views/Company/register.php");
        }
    }
}

public function register() {
    $this->create();
}
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['action']) && $_GET['action'] === 'register') {
include '../conf/database.php';
session_start();
$controller = new UsuarioExternoController($conn);
$controller->register();
}
?>
