<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

$conn = null;

try {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'ID inválido']);
        exit;
    }

    $conn = getConnection();

    $stmt = $conn->prepare("DELETE FROM lancamentos WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'Lançamento não encontrado']);
        exit;
    }

    echo json_encode(['ok' => true, 'message' => 'Lançamento excluído com sucesso']);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Erro interno ao excluir']);
} finally {
    if ($conn) closeConnection($conn);
}
