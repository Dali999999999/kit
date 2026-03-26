<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../src/LayoutGenerator.php';
use AIO\Src\LayoutGenerator;

$data = json_decode(file_get_contents('php://input'), true);

if (!$data && !empty($_POST['action'])) {
    $data = $_POST;
}

$action = $data['action'] ?? '';
$target_dir = $data['target_dir'] ?? '../';
$target_dir = rtrim($target_dir, '/\\') . DIRECTORY_SEPARATOR;

if ($action === 'generate') {
    if (!is_dir($target_dir)) {
        echo json_encode(['success' => false, 'message' => "Le dossier cible n'existe pas : " . $target_dir]);
        exit;
    }

    $archi = $data['architecture'] ?? 'frameset';
    $menuType = $data['menu_type'] ?? 'static';
    
    // Variables communes
    $indexHtml = '';
    $indexPhp = '';
    $menuHtml = '';
    $menuPhp = '';
    $infoHtml = LayoutGenerator::generateInfoHtml();
    
    // Paramètres Menu
    $isMenuStandalone = true;
    $menuTarget = 'main';

    // 1. Définir le conteneur principal (Architecture)
    if ($archi === 'frameset') {
        $indexHtml = LayoutGenerator::generateFrameset('20%, 80%', ($menuType === 'dynamic' ? 'php' : 'html'));
    } 
    elseif ($archi === 'iframe') {
        $indexHtml = LayoutGenerator::generateDashboardIframe($menuType === 'dynamic' ? 'php' : 'html');
    }
    elseif ($archi === 'php_include') {
        $indexPhp = LayoutGenerator::generatePhpIncludeCentralise('php');
        $isMenuStandalone = false; 
        $menuTarget = ''; 
    }
    elseif ($archi === 'navbar_php') {
        $indexPhp = LayoutGenerator::generatePhpIncludeNavbar('php');
        $isMenuStandalone = false; 
        $menuTarget = ''; 
    }

    // 2. Générer le Menu
    $isNavbar = ($archi === 'navbar_php');
    if ($menuType === 'static') {
        $links = $data['links'] ?? [];
        $content = LayoutGenerator::generateStaticMenu($links, $menuTarget, $isMenuStandalone, false, $isNavbar);
        if ($archi === 'php_include' || $archi === 'navbar_php') {
            $menuPhp = $content;
        } else {
            $menuHtml = $content;
        }
    } 
    elseif ($menuType === 'dynamic') {
        $table = $data['menu_table'] ?? '';
        $labelCol = $data['menu_label'] ?? '';
        $idCol = $data['menu_id'] ?? '';
        $targetUrl = $data['menu_url'] ?? 'list.php';
        
        $menuPhp = LayoutGenerator::generateDynamicMenu($table, $labelCol, $idCol, $targetUrl, $menuTarget, $isMenuStandalone, $isNavbar);
    }

    // 3. Sauvegarde
    try {
        $resultData = [
            'success' => true,
            'message' => 'L\'architecture a été générée avec succès dans le dossier cible !',
            'files' => [],
            'codes' => []
        ];

        if ($indexHtml) {
            file_put_contents($target_dir . 'index.html', $indexHtml);
            $resultData['files'][] = 'index.html';
            $resultData['codes']['index'] = $indexHtml;
        }
        if ($indexPhp) {
            file_put_contents($target_dir . 'index.php', $indexPhp);
            $resultData['files'][] = 'index.php';
            $resultData['codes']['index'] = $indexPhp;
        }
        if ($menuHtml) {
            file_put_contents($target_dir . 'menu.html', $menuHtml);
            $resultData['files'][] = 'menu.html';
            $resultData['codes']['menu'] = $menuHtml;
        }
        if ($menuPhp) {
            file_put_contents($target_dir . 'menu.php', $menuPhp);
            $resultData['files'][] = 'menu.php';
            $resultData['codes']['menu'] = $menuPhp;
        }
        
        file_put_contents($target_dir . 'info.html', $infoHtml);
        $resultData['files'][] = 'info.html';
        $resultData['codes']['info'] = $infoHtml;

        echo json_encode($resultData);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => "Erreur d'écriture : " . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action inconnue']);
exit;
