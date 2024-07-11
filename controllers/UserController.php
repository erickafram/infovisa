<?php
require_once '../conf/database.php';
require_once '../models/User.php';
require_once '../models/UsuarioExterno.php';

class UserController
{
    private $user;
    private $usuarioExterno;

    public function __construct($conn)
    {
        $this->user = new User($conn);
        $this->usuarioExterno = new UsuarioExterno($conn);
    }

    public function register()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nome_completo = $_POST['nome_completo'];
            $cpf = $_POST['cpf'];
            $email = $_POST['email'];
            $telefone = $_POST['telefone'];
            $municipio = $_POST['municipio'];
            $cargo = $_POST['cargo'];
            $nivel_acesso = $_POST['nivel_acesso'];
            $senha = $_POST['senha'];
            $confirmar_senha = $_POST['confirmar_senha'];
            $tempo_vinculo = $_POST['tempo_vinculo'];
            $escolaridade = $_POST['escolaridade'];
            $tipo_vinculo = $_POST['tipo_vinculo'];

            // Verificar se as senhas correspondem
            if ($senha !== $confirmar_senha) {
                header("Location: ../views/Admin/cadastro.php?error=" . urlencode("As senhas não correspondem."));
                exit();
            }

            // Verificar duplicidade de CPF e email
            if ($this->user->findByCPF($cpf) || $this->user->findByEmail($email)) {
                header("Location: ../views/Admin/cadastro.php?error=" . urlencode("Usuário com este CPF ou Email já está cadastrado."));
                exit();
            }

            session_start();
            $usuarioLogado = $_SESSION['user'];

            // Verificação para evitar que usuários não administradores cadastrem outros como administradores
            if ($nivel_acesso == 1 && $usuarioLogado['nivel_acesso'] != 1) {
                header("Location: ../views/Admin/cadastro.php?error=" . urlencode("Você não tem permissão para definir um usuário como administrador."));
                exit();
            }

