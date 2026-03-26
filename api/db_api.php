<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../src/DbGenerator.php';
use AIO\Src\DbGenerator;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    $host = $data['host'] ?? 'localhost';
    $user = $data['user'] ?? 'root';
    $pass = $data['pass'] ?? '';
    $dbname = $data['dbname'] ?? '';

    try {
        if ($action === 'test_connection') {
            $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo json_encode(['success' => true, 'message' => 'Connexion réussie !']);
            exit;
        }

        if ($action === 'build_database') {
            $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbname`");
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

            $tables = $data['tables'] ?? [];
            $generated = DbGenerator::buildSchemaAndSql($dbname, $tables);

            foreach ($generated['queries'] as $q) {
                $pdo->exec($q);
            }

            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

            echo json_encode([
                'success' => true, 
                'message' => 'Base de données et tables créées avec succès !',
                'schema' => $generated['schema_string'],
                'sql' => $generated['sql_string']
            ]);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
        exit;
    }
}
