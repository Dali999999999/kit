<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../src/AuthGenerator.php';
use AIO\Src\AuthGenerator;

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $data['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    $host = $data['host'] ?? 'localhost';
    $user = $data['user'] ?? 'root';
    $pass = $data['pass'] ?? '';
    $dbname = $data['dbname'] ?? '';

    try {
        if ($action === 'connect') {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode(['success' => true, 'tables' => $tables]);
            exit;
        }

        if ($action === 'fetch_fields') {
            $table = $data['table'] ?? '';
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->query("DESCRIBE `$table`");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode(['success' => true, 'fields' => $columns]);
            exit;
        }

        if ($action === 'generate') {
            $table = $data['table'] ?? '';
            $idCol = $data['id_col'] ?? '';
            $passCol = $data['pass_col'] ?? '';
            $nameCol = $data['name_col'] ?? '';
            $prenomCol = $data['prenom_col'] ?? '';

            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
            
            $stmt = $pdo->query("DESCRIBE `$table`");
            $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pk = 'id';
            foreach ($cols as $c) {
                if ($c['Key'] === 'PRI') { $pk = $c['Field']; break; }
            }

            $codes = AuthGenerator::generateAuthFiles($table, $idCol, $passCol, $nameCol, $prenomCol, $pk);

            echo json_encode(array_merge(['success' => true], $codes));
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}
