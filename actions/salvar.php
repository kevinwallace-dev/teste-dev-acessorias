<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

$conn = null;

try {
    $descricao = trim($_POST['descricao'] ?? '');
    $valorRaw  = trim($_POST['valor'] ?? '');
    $tipo      = trim($_POST['tipo'] ?? '');
    $data      = trim($_POST['data_lancamento'] ?? '');

    if ($descricao === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Descrição é obrigatória']);
        exit;
    }
    if (mb_strlen($descricao) > 255) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Descrição não pode exceder 255 caracteres']);
        exit;
    }

    if ($valorRaw === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Valor é obrigatório']);
        exit;
    }


    $valorNorm = str_replace(['.', ','], ['', '.'], $valorRaw); 
    if (!is_numeric($valorNorm) || bccomp($valorNorm, '0', 2) <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Valor deve ser um número positivo']);
        exit;
    }
    $valor = number_format($valorNorm, 2, '.', ''); 

    if (!in_array($tipo, ['credito', 'debito'], true)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Tipo inválido']);
        exit;
    }

    $dt = DateTime::createFromFormat('Y-m-d', $data);
    $isValidDate = $dt && $dt->format('Y-m-d') === $data;
    if (!$isValidDate) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Data inválida']);
        exit;
    }

    $conn = getConnection();

    $saldoResult = $conn->query("
        SELECT 
            COALESCE(SUM(CASE WHEN tipo = 'credito' THEN valor ELSE 0 END), 0) as total_creditos,
            COALESCE(SUM(CASE WHEN tipo = 'debito' THEN valor ELSE 0 END), 0) as total_debitos
        FROM lancamentos
    ");
    if (!$saldoResult) {
        throw new Exception('Erro ao calcular saldo');
    }

    $saldoRow = $saldoResult->fetch_assoc();
    $saldoAtual = bcsub($saldoRow['total_creditos'], $saldoRow['total_debitos'], 2);

    if ($tipo === 'debito' && bccomp($saldoAtual, '0', 2) < 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Saldo negativo. É permitido apenas lançar créditos.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO lancamentos (descricao, valor, tipo, data_lancamento) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $descricao, $valor, $tipo, $data);

    $stmt->execute();

    echo json_encode(['ok' => true, 'message' => 'Lançamento salvo com sucesso']);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Erro interno ao salvar']);
} finally {
    if ($conn) closeConnection($conn);
}