<?php
namespace AIO\Src;

class CrudGenerator {
    public static function generatePhpCrud($table, $primaryKey, $fields, $insertFields, $foreignKeys) {
        $code = "<?php\n\n";
        $code .= "// --- Fonctions CRUD pour la table `$table` ---\n\n";

        // 1. CREATE Function
        $argsArray = array_map(function($f) { return "\$$f"; }, $insertFields);
        $argsString = implode(', ', $argsArray);
        
        $colsArray = array_map(function($f) { return "`$f`"; }, $insertFields);
        $colsString = implode(', ', $colsArray);
        
        $placeholdersArray = array_map(function($f) { return ":$f"; }, $insertFields);
        $placeholdersString = implode(', ', $placeholdersArray);

        $code .= "/**\n * Create a new $table\n */\n";
        $code .= "function create_$table(\$pdo, $argsString) {\n";
        $code .= "    \$sql = \"INSERT INTO `$table` ($colsString) VALUES ($placeholdersString)\";\n";
        $code .= "    \$stmt = \$pdo->prepare(\$sql);\n";
        $code .= "    \$stmt->execute([\n";
        foreach ($insertFields as $f) {
            $code .= "        ':$f' => \$$f,\n";
        }
        $code .= "    ]);\n";
        $code .= "    return \$pdo->lastInsertId();\n";
        $code .= "}\n\n";

        // 2. READ Function (By ID)
        $code .= "/**\n * Get a $table by its primary key ($primaryKey)\n */\n";
        $code .= "function get_{$table}_by_id(\$pdo, \$$primaryKey) {\n";
        $code .= "    \$sql = \"SELECT * FROM `$table` WHERE `$primaryKey` = :$primaryKey\";\n";
        $code .= "    \$stmt = \$pdo->prepare(\$sql);\n";
        $code .= "    \$stmt->execute([':$primaryKey' => \$$primaryKey]);\n";
        $code .= "    return \$stmt->fetch(PDO::FETCH_ASSOC);\n";
        $code .= "}\n\n";

        // 2.5 READ Functions (By Foreign Keys)
        if (!empty($foreignKeys)) {
            foreach ($foreignKeys as $fkCol => $fkData) {
                $code .= "/**\n * Get all $table by foreign key $fkCol\n */\n";
                $code .= "function get_{$table}_by_{$fkCol}(\$pdo, \$$fkCol) {\n";
                $code .= "    \$sql = \"SELECT * FROM `$table` WHERE `$fkCol` = :$fkCol\";\n";
                $code .= "    \$stmt = \$pdo->prepare(\$sql);\n";
                $code .= "    \$stmt->execute([':$fkCol' => \$$fkCol]);\n";
                $code .= "    return \$stmt->fetchAll(PDO::FETCH_ASSOC);\n";
                $code .= "}\n\n";
            }
        }

        // 3. READ Function (All) with optional JOIN example for FK
        $code .= "/**\n * Get all $table\n */\n";
        $code .= "function get_all_{$table}(\$pdo) {\n";
        if (!empty($foreignKeys)) {
            $code .= "    // Un exemple avec JOIN est inclus en commentaire à cause des clés étrangères\n";
            $joinLines = [];
            $selectCols = ["t.*"];
            $i = 1;
            foreach ($foreignKeys as $fkCol => $fkData) {
                $refTable = $fkData['table'];
                $refCol = $fkData['column'];
                $alias = "j$i";
                $joinLines[] = "LEFT JOIN `$refTable` $alias ON t.`$fkCol` = $alias.`$refCol`";
                $selectCols[] = "$alias.*"; 
                $i++;
            }
            $selectStr = implode(', ', $selectCols);
            $joinStr = implode(' ', $joinLines);
            
            $code .= "    /*\n";
            $code .= "    \$sql = \"SELECT $selectStr FROM `$table` t $joinStr\";\n";
            $code .= "    \$stmt = \$pdo->query(\$sql);\n";
            $code .= "    return \$stmt->fetchAll(PDO::FETCH_ASSOC);\n";
            $code .= "    */\n\n";
        }
        $code .= "    \$sql = \"SELECT * FROM `$table`\";\n";
        $code .= "    \$stmt = \$pdo->query(\$sql);\n";
        $code .= "    return \$stmt->fetchAll(PDO::FETCH_ASSOC);\n";
        $code .= "}\n\n";

        // 4. UPDATE Function
        $updateFields = array_filter($fields, function($f) use ($primaryKey) { return $f !== $primaryKey; });
        
        $updateArgsArray = array_map(function($f) { return "\$$f"; }, $updateFields);
        array_unshift($updateArgsArray, "\$$primaryKey");
        $updateArgsString = implode(', ', $updateArgsArray);

        $setParts = array_map(function($f) { return "`$f` = :$f"; }, $updateFields);
        $setString = implode(', ', $setParts);

        $code .= "/**\n * Update a $table\n */\n";
        $code .= "function update_$table(\$pdo, \$$primaryKey, " . implode(', ', array_map(function($f) { return "\$$f"; }, $updateFields)) . ") {\n";
        $code .= "    \$sql = \"UPDATE `$table` SET $setString WHERE `$primaryKey` = :$primaryKey\";\n";
        $code .= "    \$stmt = \$pdo->prepare(\$sql);\n";
        $code .= "    \$stmt->execute([\n";
        $code .= "        ':$primaryKey' => \$$primaryKey,\n";
        foreach ($updateFields as $f) {
            $code .= "        ':$f' => \$$f,\n";
        }
        $code .= "    ]);\n";
        $code .= "    return \$stmt->rowCount();\n";
        $code .= "}\n\n";

        // 5. DELETE Function
        $code .= "/**\n * Delete a $table by its primary key ($primaryKey)\n */\n";
        $code .= "function delete_$table(\$pdo, \$$primaryKey) {\n";
        $code .= "    \$sql = \"DELETE FROM `$table` WHERE `$primaryKey` = :$primaryKey\";\n";
        $code .= "    \$stmt = \$pdo->prepare(\$sql);\n";
        $code .= "    \$stmt->execute([':$primaryKey' => \$$primaryKey]);\n";
        $code .= "    return \$stmt->rowCount();\n";
        $code .= "}\n\n";

        $code .= "?>";
        return $code;
    }
}
