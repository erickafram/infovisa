<?php
session_start();

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

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Limpa a mensagem após exibição
}
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Limpa a mensagem após exibição
    echo '<script>setTimeout(function() { window.location.href = "../../login.php"; }, 2000);</script>';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário Externo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>
    <style>
        .hidden-fields {
            display: none;
        }
        .centered {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Cadastro de Usuário</h2>
        <?php
        if (isset($error_message)) {
            echo '<div class="alert alert-danger" role="alert">' . $error_message . '</div>';
        }
        if (isset($success_message)) {
            echo '<div class="alert alert-success" role="alert">' . $success_message . '</div>';
        }
        ?>
        <div class="alert alert-info text-center" role="alert">
            Para se cadastrar, por favor, insira o CPF para validar as informações.
        </div>
        <form action="../../controllers/UsuarioExternoController.php?action=register" method="POST">
            <div class="row mb-3 centered">
                <div class="col-md-6">
                    <label for="cpf" class="form-label">CPF</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="cpf" name="cpf" required>
                        <button class="btn btn-primary" type="button" id="validarCpfBtn">Validar</button>
                    </div>
                </div>
            </div>
            <div id="form-fields" class="hidden-fields">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nome_completo" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="nome_completo" name="nome_completo" required>
                    </div>
                    <div class="col-md-6">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text" class="form-control" id="telefone" name="telefone" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="col-md-6">
                        <label for="vinculo_estabelecimento" class="form-label">Vínculo com Estabelecimento</label>
                        <select class="form-select" id="vinculo_estabelecimento" name="vinculo_estabelecimento" required>
                            <option value="CONTADOR">CONTADOR</option>
                            <option value="RESPONSÁVEL LEGAL">RESPONSÁVEL LEGAL</option>
                            <option value="RESPONSÁVEL TÉCNICO">RESPONSÁVEL TÉCNICO</option>
                            <option value="FUNCIONÁRIO">FUNCIONÁRIO</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>
                    <div class="col-md-6">
                        <label for="senha_confirmacao" class="form-label">Confirmar Senha</label>
                        <input type="password" class="form-control" id="senha_confirmacao" name="senha_confirmacao" required>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="history.back()">Voltar</button>
                    <button type="submit" class="btn btn-primary">Cadastrar</button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#cpf').mask('000.000.000-00', {reverse: true});
            $('#telefone').mask('(00) 00000-0000');

            $('#validarCpfBtn').click(function() {
                var cpf = $('#cpf').val().replace(/\D/g, '');
                if (cpf.length === 11) {
                    // Exibir os outros campos
                    $('#form-fields').removeClass('hidden-fields');
                } else {
                    alert('Por favor, insira um CPF válido.');
                    // Manter os campos ocultos
                    $('#form-fields').addClass('hidden-fields');
                }
            });
        });
    </script>
</body>
</html>
