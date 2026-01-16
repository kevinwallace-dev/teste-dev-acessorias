<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Lançamentos Financeiros</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container py-4">
        <div class="align-items-center mb-3">
            <h1 class="h3 m-0">Lançamentos Financeiros</h1>
        </div>

        <div id="alertBalance" class="alert alert-danger d-none">
            <strong>Saldo negativo.</strong> Inclusão de novos débitos está bloqueada, você pode lançar apenas créditos.
        </div>
        <div id="alertGlobal" class="alert d-none"></div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Total de créditos</div>
                        <div class="fs-4 fw-semibold">R$ <span id="totalCreditos">0,00</span></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Total de débitos</div>
                        <div class="fs-4 fw-semibold">R$ <span id="totalDebitos">0,00</span></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Saldo final</div>
                        <div class="fs-4 fw-semibold">R$ <span id="saldoFinal">0,00</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">Novo lançamento</div>
            <div class="card-body">
                <form id="formLancamento" class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <input type="text" name="descricao" class="form-control" maxlength="255" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Valor</label>
                        <input type="text" name="valor" class="form-control" placeholder="Ex.: 123,45" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Selecionar tipo</option>
                            <option value="credito">Crédito</option>
                            <option value="debito">Débito</option>
                        </select>
                        <div class="form-text" id="tipoHint" style="display:none;">Com saldo negativo, o sistema aceita
                            apenas créditos.</div>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Data do lançamento</label>
                        <input type="date" name="data_lancamento" class="form-control" max="<?= date('Y-m-d') ?>"
                            required>
                    </div>
                    <div class="col-12 col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100" id="btnSalvar">Salvar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Listagem</div>
            <div class="card-body">
                <form id="filterForm" class="row g-2 align-items-end mb-3">
                    <div class="col-12 col-md-3">
                        <label class="form-label">Busca</label>
                        <input type="text" name="q" class="form-control" placeholder="Descrição">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select">
                            <option value="">Todos</option>
                            <option value="credito">Crédito</option>
                            <option value="debito">Débito</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">De</label>
                        <input type="date" name="data_ini" class="form-control">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Até</label>
                        <input type="date" name="data_fim" class="form-control">
                    </div>
                    <div class="col-6 col-md-1">
                        <label class="form-label">Por página</label>
                        <select id="perPage" class="form-select" name="per_page">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary flex-fill">Filtrar</button>
                        <button type="button" class="btn btn-outline-secondary" id="btnClearFilters">Limpar</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Descrição</th>
                                <th>Tipo</th>
                                <th class="text-end">Valor</th>
                                <th>Data</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLancamentos">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Carregando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-3">
                    <div class="text-muted" id="pageInfo">&nbsp;</div>
                    <div class="btn-group" role="group" aria-label="Paginação">
                        <button class="btn btn-outline-secondary" id="btnPrevPage" type="button">Anterior</button>
                        <button class="btn btn-outline-secondary" id="btnNextPage" type="button">Próxima</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <p class="mb-3">Confirmar exclusão?</p>
                    <button class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-danger" id="confirmDelete">Excluir</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
    <script src="js/app.js"></script>
</body>

</html>