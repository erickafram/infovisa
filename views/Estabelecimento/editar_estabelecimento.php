<?php
session_start();
include '../header.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php"); // Redirecionar para a página de login se não estiver autenticado ou não for administrador
    exit();
}

require_once '../../conf/database.php';
require_once '../../models/Estabelecimento.php';


$estabelecimento = new Estabelecimento($conn);

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $dadosEstabelecimento = $estabelecimento->findById($id);

    if (!$dadosEstabelecimento) {
        echo "Estabelecimento não encontrado!";
        exit();
    }
} else {
    echo "ID do estabelecimento não fornecido!";
    exit();
}

$qsa = json_decode($dadosEstabelecimento['qsa'], true);
$cnaes_secundarios = json_decode($dadosEstabelecimento['cnaes_secundarios'], true);

function setDefaultValue($value, $defaultValue = "Não Informado")
{
    return $value ? $value : $defaultValue;
}

function formatCNAE($cnae)
{
    if (strlen($cnae) === 7) {
        return substr($cnae, 0, 4) . '-' . substr($cnae, 4, 1) . '/' . substr($cnae, 5, 2);
    }
    return $cnae;
}

?>

<div class="container">
    <div class="row">
        <div class="col-md-3">
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Menu</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="detalhes_estabelecimento.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-info-circle me-2"></i>Detalhes
                    </a>
                    <a href="editar_estabelecimento.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-edit me-2"></i>Editar
                    </a>
                    <a href="atividades.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-tasks me-2"></i>Atividades
                    </a>
                    <a href="responsaveis.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i>Responsáveis
                    </a>
                    <a href="acesso_empresa.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-building me-2"></i>Acesso Empresa
                    </a>
                    <a href="../Processo/processos.php?id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-folder-open me-2"></i>Processos
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="container mt-5">
                <h4>Editar Estabelecimento</h4>

                <?php if (isset($_GET['success'])) : ?>
                    <div class="alert alert-success" role="alert">
                        Estabelecimento atualizado com sucesso!
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])) : ?>
                    <div class="alert alert-danger" role="alert">
                        Erro ao atualizar estabelecimento: <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>

                <form id="editarEstabelecimentoForm" action="../../controllers/EstabelecimentoController.php?action=update&id=<?php echo $id; ?>" method="POST">
                    <div class="mb-3">
                        <label for="cnpj" class="form-label">CNPJ</label>
                        <input type="text" class="form-control" id="cnpj" name="cnpj" value="<?php echo htmlspecialchars($dadosEstabelecimento['cnpj']); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" id="atualizarInformacoes">Atualizar Informações do Estabelecimento</button>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nome_fantasia" class="form-label">Nome Fantasia</label>
                            <input type="text" class="form-control" id="nome_fantasia" name="nome_fantasia" value="<?php echo setDefaultValue($dadosEstabelecimento['nome_fantasia']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="razao_social" class="form-label">Razão Social</label>
                            <input type="text" class="form-control" id="razao_social" name="razao_social" value="<?php echo setDefaultValue($dadosEstabelecimento['razao_social']); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="descricao_situacao_cadastral" class="form-label">Descrição Situação Cadastral</label>
                            <input type="text" class="form-control" id="descricao_situacao_cadastral" name="descricao_situacao_cadastral" value="<?php echo setDefaultValue($dadosEstabelecimento['descricao_situacao_cadastral']); ?>" required readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="data_situacao_cadastral" class="form-label">Data Situação Cadastral</label>
                            <input type="date" class="form-control" id="data_situacao_cadastral" name="data_situacao_cadastral" value="<?php echo setDefaultValue($dadosEstabelecimento['data_situacao_cadastral'], '0000-00-00'); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="data_inicio_atividade" class="form-label">Data Início Atividade</label>
                            <input type="date" class="form-control" id="data_inicio_atividade" name="data_inicio_atividade" value="<?php echo setDefaultValue($dadosEstabelecimento['data_inicio_atividade']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="descricao_tipo_de_logradouro" class="form-label">Tipo de Logradouro</label>
                            <input type="text" class="form-control" id="descricao_tipo_de_logradouro" name="descricao_tipo_de_logradouro" value="<?php echo setDefaultValue($dadosEstabelecimento['descricao_tipo_de_logradouro']); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="logradouro" class="form-label">Logradouro</label>
                            <input type="text" class="form-control" id="logradouro" name="logradouro" value="<?php echo setDefaultValue($dadosEstabelecimento['logradouro']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="numero" class="form-label">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero" value="<?php echo setDefaultValue($dadosEstabelecimento['numero']); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="complemento" class="form-label">Complemento</label>
                            <input type="text" class="form-control" id="complemento" name="complemento" value="<?php echo setDefaultValue($dadosEstabelecimento['complemento']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="bairro" class="form-label">Bairro</label>
                            <input type="text" class="form-control" id="bairro" name="bairro" value="<?php echo setDefaultValue($dadosEstabelecimento['bairro']); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep" value="<?php echo setDefaultValue($dadosEstabelecimento['cep']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="uf" class="form-label">UF</label>
                            <input type="text" class="form-control" id="uf" name="uf" value="<?php echo setDefaultValue($dadosEstabelecimento['uf']); ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="municipio" class="form-label">Município</label>
                            <input type="text" class="form-control" id="municipio" name="municipio" value="<?php echo setDefaultValue($dadosEstabelecimento['municipio']); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="natureza_juridica" class="form-label">Natureza Jurídica</label>
                            <input type="text" class="form-control" id="natureza_juridica" name="natureza_juridica" value="<?php echo setDefaultValue($dadosEstabelecimento['natureza_juridica']); ?>">
                        </div>
                    </div>


                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ddd_telefone_2" class="form-label">DDD Telefone 2</label>
                            <input type="text" class="form-control" id="ddd_telefone_2" name="ddd_telefone_2" value="<?php echo setDefaultValue($dadosEstabelecimento['ddd_telefone_2']); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="ddd_telefone_1" class="form-label">DDD Telefone 1</label>
                            <input type="text" class="form-control" id="ddd_telefone_1" name="ddd_telefone_1" value="<?php echo setDefaultValue($dadosEstabelecimento['ddd_telefone_1']); ?>">
                        </div>
                    </div>



                    <h4>CNAE Principal</h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="cnae_fiscal" class="form-label">Código CNAE Fiscal</label>
                            <input type="text" class="form-control" id="cnae_fiscal" name="cnae_fiscal" value="<?php echo formatCNAE(htmlspecialchars($dadosEstabelecimento['cnae_fiscal'])); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="cnae_fiscal_descricao" class="form-label">Descrição CNAE Fiscal</label>
                            <input type="text" class="form-control" id="cnae_fiscal_descricao" name="cnae_fiscal_descricao" value="<?php echo htmlspecialchars($dadosEstabelecimento['cnae_fiscal_descricao']); ?>" readonly>
                        </div>
                    </div>
                    <h4>CNAEs Secundários</h4>
                    <?php foreach ($cnaes_secundarios as $cnae) : ?>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Código</label>
                                <input type="text" class="form-control" value="<?php echo formatCNAE(htmlspecialchars($cnae['codigo'])); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Descrição</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($cnae['descricao']); ?>" readonly>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <h4>Sociedade</h4>
                    <?php foreach ($qsa as $socio) : ?>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome do Sócio</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($socio['nome_socio']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Qualificação</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($socio['qualificacao_socio']); ?>" readonly>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Adicione a biblioteca jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Adicione o Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $('#atualizarInformacoes').on('click', function() {
            var cnpj = $('#cnpj').val().replace(/\D/g, '');
            if (cnpj.length !== 14) {
                alert('Por favor, insira um CNPJ válido.');
                return;
            }

            $.ajax({
                url: 'https://minhareceita.org/' + cnpj,
                method: 'GET',
                success: function(data) {
                    if (data.error) {
                        alert('Erro ao consultar o CNPJ: ' + data.error);
                        return;
                    }

                    // Atualizar os campos com os dados recebidos da API
                    $('#nome_fantasia').val(data.nome_fantasia || 'Não Informado');
                    $('#razao_social').val(data.razao_social || 'Não Informado');
                    $('#descricao_situacao_cadastral').val(data.descricao_situacao_cadastral || 'Não Informado');
                    $('#data_situacao_cadastral').val(data.data_situacao_cadastral || '0000-00-00');
                    $('#data_inicio_atividade').val(data.data_inicio_atividade || '0000-00-00');
                    $('#descricao_tipo_de_logradouro').val(data.descricao_tipo_de_logradouro || 'Não Informado');
                    $('#logradouro').val(data.logradouro || 'Não Informado');
                    $('#numero').val(data.numero || 'Não Informado');
                    $('#complemento').val(data.complemento || 'Não Informado');
                    $('#bairro').val(data.bairro || 'Não Informado');
                    $('#cep').val(data.cep || 'Não Informado');
                    $('#uf').val(data.uf || 'Não Informado');
                    $('#municipio').val(data.municipio || 'Não Informado');
                    $('#ddd_telefone_1').val(data.ddd_telefone_1 || 'Não Informado');
                    $('#ddd_telefone_2').val(data.ddd_telefone_2 || 'Não Informado');
                    $('#natureza_juridica').val(data.natureza_juridica || 'Não Informado');

                    $('#cnae_fiscal').val(data.cnae_fiscal || '0000-0/00');
                    $('#cnae_fiscal_descricao').val(data.cnae_fiscal_descricao || 'Não Informado');

                    // Atualizar os CNAEs Secundários
                    var cnaesHTML = data.cnaes_secundarios.map(function(cnae) {
                        return `
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Código</label>
                                    <input type="text" class="form-control" value="${cnae.codigo ? formatCNAE(cnae.codigo) : '0000-0/00'}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Descrição</label>
                                    <input type="text" class="form-control" value="${cnae.descricao || 'Não Informado'}" readonly>
                                </div>
                            </div>
                        `;
                    }).join('');

                    $('h4:contains("CNAEs Secundários")').next().remove(); // Remove os CNAEs Secundários antigos
                    $('h4:contains("CNAEs Secundários")').after(cnaesHTML);

                    // Atualizar os Responsáveis
                    var qsaHTML = data.qsa.map(function(socio) {
                        return `
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nome do Sócio</label>
                                    <input type="text" class="form-control" value="${socio.nome_socio || 'Não Informado'}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Qualificação</label>
                                    <input type="text" class="form-control" value="${socio.qualificacao_socio || 'Não Informado'}" readonly>
                                </div>
                            </div>
                        `;
                    }).join('');

                    $('h4:contains("Responsáveis")').next().remove(); // Remove os Responsáveis antigos
                    $('h4:contains("Responsáveis")').after(qsaHTML);
                },
                error: function() {
                    alert('Erro ao consultar o CNPJ.');
                }
            });
        });
    });
</script>

<?php
$conn->close();
include '../footer.php';
?>