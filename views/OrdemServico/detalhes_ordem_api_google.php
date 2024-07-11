<?php
session_start();
include '../header.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/OrdemServico.php';

$ordemServico = new OrdemServico($conn);

if (!isset($_GET['id'])) {
    echo "ID da ordem de serviço não fornecido!";
    exit();
}

$id = $_GET['id'];
$ordem = $ordemServico->getOrdemById($id);

if (!$ordem) {
    echo "Ordem de serviço não encontrada!";
    exit();
}

$tecnicos_ids = json_decode($ordem['tecnicos']);
$nomes_tecnicos = $ordemServico->getTecnicosNomes($tecnicos_ids);

// Buscar os nomes das ações executadas
$acoes_ids = json_decode($ordem['acoes_executadas'], true); // Use 'true' to ensure it's decoded as an array
$acoes_nomes = $ordemServico->getAcoesNomes($acoes_ids);

if (isset($_POST['status'])) {
    $status = $_POST['status'];
    $ordemServico->updateStatus($id, $status);
    header("Location: detalhes_ordem.php?id=$id");
    exit();
}

function formatDate($date)
{
    $dateTime = new DateTime($date);
    return $dateTime->format('d/m/Y');
}

$endereco = $ordem['endereco'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Detalhes da Ordem de Serviço</title>
    <script>
        function initMap() {
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({
                'address': '<?php echo $endereco; ?>'
            }, function(results, status) {
                if (status === 'OK') {
                    var mapOptions = {
                        zoom: 15,
                        center: results[0].geometry.location
                    };
                    var map = new google.maps.Map(document.getElementById('map'), mapOptions);
                    var marker = new google.maps.Marker({
                        map: map,
                        position: results[0].geometry.location
                    });

                    var latitude = results[0].geometry.location.lat();
                    var longitude = results[0].geometry.location.lng();
                    var locationInfo = `
                        <p><strong>Localização:</strong> ${results[0].formatted_address}</p>
                        <p> <button class="btn btn-success" onclick="sendWhatsApp('${latitude}', '${longitude}')"> <i class="fab fa-whatsapp"></i> Enviar Localização via WhatsApp </button> </p>
                    `;
                    document.getElementById('local-info').innerHTML = locationInfo;
                } else {
                    if (status === 'REQUEST_DENIED') {
                        setTimeout(function() {
                            location.reload();
                        }, 5000); // Atualiza a página após 5 segundos
                    } else {
                        alert('Geocode was not successful for the following reason: ' + status);
                    }
                }
            });
        }

        function copyToClipboard(elementId) {
            var copyText = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(copyText).then(function() {
                alert('Copiado: ' + copyText);
            }, function(err) {
                alert('Erro ao copiar: ' + err);
            });
        }

        function sendWhatsApp(latitude, longitude) {
            var message = `Localização do estabelecimento:\nLatitude: ${latitude}\nLongitude: ${longitude}\n\nAbra no Google Maps: https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}`;
            var whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
    </script>
</head>

<body onload="initMap()">

    <div class="container mt-5">
        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])) : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5>Ações</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <!-- SOMENTE USUARIOS ADMINISTRADORES, SUPORTE E GERENTE PODEM CADASTRAR ORDEM DE SERVIÇO -->
                        <?php if ($_SESSION['user']['nivel_acesso'] == 1 || $_SESSION['user']['nivel_acesso'] == 2 || $_SESSION['user']['nivel_acesso'] == 3) : ?>
                            <a href="editar_ordem.php?id=<?php echo htmlspecialchars($id); ?>" class="list-group-item list-group-item-action">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="excluir_ordem.php?id=<?php echo htmlspecialchars($id); ?>" class="list-group-item list-group-item-action text-danger" onclick="return confirm('Tem certeza que deseja excluir esta ordem de serviço?')">
                                <i class="fas fa-trash"></i> Excluir
                            </a>
                        <?php endif; ?>
                        <?php if ($ordem['status'] == 'ativa') : ?>
                            <button type="button" class="list-group-item list-group-item-action text-success" data-bs-toggle="modal" data-bs-target="#finalizarModal">
                                <i class="fas fa-check"></i> Finalizar
                            </button>
                        <?php else : ?>
                            <a href="reiniciar_ordem.php?id=<?php echo htmlspecialchars($id); ?>" class="list-group-item list-group-item-action text-warning">
                                <i class="fas fa-undo"></i> Reiniciar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h5>Detalhes da Ordem de Serviço</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover table-striped" style="font-size:13px;">
                            <tr>
                                <th>Número da Ordem de Serviço:</th>
                                <td>
                                    <?php echo htmlspecialchars($ordem['id'] . '.' . date('Y', strtotime($ordem['data_inicio']))); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Número do Processo:</th>
                                <td>
                                    <?php if ($ordem['processo_id']) : ?>
                                        <a href="../Processo/documentos.php?processo_id=<?php echo htmlspecialchars($ordem['processo_id']); ?>&id=<?php echo htmlspecialchars($ordem['estabelecimento_id']); ?>">
                                            <?php echo htmlspecialchars($ordem['numero_processo']); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php echo htmlspecialchars($ordem['numero_processo']); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Razão Social:</th>
                                <td>
                                    <?php if ($ordem['estabelecimento_id']) : ?>
                                        <a href="../Estabelecimento/detalhes_estabelecimento.php?id=<?php echo htmlspecialchars($ordem['estabelecimento_id']); ?>">
                                            <?php echo htmlspecialchars($ordem['razao_social']); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php echo htmlspecialchars($ordem['razao_social']); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Nome Fantasia:</th>
                                <td><?php echo htmlspecialchars($ordem['nome_fantasia']); ?></td>
                            </tr>
                            <tr>
                                <th>Endereço:</th>
                                <td><?php echo htmlspecialchars($ordem['endereco']); ?></td>
                            </tr>
                            <tr>
                                <th>Período:</th>
                                <td><?php echo htmlspecialchars(formatDate($ordem['data_inicio'])); ?> - <?php echo htmlspecialchars(formatDate($ordem['data_fim'])); ?></td>
                            </tr>
                            <tr>
                                <th>Ações Executadas:</th>
                                <td>
                                    <?php
                                    $acoes_executadas_nomes = [];
                                    if (is_array($acoes_ids)) {
                                        foreach ($acoes_ids as $acao_id) {
                                            $acoes_executadas_nomes[] = $acoes_nomes[$acao_id];
                                        }
                                    }
                                    echo htmlspecialchars(implode(', ', $acoes_executadas_nomes));
                                    ?>
                                </td>
                            </tr>
                            <?php if (!empty($ordem['observacao']) || !empty($ordem['descricao_encerramento'])) : ?>
                                <?php if (!empty($ordem['observacao'])) : ?>
                                    <tr>
                                        <th>Observação:</th>
                                        <td><?php echo htmlspecialchars($ordem['observacao']); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (!empty($ordem['descricao_encerramento'])) : ?>
                                    <tr>
                                        <th>Descrição de Encerramento:</th>
                                        <td><?php echo htmlspecialchars($ordem['descricao_encerramento'] ?? ''); ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endif; ?>


                            <tr>
                                <th>Técnicos:</th>
                                <td><?php echo htmlspecialchars(implode(', ', $nomes_tecnicos)); ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td><?php echo htmlspecialchars($ordem['status'] == 'ativa' ? 'Ativa' : 'Finalizada'); ?></td>
                            </tr>
                            <tr>
                                <th>Ordem de Serviço:</th>
                                <td>
                                    <form action="gerar_pdf.php" method="post" target="_blank">
                                        <input type="hidden" name="ordem_id" value="<?php echo htmlspecialchars($id); ?>">
                                        <button type="submit" class="btn btn-info">Gerar PDF</button>
                                    </form>
                                </td>
                            </tr>
                        </table>
                        <div id="map" style="height: 400px; width: 100%;"></div>
                        <div id="local-info" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Descrição de Encerramento -->
    <div class="modal fade" id="finalizarModal" tabindex="-1" aria-labelledby="finalizarModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="finalizarModalLabel">Descrição do Encerramento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="finalizar_ordem.php?id=<?php echo htmlspecialchars($id); ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="descricao_encerramento" class="form-label">Descrição</label>
                            <textarea class="form-control" id="descricao_encerramento" name="descricao_encerramento" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Finalizar Ordem de Serviço</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>

</html>