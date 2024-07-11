<?php

session_start();
ob_start();
include '../header.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tipo_documento']) && isset($_POST['conteudo'])) {
    $municipio = $_SESSION['user']['municipio'];
    $tipo_documento = $_POST['tipo_documento'];
    $conteudo = $_POST['conteudo'];

    // Verificar se já existe um modelo com o mesmo tipo de documento no mesmo município
    $stmt = $conn->prepare("SELECT COUNT(*) FROM modelos_documentos WHERE tipo_documento = ? AND municipio = ?");
    $stmt->bind_param('ss', $tipo_documento, $municipio);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $message = '<div class="alert alert-danger" role="alert">Já existe um modelo de documento com este tipo no seu município!</div>';
    } else {
        $stmt = $conn->prepare("INSERT INTO modelos_documentos (tipo_documento, conteudo, municipio) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $tipo_documento, $conteudo, $municipio);

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success" role="alert">Modelo de documento inserido com sucesso!</div>';
        } else {
            $message = '<div class="alert alert-danger" role="alert">Erro ao inserir modelo de documento: ' . $stmt->error . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserir Modelo de Documento</title>
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
    </script>
</head>

<body>

    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Inserir Novo Modelo de Documento</h6>
            </div>
            <div class="card-body">
                <?php echo $message; ?>
                <form action="inserir_modelo.php" method="POST">
                    <div class="mb-3">
                        <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                        <select class="form-control" id="tipo_documento" name="tipo_documento" required>
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
                    <div class="mb-3">
                        <label for="conteudo" class="form-label">Conteúdo do Modelo</label>
                        <textarea class="form-control" id="conteudo" name="conteudo" rows="10"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Inserir Modelo</button>
                </form>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>

</html>
