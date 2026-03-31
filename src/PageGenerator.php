<?php
namespace AIO\Src;

class PageGenerator {

    public static function getHtmlInputType($mysqlType) {
        if (strpos($mysqlType, 'int') !== false || strpos($mysqlType, 'decimal') !== false || strpos($mysqlType, 'float') !== false) return 'number';
        if (strpos($mysqlType, 'date') !== false) return 'date';
        if (strpos($mysqlType, 'datetime') !== false) return 'datetime-local';
        return 'text';
    }

    public static function generateStyleFile($styleConfig) {
        if (empty($styleConfig)) return "";
        $c = "/* Feuille de style personnalisée générée par AIO */\n";
        $c .= "body {\n";
        if (!empty($styleConfig['bg_color'])) $c .= "    background-color: {$styleConfig['bg_color']};\n";
        if (!empty($styleConfig['font'])) $c .= "    font-family: {$styleConfig['font']}, sans-serif;\n";
        $c .= "}\n\n";

        $c .= ".btn-save {\n";
        if (!empty($styleConfig['save_bg'])) {
            $c .= "    background-color: {$styleConfig['save_bg']} !important;\n";
            $c .= "    border-color: {$styleConfig['save_bg']} !important;\n";
        }
        if (!empty($styleConfig['save_text'])) {
            $c .= "    color: {$styleConfig['save_text']} !important;\n";
        }
        $c .= "}\n\n";

        $c .= ".btn-cancel {\n";
        if (!empty($styleConfig['cancel_bg'])) {
            $c .= "    background-color: {$styleConfig['cancel_bg']} !important;\n";
            $c .= "    border-color: {$styleConfig['cancel_bg']} !important;\n";
        }
        if (!empty($styleConfig['cancel_text'])) {
            $c .= "    color: {$styleConfig['cancel_text']} !important;\n";
        }
        $c .= "}\n";

        return $c;
    }

