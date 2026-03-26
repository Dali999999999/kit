<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Générateur d'Espace Membre (Auth)</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/atom-one-dark.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        .app-header { background: linear-gradient(135deg, #0d6efd, #0dcaf0); color: white; padding: 2rem 0; margin-bottom: 2rem; border-radius: 0 0 20px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .setup-card { border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; border-top: 4px solid #0d6efd; background: #fff;}
        .code-box { position: relative; border-radius: 0 0 10px 10px; overflow: hidden; background: #282c34; border: 1px solid #dee2e6; border-top: none;}
        .code-box pre { margin: 0; padding: 20px; font-size: 1rem; }
    </style>
</head>
<body>

<div class="app-header text-center">
    <h1><i class="bi bi-shield-lock"></i> Authentication Generator</h1>
    <p class="mb-0">Créez votre système de connexion, inscription et déconnexion en 1 clic</p>
</div>

<div class="container pb-5">
    
    <!-- 1. CONNEXION -->
    <div id="step-connection" class="card setup-card border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0"><i class="bi bi-plug"></i> 1. Connexion (BDD)</h5>
                <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-house"></i> Accueil</a>
            </div>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label text-muted small">Host</label><input type="text" id="db-host" class="form-control" value="localhost"></div>
                <div class="col-md-3"><label class="form-label text-muted small">User</label><input type="text" id="db-user" class="form-control" value="root"></div>
                <div class="col-md-3"><label class="form-label text-muted small">Password</label><input type="password" id="db-pass" class="form-control" placeholder="(vide)"></div>
                <div class="col-md-3"><label class="form-label fw-bold text-primary small">Base de données</label><input type="text" id="db-name" class="form-control" placeholder="ex: ma_base"></div>
            </div>
            <button id="btn-connect" class="btn btn-primary mt-3 w-100 fw-bold"><i class="bi bi-database-check"></i> Connecter et lister les tables</button>
        </div>
    </div>

    <!-- 2. CONFIGURATION DE LA TABLE USER -->
    <div id="step-config" class="card setup-card border-0 d-none">
        <div class="card-body">
            <h5 class="card-title mb-3"><i class="bi bi-person-badge"></i> 2. Configuration des Accès</h5>
            <div class="row align-items-end g-3 mb-3">
                <div class="col-md-12">
                    <label class="form-label fw-bold">1. Quelle est votre table Utilisateurs ?</label>
                    <select id="table-select" class="form-select form-select-lg"></select>
                </div>
            </div>
            <div id="fields-config-area" class="row g-3 d-none p-3 bg-light rounded border">
                <div class="col-md-4">
                    <label class="form-label">Colonne "Identifiant" (ex: email, pseudo)</label>
                    <select id="id-col" class="form-select border-primary"></select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Colonne "Mot de passe" (ex: password)</label>
                    <select id="pass-col" class="form-select border-danger"></select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Colonne "Nom complet" <small class="text-muted">(Optionnel)</small></label>
                    <select id="name-col" class="form-select border-success"></select>
                </div>
                <div class="col-md-12 text-end mt-4">
                    <button id="btn-generate" class="btn btn-dark btn-lg fw-bold"><i class="bi bi-magic"></i> Générer l'Espace Membre</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. RESULTAT -->
    <div id="step-result" class="d-none mt-4">
        <h3 class="mb-3"><i class="bi bi-file-earmark-code"></i> 3. Fichiers Systèmes Générés</h3>
        
        <ul class="nav nav-tabs" id="authTab" role="tablist">
            <li class="nav-item"><button class="nav-link active fw-bold text-primary" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-pane" type="button">login.php</button></li>
            <li class="nav-item"><button class="nav-link fw-bold text-success" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-pane" type="button">register.php</button></li>
            <li class="nav-item"><button class="nav-link fw-bold text-danger" id="logout-tab" data-bs-toggle="tab" data-bs-target="#logout-pane" type="button">logout.php</button></li>
            <li class="nav-item"><button class="nav-link fw-bold text-dark" id="protect-tab" data-bs-toggle="tab" data-bs-target="#protect-pane" type="button">protect.php</button></li>
        </ul>
        
        <div class="tab-content">
            <div class="tab-pane fade show active" id="login-pane">
                <div class="code-box">
                    <button id="btn-copy-login" class="btn btn-light btn-sm position-absolute top-0 end-0 m-3 fw-bold"><i class="bi bi-copy"></i> Copier</button>
                    <pre><code id="code-login" class="language-php"></code></pre>
                </div>
            </div>
            <div class="tab-pane fade" id="register-pane">
                 <div class="code-box">
                    <button id="btn-copy-register" class="btn btn-light btn-sm position-absolute top-0 end-0 m-3 fw-bold"><i class="bi bi-copy"></i> Copier</button>
                    <pre><code id="code-register" class="language-php"></code></pre>
                </div>
            </div>
            <div class="tab-pane fade" id="logout-pane">
                 <div class="code-box">
                    <button id="btn-copy-logout" class="btn btn-light btn-sm position-absolute top-0 end-0 m-3 fw-bold"><i class="bi bi-copy"></i> Copier</button>
                    <pre><code id="code-logout" class="language-php"></code></pre>
                </div>
            </div>
            <div class="tab-pane fade" id="protect-pane">
                 <div class="code-box">
                    <button id="btn-copy-protect" class="btn btn-light btn-sm position-absolute top-0 end-0 m-3 fw-bold"><i class="bi bi-copy"></i> Copier</button>
                    <pre><code id="code-protect" class="language-php"></code></pre>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info mt-4 pb-0">
            <h5><i class="bi bi-info-circle"></i> Comment utiliser <code>protect.php</code> ?</h5>
            <p>Insérez simplement <code>require_once 'protect.php';</code> tout en haut des pages que vous souhaitez rendre privées (comme <code>list_projet.php</code> ou <code>create_projet.php</code>). Si un visiteur n'est pas connecté via <code>login.php</code>, il sera immédiatement rejeté !</p>
        </div>
    </div>
</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/highlight.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

    const hostInput = document.getElementById('db-host');
    const userInput = document.getElementById('db-user');
    const passInput = document.getElementById('db-pass');
    const dbnameInput = document.getElementById('db-name');
    const tableSelect = document.getElementById('table-select');
    
    if (localStorage.getItem('dbGeneratorName')) {
        dbnameInput.value = localStorage.getItem('dbGeneratorName');
    }

    const API_URL = 'api/auth_api.php';

    document.getElementById('btn-connect').addEventListener('click', async () => {
        const payload = { action: 'connect', host: hostInput.value, user: userInput.value, pass: passInput.value, dbname: dbnameInput.value };
        localStorage.setItem('dbGeneratorName', dbnameInput.value);
        try {
            const r = await (await fetch(API_URL, { method: 'POST', body: JSON.stringify(payload) })).json();
            if (r.success) {
                document.getElementById('step-config').classList.remove('d-none');
                tableSelect.innerHTML = '<option value="">-- Choisir la table --</option>';
                r.tables.forEach(t => tableSelect.add(new Option(t, t)));
            }
        } catch(e) { alert("Erreur BDD"); }
    });

    tableSelect.addEventListener('change', async () => {
        if(!tableSelect.value) return;
        const payload = { action: 'fetch_fields', host: hostInput.value, user: userInput.value, pass: passInput.value, dbname: dbnameInput.value, table: tableSelect.value };
        try {
            const r = await (await fetch(API_URL, { method: 'POST', body: JSON.stringify(payload) })).json();
            if (r.success) {
                document.getElementById('fields-config-area').classList.remove('d-none');
                
                const idCol = document.getElementById('id-col');
                const passCol = document.getElementById('pass-col');
                const nameCol = document.getElementById('name-col');
                
                idCol.innerHTML = ''; passCol.innerHTML = ''; 
                nameCol.innerHTML = '<option value="">(Aucun)</option>';

                r.fields.forEach(f => {
                    idCol.add(new Option(f, f));
                    passCol.add(new Option(f, f));
                    nameCol.add(new Option(f, f));
                });
                
                // Auto-sélection intelligente
                Array.from(idCol.options).forEach(o => { if(['email', 'pseudo', 'login', 'username', 'identifiant'].includes(o.value.toLowerCase())) o.selected = true; });
                Array.from(passCol.options).forEach(o => { if(['password', 'mdp', 'mot_de_passe', 'pass'].includes(o.value.toLowerCase())) o.selected = true; });
                Array.from(nameCol.options).forEach(o => { if(['nom', 'name', 'nom_complet', 'fullname'].includes(o.value.toLowerCase())) o.selected = true; });
            }
        } catch(e) { alert("Erreur fetch_fields"); }
    });

    document.getElementById('btn-generate').addEventListener('click', async () => {
        const payload = { 
            action: 'generate', host: hostInput.value, user: userInput.value, pass: passInput.value, dbname: dbnameInput.value, 
            table: tableSelect.value, 
            id_col: document.getElementById('id-col').value,
            pass_col: document.getElementById('pass-col').value,
            name_col: document.getElementById('name-col').value
        };
        
        try {
            const r = await (await fetch(API_URL, { method: 'POST', body: JSON.stringify(payload) })).json();
            if (r.success) {
                document.getElementById('step-result').classList.remove('d-none');
                
                const updateCode = (id, code) => {
                    const el = document.getElementById(id);
                    el.textContent = code; delete el.dataset.highlighted; hljs.highlightElement(el);
                };
                updateCode('code-login', r.login_code);
                updateCode('code-register', r.register_code);
                updateCode('code-logout', r.logout_code);
                updateCode('code-protect', r.protect_code);
                
                window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
            } else { alert(r.message); }
        } catch(e) { alert("Erreur Génération"); }
    });

    // Copy setup
    ['login','register','logout','protect'].forEach(id => {
        document.getElementById(`btn-copy-${id}`).addEventListener('click', function() {
            navigator.clipboard.writeText(document.getElementById(`code-${id}`).textContent);
            this.innerHTML = '<i class="bi bi-check"></i> Copié';
            this.classList.replace('btn-light', 'btn-success'); this.classList.add('text-white');
            setTimeout(() => { this.innerHTML = '<i class="bi bi-copy"></i> Copier'; this.classList.replace('btn-success', 'btn-light'); this.classList.remove('text-white'); }, 2000);
        });
    });
});
</script>
</body>
</html>
