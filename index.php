<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS Builder AIO (All-In-One)</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap-icons.css">
    <style>
        body { 
            background-color: #f8f9fa; 
            font-family: 'Segoe UI', Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .hero {
            background: linear-gradient(135deg, #212529 0%, #343a40 100%);
            color: white;
            padding: 4rem 0 3rem;
            border-bottom: 5px solid #0d6efd;
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            margin-bottom: 3rem;
        }
        .tool-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            height: 100%;
            text-decoration: none;
            color: inherit;
        }
        .tool-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
            color: white;
        }
    </style>
</head>
<body>

<div class="hero text-center shadow-lg position-relative">
    <button class="btn btn-warning position-absolute top-0 end-0 m-3 fw-bold" data-bs-toggle="modal" data-bs-target="#settingsModal">
        <i class="bi bi-gear-fill"></i> Paramètres du Projet Cible
    </button>
    <div class="container pt-4">
        <h1 class="display-4 fw-bold mb-3"><i class="bi bi-rocket-takeoff text-primary"></i> AIO DEV KIT</h1>
        <p class="lead mb-4">La suite ultime pour générer instantanément l'architecture, la logique, les vues et la sécurité de votre projet PHP.</p>
        <div class="badge bg-primary fs-6 py-2 px-3 rounded-pill">Version 2.0 - Examen Ready</div>
        <div id="active-target-badge" class="mt-3 text-warning fw-bold small">Dossier Cible : (Non configuré - Fichiers générés dans AIO/)</div>
    </div>
</div>

<!-- Modal Settings -->
<div class="modal fade" id="settingsModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="bi bi-folder-symlink-fill"></i> Projet Cible Externe</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted mb-4">Configurez ici le dossier réel de votre projet d'examen. Les fichiers générés (ex: style.css, index.html) y seront sauvegardés directement !</p>
        
        <div class="mb-3">
            <label class="form-label fw-bold">Chemin Absolu du Dossier (ex: C:/wamp64/www/MonExam/)</label>
            <input type="text" id="setting-dir" class="form-control" placeholder="Aide : Terminez par un slash /">
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">URL Locale (ex: http://localhost/MonExam/)</label>
            <input type="text" id="setting-url" class="form-control" placeholder="Utilisé pour la prévisualisation du Visual Builder">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary w-100 fw-bold" id="btn-save-settings"><i class="bi bi-check-lg"></i> Sauvegarder</button>
      </div>
    </div>
  </div>
</div>