    public static function generateListFile($table, $primaryKey, $fields, $foreignKeys, $isProtected = false, $filterFk = '', $styleConfig = [], $listLayout = 'table', $adminMode = false, $autoJoin = true, $hasView = false, $filenames = []) {
        $files = array_merge([
            'list' => "list_$table.php",
            'create' => "create_$table.php",
            'edit' => "edit_$table.php",
            'delete' => "delete_$table.php",
            'view' => "view_$table.php"
        ], $filenames);

        $c = "<?php\n";
        if ($isProtected) {
            $c .= "require_once 'protect.php';\n";
        }
        $c .= "require_once 'config.php';\n";
        $c .= "require_once 'fonction.php';\n\n";
        $c .= "\$pdo = connectbd();\n\n";
        
        if (!empty($foreignKeys) && !$autoJoin) {
            foreach ($foreignKeys as $col => $fk) {
                $fkTable = $fk['table'];
                $fkCol = $fk['column'];
                $c .= "\${$fkTable}s = get_all_{$fkTable}(\$pdo);\n";
                $c .= "\${$fkTable}_map = [];\n";
                $c .= "foreach (\${$fkTable}s as \$f) { \${$fkTable}_map[\$f['$fkCol']] = \$f; }\n\n";
            }
        } else if ($autoJoin) {
            foreach ($fields as $name => $info) {
                if ($info['is_filter'] && $info['is_fk']) {
                    $fkTable = $info['fk_target'];
                    $c .= "\${$fkTable}s = get_all_{$fkTable}(\$pdo);\n";
                }
            }
        }
        
        $hasSearch = false;
        $hasFilter = false;
        $hasSort = false;
        $searchCols = [];
        $filterCols = [];
        $sorts = [];
        
        foreach ($fields as $name => $info) {
            if ($info['is_search']) { $hasSearch = true; $searchCols[] = $name; }
            if ($info['is_filter']) { $hasFilter = true; $filterCols[] = $name; }
            if (!empty($info['sort_dir'])) { 
                $hasSort = true; 
                $sorts[] = [
                    'col' => $name,
                    'dir' => strtoupper($info['sort_dir']),
                    'prio' => (int)($info['sort_prio'] ?? 0)
                ];
            }
        }

        if ($hasSort) {
            usort($sorts, function($a, $b) { return $a['prio'] <=> $b['prio']; });
            $sortCols = [];
            foreach ($sorts as $s) {
                $colPrefix = $autoJoin ? "t." : "";
                $sortCols[] = "{$colPrefix}`{$s['col']}` {$s['dir']}";
            }
        }

        $c .= "    // --- MOTEUR DE RECHERCHE, FILTRAGE & TRI ---\n";
        if ($autoJoin) {
            $selectFields = ["t.*"];
            $joins = [];
            foreach ($fields as $name => $info) {
                if ($info['is_fk'] && !empty($info['fk_display'])) {
                    $fkTable = $info['fk_target'];
                    $fkCol = $info['fk_col'];
                    $fkDisp = $info['fk_display'];
                    $alias = "ref_" . $name;
                    $selectFields[] = "{$alias}.`{$fkDisp}` as `{$name}_label`";
                    $joins[] = "LEFT JOIN `{$fkTable}` {$alias} ON t.`{$name}` = {$alias}.`{$fkCol}`";
                }
            }
            $c .= "    \$sql = \"SELECT " . implode(', ', $selectFields) . " FROM `$table` t " . implode(' ', $joins) . " WHERE 1=1\";\n";
        } else {
            $c .= "    \$sql = \"SELECT * FROM `$table` WHERE 1=1\";\n";
        }
        
        $c .= "    \$params = [];\n\n";
        
        if ($filterFk && !$adminMode) {
            $colPrefix = $autoJoin ? "t." : "";
            $c .= "    \$sql .= \" AND {$colPrefix}`$filterFk` = :session_fk\";\n";
            $c .= "    \$params[':session_fk'] = \$_SESSION['user_id'] ?? 0;\n\n";
        }
        
        if ($hasSearch) {
            $c .= "    if (!empty(\$_GET['q'])) {\n";
            $searchConds = [];
            foreach ($searchCols as $sc) {
                $colPrefix = $autoJoin ? "t." : "";
                $searchConds[] = "{$colPrefix}`$sc` LIKE :q";
            }
            $c .= "        \$sql .= \" AND (" . implode(' OR ', $searchConds) . ")\";\n";
            $c .= "        \$params[':q'] = '%' . \$_GET['q'] . '%';\n";
            $c .= "    }\n\n";
        }
        
        if ($hasFilter) {
            foreach ($filterCols as $fc) {
                $colPrefix = $autoJoin ? "t." : "";
                $c .= "    if (isset(\$_GET['f_$fc']) && \$_GET['f_$fc'] !== '') {\n";
                $c .= "        \$sql .= \" AND {$colPrefix}`$fc` = :f_$fc\";\n";
                $c .= "        \$params[':f_$fc'] = \$_GET['f_$fc'];\n";
                $c .= "    }\n\n";
            }
        }
        
        if ($hasSort) {
            $c .= "    \$sql .= \" ORDER BY " . implode(', ', $sortCols) . "\";\n";
        }
        
        $c .= "    \$stmt = \$pdo->prepare(\$sql);\n";
        $c .= "    \$stmt->execute(\$params);\n";
        $c .= "    \$items = \$stmt->fetchAll(PDO::FETCH_ASSOC);\n";
        $c .= "?>\n\n";
        
        $c .= "<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n";
        $c .= "    <meta charset=\"UTF-8\">\n";
        $c .= "    <title>Liste des ".ucfirst($table)."s</title>\n";
        $c .= "    <link href=\"assets/css/bootstrap.min.css\" rel=\"stylesheet\">\n";
        $c .= "    <link href=\"assets/css/bootstrap-icons.css\" rel=\"stylesheet\">\n";
        $c .= "    <link href=\"style.css\" rel=\"stylesheet\">\n";
        $c .= "</head>\n<body class=\"bg-light\">\n";
        $c .= "<div class=\"container mt-5\">\n";
        $c .= "    <div class=\"d-flex justify-content-between align-items-center mb-4\">\n";
        $c .= "        <h2>Liste des ".ucfirst($table)."s</h2>\n";
        $btnClass = !empty($styleConfig) ? 'btn btn-save' : 'btn btn-success';
        $c .= "        <a href=\"{$files['create']}\" class=\"$btnClass\"><i class=\"bi bi-plus-circle\"></i> Ajouter</a>\n";
        $c .= "    </div>\n\n";

        if ($hasSearch || $hasFilter) {
            $c .= "    <div class=\"card mb-4 shadow-sm\">\n";
            $c .= "        <div class=\"card-body bg-light\">\n";
            $c .= "            <form method=\"GET\" class=\"row gx-2 gy-2 align-items-center\">\n";
            if ($hasSearch) {
                $c .= "                <div class=\"col-auto\">\n";
                $c .= "                    <div class=\"input-group\">\n";
                $c .= "                        <span class=\"input-group-text bg-white\"><i class=\"bi bi-search\"></i></span>\n";
                $c .= "                        <input type=\"text\" name=\"q\" class=\"form-control\" placeholder=\"Rechercher...\" value=\"<?= htmlspecialchars(\$_GET['q'] ?? '') ?>\">\n";
                $c .= "                    </div>\n";
                $c .= "                </div>\n";
            }
            if ($hasFilter) {
                foreach ($filterCols as $fc) {
                    $info = $fields[$fc];
                    $c .= "                <div class=\"col-auto\">\n";
                    $c .= "                    <select name=\"f_$fc\" class=\"form-select\">\n";
                    $c .= "                        <option value=\"\">-- ".htmlspecialchars($info['label'])." --</option>\n";
                    if ($info['is_fk']) {
                        $fkTable = $info['fk_target'];
                        $fkDisplay = $info['fk_display'];
                        $fkCol = isset($foreignKeys[$fc]) ? $foreignKeys[$fc]['column'] : null;
                        if ($fkCol) {
                            $c .= "                        <?php foreach (\${$fkTable}s as \$f_item): ?>\n";
                            $c .= "                        <option value=\"<?= \$f_item['$fkCol'] ?>\" <?= (isset(\$_GET['f_$fc']) && \$_GET['f_$fc'] == \$f_item['$fkCol']) ? 'selected' : '' ?>>\n";
                            $c .= "                            <?= htmlspecialchars(\$f_item['$fkDisplay']) ?>\n";
                            $c .= "                        </option>\n";
                            $c .= "                        <?php endforeach; ?>\n";
                        }
                    } elseif ($info['is_enum']) {
                        foreach ($info['enum_values'] as $val) {
                            $safeVal = addslashes($val);
                            $c .= "                        <option value=\"$safeVal\" <?= (isset(\$_GET['f_$fc']) && \$_GET['f_$fc'] === '$safeVal') ? 'selected' : '' ?>>" . htmlspecialchars($val) . "</option>\n";
                        }
                    } else {
                        $c .= "                        <?php\n";
                        $c .= "                        \$stmtD = \$pdo->query(\"SELECT DISTINCT `$fc` FROM `$table` WHERE `$fc` IS NOT NULL AND `$fc` != '' ORDER BY `$fc`\");\n";
                        $c .= "                        while (\$rowD = \$stmtD->fetch(PDO::FETCH_ASSOC)): ?>\n";
                        $c .= "                            <option value=\"<?= htmlspecialchars(\$rowD['$fc']) ?>\" <?= (isset(\$_GET['f_$fc']) && \$_GET['f_$fc'] == \$rowD['$fc']) ? 'selected' : '' ?>><?= htmlspecialchars(\$rowD['$fc']) ?></option>\n";
                        $c .= "                        <?php endwhile; ?>\n";
                    }
                    $c .= "                    </select>\n";
                    $c .= "                </div>\n";
                }
            }
            $btnFilterClass = !empty($styleConfig) ? 'btn btn-save' : 'btn btn-primary fw-bold';
            $btnResetClass = !empty($styleConfig) ? 'btn btn-cancel' : 'btn btn-outline-secondary';
            $c .= "                <div class=\"col-auto\">\n";
            $c .= "                    <button type=\"submit\" class=\"$btnFilterClass\">Filtrer</button>\n";
            $c .= "                    <a href=\"{$files['list']}\" class=\"$btnResetClass\">Reset</a>\n";
            $c .= "                </div>\n";
            $c .= "            </form>\n";
            $c .= "        </div>\n";
            $c .= "    </div>\n\n";
        }

        if ($listLayout === 'cards') {
            $c .= "    <div class=\"row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4\">\n";
            $c .= "        <?php foreach (\$items as \$item): ?>\n";
            $c .= "        <div class=\"col\">\n";
            $c .= "            <div class=\"card h-100 shadow-sm border-0\">\n";
            $fileCol = null;
            foreach($fields as $name => $info) { if($info['is_file']) { $fileCol = $name; break; } }
            if ($fileCol) {
                $c .= "                <?php if (\$item['$fileCol'] && in_array(strtolower(pathinfo(\$item['$fileCol'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>\n";
                $c .= "                    <img src=\"uploads/<?= htmlspecialchars(\$item['$fileCol']) ?>\" class=\"card-img-top\" style=\"height: 200px; object-fit: cover;\">\n";
                $c .= "                <?php else: ?>\n";
                $c .= "                    <div class=\"bg-secondary text-white d-flex align-items-center justify-content-center\" style=\"height: 200px;\"><i class=\"bi bi-image fs-1 opacity-25\"></i></div>\n";
                $c .= "                <?php endif; ?>\n";
            } else {
                $c .= "                <div class=\"bg-light text-center py-4 border-bottom\"><i class=\"bi bi-file-earmark-text fs-1 text-muted\"></i></div>\n";
            }
            $c .= "                <div class=\"card-body\">\n";
            $idx = 0;
            foreach ($fields as $name => $info) {
                if ($name === $fileCol) continue;
                $label = htmlspecialchars($info['label']);
                if ($idx === 0) $c .= "                    <h5 class=\"card-title text-primary fw-bold mb-3\"><?= htmlspecialchars(\$item['$name']) ?></h5>\n";
                else {
                    $c .= "                    <p class=\"card-text mb-1\"><small class=\"text-muted fw-bold\">$label :</small><br>\n";
                    if ($info['is_fk']) {
                        if ($autoJoin) $c .= "                        <?= htmlspecialchars(\$item['{$name}_label'] ?? \$item['$name']) ?>\n";
                        else {
                            $fkTable = $info['fk_target'];
                            $fkDisplay = $info['fk_display'];
                            $c .= "                        <?= htmlspecialchars(\${$fkTable}_map[\$item['$name']]['$fkDisplay'] ?? \$item['$name']) ?>\n";
                        }
                    } else $c .= "                        <?= htmlspecialchars(\$item['$name']) ?>\n";
                    $c .= "                    </p>\n";
                }
                $idx++;
            }
            $c .= "                </div>\n";
            $c .= "                <div class=\"card-footer bg-white border-0 d-flex justify-content-between pb-3\">\n";
            if ($hasView) $c .= "                    <a href=\"{$files['view']}?{$primaryKey}=<?= \$item['$primaryKey'] ?>\" class=\"btn btn-outline-info btn-sm\"><i class=\"bi bi-eye\"></i></a>\n";
            $c .= "                    <a href=\"{$files['edit']}?{$primaryKey}=<?= \$item['$primaryKey'] ?>\" class=\"btn btn-outline-primary btn-sm\"><i class=\"bi bi-pencil\"></i> Modifier</a>\n";
            $c .= "                    <a href=\"{$files['delete']}?{$primaryKey}=<?= \$item['$primaryKey'] ?>\" class=\"btn btn-outline-danger btn-sm\" onclick=\"return confirm('Êtes-vous sûr ?')\"><i class=\"bi bi-trash\"></i> Supprimer</a>\n";
            $c .= "                </div>\n";
            $c .= "            </div>\n";
            $c .= "        </div>\n";
            $c .= "        <?php endforeach; ?>\n";
            $c .= "    </div>\n";
            $c .= "    <?php if (empty(\$items)): ?>\n";
            $c .= "        <div class=\"alert alert-info text-center py-5\">Aucune donnée trouvée</div>\n";
            $c .= "    <?php endif; ?>\n";
        } else {
            $c .= "    <div class=\"card shadow-sm\">\n";
            $c .= "        <div class=\"card-body p-0 table-responsive\">\n";
            $c .= "            <table class=\"table table-hover table-bordered mb-0\">\n";
            $c .= "                <thead class=\"table-dark\">\n";
            $c .= "                    <tr>\n";
            foreach ($fields as $name => $info) $c .= "                        <th>" . htmlspecialchars($info['label']) . "</th>\n";
            $c .= "                        <th class=\"text-center\">Actions</th>\n";
            $c .= "                    </tr>\n";
            $c .= "                </thead>\n";
            $c .= "                <tbody>\n";
            $c .= "                    <?php foreach (\$items as \$item): ?>\n";
            $c .= "                    <tr>\n";
            foreach ($fields as $name => $info) {
                if ($info['is_file']) {
                    $c .= "                        <td class=\"text-center\">\n";
                    $c .= "                            <?php if (\$item['$name']): ?>\n";
                    $c .= "                                <?php \$ext = strtolower(pathinfo(\$item['$name'], PATHINFO_EXTENSION)); ?>\n";
                    $c .= "                                <?php if (in_array(\$ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>\n";
                    $c .= "                                    <img src=\"uploads/<?= htmlspecialchars(\$item['$name']) ?>\" width=\"50\" height=\"50\" style=\"object-fit:cover; border-radius:4px;\">\n";
                    $c .= "                                <?php else: ?>\n";
                    $c .= "                                    <a href=\"uploads/<?= htmlspecialchars(\$item['$name']) ?>\" target=\"_blank\" class=\"btn btn-sm btn-outline-secondary\"><i class=\"bi bi-file-earmark-text\"></i> Fichier</a>\n";
                    $c .= "                                <?php endif; ?>\n";
                    $c .= "                            <?php else: ?>\n";
                    $c .= "                                <span class=\"text-muted\">-</span>\n";
                    $c .= "                            <?php endif; ?>\n";
                    $c .= "                        </td>\n";
                } elseif ($info['is_fk']) {
                    if ($autoJoin) $c .= "                        <td><?= htmlspecialchars(\$item['{$name}_label'] ?? \$item['$name']) ?></td>\n";
                    else {
                        $fkTable = $info['fk_target'];
                        $fkDisplay = $info['fk_display'];
                        $c .= "                        <td><?= htmlspecialchars(\${$fkTable}_map[\$item['$name']]['$fkDisplay'] ?? \$item['$name']) ?></td>\n";
                    }
                } else $c .= "                        <td><?= htmlspecialchars(\$item['$name']) ?></td>\n";
            }
            $c .= "                        <td class=\"text-center\">\n";
            if ($hasView) $c .= "                            <a href=\"{$files['view']}?{$primaryKey}=<?= \$item['$primaryKey'] ?>\" class=\"btn btn-sm btn-info\"><i class=\"bi bi-eye text-white\"></i></a>\n";
            $c .= "                            <a href=\"{$files['edit']}?{$primaryKey}=<?= \$item['$primaryKey'] ?>\" class=\"btn btn-sm btn-primary\"><i class=\"bi bi-pencil\"></i></a>\n";
            $c .= "                            <a href=\"{$files['delete']}?{$primaryKey}=<?= \$item['$primaryKey'] ?>\" class=\"btn btn-sm btn-danger\" onclick=\"return confirm('Êtes-vous sûr ?')\"><i class=\"bi bi-trash\"></i></a>\n";
            $c .= "                        </td>\n";
            $c .= "                    </tr>\n";
            $c .= "                    <?php endforeach; ?>\n";
            $c .= "                    <?php if (empty(\$items)): ?>\n";
            $c .= "                    <tr><td colspan=\"".(count($fields)+1)."\" class=\"text-center text-muted py-4\">Aucune donnée trouvée</td></tr>\n";
            $c .= "                    <?php endif; ?>\n";
            $c .= "                </tbody>\n";
            $c .= "            </table>\n";
            $c .= "        </div>\n";
            $c .= "    </div>\n";
        }
        $c .= "</div>\n";
        $c .= "</body>\n</html>";
        return $c;
    }