            // Verificação para nível de acesso 3
            if ($usuarioLogado['nivel_acesso'] == 3 && $usuarioLogado['municipio'] != $municipio) {
                header("Location: ../views/Usuario/cadastro.php?error=" . urlencode("Você só pode cadastrar usuários do mesmo município."));
                exit();
            }

            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            if ($this->user->create($nome_completo, $cpf, $email, $telefone, $municipio, $cargo, $nivel_acesso, $senha_hash, $tempo_vinculo, $escolaridade, $tipo_vinculo)) {
                header("Location: ../views/Admin/cadastro.php?success=1");
                exit();
            } else {
                header("Location: ../views/Admin/cadastro.php?error=" . urlencode($this->user->getLastError()));
                exit();
            }
        }
    }

    public function login()
    {
        if ($_SERVER["REQUEST_METHOD"] == 'POST') {
            $email = $_POST['email'];
            $senha = $_POST['senha'];

            // Primeiro, verificar se o usuário é interno
            $user = $this->user->findByEmail($email);
            if ($user) {
                if (password_verify($senha, $user['senha'])) {
                    if ($user['status'] == 'ativo') {
                        session_start();
                        $_SESSION['user'] = $user;
                        header("Location: ../views/Dashboard/dashboard.php");
                        exit();
                    } else {
                        session_start();
                        $_SESSION['error_message'] = "Seu usuário está desativado.";
                        header("Location: ../views/login.php");
                        exit();
                    }
                } else {
                    session_start();
                    $_SESSION['error_message'] = "E-mail ou senha inválidos.";
                    header("Location: ../views/login.php");
                    exit();
                }
            } else {
                // Se não for um usuário interno, verificar se é um usuário externo
                $usuarioExterno = $this->usuarioExterno->findByEmail($email);
                if ($usuarioExterno && password_verify($senha, $usuarioExterno['senha'])) {
                    session_start();
                    $_SESSION['user'] = $usuarioExterno;
                    $_SESSION['user']['tipo_usuario'] = 'externo'; // Adiciona um indicador de tipo de usuário
                    header("Location: ../views/Company/dashboard_empresa.php");
                    exit();
                } else {
                    session_start();
                    $_SESSION['error_message'] = "E-mail ou senha inválidos.";
                    header("Location: ../views/login.php");
                    exit();
                }
            }
        }
    }

    public function resetPassword()
    {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->user->resetPassword($id)) {
                header("Location: ../views/Admin/listar_usuarios.php?success=Senha redefinida com sucesso para @visa@2024.");
                exit();
            } else {
                header("Location: ../views/Admin/listar_usuarios.php?error=" . urlencode($this->user->getLastError()));
                exit();
            }
        }
    }

    public function activate()
    {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->user->activateUser($id)) {
                header("Location: ../views/Admin/listar_usuarios.php?success=Usuário ativado com sucesso.");
                exit();
            } else {
                header("Location: ../views/Admin/listar_usuarios.php?error=" . urlencode($this->user->getLastError()));
                exit();
            }
        }
    }

    public function deactivate()
    {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->user->deactivateUser($id)) {
                header("Location: ../views/Admin/listar_usuarios.php?success=Usuário desativado com sucesso.");
                exit();
            } else {
                header("Location: ../views/Admin/listar_usuarios.php?error=" . urlencode($this->user->getLastError()));
                exit();
            }
        }
    }

    public function update()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id = $_POST['id'];
            $nome_completo = $_POST['nome_completo'];
            $cpf = $_POST['cpf'];
            $email = $_POST['email'];
            $telefone = $_POST['telefone'];
            $municipio = $_POST['municipio'];
            $cargo = $_POST['cargo'];
            $nivel_acesso = $_POST['nivel_acesso'];
            $tempo_vinculo = $_POST['tempo_vinculo'];
            $escolaridade = $_POST['escolaridade'];
            $tipo_vinculo = $_POST['tipo_vinculo'];
    
            if ($this->user->update($id, $nome_completo, $cpf, $email, $telefone, $municipio, $cargo, $nivel_acesso, $tempo_vinculo, $escolaridade, $tipo_vinculo)) {
                header("Location: ../views/Admin/editar_usuario.php?id=$id&success=1");
                exit();
            } else {
                header("Location: ../views/Admin/editar_usuario.php?id=$id&error=" . urlencode($this->user->getLastError()));
                exit();
            }
        }
    }

    public function alterarNivelAcesso()
    {
        if (isset($_POST['id']) && isset($_POST['nivel_acesso'])) {
            $id = $_POST['id'];
            $nivel_acesso = $_POST['nivel_acesso'];

            if ($this->user->updateNivelAcesso($id, $nivel_acesso)) {
                header("Location: ../views/Admin/listar_usuarios.php?success=Nível de acesso alterado com sucesso");
            } else {
                header("Location: ../views/Admin/lista_usuarios.php?error=" . urlencode($this->user->getLastError()));
            }
            exit();
        }
    }
}

// Processa a ação com base no parâmetro de URL
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Verificar conexão com o banco de dados
    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }

    $controller = new UserController($conn);

    if ($action == "register") {
        $controller->register();
    } elseif ($action == "login") {
        $controller->login();
    } elseif ($action == "update") {
        $controller->update();
    } elseif ($action == "activate") {
        $controller->activate();
    } elseif ($action == "deactivate") {
        $controller->deactivate();
    } elseif ($action == "reset_password") {
        $controller->resetPassword();
    } elseif ($action == "alterar_nivel_acesso") {
        $controller->alterarNivelAcesso();
    }

    $conn->close();
}

// Verifica a ação enviada via POST
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    // Verificar conexão com o banco de dados
    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }

    $controller = new UserController($conn);

    if ($action == "alterar_nivel_acesso") {
        $controller->alterarNivelAcesso();
    }

    if ($action == "update") {
        $controller->update();
    }

    $conn->close();
}
?>
