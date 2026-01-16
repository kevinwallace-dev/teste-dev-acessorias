<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

function buildFilters(string $tipo, string $q, string $dataIni, string $dataFim): array {
    $where = [];
    $types = '';
    $params = [];

    if (in_array($tipo, ['credito', 'debito'], true)) {
        $where[] = 'tipo = ?';
        $types .= 's';
        $params[] = $tipo;
    }

    if ($dataIni && $dt = DateTime::createFromFormat('Y-m-d', $dataIni)) {
        if ($dt->format('Y-m-d') === $dataIni) {
            $where[] = 'data_lancamento >= ?';
            $types .= 's';
            $params[] = $dataIni;
        }
    }

    if ($dataFim && $dt = DateTime::createFromFormat('Y-m-d', $dataFim)) {
        if ($dt->format('Y-m-d') === $dataFim) {
            $where[] = 'data_lancamento <= ?';
            $types .= 's';
            $params[] = $dataFim;
        }
    }

    if ($q !== '') {
        $where[] = 'descricao LIKE ?';
        $types .= 's';
        $params[] = '%' . $q . '%';
    }

    $sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    return [$sql, $types, $params];
}

$conn = null;

try {
    $conn = getConnection();

    // Parâmetros de paginação
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = (int)($_GET['per_page'] ?? 10);
    $perPage = in_array($perPage, [5, 10, 20, 50], true) ? $perPage : 10;

    // Parâmetros de filtros
    $tipo = $_GET['tipo'] ?? '';
    $q = trim($_GET['q'] ?? '');
    $dataIni = $_GET['data_ini'] ?? '';
    $dataFim = $_GET['data_fim'] ?? '';

    [$filtersSql, $filterTypes, $filterParams] = buildFilters($tipo, $q, $dataIni, $dataFim);

    $totaisStmt = $conn->prepare("SELECT COALESCE(SUM(CASE WHEN tipo = 'credito' THEN valor ELSE 0 END), 0) AS total_creditos, COALESCE(SUM(CASE WHEN tipo = 'debito' THEN valor ELSE 0 END), 0) AS total_debitos FROM lancamentos $filtersSql");
    if ($filterTypes) {
        $totaisStmt->bind_param($filterTypes, ...$filterParams);
    }
    $totaisStmt->execute();
    $totaisResult = $totaisStmt->get_result();
    $totais = $totaisResult->fetch_assoc();
    $totalCreditos = $totais['total_creditos'];
    $totalDebitos  = $totais['total_debitos'];
    $saldo = bcsub($totalCreditos, $totalDebitos, 2);

    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM lancamentos $filtersSql");
    if ($filterTypes) {
        $countStmt->bind_param($filterTypes, ...$filterParams);
    }
    $countStmt->execute();
    $countRes = $countStmt->get_result();
    $countRow = $countRes->fetch_assoc();
    $totalRows = (int)$countRow['total'];

    $totalPages = max(1, (int)ceil($totalRows / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
    }

    $offset = ($page - 1) * $perPage;
    $listSql = "SELECT id, descricao, valor, tipo, data_lancamento FROM lancamentos $filtersSql ORDER BY data_lancamento DESC, id DESC LIMIT ? OFFSET ?";
    $listStmt = $conn->prepare($listSql);
    $typesList = $filterTypes . 'ii';
    $paramsList = array_merge($filterParams, [$perPage, $offset]);
    $listStmt->bind_param($typesList, ...$paramsList);
    $listStmt->execute();
    $result = $listStmt->get_result();

    $lancamentos = [];
    while ($row = $result->fetch_assoc()) {
        $lancamentos[] = [
            'id' => (int)$row['id'],
            'descricao' => $row['descricao'],
            'valor' => $row['valor'],
            'valor_formatado' => number_format($row['valor'], 2, ',', '.'),
            'tipo' => $row['tipo'],
            'data_lancamento' => date('d/m/Y', strtotime($row['data_lancamento']))
        ];
    }

    echo json_encode([
        'ok' => true,
        'lancamentos' => $lancamentos,
        'totais' => [
            'total_creditos' => $totalCreditos,
            'total_creditos_formatado' => number_format($totalCreditos, 2, ',', '.'),
            'total_debitos' => $totalDebitos,
            'total_debitos_formatado' => number_format($totalDebitos, 2, ',', '.'),
            'saldo' => $saldo,
            'saldo_formatado' => number_format($saldo, 2, ',', '.'),
            'saldo_negativo' => $saldo < 0
        ],
        'meta' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $totalRows,
            'total_pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Erro interno ao listar']);
    error_log($e->getMessage());
} finally {
    if ($conn) closeConnection($conn);
}
