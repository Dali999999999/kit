<?php
namespace AIO\Src;

class PageGenerator {

    public static function getHtmlInputType($mysqlType) {
        $typeStr = strtolower($mysqlType);
        if (strpos($typeStr, 'int') !== false || strpos($typeStr, 'decimal') !== false || strpos($typeStr, 'float') !== false || strpos($typeStr, 'double') !== false || strpos($typeStr, 'year') !== false) return 'number';
        if (strpos($typeStr, 'datetime') !== false || strpos($typeStr, 'timestamp') !== false) return 'datetime-local';
        if (strpos($typeStr, 'date') !== false) return 'date';
        if (strpos($typeStr, 'time') !== false) return 'time';
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

    public static function generateListFile($table, $primaryKey, $fields, $foreignKeys, $isProtected = false, $filterFk = '', $styleConfig = [], $listLayout = 'table', $adminMode = false, $autoJoin = true, $hasView = false, $filenames = [], $useDatatable = true, $actionConfig = []) {
        $files = array_merge([
            'list' => "list_$table.php",
            'create' => "create_$table.php",
            'edit' => "edit_$table.php",
            'delete' => "delete_$table.php",
            'view' => "view_$table.php"
        ], $filenames);

        $showView = (isset($actionConfig['show_view']) && is_bool($actionConfig['show_view'])) ? $actionConfig['show_view'] : $hasView;
        $showEdit = $actionConfig['show_edit'] ?? true;
        $showDelete = $actionConfig['show_delete'] ?? true;
        $btnType = $actionConfig['btn_type'] ?? 'icon';
        $textView = $actionConfig['text_view'] ?? 'Voir';
        $textEdit = $actionConfig['text_edit'] ?? 'Modifier';
        $textDelete = $actionConfig['text_delete'] ?? 'Supprimer';

        $colspan = 0;
        foreach ($fields as $name => $info) {
            if (isset($info['vis_list']) && !$info['vis_list']) continue;
            $colspan++;
        }
        if ($showView || $showEdit || $showDelete) $colspan++;

        $viewBtnContent = $btnType === 'icon' ? '<i class="bi bi-eye"></i>' : htmlspecialchars($textView);
        $editBtnContent = $btnType === 'icon' ? '<i class="bi bi-pencil"></i> Modifier' : htmlspecialchars($textEdit);
        $deleteBtnContent = $btnType === 'icon' ? '<i class="bi bi-trash"></i> Supprimer' : htmlspecialchars($textDelete);

        $viewBtnContentTable = $btnType === 'icon' ? '<i class="bi bi-eye text-white"></i>' : htmlspecialchars($textView);
        $editBtnContentTable = $btnType === 'icon' ? '<i class="bi bi-pencil"></i>' : htmlspecialchars($textEdit);
        $deleteBtnContentTable = $btnType === 'icon' ? '<i class="bi bi-trash"></i>' : htmlspecialchars($textDelete);

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
        if ($useDatatable && $listLayout === 'table') {
            $c .= "    <link href=\"data_table/datatables.min.css\" rel=\"stylesheet\">\n";
        }
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
                if (isset($info['vis_list']) && !$info['vis_list']) continue;
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
                    } else {
                        $typeLower = strtolower($info['type'] ?? '');
                        if ($typeLower === 'boolean' || $typeLower === 'tinyint(1)') {
                            $c .= "                        <?php if (\$item['$name'] !== null): ?>\n";
                            $c .= "                            <span class=\"badge <?= \$item['$name'] ? 'bg-success' : 'bg-secondary' ?>\"><?= \$item['$name'] ? 'Oui' : 'Non' ?></span>\n";
                            $c .= "                        <?php endif; ?>\n";
                        } else if (strpos($typeLower, 'text') !== false || strpos($typeLower, 'blob') !== false || strpos($typeLower, 'json') !== false) {
                            $c .= "                        <?php \$val = \$item['$name']; echo (mb_strlen(\$val) > 50) ? htmlspecialchars(mb_substr(\$val, 0, 50)) . '...' : htmlspecialchars(\$val); ?>\n";
                        } else {
                            $c .= "                        <?= htmlspecialchars(\$item['$name']) ?>\n";
                        }
                    }
                    $c .= "                    </p>\n";
                }
                $idx++;
            }
            $c .= "                </div>\n";
            $c .= "                <div class=\"card-footer bg-white border-0 d-flex justify-content-between pb-3\">\n";
            if ($showView) $c .= "                    <a href=\"{$files['view']}?{$primaryKey}=<?= \$item['$primaryKey'] ?>\" class=\"btn btn-outline-info btn-sm\">$viewBtnContent</a>\n";
            if ($showEdit) $c .= "                    <a href=\"{$files['edit']}?{$primaryKey}=<?= \$item['$primaryKey'] ?>\" class=\"btn btn-outline-primary btn-sm\">$editBtnContent</a>\n";
            if ($showDelete) $c .= "                    <a href=\"{$files['delete']}?{$primaryKey}=<?= \$item['$primaryKey'] ?>\" class=\"btn btn-outline-danger btn-sm\" onclick=\"return confirm('Êtes-vous sûr ?')\">$deleteBtnContent</a>\n";
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
            foreach ($fields as $name => $info) {
                if (isset($info['vis_list']) && !$info['vis_list']) continue;
                $c .= "                        <th>" . htmlspecialchars($info['label']) . "</th>\n";
            }
            if ($showView || $showEdit || $showDelete) {
                $c .= "                        <th class=\"text-center\">Actions</th>\n";
            }
            $c .= "                    </tr>\n";
            $c .= "                </thead>\n";
            $c .= "                <tbody>\n";
            $c .= "                    <?php foreach (\$items as \$item): ?>\n";
            $c .= "                    <tr>\n";
            foreach ($fields as $name => $info) {
                if (isset($info['vis_list']) && !$info['vis_list']) continue;
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
                } else {
                    $typeLower = strtolower($info['type'] ?? '');
                    if ($typeLower === 'boolean' || $typeLower === 'tinyint(1)') {
                        $c .= "                        <td>\n";
                        $c .= "                            <?php if (\$item['$name'] !== null): ?>\n";
                        $c .= "                                <span class=\"badge <?= \$item['$name'] ? 'bg-success' : 'bg-secondary' ?>\"><?= \$item['$name'] ? 'Oui' : 'Non' ?></span>\n";
                        $c .= "                            <?php endif; ?>\n";
                        $c .= "                        </td>\n";
                    } else if (strpos($typeLower, 'text') !== false || strpos($typeLower, 'blob') !== false || strpos($typeLower, 'json') !== false) {
                        $c .= "                        <td>\n";
                        $c .= "                            <?php\n";
                        $c .= "                            \$val = \$item['$name'];\n";
                        $c .= "                            echo (mb_strlen(\$val) > 50) ? htmlspecialchars(mb_substr(\$val, 0, 50)) . '...' : htmlspecialchars(\$val);\n";
                        $c .= "                            ?>\n";
                        $c .= "                        </td>\n";
                    } else {
                        $c .= "                        <td><?= htmlspecialchars(\$item['$name']) ?></td>\n";
                    }
                }
            }
            if ($showView || $showEdit || $showDelete) {
                $c .= "                        <td class=\"text-center\">\n";
                if ($showView) $c .= "                            <a href=\"{$files['view']}?{$primaryKey}=<?= \$item['$primaryKey'] ?>\" class=\"btn btn-sm btn-info\">$viewBtnContentTable</a>\n";
                if ($showEdit) $c .= "                            <a href=\"{$files['edit']}?{$primaryKey}=<?= \$item['$primaryKey'] ?>\" class=\"btn btn-sm btn-primary\">$editBtnContentTable</a>\n";
                if ($showDelete) $c .= "                            <a href=\"{$files['delete']}?{$primaryKey}=<?= \$item['$primaryKey'] ?>\" class=\"btn btn-sm btn-danger\" onclick=\"return confirm('Êtes-vous sûr ?')\">$deleteBtnContentTable</a>\n";
                $c .= "                        </td>\n";
            }
            $c .= "                    </tr>\n";
            $c .= "                    <?php endforeach; ?>\n";
            $c .= "                    <?php if (empty(\$items)" . ($useDatatable ? " && false" : "") . "): ?>\n";
            $c .= "                    <tr><td colspan=\"$colspan\" class=\"text-center text-muted py-4\">Aucune donnée trouvée</td></tr>\n";
            $c .= "                    <?php endif; ?>\n";
            $c .= "                </tbody>\n";
            $c .= "            </table>\n";
            $c .= "        </div>\n";
            $c .= "    </div>\n";
        }
        $c .= "</div>\n";
        if ($useDatatable && $listLayout === 'table') {
            $c .= "<script src=\"data_table/datatables.min.js\"></script>\n";
            $c .= "<script>\n";
            $c .= "$(document).ready(function() {\n";
            $c .= "    $('table').DataTable({\n";
            $c .= "        language: {\n";
            $c .= "            sEmptyTable: \"Aucune donnée disponible dans le tableau\",\n";
            $c .= "            sInfo: \"Affichage de l'élément _START_ à _END_ sur _TOTAL_ éléments\",\n";
            $c .= "            sInfoEmpty: \"Affichage de l'élément 0 à 0 sur 0 élément\",\n";
            $c .= "            sInfoFiltered: \"(filtré à partir de _MAX_ éléments au total)\",\n";
            $c .= "            sInfoPostFix: \"\",\n";
            $c .= "            sInfoThousands: \",\",\n";
            $c .= "            sLengthMenu: \"Afficher _MENU_ éléments\",\n";
            $c .= "            sLoadingRecords: \"Chargement...\",\n";
            $c .= "            sProcessing: \"Traitement...\",\n";
            $c .= "            sSearch: \"Rechercher :\",\n";
            $c .= "            sZeroRecords: \"Aucun élément correspondant trouvé\",\n";
            $c .= "            oPaginate: { sFirst: \"Premier\", sLast: \"Dernier\", sNext: \"Suivant\", sPrevious: \"Précédent\" },\n";
            $c .= "            oAria: { sSortAscending: \": activer pour trier la colonne par ordre croissant\", sSortDescending: \": activer pour trier la colonne par ordre décroissant\" },\n";
            $c .= "            select: { rows: { _: \"%d lignes sélectionnées\", 0: \"Aucune ligne sélectionnée\", 1: \"1 ligne sélectionnée\" } }\n";
            $c .= "        }\n";
            $c .= "    });\n";
            $c .= "});\n";
            $c .= "</script>\n";
        }
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
                if (!empty($info['is_set'])) {
                    $c .= "    \$$name = isset(\$_POST['$name']) && is_array(\$_POST['$name']) ? implode(',', \$_POST['$name']) : (\$_POST['$name'] ?? null);\n";
                } else {
                    $c .= "    \$$name = \$_POST['$name'] ?? null;\n";
                }
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
                
                $iStyle = $info['input_style'] ?? 'select';
                
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
                    if ($iStyle === 'radio' || $iStyle === 'checkbox') {
                        $js = ($iStyle === 'checkbox') ? " onclick=\"document.querySelectorAll('input[name=\\'{$name}\\']').forEach(cb => { if(cb !== this) cb.checked = false; })\"" : "";
                        $reqStr = ($iStyle === 'radio') ? " required" : "";
                        $c .= "                        <div>\n";
                        $c .= "                            <?php foreach (\${$fkTable}s as \$f_item): ?>\n";
                        $c .= "                            <div class=\"form-check\">\n";
                        $c .= "                                <input class=\"form-check-input\" type=\"$iStyle\" name=\"$name\" id=\"{$name}_<?= htmlspecialchars(\$f_item['$fkCol']) ?>\" value=\"<?= htmlspecialchars(\$f_item['$fkCol']) ?>\"$js$reqStr>\n";
                        $c .= "                                <label class=\"form-check-label\" for=\"{$name}_<?= htmlspecialchars(\$f_item['$fkCol']) ?>\">\n";
                        $c .= "                                    <?= htmlspecialchars(\$f_item['$fkDisplay']) ?>\n";
                        $c .= "                                </label>\n";
                        $c .= "                            </div>\n";
                        $c .= "                            <?php endforeach; ?>\n";
                        $c .= "                        </div>\n";
                    } else {
                        $c .= "                        <select name=\"$name\" id=\"sel_$name\" class=\"form-select\" required>\n";
                        $c .= "                            <option value=\"\">-- Sélectionner --</option>\n";
                        $c .= "                            <?php foreach (\${$fkTable}s as \$f_item): ?>\n";
                        $c .= "                            <option value=\"<?= htmlspecialchars(\$f_item['$fkCol']) ?>\"><?= htmlspecialchars(\$f_item['$fkDisplay']) ?></option>\n";
                        $c .= "                            <?php endforeach; ?>\n";
                        $c .= "                        </select>\n";
                    }
                }
            } elseif ($info['is_enum'] || !empty($info['is_set'])) {
                $iStyle = $info['input_style'] ?? 'select';
                $isSet = !empty($info['is_set']);
                if ($iStyle === 'radio' || $iStyle === 'checkbox') {
                    $c .= "                        <div>\n";
                    foreach ($info['enum_values'] as $val) {
                        $safeVal = htmlspecialchars($val);
                        // SET natively permits multi-select; ENUM does not. Checkboxes for ENUM will enforce single via JS.
                        $js = ($iStyle === 'checkbox' && !$isSet) ? " onclick=\"document.querySelectorAll('input[name=\\'{$name}\\']').forEach(cb => { if(cb !== this) cb.checked = false; })\"" : "";
                        $inputName = $isSet ? "{$name}[]" : $name;
                        $req = ($isSet || $iStyle === 'checkbox') ? "" : " required";
                        $c .= "                            <div class=\"form-check form-check-inline\">\n";
                        $c .= "                                <input class=\"form-check-input\" type=\"$iStyle\" name=\"$inputName\" id=\"{$name}_$safeVal\" value=\"$safeVal\"$js$req>\n";
                        $c .= "                                <label class=\"form-check-label\" for=\"{$name}_$safeVal\">$safeVal</label>\n";
                        $c .= "                            </div>\n";
                    }
                    $c .= "                        </div>\n";
                } else {
                    $reqArray = $isSet ? "multiple" : "required";
                    $inputName = $isSet ? "{$name}[]" : $name;
                    $c .= "                        <select name=\"$inputName\" class=\"form-select\" $reqArray>\n";
                    if (!$isSet) $c .= "                            <option value=\"\">-- Sélectionner --</option>\n";
                    foreach ($info['enum_values'] as $val) {
                        $safeVal = addslashes($val);
                        $c .= "                            <option value=\"$safeVal\">" . htmlspecialchars($val) . "</option>\n";
                    }
                    $c .= "                        </select>\n";
                }
            } elseif ($info['is_file']) {
                $c .= "                        <input type=\"file\" id=\"file_$name\" name=\"$name\" class=\"form-control file-upload-input\" data-preview=\"preview_$name\" accept=\"image/*,.pdf,.doc,.docx,.xls,.xlsx\">\n";
                $c .= "                        <div id=\"preview_$name\" class=\"mt-2 d-none position-relative\" style=\"max-width: 200px;\"></div>\n";
            } else {
                $inputType = self::getHtmlInputType($info['type']);
                $typeLower = strtolower($info['type']);
                if ($typeLower === 'boolean' || $typeLower === 'tinyint(1)') {
                    $iStyle = $info['input_style'] ?? 'select';
                    if ($iStyle === 'radio' || $iStyle === 'checkbox') {
                        $js = ($iStyle === 'checkbox') ? " onclick=\"document.querySelectorAll('input[name=\\'{$name}\\']').forEach(cb => { if(cb !== this) cb.checked = false; })\"" : "";
                        $reqStr = ($iStyle === 'radio') ? " required" : "";
                        $c .= "                        <div>\n";
                        $c .= "                            <div class=\"form-check form-check-inline\">\n";
                        $c .= "                                <input class=\"form-check-input\" type=\"$iStyle\" name=\"$name\" id=\"{$name}_1\" value=\"1\"$js$reqStr>\n";
                        $c .= "                                <label class=\"form-check-label\" for=\"{$name}_1\">Oui</label>\n";
                        $c .= "                            </div>\n";
                        $c .= "                            <div class=\"form-check form-check-inline\">\n";
                        $c .= "                                <input class=\"form-check-input\" type=\"$iStyle\" name=\"$name\" id=\"{$name}_0\" value=\"0\"$js$reqStr>\n";
                        $c .= "                                <label class=\"form-check-label\" for=\"{$name}_0\">Non</label>\n";
                        $c .= "                            </div>\n";
                        $c .= "                        </div>\n";
                    } else {
                        $c .= "                        <select name=\"$name\" class=\"form-select\" required>\n";
                        $c .= "                            <option value=\"1\">Oui</option>\n";
                        $c .= "                            <option value=\"0\">Non</option>\n";
                        $c .= "                        </select>\n";
                    }
                } else if ((strpos($typeLower, 'text') !== false || strpos($typeLower, 'blob') !== false || strpos($typeLower, 'json') !== false) && strpos($typeLower, 'varchar') === false && strpos($typeLower, 'tinytext') === false) {
                    $c .= "                        <textarea name=\"$name\" class=\"form-control\" rows=\"4\" required></textarea>\n";
                } else {
                    $stepAttr = ($inputType === 'number' && (strpos($typeLower, 'float') !== false || strpos($typeLower, 'double') !== false || strpos($typeLower, 'decimal') !== false)) ? ' step="any"' : '';
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
                if (!empty($info['is_set'])) {
                    $c .= "    \$$name = isset(\$_POST['$name']) && is_array(\$_POST['$name']) ? implode(',', \$_POST['$name']) : (\$_POST['$name'] ?? null);\n";
                } else {
                    $c .= "    \$$name = \$_POST['$name'] ?? null;\n";
                }
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
                
                $iStyle = $info['input_style'] ?? 'select';

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
                    if ($iStyle === 'radio' || $iStyle === 'checkbox') {
                        $js = ($iStyle === 'checkbox') ? " onclick=\"document.querySelectorAll('input[name=\\'{$name}\\']').forEach(cb => { if(cb !== this) cb.checked = false; })\"" : "";
                        $reqStr = ($iStyle === 'radio') ? " required" : "";
                        $c .= "                        <div>\n";
                        $c .= "                            <?php foreach (\${$fkTable}s as \$f_item): ?>\n";
                        $c .= "                            <div class=\"form-check\">\n";
                        $c .= "                                <input class=\"form-check-input\" type=\"$iStyle\" name=\"$name\" id=\"{$name}_<?= htmlspecialchars(\$f_item['$fkCol']) ?>\" value=\"<?= htmlspecialchars(\$f_item['$fkCol']) ?>\" <?= \$item['$name'] == \$f_item['$fkCol'] ? 'checked' : '' ?>$js$reqStr>\n";
                        $c .= "                                <label class=\"form-check-label\" for=\"{$name}_<?= htmlspecialchars(\$f_item['$fkCol']) ?>\">\n";
                        $c .= "                                    <?= htmlspecialchars(\$f_item['$fkDisplay']) ?>\n";
                        $c .= "                                </label>\n";
                        $c .= "                            </div>\n";
                        $c .= "                            <?php endforeach; ?>\n";
                        $c .= "                        </div>\n";
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
                }
            } elseif ($info['is_enum'] || !empty($info['is_set'])) {
                $iStyle = $info['input_style'] ?? 'select';
                $isSet = !empty($info['is_set']);
                if ($iStyle === 'radio' || $iStyle === 'checkbox') {
                    $c .= "                        <div>\n";
                    foreach ($info['enum_values'] as $val) {
                        $safeVal = htmlspecialchars($val);
                        // SET natively permits multi-select; ENUM does not. Checkboxes for ENUM will enforce single via JS.
                        $js = ($iStyle === 'checkbox' && !$isSet) ? " onclick=\"document.querySelectorAll('input[name=\\'{$name}\\']').forEach(cb => { if(cb !== this) cb.checked = false; })\"" : "";
                        $inputName = $isSet ? "{$name}[]" : $name;
                        $req = ($isSet || $iStyle === 'checkbox') ? "" : " required";
                        if ($isSet) {
                            $c .= "                            <div class=\"form-check form-check-inline\">\n";
                            $c .= "                                <input class=\"form-check-input\" type=\"$iStyle\" name=\"$inputName\" id=\"{$name}_$safeVal\" value=\"$safeVal\" <?= in_array('$safeVal', explode(',', \$item['$name'])) ? 'checked' : '' ?>$js$req>\n";
                            $c .= "                                <label class=\"form-check-label\" for=\"{$name}_$safeVal\">$safeVal</label>\n";
                            $c .= "                            </div>\n";
                        } else {
                            $c .= "                            <div class=\"form-check form-check-inline\">\n";
                            $c .= "                                <input class=\"form-check-input\" type=\"$iStyle\" name=\"$inputName\" id=\"{$name}_$safeVal\" value=\"$safeVal\" <?= \$item['$name'] === '$safeVal' ? 'checked' : '' ?>$js$req>\n";
                            $c .= "                                <label class=\"form-check-label\" for=\"{$name}_$safeVal\">$safeVal</label>\n";
                            $c .= "                            </div>\n";
                        }
                    }
                    $c .= "                        </div>\n";
                } else {
                    $reqArray = $isSet ? "multiple" : "required";
                    $inputName = $isSet ? "{$name}[]" : $name;
                    $c .= "                        <select name=\"$inputName\" class=\"form-select\" $reqArray>\n";
                    if ($isSet) {
                        foreach ($info['enum_values'] as $val) {
                            $c .= "                            <option value=\"$val\" <?= in_array('$val', explode(',', \$item['$name'])) ? 'selected' : '' ?>>" . htmlspecialchars($val) . "</option>\n";
                        }
                    } else {
                        foreach ($info['enum_values'] as $val) {
                            $c .= "                            <option value=\"$val\" <?= \$item['$name'] === '$val' ? 'selected' : '' ?>>" . htmlspecialchars($val) . "</option>\n";
                        }
                    }
                    $c .= "                        </select>\n";
                }
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
                $typeLower = strtolower($info['type']);
                if ($typeLower === 'boolean' || $typeLower === 'tinyint(1)') {
                    $iStyle = $info['input_style'] ?? 'select';
                    if ($iStyle === 'radio' || $iStyle === 'checkbox') {
                        $js = ($iStyle === 'checkbox') ? " onclick=\"document.querySelectorAll('input[name=\\'{$name}\\']').forEach(cb => { if(cb !== this) cb.checked = false; })\"" : "";
                        $reqStr = ($iStyle === 'radio') ? " required" : "";
                        $c .= "                        <div>\n";
                        $c .= "                            <div class=\"form-check form-check-inline\">\n";
                        $c .= "                                <input class=\"form-check-input\" type=\"$iStyle\" name=\"$name\" id=\"{$name}_1\" value=\"1\" <?= \$item['$name'] == 1 ? 'checked' : '' ?>$js$reqStr>\n";
                        $c .= "                                <label class=\"form-check-label\" for=\"{$name}_1\">Oui</label>\n";
                        $c .= "                            </div>\n";
                        $c .= "                            <div class=\"form-check form-check-inline\">\n";
                        $c .= "                                <input class=\"form-check-input\" type=\"$iStyle\" name=\"$name\" id=\"{$name}_0\" value=\"0\" <?= \$item['$name'] == 0 ? 'checked' : '' ?>$js$reqStr>\n";
                        $c .= "                                <label class=\"form-check-label\" for=\"{$name}_0\">Non</label>\n";
                        $c .= "                            </div>\n";
                        $c .= "                        </div>\n";
                    } else {
                        $c .= "                        <select name=\"$name\" class=\"form-select\" required>\n";
                        $c .= "                            <option value=\"1\" <?= \$item['$name'] == 1 ? 'selected' : '' ?>>Oui</option>\n";
                        $c .= "                            <option value=\"0\" <?= \$item['$name'] == 0 ? 'selected' : '' ?>>Non</option>\n";
                        $c .= "                        </select>\n";
                    }
                } else if ((strpos($typeLower, 'text') !== false || strpos($typeLower, 'blob') !== false || strpos($typeLower, 'json') !== false) && strpos($typeLower, 'varchar') === false && strpos($typeLower, 'tinytext') === false) {
                    $c .= "                        <textarea name=\"$name\" class=\"form-control\" rows=\"4\" required><?= htmlspecialchars(\$item['$name']) ?></textarea>\n";
                } elseif ($inputType == 'datetime-local') {
                    $c .= "                        <input type=\"$inputType\" name=\"$name\" class=\"form-control\" value=\"<?= date('Y-m-d\TH:i', strtotime(\$item['$name'])) ?>\" required>\n";
                } else {
                    $stepAttr = ($inputType === 'number' && (strpos($typeLower, 'float') !== false || strpos($typeLower, 'double') !== false || strpos($typeLower, 'decimal') !== false)) ? ' step="any"' : '';
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
        $c .= "\$id = \$_GET['$primaryKey'] ?? null;\nif (!\$id) { header(\"Location: {\$files['list']}\"); exit; }\n\n";
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
                 $typeLower = strtolower($info['type'] ?? '');
                 if ($typeLower === 'boolean' || $typeLower === 'tinyint(1)') {
                     $c .= "                    <td>\n";
                     $c .= "                        <?php if (\$item['$name'] !== null): ?>\n";
                     $c .= "                            <span class=\"badge <?= \$item['$name'] ? 'bg-success' : 'bg-secondary' ?>\"><?= \$item['$name'] ? 'Oui' : 'Non' ?></span>\n";
                     $c .= "                        <?php endif; ?>\n";
                     $c .= "                    </td>\n";
                 } else {
                     $c .= "                    <td><?= nl2br(htmlspecialchars(\$item['$name'])) ?></td>\n";
                 }
             }
             $c .= "                </tr>\n";
        }
        $c .= "            </table>\n        </div>\n    </div>\n</div>\n</body>\n</html>";
        return $c;
    }

    public static function generateSearchFile($table, $primaryKey, $fields, $foreignKeys, $isProtected = false, $filterFk = '', $styleConfig = [], $adminMode = false, $autoJoin = true, $filenames = []) {
        $files = array_merge([
            'list' => "list_$table.php",
            'search' => "search_$table.php",
            'view' => "view_$table.php"
        ], $filenames);

        $c = "<?php\n";
        if ($isProtected) $c .= "require_once 'protect.php';\n";
        $c .= "require_once 'config.php';\nrequire_once 'fonction.php';\n\n\$pdo = connectbd();\n\n";

        $filterCols = [];
        foreach ($fields as $name => $info) {
            if ($info['is_filter']) {
                $filterCols[] = $name;
                if ($info['is_fk']) {
                    $fkTable = $info['fk_target'];
                    $c .= "\${$fkTable}s = get_all_{$fkTable}(\$pdo);\n";
                }
            }
        }

        $c .= "\n\$items = [];\n\$hasFilter = false;\n";
        $c .= "if (\$_SERVER['REQUEST_METHOD'] === 'GET' && !empty(\$_GET)) {\n";
        $c .= "    \$hasFilter = true;\n";
        
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
        
        $c .= "    \$params = [];\n";
        foreach ($filterCols as $fc) {
            $c .= "    if (!empty(\$_GET['$fc'])) {\n";
            $c .= "        \$sql .= \" AND t.`$fc` = :$fc\";\n";
            $c .= "        \$params[':$fc'] = \$_GET['$fc'];\n";
            $c .= "    }\n";
        }
        
        $c .= "    \$stmt = \$pdo->prepare(\$sql);\n";
        $c .= "    \$stmt->execute(\$params);\n";
        $c .= "    \$items = \$stmt->fetchAll(PDO::FETCH_ASSOC);\n";
        $c .= "}\n?>\n";

        $c .= "<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n";
        $c .= "    <meta charset=\"UTF-8\">\n    <title>Recherche ".ucfirst($table)."</title>\n";
        $c .= "    <link href=\"assets/css/bootstrap.min.css\" rel=\"stylesheet\">\n";
        $c .= "    <link href=\"assets/css/bootstrap-icons.css\" rel=\"stylesheet\">\n";
        $c .= "    <link href=\"style.css\" rel=\"stylesheet\">\n</head>\n<body class=\"bg-light\">\n";
        $c .= "<div class=\"container mt-5\">\n    <div class=\"card shadow-sm mb-4\">\n";
        $c .= "        <div class=\"card-header bg-primary text-white\">\n            <h4 class=\"mb-0\"><i class=\"bi bi-search\"></i> Recherche de ".ucfirst($table)."</h4>\n        </div>\n";
        $c .= "        <div class=\"card-body bg-white\">\n            <form method=\"GET\" class=\"row g-3 items-center\">\n";
        
        foreach ($filterCols as $fc) {
            $info = $fields[$fc];
            $c .= "                <div class=\"col-md-4\">\n                    <label class=\"form-label fw-bold\">".htmlspecialchars($info['label'])."</label>\n";
            $c .= "                    <select name=\"$fc\" class=\"form-select\">\n                        <option value=\"\">-- Choisir --</option>\n";
            if ($info['is_fk']) {
                $fkTable = $info['fk_target'];
                $fkDisplay = $info['fk_display'];
                $fkCol = $info['fk_col'];
                $c .= "                        <?php foreach (\${$fkTable}s as \$f): ?>\n";
                $c .= "                        <option value=\"<?= \$f['$fkCol'] ?>\" <?= (isset(\$_GET['$fc']) && \$_GET['$fc'] == \$f['$fkCol']) ? 'selected' : '' ?>><?= htmlspecialchars(\$f['$fkDisplay']) ?></option>\n";
                $c .= "                        <?php endforeach; ?>\n";
            } else {
                $c .= "                        <?php\n";
                $c .= "                        \$stmtD = \$pdo->query(\"SELECT DISTINCT `$fc` FROM `$table` WHERE `$fc` IS NOT NULL AND `$fc` != '' ORDER BY `$fc`\");\n";
                $c .= "                        while (\$rowD = \$stmtD->fetch(PDO::FETCH_ASSOC)): ?>\n";
                $c .= "                            <option value=\"<?= htmlspecialchars(\$rowD['$fc']) ?>\" <?= (isset(\$_GET['$fc']) && \$_GET['$fc'] == \$rowD['$fc']) ? 'selected' : '' ?>><?= htmlspecialchars(\$rowD['$fc']) ?></option>\n";
                $c .= "                        <?php endwhile; ?>\n";
            }
            $c .= "                    </select>\n                </div>\n";
        }
        
        $c .= "                <div class=\"col-md-12 text-end\">\n                    <button type=\"submit\" class=\"btn btn-primary fw-bold\"><i class=\"bi bi-funnel-fill\"></i> Afficher les Résultats</button>\n";
        $c .= "                    <a href=\"{$files['search']}\" class=\"btn btn-outline-secondary\">Réinitialiser</a>\n                </div>\n            </form>\n        </div>\n    </div>\n\n";

        $c .= "    <?php if (\$hasFilter): ?>\n";
        $c .= "    <div class=\"card shadow-sm\">\n        <div class=\"card-body p-0\">\n";
        $c .= "            <table class=\"table table-hover mb-0\">\n                <thead class=\"table-light\">\n                    <tr>\n";
        foreach ($fields as $name => $info) if(!$info['is_file']) $c .= "                        <th>".htmlspecialchars($info['label'])."</th>\n";
        $c .= "                        <th>Actions</th>\n                    </tr>\n                </thead>\n                <tbody>\n";
        $c .= "                    <?php foreach (\$items as \$item): ?>\n                    <tr>\n";
        foreach ($fields as $name => $info) {
            if(!$info['is_file']) {
                if ($info['is_fk']) {
                    if ($autoJoin) $c .= "                        <td><?= htmlspecialchars(\$item['{$name}_label'] ?? \$item['$name']) ?></td>\n";
                    else $c .= "                        <td><?= htmlspecialchars(\$item['$name']) ?></td>\n";
                } else {
                    $typeLower = strtolower($info['type'] ?? '');
                    if ($typeLower === 'boolean' || $typeLower === 'tinyint(1)') {
                        $c .= "                        <td>\n";
                        $c .= "                            <?php if (\$item['$name'] !== null): ?>\n";
                        $c .= "                                <span class=\"badge <?= \$item['$name'] ? 'bg-success' : 'bg-secondary' ?>\"><?= \$item['$name'] ? 'Oui' : 'Non' ?></span>\n";
                        $c .= "                            <?php endif; ?>\n";
                        $c .= "                        </td>\n";
                    } else if (strpos($typeLower, 'text') !== false || strpos($typeLower, 'blob') !== false || strpos($typeLower, 'json') !== false) {
                        $c .= "                        <td>\n";
                        $c .= "                            <?php\n";
                        $c .= "                            \$val = \$item['$name'];\n";
                        $c .= "                            echo (mb_strlen(\$val) > 50) ? htmlspecialchars(mb_substr(\$val, 0, 50)) . '...' : htmlspecialchars(\$val);\n";
                        $c .= "                            ?>\n";
                        $c .= "                        </td>\n";
                    } else {
                        $c .= "                        <td><?= htmlspecialchars(\$item['$name']) ?></td>\n";
                    }
                }
            }
        }
        $c .= "                        <td><a href=\"{$files['view']}?{$primaryKey}=<?= \$item['$primaryKey'] ?>\" class=\"btn btn-sm btn-info text-white\"><i class=\"bi bi-eye\"></i></a></td>\n                    </tr>\n                    <?php endforeach; ?>\n";
        $c .= "                    <?php if (empty(\$items)): ?>\n                    <tr><td colspan=\"100\" class=\"text-center py-4 text-muted\">Aucun résultat correspondant à votre recherche.</td></tr>\n                    <?php endif; ?>\n                </tbody>\n            </table>\n        </div>\n    </div>\n";
        $c .= "    <?php else: ?>\n";
        $c .= "    <div class=\"alert alert-info text-center py-5\"><i class=\"bi bi-info-circle fs-2 d-block mb-3\"></i> Veuillez choisir au moins un filtre ci-dessus pour lancer la recherche.</div>\n";
        $c .= "    <?php endif; ?>\n</div>\n</body>\n</html>";
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
