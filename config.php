<?php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'financeiro');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function getConnection(): mysqli {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (mysqli_sql_exception $e) {
        error_log('Erro de conexão com banco de dados: ' . $e->getMessage());
        throw new Exception('Falha ao conectar ao banco de dados');
    }
}

function closeConnection(mysqli $conn): void {
    if ($conn) {
        $conn->close();
    }
}
