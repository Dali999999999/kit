<?php
namespace AIO\Src;

use \Exception;
use \PDO;

class LayoutGenerator {

    /**
     * 1. Architecture: Frameset Classique (Vintage)
     */
    public static function generateFrameset($ratio = '20%, 80%', $menuExt = 'php') {
        $c = "<!DOCTYPE html>\n<html>\n<head>\n    <title>Application (Frameset)</title>\n</head>\n";
        $c .= "<frameset cols=\"$ratio\">\n";
        $c .= "    <frame src=\"menu.$menuExt\" name=\"menu\" id=\"menu\">\n";
        $c .= "    <frame src=\"info.html\" name=\"main\" id=\"main\">\n";
        $c .= "</frameset>\n";
        $c .= "</html>";
        return $c;
    }

    /**
     * 2. Architecture: Dashboard Iframe (Moderne)
     */
    public static function generateDashboardIframe($menuExt = 'php') {
        $c = "<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n";
        $c .= "    <meta charset=\"UTF-8\">\n    <title>Application Dashboard</title>\n";
        $c .= "    <link href=\"assets/css/bootstrap.min.css\" rel=\"stylesheet\">\n";
        $c .= "    <style>\n";
        $c .= "        body { margin:0; overflow:hidden; }\n";
        $c .= "        .sidebar { height: 100vh; background: #212529; color: white; overflow-y:auto; border-right: 1px solid #444; }\n";
        $c .= "        .main-frame { height: 100vh; width: 100%; border: none; background: white; }\n";
        $c .= "    </style>\n";
        $c .= "</head>\n<body>\n";
        $c .= "<div class=\"d-flex\">\n";
        $c .= "    <div class=\"sidebar\" style=\"width: 250px; min-width: 250px;\">\n";
        $c .= "        <iframe src=\"menu.$menuExt\" style=\"width:100%; height:100%; border:none;\"></iframe>\n";
        $c .= "    </div>\n";
        $c .= "    <div class=\"flex-grow-1\">\n";
        $c .= "        <iframe src=\"info.html\" name=\"main\" id=\"main\" class=\"main-frame\"></iframe>\n";
        $c .= "    </div>\n";
        $c .= "</div>\n</body>\n</html>";
        return $c;
    }

    /**
     * 3. Architecture: PHP Include Centralisé (index.php?page=...)
     */
    public static function generatePhpIncludeCentralise($menuExt = 'php') {
        $c = "<?php\n";
        $c .= " // --- ARCHITECTURE CENTRALISÉE (EXAMEN) ---\n";
        $c .= " // Ce modèle est très classique en évaluation PHP.\n";
        $c .= " // Attention : Vos pages (list_X, create_X) doivent supprimer leurs balises <html> car index.php s'en charge.\n";
        $c .= " \$page = \$_GET['page'] ?? 'info';\n";
        $c .= "?>\n";
        $c .= "<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n";
        $c .= "    <meta charset=\"UTF-8\">\n    <title>Application Globale</title>\n";
        $c .= "    <link href=\"assets/css/bootstrap.min.css\" rel=\"stylesheet\">\n";
        $c .= "    <link href=\"style.css\" rel=\"stylesheet\">\n";
        $c .= "    <style>.sidebar { min-height: 100vh; background: #212529; color: white; border-right: 1px solid #444; }</style>\n";
        $c .= "</head>\n<body class=\"bg-light\">\n";
        $c .= "<div class=\"container-fluid\">\n";
        $c .= "    <div class=\"row\">\n";
        $c .= "        <!-- Menu Latéral -->\n";
        $c .= "        <div class=\"col-md-2 sidebar p-4\">\n";
        $c .= "            <?php include 'menu.$menuExt'; ?>\n";
        $c .= "        </div>\n";
        $c .= "        <!-- Contenu Central -->\n";
        $c .= "        <div class=\"col-md-10 py-4 px-5\">\n";
        $c .= "            <?php\n";
        $c .= "            \$file = \$page . '.php';\n";
        $c .= "            if (\$page === 'info') \$file = 'info.html';\n";
        $c .= "            if (file_exists(\$file)) {\n";
        $c .= "                include \$file;\n";
        $c .= "            } else {\n";
        $c .= "                echo \"<div class='alert alert-danger'>La page demandée n'existe pas ou n'a pas été générée.</div>\";\n";
        $c .= "            }\n";
        $c .= "            ?>\n";
        $c .= "        </div>\n";
        $c .= "    </div>\n";
        $c .= "</div>\n</body>\n</html>";
        return $c;
    }

