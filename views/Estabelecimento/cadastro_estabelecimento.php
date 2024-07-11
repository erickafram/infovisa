<?php
session_start();
include '../header.php';

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['nivel_acesso'], [1, 2, 3, 4])) {
    header("Location: ../../login.php"); // Redirecionar para a página de login se não estiver autenticado ou não for administrador
    exit();
}
?>

<div class="container mt-5">
    <div class="alert alert-info" role="alert">
        <strong>Atenção!</strong> Basta inserir o CNPJ do estabelecimento e uma API fará a busca automática dos dados. Lembre-se de que você só pode cadastrar estabelecimentos do seu município.
    </div>

    <?php if (isset($_GET['success'])) : ?>
        <div class="alert alert-success" role="alert">
            Estabelecimento cadastrado com sucesso!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])) : ?>
        <div class="alert alert-danger" role="alert">
            Erro ao cadastrar estabelecimento: <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <form id="cadastroEstabelecimentoForm" action="../../controllers/EstabelecimentoController.php?action=register" method="POST">
        <div class="mb-3">
            <label for="cnpj" class="form-label">CNPJ</label>
            <input type="text" class="form-control" id="cnpj" name="cnpj" placeholder="Digite o CNPJ do estabelecimento" required>
        </div>
        <div class="mb-3">
            <button type="button" class="btn btn-primary" id="consultarCNPJ">Consultar</button>
        </div>

        <!-- Campos para os dados retornados da consulta à API -->
        <div id="dadosEstabelecimento">
            <!-- Os campos para exibir e editar os dados do estabelecimento serão inseridos aqui pelo JavaScript -->
        </div>
    </form>
</div>