    public static function generateCreateFile($table, $fields, $foreignKeys, $isProtected = false, $filterFk = '', $formLayout = '1', $styleConfig = [], $conditionalRules = [], $filenames = []) {
        $files = array_merge([
            'list' => "list_$table.php",
            'create' => "create_$table.php",
            'edit' => "edit_$table.php",
            'delete' => "delete_$table.php",
            'view' => "view_$table.php"
        ], $filenames);

        $c = "<?php\n";
        if ($isProtected) {
            $c .= "require_once 'protect.php';\n";
        }
        $c .= "require_once 'config.php';\n";
        $c .= "require_once 'fonction.php';\n\n";
        $c .= "\$pdo = connectbd();\n\n";
        
        if (!empty($foreignKeys)) {
            foreach ($foreignKeys as $col => $fk) {
                $fkTable = $fk['table'];
                $c .= "\${$fkTable}s = get_all_{$fkTable}(\$pdo);\n";
            }
            $c .= "\n";
        }

        $c .= "if (\$_SERVER['REQUEST_METHOD'] === 'POST') {\n";
        $insertArgs = [];
        foreach ($fields as $name => $info) {
            if ($info['is_ai']) continue;
            if (isset($info['vis_create']) && !$info['vis_create']) {
                 continue;
            }
            
            if ($info['is_file']) {
                $c .= "    \$$name = null;\n";
                $c .= "    if (isset(\$_FILES['$name']) && \$_FILES['$name']['error'] === UPLOAD_ERR_OK) {\n";
                $c .= "        if (!is_dir('uploads')) mkdir('uploads', 0777, true);\n";
                $c .= "        \$ext = pathinfo(\$_FILES['$name']['name'], PATHINFO_EXTENSION);\n";
                $c .= "        \$filename = uniqid('{$name}_') . '.' . \$ext;\n";
                $c .= "        if (move_uploaded_file(\$_FILES['$name']['tmp_name'], 'uploads/' . \$filename)) {\n";
                $c .= "            \$$name = \$filename;\n";
                $c .= "        }\n";
                $c .= "    }\n";
            } elseif ($name === $filterFk) {
                $c .= "    \$$name = \$_SESSION['user_id'] ?? null;\n";
            } else {
                $c .= "    \$$name = \$_POST['$name'] ?? null;\n";
            }
            $insertArgs[] = "\$$name";
        }
        
        $c .= "\n    create_$table(\$pdo, " . implode(', ', $insertArgs) . ");\n";
        $c .= "    header('Location: {$files['list']}');\n";
        $c .= "    exit;\n";
        $c .= "}\n";
        $c .= "?>\n\n";

        $c .= "<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n";
        $c .= "    <meta charset=\"UTF-8\">\n";
        $c .= "    <title>Ajouter un ".ucfirst($table)."</title>\n";
        $c .= "    <link href=\"assets/css/bootstrap.min.css\" rel=\"stylesheet\">\n";
        $c .= "    <link href=\"assets/css/bootstrap-icons.css\" rel=\"stylesheet\">\n";
        $c .= "    <link href=\"style.css\" rel=\"stylesheet\">\n";
        $c .= "</head>\n<body class=\"bg-light\">\n";

        $maxWidth = ($formLayout == '2') ? '900px' : '600px';
        $c .= "<div class=\"container mt-5\" style=\"max-width: {$maxWidth};\">\n";
        $c .= "    <div class=\"card shadow-sm\">\n";
        $c .= "        <div class=\"card-header bg-success text-white\">\n";
        $c .= "            <h4 class=\"mb-0\">Ajouter un ".ucfirst($table)."</h4>\n";
        $c .= "        </div>\n";
        $c .= "        <div class=\"card-body\">\n";
        $c .= "            <form method=\"POST\" enctype=\"multipart/form-data\">\n";

        if ($formLayout == '2') $c .= "                <div class=\"row\">\n";

        $dependentScripts = [];

        foreach ($fields as $name => $info) {
            if ($info['is_ai'] || $name === $filterFk) continue; 
            // Sauter les champs non visibles
            if (isset($info['vis_create']) && !$info['vis_create']) continue;

            if ($formLayout == '2') $c .= "                    <div class=\"col-md-6 mb-3 " . "field-container-{$name}\">\n";
            else $c .= "                <div class=\"mb-3 " . "field-container-{$name}\">\n";

            $c .= "                        <label class=\"form-label fw-bold\">" . htmlspecialchars($info['label']) . "</label>\n";
            
            if ($info['is_fk']) {
                $fkTable = $info['fk_target'];
                $fkCol = $info['fk_col'];
                $fkDisplay = $info['fk_display'];
                
                $dependsOn = $info['depends_on'] ?? '';
                $dependsCol = $info['depends_col'] ?? '';
                
                if (!empty($dependsOn) && !empty($dependsCol)) {
                    $dependentScripts[] = ['child' => $name, 'parent' => $dependsOn];
                    $c .= "                        <select name=\"$name\" id=\"sel_$name\" class=\"form-select dependent-select\" required>\n";
                    $c .= "                            <option value=\"\" data-parent=\"\">-- Attente " . htmlspecialchars($dependsOn) . " --</option>\n";
                    $c .= "                            <?php foreach (\${$fkTable}s as \$f_item): ?>\n";
                    $c .= "                            <option value=\"<?= htmlspecialchars(\$f_item['$fkCol']) ?>\" data-parent=\"<?= htmlspecialchars(\$f_item['$dependsCol']) ?>\" class=\"d-none\">\n";
                    $c .= "                                <?= htmlspecialchars(\$f_item['$fkDisplay']) ?>\n";
                    $c .= "                            </option>\n";
                    $c .= "                            <?php endforeach; ?>\n";
                    $c .= "                        </select>\n";
                } else {
                    $c .= "                        <select name=\"$name\" id=\"sel_$name\" class=\"form-select\" required>\n";
                    $c .= "                            <option value=\"\">-- Sélectionner --</option>\n";
                    $c .= "                            <?php foreach (\${$fkTable}s as \$f_item): ?>\n";
                    $c .= "                            <option value=\"<?= htmlspecialchars(\$f_item['$fkCol']) ?>\"><?= htmlspecialchars(\$f_item['$fkDisplay']) ?></option>\n";
                    $c .= "                            <?php endforeach; ?>\n";
                    $c .= "                        </select>\n";
                }
            } elseif ($info['is_enum']) {
                $c .= "                        <select name=\"$name\" class=\"form-select\" required>\n";
                $c .= "                            <option value=\"\">-- Sélectionner --</option>\n";
                foreach ($info['enum_values'] as $val) {
                    $safeVal = addslashes($val);
                    $c .= "                            <option value=\"$safeVal\">" . htmlspecialchars($val) . "</option>\n";
                }
                $c .= "                        </select>\n";
            } elseif ($info['is_file']) {
                $c .= "                        <input type=\"file\" id=\"file_$name\" name=\"$name\" class=\"form-control file-upload-input\" data-preview=\"preview_$name\" accept=\"image/*,.pdf,.doc,.docx,.xls,.xlsx\">\n";
                $c .= "                        <div id=\"preview_$name\" class=\"mt-2 d-none position-relative\" style=\"max-width: 200px;\"></div>\n";
            } else {
                $inputType = self::getHtmlInputType($info['type']);
                if (strpos($info['type'], 'text') !== false && strpos($info['type'], 'varchar') === false) {
                    $c .= "                        <textarea name=\"$name\" class=\"form-control\" rows=\"4\" required></textarea>\n";
                } else {
                    $stepAttr = ($inputType === 'number' && (strpos($info['type'], 'float') !== false || strpos($info['type'], 'double') !== false || strpos($info['type'], 'decimal') !== false)) ? ' step="any"' : '';
                    $c .= "                        <input type=\"$inputType\" name=\"$name\" class=\"form-control\"$stepAttr required>\n";
                }
            }
            if ($formLayout == '2') $c .= "                    </div>\n";
            else $c .= "                </div>\n";
        }

        if ($formLayout == '2') $c .= "                </div>\n";

        $btnBack = !empty($styleConfig) ? 'btn btn-cancel' : 'btn btn-secondary';
        $btnSave = !empty($styleConfig) ? 'btn btn-save' : 'btn btn-success';

        $c .= "                <div class=\"d-flex justify-content-between mt-4\">\n";
        $c .= "                    <a href=\"{$files['list']}\" class=\"$btnBack\">Retour</a>\n";
        $c .= "                    <button type=\"submit\" class=\"$btnSave\">Enregistrer</button>\n";
        $c .= "                </div>\n";
        $c .= "            </form>\n";
        $c .= "        </div>\n";
        $c .= "    </div>\n";
        $c .= "</div>\n\n";

        // Inject Dependent Scripts
        if (!empty($dependentScripts)) {
            $c .= "<script>\n";
            $c .= "document.addEventListener('DOMContentLoaded', function() {\n";
            foreach ($dependentScripts as $dep) {
                $child = $dep['child'];
                $parent = $dep['parent'];
                $c .= "    const parent_$parent = document.getElementById('sel_$parent');\n";
                $c .= "    const child_$child = document.getElementById('sel_$child');\n";
                $c .= "    if(parent_$parent && child_$child) {\n";
                $c .= "        parent_$parent.addEventListener('change', function() {\n";
                $c .= "            const parentVal = this.value;\n";
                $c .= "            child_$child.value = '';\n";
                $c .= "            Array.from(child_$child.options).forEach(opt => {\n";
                $c .= "                if(!opt.value) return;\n";
                $c .= "                if(parentVal && opt.getAttribute('data-parent') === String(parentVal)) opt.classList.remove('d-none');\n";
                $c .= "                else opt.classList.add('d-none');\n";
                $c .= "            });\n";
                $c .= "        });\n";
                $c .= "        parent_$parent.dispatchEvent(new Event('change'));\n"; // Trigger initial filter
                $c .= "    }\n";
            }
            $c .= "});\n";
            $c .= "</script>\n";
        }

        // Inject File Preview Scripts
        $hasFile = false;
        foreach ($fields as $info) { if ($info['is_file']) { $hasFile = true; break; } }
        if ($hasFile) {
            $c .= "<script>\n";
            $c .= "document.querySelectorAll('.file-upload-input').forEach(input => {\n";
            $c .= "    input.addEventListener('change', function(e) {\n";
            $c .= "        const previewId = this.getAttribute('data-preview');\n";
            $c .= "        const previewContainer = document.getElementById(previewId);\n";
            $c .= "        if (this.files && this.files[0]) {\n";
            $c .= "            const file = this.files[0];\n";
            $c .= "            const reader = new FileReader();\n";
            $c .= "            reader.onload = function(e) {\n";
            $c .= "                let previewHtml = file.type.startsWith('image/') ? `<img src=\"\${e.target.result}\" class=\"img-fluid rounded shadow-sm border\" style=\"max-height: 150px;\">` : `<div class=\"p-3 bg-light border\">\${file.name}</div>`;\n";
            $c .= "                previewContainer.innerHTML = previewHtml;\n";
            $c .= "                previewContainer.classList.remove('d-none');\n";
            $c .= "            }\n";
            $c .= "            reader.readAsDataURL(file);\n";
            $c .= "        } else {\n";
            $c .= "            previewContainer.classList.add('d-none');\n";
            $c .= "        }\n";
            $c .= "    });\n";
            $c .= "});\n";
            $c .= "</script>\n";
        }

        $c .= self::generateConditionalLogicJS($conditionalRules);

        $c .= "</body>\n</html>";
        return $c;
    }

