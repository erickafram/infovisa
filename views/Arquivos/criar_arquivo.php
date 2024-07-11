<?php
session_start();
ob_start();
include '../header.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../controllers/ArquivoController.php';
require_once '../../models/Logomarca.php';
require_once '../../models/Usuario.php';

$controller = new ArquivoController($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] == 'previsualizar') {
        $controller->previsualizar();
    } elseif ($_POST['acao'] == 'salvar') {
        $controller->create();
    } elseif ($_POST['acao'] == 'rascunho') {
        $controller->createDraft();
    }
}

if (!isset($_GET['processo_id']) || !isset($_GET['id'])) {
    echo "ID do processo ou estabelecimento não fornecido!";
    exit();
}

$processo_id = $_GET['processo_id'];
$estabelecimento_id = $_GET['id'];

$usuario_logado = (new Usuario($conn))->findById($_SESSION['user']['id']);
$logomarcaModel = new Logomarca($conn);
$logomarca = $logomarcaModel->getLogomarcaByMunicipio($usuario_logado['municipio']);

// Buscar todos os usuários do mesmo município do usuário logado
$usuariosMunicipio = $conn->query("SELECT id, nome_completo, cpf FROM usuarios WHERE municipio = '{$usuario_logado['municipio']}'");

// Função para formatar o CNAE
function formatCnae($cnae)
{
    return preg_replace('/(\d{4})(\d)(\d{2})/', '$1-$2/$3', $cnae);
}

// Consulta para obter os CNAEs que estão nos grupos de risco associados ao estabelecimento
$query = "
    SELECT 
        e.cnae_fiscal AS cnae_fiscal,
        e.cnae_fiscal_descricao AS cnae_fiscal_descricao,
        cnaes.codigo AS cnae_secundario,
        cnaes.descricao AS cnae_secundario_descricao,
        gr_fiscal.descricao AS grupo_risco_fiscal,
        gr_secundario.descricao AS grupo_risco_secundario
    FROM 
        estabelecimentos e
    LEFT JOIN 
        atividade_grupo_risco agr_fiscal ON e.cnae_fiscal = agr_fiscal.cnae AND e.municipio = agr_fiscal.municipio
    LEFT JOIN 
        grupo_risco gr_fiscal ON agr_fiscal.grupo_risco_id = gr_fiscal.id
    LEFT JOIN 
        JSON_TABLE(
            e.cnaes_secundarios, 
            '$[*]' COLUMNS (
                codigo VARCHAR(255) PATH '$.codigo', 
                descricao VARCHAR(255) PATH '$.descricao'
            )
        ) AS cnaes ON JSON_VALID(e.cnaes_secundarios)
    LEFT JOIN 
        atividade_grupo_risco agr_secundario ON cnaes.codigo = agr_secundario.cnae AND e.municipio = agr_secundario.municipio
    LEFT JOIN 
        grupo_risco gr_secundario ON agr_secundario.grupo_risco_id = gr_secundario.id
    WHERE 
        e.id = $estabelecimento_id AND JSON_VALID(e.cnaes_secundarios)
";

$result = $conn->query($query);

