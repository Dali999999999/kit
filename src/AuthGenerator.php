<?php
namespace AIO\Src;

class AuthGenerator {
    public static function generateAuthFiles($table, $idCol, $passCol, $nameCol, $pk) {
        // --- 1. logout.php ---
        $logoutCode = "<?php\nsession_start();\nsession_destroy();\nheader('Location: login.php');\nexit;\n?>";

        // --- 2. protect.php ---
        $protectCode = "<?php\nsession_start();\nif (!isset(\$_SESSION['user_id'])) {\n    header('Location: login.php');\n    exit;\n}\n?>";

        // --- 3. login.php ---
        $loginCode = "<?php\nsession_start();\nrequire_once 'config.php';\n\n";
        $loginCode .= "\$error = '';\n";
        $loginCode .= "if (\$_SERVER['REQUEST_METHOD'] === 'POST') {\n";
        $loginCode .= "    \$pdo = connectbd();\n";
        $loginCode .= "    \$identifiant = \$_POST['identifiant'] ?? '';\n";
        $loginCode .= "    \$mot_de_passe = \$_POST['mot_de_passe'] ?? '';\n\n";
        $loginCode .= "    \$stmt = \$pdo->prepare(\"SELECT * FROM `$table` WHERE `$idCol` = :id LIMIT 1\");\n";
        $loginCode .= "    \$stmt->execute(['id' => \$identifiant]);\n";
        $loginCode .= "    \$user = \$stmt->fetch();\n\n";
        $loginCode .= "    if (\$user && password_verify(\$mot_de_passe, \$user['$passCol'])) {\n";
        $loginCode .= "        \$_SESSION['user_id'] = \$user['$pk'];\n";
        if ($nameCol) {
            $loginCode .= "        \$_SESSION['user_name'] = \$user['$nameCol'];\n";
        }
        $loginCode .= "        header('Location: index.php'); // Redirection après connexion\n";
        $loginCode .= "        exit;\n";
        $loginCode .= "    } else {\n";
        $loginCode .= "        \$error = 'Identifiants incorrects.';\n";
        $loginCode .= "    }\n";
        $loginCode .= "}\n?>\n\n";
        $loginCode .= "<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n";
        $loginCode .= "    <meta charset=\"UTF-8\">\n    <title>Connexion</title>\n";
        $loginCode .= "    <link href=\"assets/css/bootstrap.min.css\" rel=\"stylesheet\">\n</head>\n";
        $loginCode .= "<body class=\"bg-light d-flex align-items-center justify-content-center\" style=\"height: 100vh;\">\n";
        $loginCode .= "    <div class=\"card shadow p-4\" style=\"width: 400px; border-top: 5px solid #0d6efd;\">\n";
        $loginCode .= "        <h3 class=\"text-center mb-4\">Connexion</h3>\n";
        $loginCode .= "        <?php if (\$error): ?>\n            <div class=\"alert alert-danger\"><?= \$error ?></div>\n        <?php endif; ?>\n";
        $loginCode .= "        <form method=\"POST\">\n";
        $loginCode .= "            <div class=\"mb-3\">\n                <label>Identifiant ($idCol)</label>\n                <input type=\"text\" name=\"identifiant\" class=\"form-control\" required>\n            </div>\n";
        $loginCode .= "            <div class=\"mb-3\">\n                <label>Mot de passe</label>\n                <input type=\"password\" name=\"mot_de_passe\" class=\"form-control\" required>\n            </div>\n";
        $loginCode .= "            <button type=\"submit\" class=\"btn btn-primary w-100\">Se connecter</button>\n";
        $loginCode .= "        </form>\n";
        $loginCode .= "        <div class=\"text-center mt-3\">\n            <a href=\"register.php\" class=\"text-decoration-none\">Pas encore de compte ? S'inscrire</a>\n        </div>\n";
        $loginCode .= "    </div>\n</body>\n</html>";

        // --- 4. register.php ---
        $registerCode = "<?php\nsession_start();\nrequire_once 'config.php';\nrequire_once 'fonction.php';\n\n";
        $registerCode .= "\$error = '';\n\$success = '';\n";
        $registerCode .= "if (\$_SERVER['REQUEST_METHOD'] === 'POST') {\n";
        $registerCode .= "    \$pdo = connectbd();\n";
        $registerCode .= "    \$identifiant = \$_POST['identifiant'] ?? '';\n";
        $registerCode .= "    \$mot_de_passe = \$_POST['mot_de_passe'] ?? '';\n";
        if ($nameCol) {
            $registerCode .= "    \$nom = \$_POST['nom'] ?? '';\n";
        }
        $registerCode .= "\n    // Vérifier si l'identifiant existe déjà\n";
        $registerCode .= "    \$stmt = \$pdo->prepare(\"SELECT `$pk` FROM `$table` WHERE `$idCol` = :id\");\n";
        $registerCode .= "    \$stmt->execute(['id' => \$identifiant]);\n";
        $registerCode .= "    if (\$stmt->fetch()) {\n";
        $registerCode .= "        \$error = 'Cet identifiant est déjà utilisé.';\n";
        $registerCode .= "    } else {\n";
        $registerCode .= "        \$hashed_password = password_hash(\$mot_de_passe, PASSWORD_DEFAULT);\n";
        
        if ($nameCol) {
            $registerCode .= "        \$stmt = \$pdo->prepare(\"INSERT INTO `$table` (`$idCol`, `$passCol`, `$nameCol`) VALUES (:id, :pass, :nom)\");\n";
            $registerCode .= "        \$stmt->execute(['id' => \$identifiant, 'pass' => \$hashed_password, 'nom' => \$nom]);\n";
        } else {
            $registerCode .= "        \$stmt = \$pdo->prepare(\"INSERT INTO `$table` (`$idCol`, `$passCol`) VALUES (:id, :pass)\");\n";
            $registerCode .= "        \$stmt->execute(['id' => \$identifiant, 'pass' => \$hashed_password]);\n";
        }
        
        $registerCode .= "        \$success = 'Compte créé avec succès ! <a href=\"login.php\">Connectez-vous</a>';\n";
        $registerCode .= "    }\n";
        $registerCode .= "}\n?>\n\n";
        
        $registerCode .= "<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n";
        $registerCode .= "    <meta charset=\"UTF-8\">\n    <title>Inscription</title>\n";
        $registerCode .= "    <link href=\"assets/css/bootstrap.min.css\" rel=\"stylesheet\">\n</head>\n";
        $registerCode .= "<body class=\"bg-light d-flex align-items-center justify-content-center\" style=\"height: 100vh;\">\n";
        $registerCode .= "    <div class=\"card shadow p-4\" style=\"width: 400px; border-top: 5px solid #198754;\">\n";
        $registerCode .= "        <h3 class=\"text-center mb-4\">Créer un compte</h3>\n";
        $registerCode .= "        <?php if (\$error): ?>\n            <div class=\"alert alert-danger\"><?= \$error ?></div>\n        <?php endif; ?>\n";
        $registerCode .= "        <?php if (\$success): ?>\n            <div class=\"alert alert-success\"><?= \$success ?></div>\n        <?php endif; ?>\n";
        $registerCode .= "        <form method=\"POST\">\n";
        if ($nameCol) {
            $registerCode .= "            <div class=\"mb-3\">\n                <label>Nom Complet ($nameCol)</label>\n                <input type=\"text\" name=\"nom\" class=\"form-control\" required>\n            </div>\n";
        }
        $registerCode .= "            <div class=\"mb-3\">\n                <label>Identifiant ($idCol)</label>\n                <input type=\"text\" name=\"identifiant\" class=\"form-control\" required>\n            </div>\n";
        $registerCode .= "            <div class=\"mb-3\">\n                <label>Mot de passe</label>\n                <input type=\"password\" name=\"mot_de_passe\" class=\"form-control\" required>\n            </div>\n";
        $registerCode .= "            <button type=\"submit\" class=\"btn btn-success w-100\">S'inscrire</button>\n";
        $registerCode .= "        </form>\n";
        $registerCode .= "        <div class=\"text-center mt-3\">\n            <a href=\"login.php\" class=\"text-secondary text-decoration-none\">Déjà un compte ? Connexion</a>\n        </div>\n";
        $registerCode .= "    </div>\n</body>\n</html>";

        return [
            'login_code' => $loginCode,
            'register_code' => $registerCode,
            'logout_code' => $logoutCode,
            'protect_code' => $protectCode
        ];
    }
}
