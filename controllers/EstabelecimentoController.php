<?php
require_once '../conf/database.php';
require_once '../models/Estabelecimento.php';

class EstabelecimentoController
{
    private $estabelecimento;

    public function __construct($conn)
    {
        $this->estabelecimento = new Estabelecimento($conn);
    }

    public function register()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            session_start();
            $usuarioMunicipio = $_SESSION['user']['municipio'];
            $usuarioNivelAcesso = $_SESSION['user']['nivel_acesso'];
            $estabelecimentoMunicipio = $_POST['municipio'];

            // Verifica se o usuário não é administrador e se o município do estabelecimento é diferente do município do usuário
            if ($usuarioNivelAcesso != 1 && $usuarioMunicipio !== $estabelecimentoMunicipio) {
                header("Location: ../views/Estabelecimento/cadastro_estabelecimento.php?error=" . urlencode("Só é permitido cadastrar estabelecimentos do mesmo município que o usuário."));
                exit();
            }

            $data = [
                'cnpj' => $_POST['cnpj'],
                'descricao_identificador_matriz_filial' => $_POST['descricao_identificador_matriz_filial'],
                'nome_fantasia' => $_POST['nome_fantasia'],
                'descricao_situacao_cadastral' => $_POST['descricao_situacao_cadastral'],
                'data_situacao_cadastral' => $_POST['data_situacao_cadastral'],
                'data_inicio_atividade' => $_POST['data_inicio_atividade'],
                'cnae_fiscal' => $_POST['cnae_fiscal'],
                'cnae_fiscal_descricao' => $_POST['cnae_fiscal_descricao'],
                'descricao_tipo_de_logradouro' => $_POST['descricao_tipo_de_logradouro'],
                'logradouro' => $_POST['logradouro'],
                'numero' => $_POST['numero'],
                'complemento' => $_POST['complemento'],
                'bairro' => $_POST['bairro'],
                'cep' => $_POST['cep'],
                'uf' => $_POST['uf'],
                'municipio' => $_POST['municipio'],
                'ddd_telefone_1' => $_POST['ddd_telefone_1'],
                'ddd_telefone_2' => $_POST['ddd_telefone_2'],
                'razao_social' => $_POST['razao_social'],
                'natureza_juridica' => $_POST['natureza_juridica'],
                'qsa' => isset($_POST['qsa']) ? json_decode($_POST['qsa'], true) : [],
                'cnaes_secundarios' => isset($_POST['cnaes_secundarios']) ? json_decode($_POST['cnaes_secundarios'], true) : [],
                'status' => 'aprovado'
            ];

