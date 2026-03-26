<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AIO Pro - Universal Layout Builder</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/highlight-github.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .app-header { background: linear-gradient(135deg, #0d6efd, #0dcaf0); color: white; padding: 2rem 0; margin-bottom: 2rem; border-bottom: 5px solid #0b5ed7; }
        .setup-card { border: none; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 2rem; background: white; }
        .code-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; position: relative; }
        .btn-copy { position: absolute; top: 10px; right: 10px; z-index: 10; }
        .code-box pre { margin: 0; padding: 20px; font-size: 1rem; }
        .nav-tabs .nav-link { font-weight: 600; color: #6c757d; }
        .nav-tabs .nav-link.active { color: #0d6efd; border-top: 3px solid #0d6efd; }
    </style>
</head>
<body>

<div class="app-header text-center shadow-sm">
    <h1><i class="bi bi-layout-text-window-reverse"></i> Universal Layout Builder</h1>
    <p class="mb-0">Génère automatiquement `index`, `menu`, et `info` selon n'importe quelle contrainte d'examen.</p>
</div>

<div class="container pb-5">
    <a href="index.php" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-house"></i> Retour à l'accueil</a>
    
    <!-- GESTION DE LA CIBLE EXTERNE -->
    <div class="alert alert-secondary d-flex justify-content-between align-items-center mb-4 border border-secondary shadow-sm">
        <div>
            <strong><i class="bi bi-folder-check text-primary"></i> Dossier Cible :</strong>
            <span id="target-dir-display" class="font-monospace ms-2 bg-white px-2 py-1 rounded border">Non défini (Dossier AIO utilisé)</span>
        </div>
        <a href="index.php" class="btn btn-sm btn-light border">Changer de Cible</a>
    </div>

    <div class="row">
        <!-- 1. ARCHITECTURE -->
        <div class="col-lg-6 mb-4">
            <div class="card setup-card h-100 border-primary">
                <div class="card-body">
                    <h4 class="card-title text-primary"><i class="bi bi-diagram-3"></i> 1. Architecture Globale</h4>
                    <p class="text-muted small">Choisissez la structure d'accueil de la SPA (Single Page App feeling).</p>
                    
                    <div class="mb-4 mt-4">
                        <select id="sel-archi" class="form-select form-select-lg border-primary shadow-sm" style="font-weight:bold;">
                            <option value="frameset">Frameset Classique (Vieil Examen)</option>
                            <option value="iframe">Dashboard Moderne (Iframe & Sidebar)</option>
                            <option value="php_include">Include PHP Centralisé (Sidebar Gauche)</option>
                            <option value="navbar_php">Include PHP avec Navbar Haut (Examen Moderne)</option>
                        </select>
                    </div>
                    
                    <div id="desc-archi" class="alert alert-info small mt-3">
                        <strong>Frameset :</strong> Utilise les balises <code>&lt;frameset&gt;</code> HTML4. Solution 100% robuste pour isoler les pages. Menu à gauche, vue à droite.
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. MENU -->
        <div class="col-lg-6 mb-4">
            <div class="card setup-card h-100 border-success">
                <div class="card-body">
                    <h4 class="card-title text-success"><i class="bi bi-list-nested"></i> 2. Menu de Navigation</h4>
                    <p class="text-muted small">Comment le fichier `menu.php/html` doit-il lister les liens ?</p>
                    
                    <div class="mb-3">
                        <select id="sel-menu" class="form-select shadow-sm" style="font-weight:bold; color:#198754">
                            <option value="static">Menu Statique (Manuel)</option>
                            <option value="dynamic">Menu Dynamique (Lié à une BD)</option>
                        </select>
                    </div>

                    <!-- BLOC STATIQUE -->
                    <div id="bloc-static" class="mt-3">
                        <label class="form-label fw-bold small text-muted">Liens Manuels</label>
                        <div id="links-container" style="max-height: 180px; overflow-y:auto; overflow-x:hidden"></div>
                        <button id="btn-add-link" class="btn btn-outline-success btn-sm mt-3 fw-bold w-100"><i class="bi bi-plus"></i> Insérer un lien</button>
                    </div>

                    <!-- BLOC DYNAMIQUE -->
                    <div id="bloc-dynamic" class="d-none mt-3">
                        <div id="db-alert" class="alert alert-danger small py-2"><i class="bi bi-exclamation-triangle"></i> Requis : Connectez-vous à la Base de Données (Bloc inférieur)</div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Boucler sur quelle Table ?</label>
                            <select id="dyn-table" class="form-select form-select-sm" disabled></select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Libellé du lien (Texte)</label>
                                <select id="dyn-label" class="form-select form-select-sm" disabled></select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Valeur de l'ID (URL GET)</label>
                                <select id="dyn-id" class="form-select form-select-sm" disabled></select>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Page cible avec paramètre (ex: list.php)</label>
                            <input type="text" id="dyn-url" class="form-control form-control-sm" value="list.php">
                            <div class="form-text mt-1 text-success" style="font-size:12px" id="url-preview">Aperçu : <code>list.php?id=X</code></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BD CONNECTION -->
    <div class="card setup-card border-warning mb-4 shadow" id="db-card" style="display:none; transition: all 0.3s">
        <div class="card-body bg-light">
            <h5 class="card-title text-warning text-dark"><i class="bi bi-database-fill-gear"></i> Paramètres Base de Données (Pour Menu Dynamique)</h5>
            <div class="row g-2 mt-3">
                <div class="col-md-3"><input type="text" id="db-host" class="form-control" placeholder="Host (localhost)"></div>
                <div class="col-md-3"><input type="text" id="db-user" class="form-control" placeholder="User (root)"></div>
                <div class="col-md-2"><input type="password" id="db-pass" class="form-control" placeholder="Pass (vide)"></div>
                <div class="col-md-3"><input type="text" id="db-name" class="form-control" placeholder="Nom de la base requise"></div>
                <div class="col-md-1"><button id="btn-connect" class="btn btn-warning w-100 fw-bold"><i class="bi bi-plug"></i></button></div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5 mb-5">
        <button id="btn-generate" class="btn btn-primary btn-lg fw-bold px-5 py-3 shadow w-75" style="border-radius: 50px; font-size:1.2rem;">
            <i class="bi bi-rocket-fill"></i> GÉNÉRER TOUS LES FICHIERS DE L'ARCHITECTURE
        </button>
    </div>

    <!-- RÉSULTAT CODE -->
    <div id="step-result" class="d-none mt-5">
        <h3 class="mb-3 text-center"><i class="bi bi-check-circle-fill text-success"></i> Succès : Fichiers créés dans votre projet</h3>
        
        <ul class="nav nav-tabs justify-content-center" id="codeTab" role="tablist">
            <li class="nav-item d-none" role="presentation" id="li-tab-index">
                <button class="nav-link active" id="index-tab" data-bs-toggle="tab" data-bs-target="#index-pane" type="button"><i class="bi bi-house"></i> <span id="label-index">index.html</span></button>
            </li>
            <li class="nav-item" role="presentation" id="li-tab-menu">
                <button class="nav-link" id="menu-tab" data-bs-toggle="tab" data-bs-target="#menu-pane" type="button"><i class="bi bi-list"></i> <span id="label-menu">menu.html</span></button>
            </li>
            <li class="nav-item" role="presentation" id="li-tab-info">
                <button class="nav-link" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-pane" type="button"><i class="bi bi-file-text"></i> info.html</button>
            </li>
        </ul>
        
        <div class="tab-content border border-top-0 rounded-bottom bg-white shadow-sm" id="codeTabContent">
            <div class="tab-pane fade show active p-3" id="index-pane">
                <div class="code-box">
                    <pre><code class="language-php" id="code-index"></code></pre>
                </div>
            </div>
            <div class="tab-pane fade p-3" id="menu-pane">
                <div class="code-box">
                    <pre><code class="language-php" id="code-menu"></code></pre>
                </div>
            </div>
            <div class="tab-pane fade p-3" id="info-pane">
                <div class="code-box">
                    <pre><code class="language-html" id="code-info"></code></pre>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/highlight.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

    const targetDir = localStorage.getItem('aio_target_dir') || '';
    if (targetDir) document.getElementById('target-dir-display').textContent = targetDir;
    
    // Auto-fill DB
    document.getElementById('db-host').value = localStorage.getItem('aio_db_host') || 'localhost';
    document.getElementById('db-user').value = localStorage.getItem('aio_db_user') || 'root';
    document.getElementById('db-pass').value = localStorage.getItem('aio_db_pass') || '';
    document.getElementById('db-name').value = localStorage.getItem('aio_db_name') || '';

    // --- ARCHITECTURE SELECT ---
    const descriptions = {
         'frameset': "<strong>Frameset Classique :</strong> Utilise les balises `<frameset>` HTML4. Solution robuste par défaut pour isoler la navigation du contenu.",
         'iframe': "<strong>Dashboard Moderne :</strong> Design moderne Bootstrap (Sidebar) structuré via une `<iframe>` au centre pour naviguer sans recharger le menu.",
         'php_include': "<strong>Include PHP Centralisé (Sidebar Gauche) :</strong> Architecture avec navigation latérale. Un `index.php` dynamique inclut le menu et charge le contenu via `?page=X`. ⚠️ Vos pages Vues ne doivent plus générer les balises `<html>`.",
         'navbar_php': "<strong>Include PHP avec Navbar Haut :</strong> Architecture classique moderne. Une barre de navigation horizontale Bootstrap en haut de l'écran avec le contenu au centre."
    };
    document.getElementById('sel-archi').addEventListener('change', (e) => {
        document.getElementById('desc-archi').innerHTML = descriptions[e.target.value];
        updateUrlPreview();
    });

    // --- MENU SELECT ---
    document.getElementById('sel-menu').addEventListener('change', (e) => {
        if(e.target.value === 'static') {
            document.getElementById('bloc-static').classList.remove('d-none');
            document.getElementById('bloc-dynamic').classList.add('d-none');
            document.getElementById('db-card').style.display = 'none';
        } else {
            document.getElementById('bloc-static').classList.add('d-none');
            document.getElementById('bloc-dynamic').classList.remove('d-none');
            document.getElementById('db-card').style.display = 'block';
        }
    });

    // --- LINKS (STATIC) ---
    const linksContainer = document.getElementById('links-container');
    function addLinkRow(label = '', url = 'list.php') {
        const div = document.createElement('div');
        div.className = 'row g-2 mb-2 align-items-center link-row';
        div.innerHTML = `
            <div class="col-5"><input type="text" class="form-control form-control-sm i-label" placeholder="Texte (ex: Projets)" value="${label}"></div>
            <div class="col-6"><input type="text" class="form-control form-control-sm i-url" placeholder="Lien (ex: list.php)" value="${url}"></div>
            <div class="col-1 text-end"><button class="btn btn-sm btn-outline-danger w-100" onclick="this.parentElement.parentElement.remove()"><i class="bi bi-x"></i></button></div>
        `;
        linksContainer.appendChild(div);
    }
    document.getElementById('btn-add-link').addEventListener('click', () => addLinkRow());
    addLinkRow('Accueil', 'info.html');
    addLinkRow('Déconnexion', 'logout.php');

    // --- DB DYNAMIC MENU ---
    async function apiDbCall(payload) {
        payload.host = document.getElementById('db-host').value;
        payload.user = document.getElementById('db-user').value;
        payload.pass = document.getElementById('db-pass').value;
        payload.dbname = document.getElementById('db-name').value;
        
        localStorage.setItem('aio_db_host', payload.host);
        localStorage.setItem('aio_db_user', payload.user);
        localStorage.setItem('aio_db_pass', payload.pass);
        localStorage.setItem('aio_db_name', payload.dbname);

        const res = await fetch('api/page_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        return await res.json();
    }

    document.getElementById('btn-connect').addEventListener('click', async () => {
        try {
            const data = await apiDbCall({action: 'connect'});
            if (data.success) {
                document.getElementById('db-alert').className = "alert alert-success small py-2";
                document.getElementById('db-alert').innerHTML = "<i class=\"bi bi-check\"></i> Connecté à la Base de données.";
                
                const sel = document.getElementById('dyn-table');
                sel.innerHTML = '<option value="">Choisir la table...</option>';
                data.tables.forEach(t => sel.innerHTML += `<option value="${t}">${t}</option>`);
                sel.disabled = false;
            } else { alert(data.message); }
        } catch(e) { alert("Erreur de connexion DB"); }
    });

    document.getElementById('dyn-table').addEventListener('change', async (e) => {
        if (!e.target.value) return;
        try {
            const data = await apiDbCall({action: 'fetch_fields_config', table: e.target.value});
            if (data.success) {
                const labelSel = document.getElementById('dyn-label');
                const idSel = document.getElementById('dyn-id');
                labelSel.innerHTML = '';
                idSel.innerHTML = '';
                data.fields.forEach(f => {
                    labelSel.innerHTML += `<option value="${f.name}">${f.name}</option>`;
                    idSel.innerHTML += `<option value="${f.name}">${f.name}</option>`;
                });
                labelSel.disabled = false;
                idSel.disabled = false;
                updateUrlPreview();
            }
        } catch(e) { alert("Erreur chargement colonnes"); }
    });

    function updateUrlPreview() {
        const archi = document.getElementById('sel-archi').value;
        const idCol = document.getElementById('dyn-id').value || 'id';
        const urlStr = document.getElementById('dyn-url').value;
        
        let preview = '';
        if (archi === 'php_include') {
             let c = urlStr.includes('?') ? '&' : '?';
             preview = `index.php?page=${urlStr.replace('.php','')}${c}${idCol}=X`;
        } else {
             let c = urlStr.includes('?') ? '&' : '?';
             preview = `${urlStr}${c}${idCol}=X`;
        }
        document.getElementById('url-preview').innerHTML = `Aperçu final : <code>${preview}</code>`;
    }

    ['dyn-url', 'dyn-id'].forEach(id => document.getElementById(id).addEventListener('input', updateUrlPreview));

    // --- GENERATE ---
    document.getElementById('btn-generate').addEventListener('click', async () => {
        const payload = {
            action: 'generate',
            target_dir: targetDir,
            architecture: document.getElementById('sel-archi').value,
            menu_type: document.getElementById('sel-menu').value
        };

        if (payload.menu_type === 'static') {
            payload.links = [];
            document.querySelectorAll('.link-row').forEach(row => {
                const l = row.querySelector('.i-label').value;
                const u = row.querySelector('.i-url').value;
                if (l && u) payload.links.push({label: l, url: u});
            });
        } else {
            payload.menu_table = document.getElementById('dyn-table').value;
            payload.menu_label = document.getElementById('dyn-label').value;
            payload.menu_id = document.getElementById('dyn-id').value;
            payload.menu_url = document.getElementById('dyn-url').value;

            if(!payload.menu_table || !payload.menu_label || !payload.menu_id) {
                alert("Veuillez configurer complètement les options de Menu Dynamique (BDD) !");
                return;
            }
        }

        const btn = document.getElementById('btn-generate');
        const oldText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> GÉNÉRATION ET SAUVEGARDE EN COURS...';
        btn.disabled = true;

        try {
            const res = await fetch('api/layout_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            
            if (data.success) {
                // UI update
                document.getElementById('step-result').classList.remove('d-none');
                
                // Reset tabs visibility
                ['index', 'menu', 'info'].forEach(id => document.getElementById(`li-tab-${id}`).classList.add('d-none'));
                
                // Populate data
                if (data.codes.index) {
                    document.getElementById('li-tab-index').classList.remove('d-none');
                    document.getElementById('code-index').textContent = data.codes.index;
                    let nm = data.files.find(f => f.startsWith('index'));
                    document.getElementById('label-index').textContent = nm;
                }
                if (data.codes.menu) {
                    document.getElementById('li-tab-menu').classList.remove('d-none');
                    document.getElementById('code-menu').textContent = data.codes.menu;
                    let nm = data.files.find(f => f.startsWith('menu'));
                    document.getElementById('label-menu').textContent = nm;
                }
                if (data.codes.info) {
                    document.getElementById('li-tab-info').classList.remove('d-none');
                    document.getElementById('code-info').textContent = data.codes.info;
                }

                // Highlight.js reset
                sublHighlight('code-index');
                sublHighlight('code-menu');
                sublHighlight('code-info');

                // Auto Select first available tab
                ['index', 'menu', 'info'].some(id => {
                    if (data.codes[id]) {
                        new bootstrap.Tab(document.getElementById(`${id}-tab`)).show();
                        return true;
                    }
                    return false;
                });

                window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
            } else {
                alert(data.message);
            }
        } catch(e) {
            alert("Erreur fatale lors de l'appel au serveur API Layout.");
        } finally {
            btn.innerHTML = oldText;
            btn.disabled = false;
        }
    });

    function sublHighlight(id) {
        const el = document.getElementById(id);
        if(el) {
            delete el.dataset.highlighted;
            hljs.highlightElement(el);
        }
    }

});
</script>
</body>
</html>
