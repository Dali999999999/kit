<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../src/CrudGenerator.php';
use AIO\Src\CrudGenerator;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

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

            echo json_encode(['success' => true, 'tables' => $tables, 'message' => 'Connexion réussie !']);
            exit;
        }

        if ($action === 'generate') {
            $table = $data['table'] ?? '';
            if (empty($table)) {
                echo json_encode(['success' => false, 'message' => 'Nom de table manquant.']);
                exit;
            }

            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->query("DESCRIBE `$table`");
            $columnsInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $fkQuery = "
                SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = :dbname AND TABLE_NAME = :tablename AND REFERENCED_TABLE_NAME IS NOT NULL
            ";
            $fkStmt = $pdo->prepare($fkQuery);
            $fkStmt->execute(['dbname' => $dbname, 'tablename' => $table]);
            $foreignKeysRaw = $fkStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $foreignKeys = [];
            foreach ($foreignKeysRaw as $fk) {
                $foreignKeys[$fk['COLUMN_NAME']] = [
                    'table' => $fk['REFERENCED_TABLE_NAME'],
                    'column' => $fk['REFERENCED_COLUMN_NAME']
                ];
            }

            $primaryKey = null;
            $fields = [];
            $insertFields = [];
            
            foreach ($columnsInfo as $col) {
                $name = $col['Field'];
                $isAutoIncrement = strpos($col['Extra'], 'auto_increment') !== false;
                
                if ($col['Key'] === 'PRI') {
                    $primaryKey = $name;
                }
                
                $fields[] = $name;
                if (!$isAutoIncrement) {
                    $insertFields[] = $name;
                }
            }

            if (!$primaryKey) {
                echo json_encode(['success' => false, 'message' => "La table $table n'a pas de clé primaire. Génération annulée."]);
                exit;
            }

            $phpCode = CrudGenerator::generatePhpCrud($table, $primaryKey, $fields, $insertFields, $foreignKeys);

            echo json_encode(['success' => true, 'code' => $phpCode, 'message' => 'Génération réussie !']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
        exit;
    }
}
