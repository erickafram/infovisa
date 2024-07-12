<?php
session_start();
require_once '../../conf/database.php';
require_once '../../models/Arquivo.php';
require_once '../../models/Estabelecimento.php';
require_once '../../models/Assinatura.php';
require_once '../../models/Usuario.php'; // Incluindo o modelo de Usuário

// Verificar se o usuário está autenticado
if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

// Instanciar os modelos
$arquivoModel = new Arquivo($conn);
$estabelecimentoModel = new Estabelecimento($conn);
$assinaturaModel = new Assinatura($conn);
$usuarioModel = new Usuario($conn);

if (isset($_GET['arquivo_id']) && isset($_GET['processo_id']) && isset($_GET['estabelecimento_id'])) {
    $arquivo_id = $_GET['arquivo_id'];
    $processo_id = $_GET['processo_id'];
    $estabelecimento_id = $_GET['estabelecimento_id'];

    $arquivo = $arquivoModel->getArquivoById($arquivo_id);
    if ($arquivo) {
        // Buscar informações do estabelecimento
        $estabelecimento = $estabelecimentoModel->findById($estabelecimento_id);

        // Buscar todas as assinaturas
        $assinaturas = $assinaturaModel->getAssinaturasPorArquivo($arquivo_id);

        // Processar adição de assinaturas, somente se o arquivo não estiver finalizado
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_assinatura']) && $arquivo['status'] != 'finalizado') {
            $usuario_id = $_POST['usuario_id'];
            if (!$assinaturaModel->isAssinaturaExistente($arquivo_id, $usuario_id)) {
                $assinaturaModel->addAssinatura($arquivo_id, $usuario_id);
            }
            header("Location: pre_visualizar_arquivo.php?arquivo_id={$arquivo_id}&processo_id={$processo_id}&estabelecimento_id={$estabelecimento_id}");
            exit();
        }

        // Processar remoção de assinaturas, somente se o arquivo não estiver finalizado
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remover_assinatura']) && $arquivo['status'] != 'finalizado') {
            $usuario_id = $_POST['usuario_id'];
            if ($usuario_id != $_SESSION['user']['id']) {
                $assinaturaModel->removeAssinatura($arquivo_id, $usuario_id);
            }
            header("Location: pre_visualizar_arquivo.php?arquivo_id={$arquivo_id}&processo_id={$processo_id}&estabelecimento_id={$estabelecimento_id}");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assinar_arquivo'])) {
            $usuario_id = $_SESSION['user']['id'];
            if ($assinaturaModel->isAssinaturaExistente($arquivo_id, $usuario_id)) {
                $assinaturaModel->addOrUpdateAssinatura($arquivo_id, $usuario_id);
            }
            header("Location: pre_visualizar_arquivo.php?arquivo_id={$arquivo_id}&processo_id={$processo_id}&estabelecimento_id={$estabelecimento_id}");
            exit();
        }

        // Buscar usuários do mesmo município logado
        $usuariosMunicipio = $usuarioModel->getUsuariosPorMunicipio($_SESSION['user']['municipio']);

        include '../header.php';
