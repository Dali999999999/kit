<?php
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$target_dir = $_GET['target_dir'] ?? $_POST['target_dir'] ?? '';

// Fallback to internal AIO directory if no target dir is provided
// A path shouldn't end solely in slash unless it's the very root, but we trust the user config here.
if (empty($target_dir)) {
    $target_dir = '../';
}

// Clean up trailing slash to standardize
$target_dir = rtrim($target_dir, '/\\') . DIRECTORY_SEPARATOR;

if ($action === 'list_files') {
    $files = [];
    if (!is_dir($target_dir)) {
        echo json_encode(['success' => false, 'message' => "Le dossier cible n'existe pas : " . $target_dir]);
        exit;
    }
    
    $dir = opendir($target_dir);
    if ($dir) {
        while (($file = readdir($dir)) !== false) {
            // Ignore AIO builder files if we are exploring the AIO directory
            $isAioSystemFile = ($target_dir === '../' || $target_dir === '..\\') && in_array($file, ['builder.php', 'index.php', 'page.php', 'db.php', 'fonction.php', 'layout.php', 'auth.php']);
            
            if ($file !== '.' && $file !== '..' && !is_dir($target_dir . $file) && !$isAioSystemFile) {
                if (str_ends_with($file, '.php') || str_ends_with($file, '.html')) {
                    $files[] = $file;
                }
            }
        }
        closedir($dir);
    }
    echo json_encode(['success' => true, 'files' => $files]);
    exit;
}

if ($action === 'load_css') {
    $cssFile = $target_dir . 'style.css';
    $content = '';
    if (file_exists($cssFile)) {
        $content = file_get_contents($cssFile);
    }
    echo json_encode(['success' => true, 'css' => $content]);
    exit;
}

if ($action === 'save_css') {
    $cssContent = $_POST['css'] ?? '';
    
    if (!is_dir($target_dir)) {
        echo json_encode(['success' => false, 'message' => "Le dossier cible n'existe pas : " . $target_dir]);
        exit;
    }
    
    if (file_put_contents($target_dir . 'style.css', $cssContent) !== false) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => "Erreur d'écriture dans style.css. Vérifiez les permissions."]);
    }
    exit;
}

if ($action === 'load_css') {
    if (file_exists($target_dir . 'style.css')) {
        echo json_encode(['success' => true, 'css' => file_get_contents($target_dir . 'style.css')]);
    } else {
        echo json_encode(['success' => true, 'css' => '']); // Pas d'erreur si fichier non existant
    }
    exit;
}

if ($action === 'replace_text') {
    $file = $_POST['file'] ?? '';
    $old_text = $_POST['old_text'] ?? '';
    $new_text = $_POST['new_text'] ?? '';

    if (empty($file) || empty($old_text)) {
        echo json_encode(['success' => false, 'message' => "Données manquantes pour le remplacement."]);
        exit;
    }

    $filepath = $target_dir . basename($file);
    if (!file_exists($filepath)) {
        echo json_encode(['success' => false, 'message' => "Le fichier cible n'existe pas."]);
        exit;
    }

    $content = file_get_contents($filepath);
    
    // Remplacement strict et sécurisé.
    // Si $old_text n'est pas trouvé (ex: c'était une balise PHP <?php echo $var; ), ça ne fera rien !
    $updated_content = str_replace($old_text, $new_text, $content);

    if ($updated_content === $content) {
        echo json_encode(['success' => false, 'message' => "Impossible de modifier ce texte. Il s'agit probablement d'une donnée dynamique générée par PHP."]);
        exit;
    }

    if (file_put_contents($filepath, $updated_content) !== false) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => "Erreur de sauvegarde PHP."]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action invalide']);
exit;
