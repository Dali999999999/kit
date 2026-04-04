<?php
namespace AIO\Src;

class DbGenerator {
    public static function buildSchemaAndSql($dbname, $tables) {
        $sqlFullCode = "CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\nUSE `$dbname`;\nSET FOREIGN_KEY_CHECKS=0;\n\n";
        $schemaConceptuel = "";
        
        $sqlQueriesToExecute = [];

        foreach ($tables as $table) {
            $tableName = $table['name'];
            $fields = $table['fields'];
            
            $sqlFields = [];
            $foreignKeys = [];
            
            $schemaPK = [];
            $schemaNormal = [];
            $schemaFK = [];

            foreach ($fields as $field) {
                $name = $field['name'];
                $type = !empty($field['type']) ? trim($field['type']) : 'VARCHAR';
                $length = isset($field['length']) ? trim($field['length']) : '';
                $isNn = $field['nn'] ?? $field['isNn'] ?? false; // Support old/new keys
                $isUq = $field['uq'] ?? $field['isUq'] ?? false;
                $isPk = $field['pk'] ?? $field['isPk'] ?? false;
                $isAi = $field['ai'] ?? $field['isAi'] ?? false;
                $isFi = $field['fi'] ?? $field['isFi'] ?? false;
                $isFk = $field['fk'] ?? $field['isFk'] ?? false;
                $fkTable = $field['fkTable'] ?? '';
                $fkField = $field['fkField'] ?? '';

                if (strtoupper($type) === 'VARCHAR' && empty($length)) {
                    $length = '255';
                }

                $fullType = strtoupper($type);
                
                // Types that do not take a length parameter, or where length causes errors
                $noLengthTypes = ['DATE', 'DATETIME', 'BOOLEAN', 'TEXT', 'TINYINT', 'LONGTEXT', 'MEDIUMTEXT'];
                if (in_array($fullType, $noLengthTypes) && is_numeric($length)) {
                    if ($fullType === 'DATETIME' && (int)$length <= 6) {
                        // DATETIME safely accepts 0-6 length
                    } else {
                        $length = '';
                    }
                }

                if (!empty($length)) {
                    $fullType .= "($length)";
                }

                $sqlLine = "`$name` $fullType";

                if ($isNn) $sqlLine .= " NOT NULL";
                if ($isUq) $sqlLine .= " UNIQUE";
                if ($isAi) $sqlLine .= " AUTO_INCREMENT";
                if ($isFi) $sqlLine .= " COMMENT 'is_file'";

                if ($isPk) {
                    $sqlLine .= " PRIMARY KEY";
                    $schemaPK[] = "*$name";
                } else if ($isFk && !empty($fkTable) && !empty($fkField)) {
                    $foreignKeys[] = "FOREIGN KEY (`$name`) REFERENCES `$fkTable`(`$fkField`) ON DELETE CASCADE";
                    $schemaFK[] = "#$name";
                } else {
                    $schemaNormal[] = $name;
                }

                $sqlFields[] = $sqlLine;
            }

            $sqlQuery = "CREATE TABLE IF NOT EXISTS `$tableName` (\n";
            $sqlQuery .= implode(",\n", $sqlFields);
            if (!empty($foreignKeys)) {
                $sqlQuery .= ",\n" . implode(",\n", $foreignKeys);
            }
            $sqlQuery .= "\n) ENGINE=InnoDB;";

            $sqlQueriesToExecute[] = $sqlQuery;
            $sqlFullCode .= $sqlQuery . "\n\n";

            $allSchemaFields = array_merge($schemaPK, $schemaNormal, $schemaFK);
            $schemaConceptuel .= ucfirst($tableName) . ": (" . implode(", ", $allSchemaFields) . ")\n";
        }

        $sqlFullCode .= "SET FOREIGN_KEY_CHECKS=1;";

        return [
            'sql_string' => $sqlFullCode,
            'schema_string' => $schemaConceptuel,
            'queries' => $sqlQueriesToExecute
        ];
    }
}