?>
        <!DOCTYPE html>
        <html lang="pt-BR">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Pré-visualizar Arquivo</title>
            <style>
                .content {
                    border: 1px solid #ddd;
                    padding: 20px;
                    border-radius: 5px;
                    background-color: #f9f9f9;
                    font-size: 12px;
                }

                h3 {
                    text-align: center;
                    margin-bottom: 20px;
                }

                .section-title {
                    font-weight: bold;
                    margin-top: 10px;
                }

                .info-table {
                    width: 100%;
                    margin-bottom: 20px;
                }

                .info-table th,
                .info-table td {
                    padding: 8px;
                    border: 1px solid #ddd;
                }

                .assinaturas-table th,
                .assinaturas-table td {
                    padding: 8px;
                    border: 1px solid #ddd;
                }
            </style>

            <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

        </head>

        <body>
            <div class="container" style="padding-bottom:10px;">
                <h3 style="padding-top:10px;"><?php echo htmlspecialchars($arquivo['tipo_documento']); ?></h3>
                <?php if ($estabelecimento) : ?>
                    <div class="content">
                        <h6 class="section-title">Dados da Empresa</h6>
                        <table class="info-table">
                            <tr>
                                <th>Nome Fantasia</th>
                                <td><?php echo htmlspecialchars($estabelecimento['nome_fantasia']); ?></td>
                            </tr>
                            <tr>
                                <th>Razão Social</th>
                                <td><?php echo htmlspecialchars($estabelecimento['razao_social']); ?></td>
                            </tr>
                            <tr>
                                <th>CNPJ</th>
                                <td><?php echo htmlspecialchars($estabelecimento['cnpj']); ?></td>
                            </tr>
                            <tr>
                                <th>Endereço</th>
                                <td><?php echo htmlspecialchars($estabelecimento['logradouro'] . ', ' . $estabelecimento['numero'] . ', ' . $estabelecimento['bairro'] . ', ' . $estabelecimento['municipio'] . '-' . $estabelecimento['uf']); ?></td>
                            </tr>
                            <tr>
                                <th>CEP</th>
                                <td><?php echo htmlspecialchars($estabelecimento['cep']); ?></td>
                            </tr>
                            <tr>
                                <th>Telefone</th>
                                <td><?php echo htmlspecialchars($estabelecimento['ddd_telefone_1'] . ' / ' . $estabelecimento['ddd_telefone_2']); ?></td>
                            </tr>
                        </table>
                    </div>
                <?php endif; ?>
                <div class="content">
                    <h6 class="section-title">Conteúdo do Arquivo</h6>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#conteudoModal" onclick="carregarConteudoModal()">
                        Ver Conteúdo do Documento
                    </button>
                </div>

                <div class="content">
                    <h6 class="section-title">Assinaturas</h6>
                    <?php if (!empty($assinaturas)) : ?>
                        <table class="table assinaturas-table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Data da Assinatura</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assinaturas as $assinatura) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($assinatura['nome_completo']); ?></td>
                                        <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($assinatura['data_assinatura']))); ?></td>
                                        <td><?php echo htmlspecialchars($assinatura['status']); ?></td>
                                        <td>
                                            <?php if ($assinatura['usuario_id'] == $_SESSION['user']['id'] && $assinatura['status'] != 'assinado') : ?>
                                                <form method="POST" action="">
                                                    <button type="submit" name="assinar_arquivo" class="btn btn-primary btn-sm">Assinar</button>
                                                </form>
                                            <?php elseif ($assinatura['usuario_id'] != $_SESSION['user']['id'] && $assinatura['status'] != 'assinado' && $arquivo['status'] != 'finalizado') : ?>
                                                <form method="POST" action="">
                                                    <input type="hidden" name="usuario_id" value="<?php echo $assinatura['usuario_id']; ?>">
                                                    <button type="submit" name="remover_assinatura" class="btn btn-danger btn-sm">Remover</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p>Não há assinaturas.</p>
                    <?php endif; ?>
                </div>

                <?php if ($arquivo['status'] != 'finalizado') : ?>
                    <div class="content">
                        <h6 class="section-title">Adicionar Assinatura</h6>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="usuario_id">Usuário</label>
                                <select class="form-control" id="usuario_id" name="usuario_id" required>
                                    <?php foreach ($usuariosMunicipio as $usuario) : ?>
                                        <option value="<?php echo $usuario['id']; ?>"><?php echo htmlspecialchars($usuario['nome_completo']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="adicionar_assinatura" class="btn btn-primary mt-3">Adicionar</button>
                        </form>
                    </div>
                <?php else : ?>
                    <div class="content">
                        <p class="text-danger">Este documento está finalizado e não pode ser alterado.</p>
                    </div>
                <?php endif; ?>
                <a href="documentos.php?processo_id=<?php echo $processo_id; ?>&id=<?php echo $estabelecimento_id; ?>" class="btn btn-primary mt-3">Voltar</a>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="conteudoModal" tabindex="-1" role="dialog" aria-labelledby="conteudoModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="conteudoModalLabel">Conteúdo do Arquivo</h5>
                        </div>
                        <div class="modal-body" id="conteudoModalBody">
                            <!-- O conteúdo do arquivo será carregado aqui via JavaScript -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function carregarConteudoModal() {
                    var conteudoCompleto = `<?php echo addslashes($arquivo['conteudo']); ?>`;
                    document.getElementById('conteudoModalBody').innerHTML = conteudoCompleto;
                }
            </script>


        </body>

        </html>
<?php
    } else {
        echo "<p>Arquivo não encontrado.</p>";
    }
} else {
    echo "<p>ID do arquivo ou outros parâmetros não fornecidos!</p>";
}
?>