    public static function generateEditFile($table, $primaryKey, $fields, $foreignKeys, $isProtected = false, $filterFk = '', $formLayout = '1', $styleConfig = [], $conditionalRules = [], $filenames = []) {
        $files = array_merge([
            'list' => "list_$table.php",
            'create' => "create_$table.php",
            'edit' => "edit_$table.php",
            'delete' => "delete_$table.php",
            'view' => "view_$table.php"
        ], $filenames);

        $c = "<?php\n";
        if ($isProtected) $c .= "require_once 'protect.php';\n";
        $c .= "require_once 'config.php';\nrequire_once 'fonction.php';\n\n\$pdo = connectbd();\n\n";
        $c .= "\$$primaryKey = \$_GET['$primaryKey'] ?? null;\nif (!\$$primaryKey) { header(\"Location: {\$files['list']}\"); exit; }\n\n";
        $c .= "\$item = get_{$table}_by_id(\$pdo, \$$primaryKey);\nif (!\$item) { die('Enregistrement introuvable.'); }\n\n";

        if ($filterFk) {
            $c .= "if (\$item['$filterFk'] != \$_SESSION['user_id']) { die('Accès refusé. Ce contenu ne vous appartient pas.'); }\n\n";
        }

        if (!empty($foreignKeys)) {
            foreach ($foreignKeys as $col => $fk) {
                $fkTable = $fk['table'];
                $c .= "\${$fkTable}s = get_all_{$fkTable}(\$pdo);\n";
            }
            $c .= "\n";
        }

        $c .= "if (\$_SERVER['REQUEST_METHOD'] === 'POST') {\n";
        $updateArgs = ["\$$primaryKey"];
        foreach ($fields as $name => $info) {
            if ($name === $primaryKey) continue;
            if (isset($info['vis_edit']) && !$info['vis_edit']) {
                 continue;
            }
            
            if ($info['is_file']) {
                $c .= "    \$$name = \$item['$name'];\n";
                $c .= "    if (isset(\$_FILES['$name']) && \$_FILES['$name']['error'] === UPLOAD_ERR_OK) {\n";
                $c .= "        if (!is_dir('uploads')) mkdir('uploads', 0777, true);\n";
                $c .= "        \$ext = pathinfo(\$_FILES['$name']['name'], PATHINFO_EXTENSION);\n";
                $c .= "        \$filename = uniqid('{$name}_') . '.' . \$ext;\n";
                $c .= "        if (move_uploaded_file(\$_FILES['$name']['tmp_name'], 'uploads/' . \$filename)) {\n";
                $c .= "            \$$name = \$filename;\n";
                $c .= "            if (\$item['$name'] && file_exists('uploads/' . \$item['$name'])) { unlink('uploads/' . \$item['$name']); }\n";
                $c .= "        }\n";
                $c .= "    }\n";
            } elseif ($name === $filterFk) {
                $c .= "    \$$name = \$_SESSION['user_id'] ?? null;\n";
            } else {
                $c .= "    \$$name = \$_POST['$name'] ?? null;\n";
            }
            $updateArgs[] = "\$$name";
        }
        $c .= "\n    update_$table(\$pdo, " . implode(', ', $updateArgs) . ");\n";
        $c .= "    header('Location: {$files['list']}');\n";
        $c .= "    exit;\n";
        $c .= "}\n?>\n\n";

        $c .= "<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n";
        $c .= "    <meta charset=\"UTF-8\">\n";
        $c .= "    <title>Modifier le ".ucfirst($table)."</title>\n";
        $c .= "    <link href=\"assets/css/bootstrap.min.css\" rel=\"stylesheet\">\n";
        $c .= "    <link href=\"style.css\" rel=\"stylesheet\">\n";
        $c .= "</head>\n<body class=\"bg-light\">\n";

        $maxWidth = ($formLayout == '2') ? '900px' : '600px';
        $c .= "<div class=\"container mt-5\" style=\"max-width: {$maxWidth};\">\n";
        $c .= "    <div class=\"card shadow-sm\">\n";
        $c .= "        <div class=\"card-header bg-primary text-white\">\n";
        $c .= "            <h4 class=\"mb-0\">Modifier le ".ucfirst($table)."</h4>\n";
        $c .= "        </div>\n";
        $c .= "        <div class=\"card-body\">\n";
        $c .= "            <form method=\"POST\" enctype=\"multipart/form-data\">\n";

        if ($formLayout == '2') $c .= "                <div class=\"row\">\n";
        $dependentScripts = [];

        foreach ($fields as $name => $info) {
            if ($name === $primaryKey || $name === $filterFk) continue;
            // Sauter les champs non visibles
            if (isset($info['vis_edit']) && !$info['vis_edit']) continue;

            if ($formLayout == '2') $c .= "                    <div class=\"col-md-6 mb-3 field-container-{$name}\">\n";
            else $c .= "                <div class=\"mb-3 field-container-{$name}\">\n";

            $c .= "                        <label class=\"form-label fw-bold\">" . htmlspecialchars($info['label']) . "</label>\n";
            
            if ($info['is_fk']) {
                $fkTable = $info['fk_target'];
                $fkCol = $info['fk_col'];
                $fkDisplay = $info['fk_display'];
                
                $dependsOn = $info['depends_on'] ?? '';
                $dependsCol = $info['depends_col'] ?? '';

                if (!empty($dependsOn) && !empty($dependsCol)) {
                    $dependentScripts[] = ['child' => $name, 'parent' => $dependsOn];
                    $c .= "                        <select name=\"$name\" id=\"sel_$name\" class=\"form-select dependent-select\" data-selected=\"<?= htmlspecialchars(\$item['$name']) ?>\" required>\n";
                    $c .= "                            <option value=\"\">-- Attente " . htmlspecialchars($dependsOn) . " --</option>\n";
                    $c .= "                            <?php foreach (\${$fkTable}s as \$f_item): ?>\n";
                    $c .= "                            <option value=\"<?= htmlspecialchars(\$f_item['$fkCol']) ?>\" data-parent=\"<?= htmlspecialchars(\$f_item['$dependsCol']) ?>\" <?= \$item['$name'] == \$f_item['$fkCol'] ? 'selected' : '' ?> class=\"d-none\">\n";
                    $c .= "                                <?= htmlspecialchars(\$f_item['$fkDisplay']) ?>\n";
                    $c .= "                            </option>\n";
                    $c .= "                            <?php endforeach; ?>\n";
                    $c .= "                        </select>\n";
                } else {
                    $c .= "                        <select name=\"$name\" id=\"sel_$name\" class=\"form-select\" required>\n";
                    $c .= "                            <option value=\"\">-- Sélectionner --</option>\n";
                    $c .= "                            <?php foreach (\${$fkTable}s as \$f_item): ?>\n";
                    $c .= "                            <option value=\"<?= htmlspecialchars(\$f_item['$fkCol']) ?>\" <?= \$item['$name'] == \$f_item['$fkCol'] ? 'selected' : '' ?>>\n";
                    $c .= "                                <?= htmlspecialchars(\$f_item['$fkDisplay']) ?>\n";
                    $c .= "                            </option>\n";
                    $c .= "                            <?php endforeach; ?>\n";
                    $c .= "                        </select>\n";
                }
            } elseif ($info['is_enum']) {
                $c .= "                        <select name=\"$name\" class=\"form-select\" required>\n";
                foreach ($info['enum_values'] as $val) {
                    $c .= "                            <option value=\"$val\" <?= \$item['$name'] === '$val' ? 'selected' : '' ?>>" . htmlspecialchars($val) . "</option>\n";
                }
                $c .= "                        </select>\n";
            } elseif ($info['is_file']) {
                $c .= "                        <?php if (\$item['$name']): ?>\n";
                $c .= "                        <div class=\"mb-2 p-2 border rounded bg-white\">\n";
                $c .= "                            <div class=\"fw-bold small text-muted\">Fichier actuel :</div>\n";
                $c .= "                            <a href=\"uploads/<?= htmlspecialchars(\$item['$name']) ?>\" target=\"_blank\"><?= htmlspecialchars(\$item['$name']) ?></a>\n";
                $c .= "                        </div>\n";
                $c .= "                        <?php endif; ?>\n";
                $c .= "                        <input type=\"file\" id=\"file_$name\" name=\"$name\" class=\"form-control file-upload-input\" accept=\"image/*,.pdf,.doc,.docx,.xls,.xlsx\">\n";
            } else {
                $inputType = self::getHtmlInputType($info['type']);
                if (strpos($info['type'], 'text') !== false && strpos($info['type'], 'varchar') === false) {
                    $c .= "                        <textarea name=\"$name\" class=\"form-control\" rows=\"4\" required><?= htmlspecialchars(\$item['$name']) ?></textarea>\n";
                } elseif ($inputType == 'datetime-local') {
                    $c .= "                        <input type=\"$inputType\" name=\"$name\" class=\"form-control\" value=\"<?= date('Y-m-d\TH:i', strtotime(\$item['$name'])) ?>\" required>\n";
                } else {
                    $stepAttr = ($inputType === 'number') ? ' step="any"' : '';
                    $c .= "                        <input type=\"$inputType\" name=\"$name\" class=\"form-control\" value=\"<?= htmlspecialchars(\$item['$name']) ?>\"$stepAttr required>\n";
                }
            }
            if ($formLayout == '2') $c .= "                    </div>\n";
            else $c .= "                </div>\n";
        }
        if ($formLayout == '2') $c .= "                </div>\n";

        $btnBack = !empty($styleConfig) ? 'btn btn-cancel' : 'btn btn-secondary';
        $btnSave = !empty($styleConfig) ? 'btn btn-save' : 'btn btn-primary';

        $c .= "                <div class=\"d-flex justify-content-between mt-4\">\n";
        $c .= "                    <a href=\"{$files['list']}\" class=\"$btnBack\">Retour</a>\n";
        $c .= "                    <button type=\"submit\" class=\"$btnSave\">Modifier</button>\n";
        $c .= "                </div>\n";
        $c .= "            </form>\n";
        $c .= "        </div>\n";
        $c .= "    </div>\n";
        $c .= "</div>\n";
        
        if (!empty($dependentScripts)) {
            $c .= "<script>\n";
            $c .= "document.addEventListener('DOMContentLoaded', function() {\n";
            foreach ($dependentScripts as $dep) {
                $child = $dep['child'];
                $parent = $dep['parent'];
                $c .= "    const parent_$parent = document.getElementById('sel_$parent');\n";
                $c .= "    const child_$child = document.getElementById('sel_$child');\n";
                $c .= "    if(parent_$parent && child_$child) {\n";
                $c .= "        const originalValue = child_$child.getAttribute('data-selected');\n";
                $c .= "        parent_$parent.addEventListener('change', function() {\n";
                $c .= "            const parentVal = this.value;\n";
                $c .= "            const currentSelected = child_$child.value;\n";
                $c .= "            let matchFound = false;\n";
                $c .= "            Array.from(child_$child.options).forEach(opt => {\n";
                $c .= "                if(!opt.value) return;\n";
                $c .= "                if(parentVal && opt.getAttribute('data-parent') === String(parentVal)) {\n";
                $c .= "                    opt.classList.remove('d-none');\n";
                $c .= "                    if(opt.value === currentSelected || opt.value === originalValue) matchFound = true;\n";
                $c .= "                } else opt.classList.add('d-none');\n";
                $c .= "            });\n";
                $c .= "            if(!matchFound) child_$child.value = '';\n";
                $c .= "        });\n";
                $c .= "        parent_$parent.dispatchEvent(new Event('change'));\n";
                $c .= "        if (originalValue) child_$child.value = originalValue;\n";
                $c .= "    }\n";
            }
            $c .= "});\n";
            $c .= "</script>\n";
        }

        $c .= self::generateConditionalLogicJS($conditionalRules);

        $c .= "</body>\n</html>";
        return $c;
    }

