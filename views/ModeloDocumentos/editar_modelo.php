<?php
session_start();
ob_start();
include '../header.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';

if (!isset($_GET['id'])) {
    echo "ID do modelo de documento não fornecido!";
    exit();
}

$modelo_id = $_GET['id'];
$message = '';
$municipio = $_SESSION['user']['municipio'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tipo_documento']) && isset($_POST['conteudo'])) {
    $tipo_documento = $_POST['tipo_documento'];
    $conteudo = $_POST['conteudo'];

    // Verificar se já existe um modelo com o mesmo tipo de documento no mesmo município (excluindo o próprio modelo que está sendo editado)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM modelos_documentos WHERE tipo_documento = ? AND municipio = ? AND id != ?");
    $stmt->bind_param('ssi', $tipo_documento, $municipio, $modelo_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $message = '<div class="alert alert-danger" role="alert">Já existe um modelo de documento com este tipo no seu município!</div>';
    } else {
        $stmt = $conn->prepare("UPDATE modelos_documentos SET tipo_documento = ?, conteudo = ? WHERE id = ?");
        $stmt->bind_param('ssi', $tipo_documento, $conteudo, $modelo_id);

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success" role="alert">Modelo de documento atualizado com sucesso!</div>';
        } else {
            $message = '<div class="alert alert-danger" role="alert">Erro ao atualizar modelo de documento: ' . $stmt->error . '</div>';
        }
    }
} else {
    // Carregar os dados do modelo de documento para exibição no formulário
    $stmt = $conn->prepare("SELECT tipo_documento, conteudo FROM modelos_documentos WHERE id = ? AND municipio = ?");
    $stmt->bind_param('is', $modelo_id, $municipio);
    $stmt->execute();
    $stmt->bind_result($tipo_documento, $conteudo);
    $stmt->fetch();
    $stmt->close();

    if (!$tipo_documento) {
        echo "Modelo de documento não encontrado ou não pertence ao seu município!";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Modelo de Documento</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
                <h6 class="mb-0">Editar Modelo de Documento</h6>
            </div>
            <div class="card-body">
                <?php echo $message; ?>
                <form action="editar_modelo.php?id=<?php echo $modelo_id; ?>" method="POST">
                    <div class="mb-3">
                        <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                        <select class="form-control" id="tipo_documento" name="tipo_documento" required>
                            <option value="">Selecione o tipo de documento</option>
                            <option value="ALVARÁ SANITÁRIO" <?php if ($tipo_documento == 'ALVARÁ SANITÁRIO') echo 'selected'; ?>>ALVARÁ SANITÁRIO</option>
                            <option value="AUTO DE INFRAÇÃO" <?php if ($tipo_documento == 'AUTO DE INFRAÇÃO') echo 'selected'; ?>>AUTO DE INFRAÇÃO</option>
                            <option value="CERTIDÃO" <?php if ($tipo_documento == 'CERTIDÃO') echo 'selected'; ?>>CERTIDÃO</option>
                            <option value="DECLARAÇÃO" <?php if ($tipo_documento == 'DECLARAÇÃO') echo 'selected'; ?>>DECLARAÇÃO</option>
                            <option value="DESPACHO" <?php if ($tipo_documento == 'DESPACHO') echo 'selected'; ?>>DESPACHO</option>
                            <option value="INSTRUÇÃO NORMATIVA" <?php if ($tipo_documento == 'INSTRUÇÃO NORMATIVA') echo 'selected'; ?>>INSTRUÇÃO NORMATIVA</option>
                            <option value="LAUDO TÉCNICO" <?php if ($tipo_documento == 'LAUDO TÉCNICO') echo 'selected'; ?>>LAUDO TÉCNICO</option>
                            <option value="MEMORANDO" <?php if ($tipo_documento == 'MEMORANDO') echo 'selected'; ?>>MEMORANDO</option>
                            <option value="NOTIFICAÇÃO" <?php if ($tipo_documento == 'NOTIFICAÇÃO') echo 'selected'; ?>>NOTIFICAÇÃO</option>
                            <option value="ORDEM DE SERVIÇO" <?php if ($tipo_documento == 'ORDEM DE SERVIÇO') echo 'selected'; ?>>ORDEM DE SERVIÇO</option>
                            <option value="PARECER TÉCNICO" <?php if ($tipo_documento == 'PARECER TÉCNICO') echo 'selected'; ?>>PARECER TÉCNICO</option>
                            <option value="RELATÓRIO TÉCNICO" <?php if ($tipo_documento == 'RELATÓRIO TÉCNICO') echo 'selected'; ?>>RELATÓRIO TÉCNICO</option>
                            <option value="REQUERIMENTO" <?php if ($tipo_documento == 'REQUERIMENTO') echo 'selected'; ?>>REQUERIMENTO</option>
                            <option value="TERMO DE AVALIAÇÃO" <?php if ($tipo_documento == 'TERMO DE AVALIAÇÃO') echo 'selected'; ?>>TERMO DE AVALIAÇÃO</option>
                            <option value="TERMO DE APREENSÃO" <?php if ($tipo_documento == 'TERMO DE APREENSÃO') echo 'selected'; ?>>TERMO DE APREENSÃO</option>
                            <option value="TERMO DE COMPROMISSO" <?php if ($tipo_documento == 'TERMO DE COMPROMISSO') echo 'selected'; ?>>TERMO DE COMPROMISSO</option>
                            <option value="TERMO DE VISTORIA" <?php if ($tipo_documento == 'TERMO DE VISTORIA') echo 'selected'; ?>>TERMO DE VISTORIA</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="conteudo" class="form-label">Conteúdo do Modelo</label>
                        <textarea class="form-control" id="conteudo" name="conteudo" rows="10"><?php echo htmlspecialchars($conteudo); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Atualizar Modelo</button>
                </form>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>

</html>
