(function () {
  const state = {
    page: 1,
    per_page: 10,
    tipo: '',
    data_ini: '',
    data_fim: '',
    q: '',
  };

  function showAlert(type, text) {
    const $a = $('#alertGlobal');
    $a.removeClass('d-none alert-success alert-danger alert-warning');
    $a.addClass('alert-' + type);
    $a.text(text);
  }

  function clearAlert() {
    $('#alertGlobal').addClass('d-none').text('');
  }

  function setSaldoUI(totais) {
    $('#totalCreditos').text(totais.total_creditos_formatado);
    $('#totalDebitos').text(totais.total_debitos_formatado);
    $('#saldoFinal').text(totais.saldo_formatado);

    if (totais.saldo_negativo) {
      $('#alertBalance').removeClass('d-none');
      $('#tipoHint').show();
    } else {
      $('#alertBalance').addClass('d-none');
      $('#tipoHint').hide();
    }
  }

  function setPaginationUI(meta) {
    const $info = $('#pageInfo');
    const $prev = $('#btnPrevPage');
    const $next = $('#btnNextPage');

    const showingFrom = meta.total === 0 ? 0 : (meta.page - 1) * meta.per_page + 1;
    const showingTo = meta.total === 0 ? 0 : Math.min(meta.total, showingFrom + meta.per_page - 1);

    $info.text(`Mostrando ${showingFrom}-${showingTo} de ${meta.total}`);
    $prev.prop('disabled', !meta.has_prev);
    $next.prop('disabled', !meta.has_next);
  }

  function renderTable(items) {
    const $tb = $('#tbodyLancamentos');
    $tb.empty();

    if (!items || items.length === 0) {
      $tb.append(
        '<tr><td colspan="5" class="text-center text-muted py-4">Nenhum lançamento cadastrado.</td></tr>'
      );
      return;
    }

    items.forEach(function (it) {
      const tipoLabel = it.tipo === 'credito' ? 'Crédito' : 'Débito';
      const row = `
        <tr>
          <td>${escapeHtml(it.descricao)}</td>
          <td>${tipoLabel}</td>
          <td class="text-end">R$ ${it.valor_formatado}</td>
          <td>${it.data_lancamento}</td>
          <td class="text-end">
            <button class="btn btn-sm btn-outline-danger" data-action="del" data-id="${it.id}">Excluir</button>
          </td>
        </tr>
      `;
      $tb.append(row);
    });
  }

  function escapeHtml(s) {
    return String(s)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function refreshAll(overrides = {}) {
    Object.assign(state, overrides);

    $.getJSON('actions/listar.php', state)
      .done(function (res) {
        if (!res.ok) {
          showAlert('danger', res.message || 'Falha ao carregar a listagem.');
          return;
        }
        // Atualiza estado com o que veio do servidor (caso tenha ajustado página)
        Object.assign(state, {
          page: res.meta.page,
          per_page: res.meta.per_page,
          tipo: state.tipo,
          data_ini: state.data_ini,
          data_fim: state.data_fim,
          q: state.q,
        });
        clearAlert();
        setSaldoUI(res.totais);
        setPaginationUI(res.meta);
        renderTable(res.lancamentos);
      })
      .fail(function () {
        showAlert('danger', 'Erro de comunicação ao listar.');
      });
  }

  $('#formLancamento').on('submit', function (e) {
    e.preventDefault();
    clearAlert();

    const $btn = $('#btnSalvar');
    $btn.prop('disabled', true);

    $.ajax({
      url: 'actions/salvar.php',
      method: 'POST',
      dataType: 'json',
      data: $(this).serialize(),
    })
      .done(function (res) {
        if (!res.ok) {
          showAlert('warning', res.message || 'Validação falhou.');
          refreshAll();
          return;
        }
        showAlert('success', res.message || 'Salvo.');
        $('#formLancamento')[0].reset();
        refreshAll({ page: 1 });
      })
      .fail(function (xhr) {
        const msg =
          xhr && xhr.responseJSON && xhr.responseJSON.message
            ? xhr.responseJSON.message
            : 'Erro ao salvar.';
        showAlert('danger', msg);
      })
      .always(function () {
        $btn.prop('disabled', false);
      });
  });

  $('#filterForm').on('submit', function (e) {
    e.preventDefault();
    clearAlert();

    const formData = $(this).serializeArray();
    const payload = { page: 1 };
    formData.forEach(function (f) {
      payload[f.name] = f.value;
    });
    payload.per_page = parseInt($('#perPage').val(), 10) || state.per_page;

    refreshAll(payload);
  });

  $('#btnClearFilters').on('click', function () {
    $('#filterForm')[0].reset();
    const perPageVal = parseInt($('#perPage').val(), 10) || state.per_page;
    refreshAll({ page: 1, tipo: '', data_ini: '', data_fim: '', q: '', per_page: perPageVal });
  });

  $('#perPage').on('change', function () {
    const val = parseInt($(this).val(), 10);
    if (!isNaN(val)) {
      refreshAll({ per_page: val, page: 1 });
    }
  });

  $('#btnPrevPage').on('click', function () {
    if ($(this).prop('disabled')) return;
    refreshAll({ page: state.page - 1 });
  });

  $('#btnNextPage').on('click', function () {
    if ($(this).prop('disabled')) return;
    refreshAll({ page: state.page + 1 });
  });

  let deleteId = null;

  $('#tbodyLancamentos').on('click', 'button[data-action="del"]', function () {
    deleteId = $(this).data('id');
    const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    modal.show();
  });

  $('#confirmDelete').on('click', function () {
    if (!deleteId) return;

    const modal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
    const $btn = $(this);
    $btn.prop('disabled', true);

    $.ajax({
      url: 'actions/excluir.php',
      method: 'POST',
      dataType: 'json',
      data: { id: deleteId },
    })
      .done(function (res) {
        if (!res.ok) {
          showAlert('warning', res.message || 'Não foi possível excluir.');
          modal.hide();
          return;
        }
        showAlert('success', res.message || 'Excluído.');
        refreshAll();
        modal.hide();
      })
      .fail(function () {
        showAlert('danger', 'Erro ao excluir.');
        modal.hide();
      })
      .always(function () {
        deleteId = null;
        $btn.prop('disabled', false);
      });
  });

  refreshAll();
})();