    public static function generateDeleteFile($table, $primaryKey, $fields, $isProtected = false, $filterFk = '', $adminMode = false, $filenames = []) {
        $files = array_merge([
            'list' => "list_$table.php",
            'create' => "create_$table.php",
            'edit' => "edit_$table.php",
            'delete' => "delete_$table.php",
            'view' => "view_$table.php"
        ], $filenames);

        $c = "<?php\n";
        if ($isProtected) $c .= "require_once 'protect.php';\n";
        $c .= "require_once 'config.php';\nrequire_once 'fonction.php';\n\n\$pdo = connectbd();\n\n";
        $c .= "if (isset(\$_GET['$primaryKey'])) {\n";
        $c .= "    \$id = \$_GET['$primaryKey'];\n";
        $c .= "    \$item = get_{$table}_by_id(\$pdo, \$id);\n";
        
        if ($filterFk && !$adminMode) {
            $c .= "    if (\$item && \$item['$filterFk'] == \$_SESSION['user_id']) {\n";
        } else {
            $c .= "    if (\$item) {\n";
        }

        foreach ($fields as $name => $info) {
            if ($info['is_file']) {
                $c .= "        if (\$item['$name'] && file_exists('uploads/' . \$item['$name'])) {\n";
                $c .= "            unlink('uploads/' . \$item['$name']);\n";
                $c .= "        }\n";
            }
        }
        $c .= "        delete_$table(\$pdo, \$id);\n";
        $c .= "    }\n";
        $c .= "}\n\n";
        $c .= "header(\"Location: {$files['list']}\");\nexit;\n?>";
        return $c;
    }

