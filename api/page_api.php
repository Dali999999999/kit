<?php
session_start();
header('Content-Type: application/json');

require_once '../src/PageGenerator.php';
use AIO\Src\PageGenerator;

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

    if ($action === 'fetch_fields_config') {
        $table = $data['table'] ?? '';
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->query("DESCRIBE `$table`");
        $columnsInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $fkQuery = "
            SELECT COLUMN_NAME, REFERENCED_TABLE_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = :dbname AND TABLE_NAME = :tablename AND REFERENCED_TABLE_NAME IS NOT NULL
        ";
        $fkStmt = $pdo->prepare($fkQuery);
        $fkStmt->execute(['dbname' => $dbname, 'tablename' => $table]);
        $foreignKeysRaw = $fkStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $foreignKeys = [];
        foreach ($foreignKeysRaw as $fk) {
            $foreignKeys[$fk['COLUMN_NAME']] = ['table' => $fk['REFERENCED_TABLE_NAME']];
        }

        $fields = [];
        foreach ($columnsInfo as $col) {
            $name = $col['Field'];
            $isFk = isset($foreignKeys[$name]);
            $fkTargetColumns = [];
            
            if ($isFk) {
                $fkTable = $foreignKeys[$name]['table'];
                $fkStmt = $pdo->query("DESCRIBE `$fkTable`");
                $fkTargetColumns = $fkStmt->fetchAll(PDO::FETCH_COLUMN);
            }

            $typeRaw = $col['Type'] ?? '';
            $fields[] = [
                'name' => $name,
                'type' => $typeRaw,
                'is_fk' => $isFk,
                'fk_target' => $isFk ? $foreignKeys[$name]['table'] : null,
                'fk_columns' => $fkTargetColumns
            ];
        }
        
        echo json_encode(['success' => true, 'fields' => $fields]);
        exit;
    }

    if ($action === 'generate') {
        $table = $data['table'] ?? '';
        $fieldsConfig = $data['fields_config'] ?? [];
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

        $commentQuery = "SELECT COLUMN_NAME, COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :dbname AND TABLE_NAME = :tablename";
        $commentStmt = $pdo->prepare($commentQuery);
        $commentStmt->execute(['dbname' => $dbname, 'tablename' => $table]);
        $commentsRaw = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
        $colComments = [];
        foreach ($commentsRaw as $row) {
            $colComments[$row['COLUMN_NAME']] = $row['COLUMN_COMMENT'];
        }

        $primaryKey = null;
        $fields = [];
        
        foreach ($columnsInfo as $col) {
            $name = $col['Field'];
            $isAutoIncrement = strpos($col['Extra'], 'auto_increment') !== false;
            $type = $col['Type'];
            
            if ($col['Key'] === 'PRI') $primaryKey = $name;
            
            $isEnum = strpos($type, 'enum') === 0;
            $isSet = strpos($type, 'set') === 0;
            $enumValues = [];
            if ($isEnum || $isSet) {
                preg_match_all("/'([^']+)'/", $type, $matches);
                $enumValues = $matches[1] ?? [];
            }
            
            $cfg = $fieldsConfig[$name] ?? [];
            $fields[$name] = [
                'type' => $type,
                'is_pk' => $col['Key'] === 'PRI',
                'is_ai' => $isAutoIncrement,
                'is_fk' => isset($foreignKeys[$name]),
                'fk_target' => isset($foreignKeys[$name]) ? $foreignKeys[$name]['table'] : null,
                'fk_col' => isset($foreignKeys[$name]) ? $foreignKeys[$name]['column'] : null,
                'is_file' => (isset($colComments[$name]) && $colComments[$name] === 'is_file'),
                'is_enum' => $isEnum,
                'is_set' => $isSet,
                'enum_values' => $enumValues,
                'input_style' => !empty($cfg['input_style']) ? $cfg['input_style'] : 'select',
                'is_search' => !empty($cfg['is_search']),
                'is_filter' => !empty($cfg['is_filter']),
                'vis_list' => !isset($cfg['vis_list']) || $cfg['vis_list'] === true,
                'vis_create' => !isset($cfg['vis_create']) || $cfg['vis_create'] === true,
                'vis_edit' => !isset($cfg['vis_edit']) || $cfg['vis_edit'] === true,
                'sort_prio' => !empty($cfg['sort_prio']) ? (int)$cfg['sort_prio'] : 999,
                'sort_dir'  => !empty($cfg['sort_dir']) ? $cfg['sort_dir'] : '',
                'depends_on' => !empty($cfg['depends_on']) ? $cfg['depends_on'] : '',
                'depends_col' => !empty($cfg['depends_col']) ? $cfg['depends_col'] : '',
                'label' => !empty($cfg['label']) ? $cfg['label'] : ucfirst(str_replace('_', ' ', $name)),
                'fk_display' => !empty($cfg['fk_display']) ? $cfg['fk_display'] : (isset($foreignKeys[$name]) ? $foreignKeys[$name]['column'] : null)
            ];
        }

        if (!$primaryKey) {
            echo json_encode(['success' => false, 'message' => "La table $table n'a pas de clé primaire. Génération annulée."]);
            exit;
        }

        $isProtected = $data['is_protected'] ?? false;
        $filterFk = $data['filter_fk'] ?? '';
        $adminMode = $data['admin_mode'] ?? false;
        $generateView = $data['generate_view'] ?? false;
        $generateSearch = $data['generate_search'] ?? false;
        $autoJoin = $data['auto_join'] ?? false;
        $conditionalRules = $data['conditional_rules'] ?? [];
        $styleConfig = $data['style_config'] ?? [];
        $formLayout = $data['form_layout'] ?? '1';
        $listLayout = $data['list_layout'] ?? 'table';
        $useDatatable = $data['use_datatable'] ?? true;
        $actionConfig = $data['action_config'] ?? [];
        $filenames = $data['filenames'] ?? [];

        $listCode = PageGenerator::generateListFile($table, $primaryKey, $fields, $foreignKeys, $isProtected, $filterFk, $styleConfig, $listLayout, $adminMode, $autoJoin, $generateView, $filenames, $useDatatable, $actionConfig);
        $createCode = PageGenerator::generateCreateFile($table, $fields, $foreignKeys, $isProtected, $filterFk, $formLayout, $styleConfig, $conditionalRules, $filenames);
        $editCode = PageGenerator::generateEditFile($table, $primaryKey, $fields, $foreignKeys, $isProtected, $filterFk, $formLayout, $styleConfig, $conditionalRules, $filenames);
        $deleteCode = PageGenerator::generateDeleteFile($table, $primaryKey, $fields, $isProtected, $filterFk, $adminMode, $filenames);
        $viewCode = $generateView ? PageGenerator::generateViewFile($table, $primaryKey, $fields, $foreignKeys, $isProtected, $filterFk, $styleConfig, $adminMode, $filenames) : '';
        $searchCode = $generateSearch ? PageGenerator::generateSearchFile($table, $primaryKey, $fields, $foreignKeys, $isProtected, $filterFk, $styleConfig, $adminMode, $autoJoin, $filenames) : '';
        $styleCode = PageGenerator::generateStyleFile($styleConfig);

        echo json_encode([
            'success' => true, 
            'list_code' => $listCode,
            'create_code' => $createCode,
            'edit_code' => $editCode,
            'delete_code' => $deleteCode,
            'view_code' => $viewCode,
            'search_code' => $searchCode,
            'style_code' => $styleCode,
            'message' => 'Génération réussie !'
        ]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    exit;
}