            if ($this->estabelecimento->create($data)) {
                header("Location: ../views/Estabelecimento/listar_estabelecimentos.php?success=1");
                exit();
            } else {
                header("Location: ../views/Estabelecimento/cadastro_estabelecimento.php?error=" . urlencode($this->estabelecimento->getLastError()));
                exit();
            }
        }
    }

    public function checkCnpj()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $cnpj = $_POST['cnpj'];
            $exists = $this->estabelecimento->checkCnpjExists($cnpj);
            echo json_encode(['exists' => $exists]);
        }
    }

    public function checkCnpjDuplicado()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $cnpj = $_POST['cnpj'];
            $exists = $this->estabelecimento->checkCnpjExists($cnpj);
            echo json_encode(['exists' => $exists]);
        }
    }

    public function update()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id = $_GET['id'];
            $data = [
                'descricao_identificador_matriz_filial' => $_POST['descricao_identificador_matriz_filial'],
                'nome_fantasia' => $_POST['nome_fantasia'],
                'descricao_situacao_cadastral' => $_POST['descricao_situacao_cadastral'],
                'data_situacao_cadastral' => $_POST['data_situacao_cadastral'],
                'data_inicio_atividade' => $_POST['data_inicio_atividade'],
                'descricao_tipo_de_logradouro' => $_POST['descricao_tipo_de_logradouro'],
                'logradouro' => $_POST['logradouro'],
                'numero' => $_POST['numero'],
                'complemento' => $_POST['complemento'],
                'bairro' => $_POST['bairro'],
                'cep' => $_POST['cep'],
                'uf' => $_POST['uf'],
                'municipio' => $_POST['municipio'],
                'ddd_telefone_1' => $_POST['ddd_telefone_1'],
                'ddd_telefone_2' => $_POST['ddd_telefone_2'],
                'razao_social' => $_POST['razao_social'],
                'natureza_juridica' => $_POST['natureza_juridica']
            ];

            if ($this->estabelecimento->update($id, $data)) {
                header("Location: ../views/Estabelecimento/editar_estabelecimento.php?id=$id&success=1");
                exit();
            } else {
                header("Location: ../views/Estabelecimento/editar_estabelecimento.php?id=$id&error=" . urlencode($this->estabelecimento->getLastError()));
                exit();
            }
        }
    }

    public function registerEmpresa()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            session_start();
            $usuarioExternoId = $_SESSION['user']['id']; // Pegando o ID do usuário externo logado

            $cnpj = $_POST['cnpj'];
            if ($this->estabelecimento->checkCnpjExists($cnpj)) {
                $municipio = $this->estabelecimento->getMunicipioByCnpj($cnpj);
                header("Location: ../views/Company/cadastro_estabelecimento_empresa.php?error=" . urlencode("Estabelecimento já existe. Entre em contato com a Vigilância Sanitária Municipal de " . $municipio . "."));
                exit();
            }

            $data = [
                'cnpj' => $_POST['cnpj'],
                'descricao_identificador_matriz_filial' => $_POST['descricao_identificador_matriz_filial'],
                'nome_fantasia' => $_POST['nome_fantasia'],
                'descricao_situacao_cadastral' => $_POST['descricao_situacao_cadastral'],
                'data_situacao_cadastral' => $_POST['data_situacao_cadastral'],
                'data_inicio_atividade' => $_POST['data_inicio_atividade'],
                'cnae_fiscal' => $_POST['cnae_fiscal'],
                'cnae_fiscal_descricao' => $_POST['cnae_fiscal_descricao'],
                'descricao_tipo_de_logradouro' => $_POST['descricao_tipo_de_logradouro'],
                'logradouro' => $_POST['logradouro'],
                'numero' => $_POST['numero'],
                'complemento' => $_POST['complemento'],
                'bairro' => $_POST['bairro'],
                'cep' => $_POST['cep'],
                'uf' => $_POST['uf'],
                'municipio' => $_POST['municipio'],
                'ddd_telefone_1' => $_POST['ddd_telefone_1'],
                'ddd_telefone_2' => $_POST['ddd_telefone_2'],
                'razao_social' => $_POST['razao_social'],
                'natureza_juridica' => $_POST['natureza_juridica'],
                'qsa' => isset($_POST['qsa']) ? json_decode($_POST['qsa'], true) : [],
                'cnaes_secundarios' => isset($_POST['cnaes_secundarios']) ? json_decode($_POST['cnaes_secundarios'], true) : [],
                'status' => 'pendente',
                'usuario_externo_id' => $usuarioExternoId // Adicionando o ID do usuário externo
            ];

            $estabelecimentoId = $this->estabelecimento->create($data);
            if ($estabelecimentoId) {
                // Vincula o usuário externo ao estabelecimento
                $this->estabelecimento->vincularUsuarioEstabelecimento($usuarioExternoId, $estabelecimentoId, 'CONTADOR');

                header("Location: ../views/Company/cadastro_estabelecimento_empresa.php?success=1");
                exit();
            } else {
                header("Location: ../views/Company/cadastro_estabelecimento_empresa.php?error=" . urlencode("Erro ao cadastrar estabelecimento."));
                exit();
            }
        }
    }


    public function reiniciar()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->estabelecimento->reiniciarEstabelecimento($id)) {
                header("Location: ../views/Estabelecimento/listar_estabelecimentos_rejeitados.php?success=1");
                exit();
            } else {
                header("Location: ../views/Estabelecimento/listar_estabelecimentos_rejeitados.php?error=" . urlencode("Erro ao reiniciar estabelecimento."));
                exit();
            }
        }
    }

    public function approveEstabelecimento()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
            $id = $_POST['id'];
            if ($this->estabelecimento->approve($id)) {
                header("Location: ../views/Estabelecimento/listar_estabelecimentos.php?success=1");
                exit();
            } else {
                header("Location: ../views/Estabelecimento/listar_estabelecimentos.php?error=" . urlencode("Erro ao aprovar estabelecimento."));
                exit();
            }
        }
    }


    public function rejectEstabelecimento()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['motivo'])) {
            $id = $_POST['id'];
            $motivo = $_POST['motivo'];
            if ($this->estabelecimento->reject($id, $motivo)) {
                header("Location: ../views/Dashboard/dashboard.php?success=1");
                exit();
            } else {
                header("Location: ../views/Estabelecimento/listar_estabelecimentos.php?error=" . urlencode("Erro ao rejeitar estabelecimento."));
                exit();
            }
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

    $controller = new EstabelecimentoController($conn);

    if ($action == "register") {
        $controller->register();
    } elseif ($action == "rejectEstabelecimento") {
        $controller->rejectEstabelecimento();
    } elseif ($action == "checkCnpj") {
        $controller->checkCnpj();
    } elseif ($action == "update") {
        $controller->update();
    } elseif ($action == 'registerEmpresa') {
        $controller->registerEmpresa();
    } elseif ($action == 'checkCnpjDuplicado') {
        $controller->checkCnpjDuplicado();
    } elseif ($action == 'approveEstabelecimento') {
        $controller->approveEstabelecimento();
    } elseif ($action == 'reiniciar') { // Nova ação para reiniciar o estabelecimento
        $controller->reiniciar();
    }

    $conn->close();
}