<div class="container pb-5">
    <div class="row g-4 justify-content-center">
        
        <!-- MODULE 1: DB BUILDER -->
        <div class="col-md-6 col-lg-3">
            <a href="db.php" class="card tool-card shadow-sm text-center d-block">
                <div class="card-body p-4">
                    <div class="icon-circle bg-danger shadow">
                        <i class="bi bi-database-add"></i>
                    </div>
                    <h4 class="card-title fw-bold">1. DB Builder</h4>
                    <span class="badge bg-danger mb-3">db.php</span>
                    <p class="card-text text-muted">Créez visuellement vos tables, colonnes, types, clés primaires/étrangères, et uploadez le SQL !</p>
                </div>
            </a>
        </div>

        <!-- MODULE 2: FUNCTION GENERATOR -->
        <div class="col-md-6 col-lg-3">
            <a href="fonction.php" class="card tool-card shadow-sm text-center d-block">
                <div class="card-body p-4">
                    <div class="icon-circle bg-success shadow">
                        <i class="bi bi-server"></i>
                    </div>
                    <h4 class="card-title fw-bold">2. CRUD Engine</h4>
                    <span class="badge bg-success mb-3">fonction.php</span>
                    <p class="card-text text-muted">Génère automatiquement les fonctions PHP (Create, Read, Update, Delete) avec détection des clés étrangères !</p>
                </div>
            </a>
        </div>

        <!-- MODULE 3: VIEWS GENERATOR -->
        <div class="col-md-6 col-lg-3">
            <a href="page.php" class="card tool-card shadow-sm text-center d-block">
                <div class="card-body p-4">
                    <div class="icon-circle shadow" style="background-color: #6f42c1;">
                        <i class="bi bi-window-stack"></i>
                    </div>
                    <h4 class="card-title fw-bold">3. Interface UI</h4>
                    <span class="badge mb-3" style="background-color: #6f42c1;">page.php</span>
                    <p class="card-text text-muted">Génère les superbes interfaces HTML (List, Create, Edit) avec support d'upload d'images et liaisons dynamiques.</p>
                </div>
            </a>
        </div>

        <!-- MODULE 4: AUTH GENERATOR -->
        <div class="col-md-6 col-lg-3">
            <a href="auth.php" class="card tool-card shadow-sm text-center d-block">
                <div class="card-body p-4">
                    <div class="icon-circle bg-primary shadow">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h4 class="card-title fw-bold">4. Espace Membre</h4>
                    <span class="badge bg-primary mb-3">auth.php</span>
                    <p class="card-text text-muted">Création en 1 clic du module de sécurité : Inscription, Connexion, Déconnexion et verrouillage (Session).</p>
                </div>
            </a>
        </div>

        <!-- MODULE 5: VINTAGE EXAM LAYOUT -->
        <div class="col-md-6 col-lg-3">
            <a href="layout.php" class="card tool-card shadow-sm text-center d-block">
                <div class="card-body p-4">
                    <div class="icon-circle shadow" style="background-color: #fd7e14;">
                        <i class="bi bi-layout-split"></i>
                    </div>
                    <h4 class="card-title fw-bold">5. Examen Vintage</h4>
                    <span class="badge mb-3" style="background-color: #fd7e14;">layout.php</span>
                    <p class="card-text text-muted">Acheminez vos vues avec un système &lt;frameset&gt;, menu de liens et pages statiques traditionnelles.</p>
                </div>
            </a>
        </div>

        <!-- MODULE 6: VISUAL BUILDER -->
        <div class="col-md-6 col-lg-3">
            <a href="builder.php" class="card tool-card shadow-sm text-center d-block" style="border: 2px solid #0dcaf0;">
                <div class="card-body p-4">
                    <div class="icon-circle shadow" style="background-color: #0dcaf0;">
                        <i class="bi bi-palette-fill"></i>
                    </div>
                    <h4 class="card-title fw-bold">6. Visual Builder</h4>
                    <span class="badge mb-3 text-dark" style="background-color: #0dcaf0;">builder.php</span>
                    <p class="card-text text-muted">Éditeur visuel (Hover, Couleurs, Padding, Marges) pour customiser vos pages avec précision !</p>
                </div>
            </a>
        </div>

    </div>

    <div class="mt-5 text-center text-muted">
        <p><small><i class="bi bi-info-circle"></i> Le projet est entièrement fonctionnel **Hors-Ligne**. Tous les assets (Bootstrap 5, Icons) sont hébergés en local.</small></p>
    </div>
</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dirInput = document.getElementById('setting-dir');
    const urlInput = document.getElementById('setting-url');
    const badge = document.getElementById('active-target-badge');

    function loadSettings() {
        const dir = localStorage.getItem('aio_target_dir') || '';
        const url = localStorage.getItem('aio_target_url') || '';
        dirInput.value = dir;
        urlInput.value = url;
        
        if (dir) {
            badge.innerHTML = `<i class="bi bi-folder-check"></i> Cible actuelle : <code>${dir}</code>`;
        } else {
            badge.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Aucun dossier cible configuré. Les fichiers iront dans AIO/.`;
        }
    }

    // Auto-fill URL based on standard WAMP/XAMPP paths
    dirInput.addEventListener('input', () => {
        let path = dirInput.value.replace(/\\/g, '/'); // Normalize slashes
        if (path.toLowerCase().includes('/www/')) {
            let relative = path.substring(path.toLowerCase().indexOf('/www/') + 5);
            urlInput.value = 'http://localhost/' + relative + (relative.endsWith('/') ? '' : '/');
        } else if (path.toLowerCase().includes('/htdocs/')) {
            let relative = path.substring(path.toLowerCase().indexOf('/htdocs/') + 8);
            urlInput.value = 'http://localhost/' + relative + (relative.endsWith('/') ? '' : '/');
        }
    });

    document.getElementById('btn-save-settings').addEventListener('click', () => {
        // Enlever le focus pour résoudre l'erreur ARIA-hidden de Bootstrap
        if (document.activeElement) {
            document.activeElement.blur();
        }

        let dir = dirInput.value.trim();
        let url = urlInput.value.trim();
        
        // Auto-fix paths
        if (dir && !dir.endsWith('/') && !dir.endsWith('\\')) {
            dir += (dir.includes('\\') ? '\\' : '/');
        }
        if (url && !url.endsWith('/')) url += '/';
        
        localStorage.setItem('aio_target_dir', dir);
        localStorage.setItem('aio_target_url', url);
        loadSettings();
        
        const myModal = bootstrap.Modal.getInstance(document.getElementById('settingsModal'));
        if (myModal) myModal.hide();
    });

    loadSettings();
});
</script>
</body>
</html>