    /**
     * 4. Architecture: PHP Include Centralisé avec Navbar Bootstrap en haut
     */
    public static function generatePhpIncludeNavbar($menuExt = 'php') {
        $c = "<?php\n";
        $c .= " // --- ARCHITECTURE NAVBAR CENTRALISÉE ---\n";
        $c .= " // Modèle moderne avec menu horizontal en haut.\n";
        $c .= " // Vos pages (list_X, create_X) doivent supprimer leurs balises <html> car index.php s'en charge.\n";
        $c .= " \$page = \$_GET['page'] ?? 'info';\n";
        $c .= "?>\n";
        $c .= "<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n";
        $c .= "    <meta charset=\"UTF-8\">\n    <title>Application Globale</title>\n";
        $c .= "    <link href=\"assets/css/bootstrap.min.css\" rel=\"stylesheet\">\n";
        $c .= "    <link href=\"style.css\" rel=\"stylesheet\">\n";
        $c .= "</head>\n<body class=\"bg-light\">\n";
        $c .= "    <?php include 'menu.$menuExt'; ?>\n";
        $c .= "    <div class=\"container py-4\">\n";
        $c .= "        <div class=\"bg-white p-4 shadow-sm rounded\">\n";
        $c .= "        <?php\n";
        $c .= "        \$file = \$page . '.php';\n";
        $c .= "        if (\$page === 'info') \$file = 'info.html';\n";
        $c .= "        if (file_exists(\$file)) {\n";
        $c .= "            include \$file;\n";
        $c .= "        } else {\n";
        $c .= "            echo \"<div class='alert alert-danger'>La page demandée n'existe pas ou n'a pas été générée.</div>\";\n";
        $c .= "        }\n";
        $c .= "        ?>\n";
        $c .= "        </div>\n";
        $c .= "    </div>\n";
        $c .= "    <script src=\"assets/js/bootstrap.bundle.min.js\"></script>\n";
        $c .= "</body>\n</html>";
        return $c;
    }

    /**
     * Menu Statique
     */
    public static function generateStaticMenu($links, $targetAttr = 'main', $standalone = true, $isPhp = true, $isNavbar = false) {
        $c = "";
        if ($standalone) {
            $c .= $isPhp ? "<?php\n?>\n" : "";
            $c .= "<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n";
            $c .= "    <meta charset=\"UTF-8\">\n    <title>Menu</title>\n";
            $c .= "    <link href=\"assets/css/bootstrap.min.css\" rel=\"stylesheet\">\n";
            if (!$isNavbar) {
                $c .= "    <style>\n";
                $c .= "        body { background:#212529; color:white; padding:15px; }\n";
                $c .= "        a.nav-link { color:#adb5bd; padding:12px; border-bottom:1px solid #343a40; transition: 0.2s; }\n";
                $c .= "        a.nav-link:hover { color:white; background:#343a40; border-radius: 5px; }\n";
                $c .= "    </style>\n";
            }
            $c .= "</head>\n<body>\n";
        }
        
        $tgt =  !empty($targetAttr) ? " target=\"$targetAttr\"" : "";

        if ($isNavbar) {
            $c .= "    <nav class=\"navbar navbar-expand-lg navbar-dark bg-dark mb-4\">\n";
            $c .= "      <div class=\"container-fluid\">\n";
            $c .= "        <a class=\"navbar-brand fw-bold\" href=\"#\">Mon Application</a>\n";
            $c .= "        <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarNav\">\n";
            $c .= "          <span class=\"navbar-toggler-icon\"></span>\n";
            $c .= "        </button>\n";
            $c .= "        <div class=\"collapse navbar-collapse\" id=\"navbarNav\">\n";
            $c .= "          <ul class=\"navbar-nav me-auto\">\n";
            foreach ($links as $l) {
                $c .= "            <li class=\"nav-item\"><a class=\"nav-link\" href=\"{$l['url']}\"{$tgt}>{$l['label']}</a></li>\n";
            }
            $c .= "          </ul>\n";
            $c .= "        </div>\n";
            $c .= "      </div>\n";
            $c .= "    </nav>\n";
        } else {
            $c .= "    <h4 class=\"text-center mb-4 text-white fw-bold\">Menu</h4>\n    <ul class=\"nav flex-column\">\n";
            foreach ($links as $l) {
                $c .= "        <li class=\"nav-item\"><a class=\"nav-link\" href=\"{$l['url']}\"{$tgt}>{$l['label']}</a></li>\n";
            }
            $c .= "    </ul>\n";
        }

        if ($standalone) {
            $c .= "</body>\n</html>";
        }
        return $c;
    }