$cnaes = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['grupo_risco_fiscal'])) {
            //$cnaes[] = formatCnae($row['cnae_fiscal']) . " - " . $row['cnae_fiscal_descricao'] . " (Grupo de Risco: " . $row['grupo_risco_fiscal'] . ")";
            $cnaes[] = formatCnae($row['cnae_fiscal']) . " - " . $row['cnae_fiscal_descricao'];
        }
        if (!empty($row['grupo_risco_secundario'])) {
            //$cnaes[] = formatCnae($row['cnae_secundario']) . " - " . $row['cnae_secundario_descricao'] . " (Grupo de Risco: " . $row['grupo_risco_secundario'] . ")";
            $cnaes[] = formatCnae($row['cnae_secundario']) . " - " . $row['cnae_secundario_descricao'];
        }
    }
    $cnaes = array_unique($cnaes);
} else {
    echo "Erro ao buscar informações do estabelecimento!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Arquivo</title>
    <script>
        tinymce.init({
            selector: '#conteudo',
            plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak image imagetools',
            toolbar_mode: 'floating',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image',
            images_upload_url: 'upload_image.php',
            automatic_uploads: true,
            file_picker_types: 'image',
            file_picker_callback: function(cb, value, meta) {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');

                input.onchange = function() {
                    var file = this.files[0];

                    var reader = new FileReader();
                    reader.onload = function() {
                        var id = 'blobid' + (new Date()).getTime();
                        var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                        var base64 = reader.result.split(',')[1];
                        var blobInfo = blobCache.create(id, file, base64);
                        blobCache.add(blobInfo);

                        cb(blobInfo.blobUri(), {
                            title: file.name
                        });
                    };
                    reader.readAsDataURL(file);
                };

                input.click();
            }
        });

        function carregarModelo(tipoDocumento) {
            if (tipoDocumento) {
                fetch(`obter_modelo.php?tipo_documento=${tipoDocumento}`)
                    .then(response => response.text())
                    .then(data => {
                        tinymce.get('conteudo').setContent(data);
                    })
                    .catch(error => console.error('Erro ao carregar modelo:', error));
            } else {
                tinymce.get('conteudo').setContent('');
            }
        }
    </script>
</head>

<body>

    <div class="container mt-5" style="padding-bottom:10px;">
        <div class="card-body">
            <form id="arquivo-form" action="criar_arquivo.php?processo_id=<?php echo $processo_id; ?>&id=<?php echo $estabelecimento_id; ?>" method="POST" target="previewIframe" enctype="multipart/form-data">
                <?php if ($logomarca) : ?>
                    <div class="mb-3">
                        <center><img src="<?php echo $logomarca['caminho_logomarca']; ?>" alt="Logomarca" width="100"></center>
                    </div>
                <?php endif; ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                        <select class="form-control" id="tipo_documento" name="tipo_documento" required onchange="carregarModelo(this.value)">
                            <option value="">Selecione o tipo de documento</option>
                            <option value="ALVARÁ SANITÁRIO">ALVARÁ SANITÁRIO</option>
                            <option value="AUTO DE INFRAÇÃO">AUTO DE INFRAÇÃO</option>
                            <option value="CERTIDÃO">CERTIDÃO</option>
                            <option value="DECLARAÇÃO">DECLARAÇÃO</option>
                            <option value="DESPACHO">DESPACHO</option>
                            <option value="INSTRUÇÃO NORMATIVA">INSTRUÇÃO NORMATIVA</option>
                            <option value="LAUDO TÉCNICO">LAUDO TÉCNICO</option>
                            <option value="MEMORANDO">MEMORANDO</option>
                            <option value="NOTIFICAÇÃO">NOTIFICAÇÃO</option>
                            <option value="ORDEM DE SERVIÇO">ORDEM DE SERVIÇO</option>
                            <option value="PARECER TÉCNICO">PARECER TÉCNICO</option>
                            <option value="RELATÓRIO TÉCNICO">RELATÓRIO TÉCNICO</option>
                            <option value="REQUERIMENTO">REQUERIMENTO</option>
                            <option value="TERMO DE AVALIAÇÃO">TERMO DE AVALIAÇÃO</option>
                            <option value="TERMO DE APREENSÃO">TERMO DE APREENSÃO</option>
                            <option value="TERMO DE COMPROMISSO">TERMO DE COMPROMISSO</option>
                            <option value="TERMO DE VISTORIA">TERMO DE VISTORIA</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="sigiloso" class="form-label">Documento Sigiloso</label>
                        <select class="form-select" id="sigiloso" name="sigiloso" required>
                            <option value="0">Não</option>
                            <option value="1">Sim</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="conteudo" class="form-label">Conteúdo do Documento</label>
                    <textarea class="form-control" id="conteudo" name="conteudo" rows="15"></textarea>
                </div>

                <!-- Mostrar informações dos CNAEs vinculados aos grupos de risco -->
                <div class="mb-3">
                    <label for="estabelecimento_info" class="form-label">CNAEs vinculados aos Grupos</label>
                    <table class="table table-bordered">
                        <tr>
                            <td>
                                <?php
                                foreach ($cnaes as $cnae) {
                                    echo htmlspecialchars($cnae, ENT_QUOTES, 'UTF-8') . "<br>";
                                }
                                ?>
                                <button type="button" class="btn btn-secondary btn-sm" style="margin-top:5px;" onclick="copiarCNAEs()">Copiar CNAEs</button>
                                <textarea id="cnaesParaCopiar" style="position: absolute; left: -9999px;"></textarea>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="mb-3">
                    <label for="assinantes" class="form-label">Definir Assinaturas Digital</label>

                    <!-- Para usuários com nível de acesso 1, 2, 3 -->
                    <?php if ($usuario_logado['nivel_acesso'] == 4) : ?>
                        <?php while ($usuario = $usuariosMunicipio->fetch_assoc()) : ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="assinante_<?php echo $usuario['id']; ?>" name="assinantes[]" value="<?php echo $usuario['id']; ?>" <?php echo $usuario['id'] == $usuario_logado['id'] ? 'checked disabled' : ''; ?>>
                                <?php if ($usuario['id'] == $usuario_logado['id']) : ?>
                                    <input type="hidden" name="assinantes[]" value="<?php echo $usuario['id']; ?>">
                                <?php endif; ?>
                                <label class="form-check-label" for="assinante_<?php echo $usuario['id']; ?>">
                                    <?php echo htmlspecialchars($usuario['nome_completo'] . " - " . $usuario['cpf'], ENT_QUOTES, 'UTF-8'); ?>
                                </label>
                            </div>
                        <?php endwhile; ?>

                        <!-- Para usuários com nível de acesso 4 -->
                    <?php elseif (in_array($usuario_logado['nivel_acesso'], [1, 2, 3])) : ?>
                        <?php while ($usuario = $usuariosMunicipio->fetch_assoc()) : ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="assinante_<?php echo $usuario['id']; ?>" name="assinantes[]" value="<?php echo $usuario['id']; ?>" <?php echo $usuario['id'] == $usuario_logado['id'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="assinante_<?php echo $usuario['id']; ?>">
                                    <?php echo htmlspecialchars($usuario['nome_completo'] . " - " . $usuario['cpf'], ENT_QUOTES, 'UTF-8'); ?>
                                </label>
                            </div>
                        <?php endwhile; ?>

                        <!-- Para outros usuários -->
                    <?php else : ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="assinante_<?php echo $usuario_logado['id']; ?>" name="assinantes[]" value="<?php echo $usuario_logado['id']; ?>" checked>
                            <label class="form-check-label" for="assinante_<?php echo $usuario_logado['id']; ?>">
                                <?php echo htmlspecialchars($usuario_logado['nome_completo'] . " - " . $usuario_logado['cpf'], ENT_QUOTES, 'UTF-8'); ?>
                            </label>
                        </div>
                    <?php endif; ?>
                </div>


                <input type="hidden" name="processo_id" value="<?php echo $processo_id; ?>">
                <input type="hidden" name="estabelecimento_id" value="<?php echo $estabelecimento_id; ?>">
                <input type="hidden" name="acao" id="acao" value="">
                <button type="button" class="btn btn-secondary" onclick="salvarRascunho()">Salvar como Rascunho</button>
                <button type="button" class="btn btn-primary" onclick="salvarPDF()">Finalizar Documento</button>
            </form>
        </div>
    </div>

    <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">Pré-visualização do Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe id="previewIframe" name="previewIframe" src="" width="100%" height="500px"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarPDF()">Salvar Documento</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmSaveModal" tabindex="-1" role="dialog" aria-labelledby="confirmSaveModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmSaveModalLabel">Confirmação de Salvamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Após Finalizar o documento de forma definitiva, não será possível editar o documento. Caso queira que o documento seja editado, por favor, salve como rascunho.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="confirmarSalvarPDF()">Confirmar e Salvar</button>
                </div>
            </div>
        </div>
    </div>


    <script>
        function previsualizarPDF() {
            document.getElementById('acao').value = 'previsualizar';
            document.getElementById('arquivo-form').action = 'previsualizar.php';
            document.getElementById('arquivo-form').target = 'previewIframe';
            document.getElementById('arquivo-form').submit();
            $('#previewModal').modal('show');
        }

        function validarFormulario() {
            var tipoDocumento = document.getElementById('tipo_documento').value;
            if (tipoDocumento === '') {
                alert('Por favor, selecione um tipo de documento.');
                return false;
            }
            return true;
        }


        function salvarPDF() {
            if (validarFormulario()) {
                $('#confirmSaveModal').modal('show');
            }
        }

        function confirmarSalvarPDF() {
            if (validarFormulario()) {
                document.getElementById('acao').value = 'salvar';
                document.getElementById('arquivo-form').action = 'criar_arquivo.php';
                document.getElementById('arquivo-form').target = '';
                document.getElementById('arquivo-form').submit();
                $('#confirmSaveModal').modal('hide');
            }
        }

        function salvarRascunho() {
            if (validarFormulario()) {
                document.getElementById('acao').value = 'rascunho';
                document.getElementById('arquivo-form').action = 'criar_arquivo.php';
                document.getElementById('arquivo-form').target = '';
                document.getElementById('arquivo-form').submit();
            }
        }


        function copiarCNAEs() {
            var cnaes = <?php echo json_encode($cnaes); ?>;
            console.log(cnaes); // Adicione esta linha para depuração
            if (typeof cnaes === 'object') {
                var cnaesArray = Object.values(cnaes);
                var cnaesTexto = cnaesArray.join('\n');
                var cnaesTextarea = document.getElementById('cnaesParaCopiar');
                cnaesTextarea.value = cnaesTexto;
                cnaesTextarea.select();
                document.execCommand('copy');
                alert('CNAEs copiados para a área de transferência!');
            } else {
                alert('Erro ao copiar CNAEs!');
            }
        }
    </script>

    <?php include '../footer.php'; ?>
</body>

</html>