<!-- Adicione a biblioteca jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Adicione o Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<!-- Adicione a biblioteca de máscara -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function() {
        $('#cnpj').mask('00.000.000/0000-00');

        function setDefaultValue(value, defaultValue = "Não Informado") {
            return value ? value : defaultValue;
        }

        $('#consultarCNPJ').on('click', function() {
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

                    // Preencher os campos com os dados recebidos da API
                    var qsaHTML = data.qsa.map(function(socio) {
                        return `
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nome do Sócio</label>
                                    <input type="text" class="form-control" value="${setDefaultValue(socio.nome_socio)}" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Qualificação</label>
                                    <input type="text" class="form-control" value="${setDefaultValue(socio.qualificacao_socio)}" disabled>
                                </div>
                            </div>
                        `;
                    }).join('');

                    var cnaePrincipalHTML = `
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cnae_fiscal" class="form-label">Código CNAE Fiscal</label>
                                <input type="text" class="form-control cnae-mask" id="cnae_fiscal" name="cnae_fiscal" value="${setDefaultValue(data.cnae_fiscal)}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="cnae_fiscal_descricao" class="form-label">Descrição CNAE Fiscal</label>
                                <input type="text" class="form-control" id="cnae_fiscal_descricao" name="cnae_fiscal_descricao" value="${setDefaultValue(data.cnae_fiscal_descricao)}" readonly>
                            </div>
                        </div>
                    `;

                    var cnaesHTML = data.cnaes_secundarios.map(function(cnae) {
                        return `
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Código</label>
                                    <input type="text" class="form-control cnae-mask" value="${setDefaultValue(cnae.codigo)}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Descrição</label>
                                    <input type="text" class="form-control" value="${setDefaultValue(cnae.descricao)}" readonly>
                                </div>
                            </div>
                        `;
                    }).join('');

                    var dadosHTML = `
                        <ul class="nav nav-tabs" id="estabelecimentoTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="dados-tab" data-bs-toggle="tab" data-bs-target="#dados" type="button" role="tab" aria-controls="dados" aria-selected="true">Dados da Empresa</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="cnae-principal-tab" data-bs-toggle="tab" data-bs-target="#cnae-principal" type="button" role="tab" aria-controls="cnae-principal" aria-selected="false">CNAE Principal</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="cnaes-secundarios-tab" data-bs-toggle="tab" data-bs-target="#cnaes-secundarios" type="button" role="tab" aria-controls="cnaes-secundarios" aria-selected="false">CNAEs Secundários</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="qsa-tab" data-bs-toggle="tab" data-bs-target="#qsa" type="button" role="tab" aria-controls="qsa" aria-selected="false">Responsáveis</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="estabelecimentoTabsContent">
                            <div class="tab-pane fade show active" id="dados" role="tabpanel" aria-labelledby="dados-tab" style="font-size:14px">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="descricao_identificador_matriz_filial" class="form-label">Descrição Identificador Matriz/Filial</label>
                                        <input type="text" class="form-control" id="descricao_identificador_matriz_filial" name="descricao_identificador_matriz_filial" value="${setDefaultValue(data.descricao_identificador_matriz_filial)}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="nome_fantasia" class="form-label">Nome Fantasia</label>
                                        <input type="text" class="form-control" id="nome_fantasia" name="nome_fantasia" value="${setDefaultValue(data.nome_fantasia)}" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="descricao_situacao_cadastral" class="form-label">Descrição Situação Cadastral</label>
                                        <input type="text" class="form-control" id="descricao_situacao_cadastral" name="descricao_situacao_cadastral" value="${setDefaultValue(data.descricao_situacao_cadastral)}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="data_situacao_cadastral" class="form-label">Data Situação Cadastral</label>
                                        <input type="date" class="form-control" id="data_situacao_cadastral" name="data_situacao_cadastral" value="${setDefaultValue(data.data_situacao_cadastral, '0000-00-00')}" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="data_inicio_atividade" class="form-label">Data Início Atividade</label>
                                        <input type="date" class="form-control" id="data_inicio_atividade" name="data_inicio_atividade" value="${setDefaultValue(data.data_inicio_atividade)}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="descricao_tipo_de_logradouro" class="form-label">Tipo de Logradouro</label>
                                        <input type="text" class="form-control" id="descricao_tipo_de_logradouro" name="descricao_tipo_de_logradouro" value="${setDefaultValue(data.descricao_tipo_de_logradouro)}" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="logradouro" class="form-label">Logradouro</label>
                                        <input type="text" class="form-control" id="logradouro" name="logradouro" value="${setDefaultValue(data.logradouro)}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="numero" class="form-label">Número</label>
                                        <input type="text" class="form-control" id="numero" name="numero" value="${setDefaultValue(data.numero)}" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="complemento" class="form-label">Complemento</label>
                                        <input type="text" class="form-control" id="complemento" name="complemento" value="${setDefaultValue(data.complemento)}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="bairro" class="form-label">Bairro</label>
                                        <input type="text" class="form-control" id="bairro" name="bairro" value="${setDefaultValue(data.bairro)}" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="cep" class="form-label">CEP</label>
                                        <input type="text" class="form-control" id="cep" name="cep" value="${setDefaultValue(data.cep)}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="uf" class="form-label">UF</label>
                                        <input type="text" class="form-control" id="uf" name="uf" value="${setDefaultValue(data.uf)}" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="municipio" class="form-label">Município</label>
                                        <input type="text" class="form-control" id="municipio" name="municipio" value="${setDefaultValue(data.municipio)}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="ddd_telefone_1" class="form-label">DDD Telefone 1</label>
                                        <input type="text" class="form-control" id="ddd_telefone_1" name="ddd_telefone_1" value="${setDefaultValue(data.ddd_telefone_1)}">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="ddd_telefone_2" class="form-label">DDD Telefone 2</label>
                                        <input type="text" class="form-control" id="ddd_telefone_2" name="ddd_telefone_2" value="${setDefaultValue(data.ddd_telefone_2)}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="razao_social" class="form-label">Razão Social</label>
                                        <input type="text" class="form-control" id="razao_social" name="razao_social" value="${setDefaultValue(data.razao_social)}" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="natureza_juridica" class="form-label">Natureza Jurídica</label>
                                        <input type="text" class="form-control" id="natureza_juridica" name="natureza_juridica" value="${setDefaultValue(data.natureza_juridica)}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="cnae-principal" role="tabpanel" aria-labelledby="cnae-principal-tab">
                                ${cnaePrincipalHTML}
                            </div>
                            <div class="tab-pane fade" id="cnaes-secundarios" role="tabpanel" aria-labelledby="cnaes-secundarios-tab">
                                ${cnaesHTML}
                            </div>
                            <div class="tab-pane fade" id="qsa" role="tabpanel" aria-labelledby="qsa-tab">
                                ${qsaHTML}
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="qsa" value='${JSON.stringify(data.qsa)}'>
                            <input type="hidden" name="cnaes_secundarios" value='${JSON.stringify(data.cnaes_secundarios)}'>
                            <input type="hidden" name="cnae_fiscal" value="${setDefaultValue(data.cnae_fiscal)}">
                            <input type="hidden" name="cnae_fiscal_descricao" value="${setDefaultValue(data.cnae_fiscal_descricao)}">
                            <button type="submit" class="btn btn-primary btn-lg" id="salvarEstabelecimento">Salvar</button>
                        </div>
                    `;

                    $('#dadosEstabelecimento').html(dadosHTML);

                    $('.cnae-mask').mask('0000-0/00');

                    $('#cadastroEstabelecimentoForm').off('submit').on('submit', function(e) {
                        e.preventDefault();

                        var cnpj = $('#cnpj').val().replace(/\D/g, '');

                        $.ajax({
                            url: '../../controllers/EstabelecimentoController.php?action=checkCnpj',
                            method: 'POST',
                            data: {
                                cnpj: cnpj
                            },
                            success: function(response) {
                                var result = JSON.parse(response);
                                if (result.exists) {
                                    alert('Já existe um cadastro com esse CNPJ, entre em contato com a Vigilância Sanitária Municipal.');
                                } else {
                                    $('#cadastroEstabelecimentoForm')[0].submit();
                                }
                            },
                            error: function() {
                                alert('Erro ao verificar o CNPJ.');
                            }
                        });
                    });
                },
                error: function() {
                    alert('Erro ao consultar o CNPJ.');
                }
            });
        });
    });
</script>


<?php include '../footer.php'; ?>