    /**
     * Menu Dynamique (Base de données)
     */
    public static function generateDynamicMenu($table, $labelCol, $idCol, $targetUrl, $targetAttr = 'main', $standalone = true, $isNavbar = false) {
        $c = "<?php\n";
        $c .= "require_once 'config.php';\n";
        $c .= "try {\n";
        $c .= "    \$pdo = connectbd();\n";
        $c .= "    \$stmt = \$pdo->query(\"SELECT `$idCol`, `$labelCol` FROM `$table` ORDER BY `$labelCol` ASC\");\n";
        $c .= "    \$items = \$stmt->fetchAll(PDO::FETCH_ASSOC);\n";
        $c .= "} catch (Exception \$e) {\n";
        $c .= "    die(\"Erreur Menu Dynamique : \" . \$e->getMessage());\n";
        $c .= "}\n";
        $c .= "?>\n";

        if ($standalone) {
            $c .= "<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n";
            $c .= "    <meta charset=\"UTF-8\">\n    <title>Menu</title>\n";
            $c .= "    <link href=\"assets/css/bootstrap.min.css\" rel=\"stylesheet\">\n";
            if (!$isNavbar) {
                $c .= "    <style>\n";
                $c .= "        body { background:#212529; color:white; padding:15px; }\n";
                $c .= "        a.nav-link { color:#adb5bd; padding:12px; border-bottom:1px solid #343a40; transition: 0.2s; }\n";
                $c .= "        a.nav-link:hover { color:white; background:#343a40; border-radius: 5px; }\n";
                $c .= "    </style>\n";
            }
            $c .= "</head>\n<body>\n";
        }

        $tgt = !empty($targetAttr) ? " target=\"$targetAttr\"" : "";

        if ($isNavbar) {
            $c .= "    <nav class=\"navbar navbar-expand-lg navbar-dark bg-dark mb-4\">\n";
            $c .= "      <div class=\"container-fluid\">\n";
            $c .= "        <a class=\"navbar-brand fw-bold\" href=\"#\">Mon Application</a>\n";
            $c .= "        <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarNav\">\n";
            $c .= "          <span class=\"navbar-toggler-icon\"></span>\n";
            $c .= "        </button>\n";
            $c .= "        <div class=\"collapse navbar-collapse\" id=\"navbarNav\">\n";
            $c .= "          <ul class=\"navbar-nav me-auto\">\n";
            $c .= "          <?php foreach (\$items as \$i): ?>\n";
            if (strpos($targetUrl, '?') !== false) {
                $c .= "            <?php \$url = \"{$targetUrl}&\" . urlencode(\"{$idCol}\") . \"=\" . urlencode(\$i['$idCol']); ?>\n";
            } else {
                $c .= "            <?php \$url = \"{$targetUrl}?\" . urlencode(\"{$idCol}\") . \"=\" . urlencode(\$i['$idCol']); ?>\n";
            }
            $c .= "            <li class=\"nav-item\"><a class=\"nav-link\" href=\"<?= \$url ?>\"{$tgt}><?= htmlspecialchars(\$i['$labelCol']) ?></a></li>\n";
            $c .= "          <?php endforeach; ?>\n";
            $c .= "          </ul>\n";
            $c .= "        </div>\n";
            $c .= "      </div>\n";
            $c .= "    </nav>\n";
        } else {
            $c .= "    <h4 class=\"text-center mb-4 text-white fw-bold\">Rubriques</h4>\n    <ul class=\"nav flex-column\">\n";
            $c .= "    <?php foreach (\$items as \$i): ?>\n";
            if (strpos($targetUrl, '?') !== false) {
                $c .= "        <?php \$url = \"{$targetUrl}&\" . urlencode(\"{$idCol}\") . \"=\" . urlencode(\$i['$idCol']); ?>\n";
            } else {
                $c .= "        <?php \$url = \"{$targetUrl}?\" . urlencode(\"{$idCol}\") . \"=\" . urlencode(\$i['$idCol']); ?>\n";
            }
            $c .= "        <li class=\"nav-item\"><a class=\"nav-link\" href=\"<?= \$url ?>\"{$tgt}><?= htmlspecialchars(\$i['$labelCol']) ?></a></li>\n";
            $c .= "    <?php endforeach; ?>\n";
            $c .= "    </ul>\n";
        }

        if ($standalone) {
            $c .= "</body>\n</html>";
        }
        return $c;
    }

    /**
     * Page Info.html de démonstration
     */
    public static function generateInfoHtml() {
        return "<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Accueil</title>\n    <link href=\"assets/css/bootstrap.min.css\" rel=\"stylesheet\">\n</head>\n<body class=\"bg-light d-flex align-items-center justify-content-center\" style=\"height: 100vh;\">\n    <div class=\"text-center\">\n        <h1 class=\"display-4 text-primary fw-bold mb-3\"><i class=\"bi bi-house\"></i> Accueil</h1>\n        <p class=\"lead text-muted\">Sélectionnez un élément dans le menu pour démarrer la navigation.</p>\n    </div>\n</body>\n</html>";
    }
}