    public static function generateViewFile($table, $primaryKey, $fields, $foreignKeys, $isProtected = false, $filterFk = '', $styleConfig = [], $adminMode = false, $filenames = []) {
        $files = array_merge([
            'list' => "list_$table.php",
            'create' => "create_$table.php",
            'edit' => "edit_$table.php",
            'delete' => "delete_$table.php",
            'view' => "view_$table.php"
        ], $filenames);

        $c = "<?php\n";
        if ($isProtected) $c .= "require_once 'protect.php';\n";
        $c .= "require_once 'config.php';\nrequire_once 'fonction.php';\n\n\$pdo = connectbd();\n\n";
        $c .= "\$id = \$_GET['$primaryKey'] ?? null;\nif (!\$id) { header(\"Location: {$files['list']}\"); exit; }\n\n";
        $c .= "\$item = get_{$table}_by_id(\$pdo, \$id);\nif (!\$item) { die('Enregistrement introuvable.'); }\n\n";

        if ($filterFk && !$adminMode) {
            $c .= "if (\$item['$filterFk'] != \$_SESSION['user_id']) { die('Accès refusé.'); }\n\n";
        }

        if (!empty($foreignKeys)) {
            foreach ($foreignKeys as $col => $fk) {
                $fkTable = $fk['table'];
                $fkCol = $fk['column'];
                $c .= "\${$fkTable}s = get_all_{$fkTable}(\$pdo);\n";
                $c .= "\${$fkTable}_map = [];\n";
                $c .= "foreach (\${$fkTable}s as \$f) { \${$fkTable}_map[\$f['$fkCol']] = \$f; }\n\n";
            }
        }
        $c .= "?>\n<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Détails</title>\n";
        $c .= "    <link href=\"assets/css/bootstrap.min.css\" rel=\"stylesheet\">\n";
        $c .= "    <link href=\"style.css\" rel=\"stylesheet\">\n</head>\n<body class=\"bg-light\">\n";
        $c .= "<div class=\"container mt-5\" style=\"max-width: 800px;\">\n    <div class=\"card shadow-sm\">\n";
        $c .= "        <div class=\"card-header bg-info text-white d-flex justify-content-between align-items-center\">\n";
        $c .= "            <h4 class=\"mb-0\">Détails du ".ucfirst($table)."</h4>\n";
        $c .= "            <a href=\"{\$files['list']}\" class=\"btn btn-sm btn-light\">Retour</a>\n        </div>\n";
        $c .= "        <div class=\"card-body\">\n            <table class=\"table table-bordered\">\n";
        foreach ($fields as $name => $info) {
             $c .= "                <tr>\n                    <th width=\"30%\" class=\"bg-light\">".htmlspecialchars($info['label'])."</th>\n";
             if ($info['is_file']) {
                 $c .= "                    <td>\n                        <?php if (\$item['$name']): ?>\n";
                 $c .= "                            <a href=\"uploads/<?= htmlspecialchars(\$item['$name']) ?>\" target=\"_blank\">Voir le fichier</a>\n";
                 $c .= "                        <?php else: ?>\n                            -\n                        <?php endif; ?>\n                    </td>\n";
             } elseif ($info['is_fk']) {
                 $fkTable = $info['fk_target'];
                 $fkDisplay = $info['fk_display'];
                 $c .= "                    <td><?= htmlspecialchars(\${$fkTable}_map[\$item['$name']]['$fkDisplay'] ?? \$item['$name']) ?></td>\n";
             } else {
                 $c .= "                    <td><?= nl2br(htmlspecialchars(\$item['$name'])) ?></td>\n";
             }
             $c .= "                </tr>\n";
        }
        $c .= "            </table>\n        </div>\n    </div>\n</div>\n</body>\n</html>";
        return $c;
    }

    private static function generateConditionalLogicJS($conditionalRules) {
        if (empty($conditionalRules)) return "";
        $js = "\n<script>\n";
        $js .= "document.addEventListener('DOMContentLoaded', function() {\n";
        $js .= "    const rules = " . json_encode($conditionalRules) . ";\n";
        $js .= "    rules.forEach(rule => {\n";
        $js .= "        const trigger = document.querySelector('[name=\"' + rule.trigger + '\"]');\n";
        $js .= "        const target = document.querySelector('.field-container-' + rule.target);\n";
        $js .= "        if(trigger && target) {\n";
        $js .= "            const applyRule = () => {\n";
        $js .= "                const isMatch = String(trigger.value) === String(rule.value);\n";
        $js .= "                if(rule.action === 'show') target.style.display = isMatch ? 'block' : 'none';\n";
        $js .= "                else target.style.display = isMatch ? 'none' : 'block';\n";
        $js .= "            };\n";
        $js .= "            trigger.addEventListener('change', applyRule);\n";
        $js .= "            applyRule();\n";
        $js .= "        }\n";
        $js .= "    });\n";
        $js .= "});\n";
        $js .= "</script>\n";
        return $js;
    }
}
