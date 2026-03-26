<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générateur de Fonctions CRUD PHP</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap-icons.css">
    <!-- highlight.js for PHP syntax coloring -->
    <link rel="stylesheet" href="assets/css/atom-one-dark.min.css">
    
    <style>
        body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"; background-color: #f4f7f6; color: #333; }
        .app-header { background: linear-gradient(135deg, #198754, #20c997); color: white; padding: 2rem 0; margin-bottom: 2rem; border-radius: 0 0 20px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .setup-card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; background: #fff; border-top: 4px solid #198754; }
        .code-box { position: relative; border-radius: 10px; overflow: hidden; }
        .code-box pre { margin: 0; padding: 20px; font-size: 1rem; }
    </style>
</head>
<body>

<div class="app-header text-center">
    <h1><i class="bi bi-file-earmark-code-fill"></i> CRUD Function Generator</h1>
    <p class="mb-0">Générer le code PHP pour vos requêtes SQL en un clic</p>
</div>

<div class="container pb-5">
    
    <!-- 1. CONNEXION -->
    <div id="step-connection" class="card setup-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0"><i class="bi bi-plug"></i> 1. Connexion à la Base de Données</h5>
                <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-house"></i> Accueil</a>
            </div>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label text-muted small">Host</label><input type="text" id="db-host" class="form-control" value="localhost"></div>
                <div class="col-md-3"><label class="form-label text-muted small">Utilisateur</label><input type="text" id="db-user" class="form-control" value="root"></div>
                <div class="col-md-3"><label class="form-label text-muted small">Mot de passe</label><input type="password" id="db-pass" class="form-control" placeholder="(vide par défaut)"></div>
                <div class="col-md-3"><label class="form-label text-muted small fw-bold text-success">Nom de la BDD</label><input type="text" id="db-name" class="form-control" placeholder="ex: ma_base"></div>
            </div>
            <button id="btn-connect" class="btn btn-success mt-3 w-100 fw-bold"><i class="bi bi-database-check"></i> Connecter et Lister les Tables</button>
            <div id="conn-msg" class="mt-2 text-center fw-bold"></div>
        </div>
    </div>

    <!-- 2. SELECTION TABLE -->
    <div id="step-table" class="card setup-card d-none">
        <div class="card-body">
            <h5 class="card-title mb-3"><i class="bi bi-list-ul"></i> 2. Sélection de la Table</h5>
            <div class="row align-items-end g-3">
                <div class="col-md-8">
                    <label class="form-label text-muted small">Choisissez une table pour générer son CRUD</label>
                    <select id="table-select" class="form-select form-select-lg"></select>
                </div>
                <div class="col-md-4">
                    <button id="btn-generate" class="btn btn-primary btn-lg w-100 fw-bold"><i class="bi bi-magic"></i> Générer le PHP</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. RESULTAT -->
    <div id="step-result" class="d-none mt-4">
        <h3 class="mb-3"><i class="bi bi-braces"></i> 3. Code Généré</h3>
        <div class="code-box">
            <button id="btn-copy" class="btn btn-light btn-sm position-absolute top-0 end-0 m-3 fw-bold" style="z-index: 10;"><i class="bi bi-copy"></i> Copier le Code</button>
            <pre><code id="code-output" class="language-php"></code></pre>
        </div>
    </div>
</div>

<script src="assets/js/highlight.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

    const hostInput = document.getElementById('db-host');
    const userInput = document.getElementById('db-user');
    const passInput = document.getElementById('db-pass');
    const dbnameInput = document.getElementById('db-name');
    const connMsg = document.getElementById('conn-msg');
    const tableSelect = document.getElementById('table-select');
    const API_URL = 'api/fonction_api.php';

    // Sauvegarder la BDD utilisée la dernière fois
    if (localStorage.getItem('dbGeneratorName')) {
        dbnameInput.value = localStorage.getItem('dbGeneratorName');
    }

    // --- BTN CONNECT ---
    document.getElementById('btn-connect').addEventListener('click', async () => {
        const host = hostInput.value;
        const user = userInput.value;
        const pass = passInput.value;
        const dbname = dbnameInput.value;

        if(!dbname) { connMsg.innerHTML = '<span class="text-danger">Veuillez renseigner un nom de base de données.</span>'; return; }
        
        localStorage.setItem('dbGeneratorName', dbname);
        connMsg.innerHTML = '<span class="text-success"><span class="spinner-border spinner-border-sm"></span> Connexion...</span>';

        try {
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: 'connect', host, user, pass, dbname })
            });
            const data = await res.json();

            if (data.success) {
                connMsg.innerHTML = `<span class="text-success"><i class="bi bi-check"></i> Tables récupérées avec succès !</span>`;
                document.getElementById('step-table').classList.remove('d-none');
                
                tableSelect.innerHTML = '';
                if(data.tables.length === 0) {
                    tableSelect.innerHTML = '<option value="">Aucune table trouvée</option>';
                    document.getElementById('btn-generate').disabled = true;
                } else {
                    document.getElementById('btn-generate').disabled = false;
                    data.tables.forEach(t => {
                        tableSelect.add(new Option(t, t));
                    });
                }
            } else {
                connMsg.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> ${data.message}</span>`;
            }
        } catch (e) {
            connMsg.innerHTML = `<span class="text-danger">Erreur serveur.</span>`;
        }
    });

    // --- BTN GENERATE ---
    document.getElementById('btn-generate').addEventListener('click', async () => {
        const host = hostInput.value;
        const user = userInput.value;
        const pass = passInput.value;
        const dbname = dbnameInput.value;
        const table = tableSelect.value;
        
        const btn = document.getElementById('btn-generate');
        const oldText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Création...';

        try {
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: 'generate', host, user, pass, dbname, table })
            });
            const data = await res.json();

            if (data.success) {
                document.getElementById('step-result').classList.remove('d-none');
                const codeOutput = document.getElementById('code-output');
                
                // Set text first safely, then highlight
                codeOutput.textContent = data.code;
                delete codeOutput.dataset.highlighted; // Force highlight re-render
                hljs.highlightElement(codeOutput);
                
                window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
            } else {
                alert(data.message);
            }
        } catch (e) {
            alert("Erreur lors de la génération.");
        } finally {
            btn.innerHTML = oldText;
        }
    });

    // --- BTN COPY ---
    document.getElementById('btn-copy').addEventListener('click', () => {
        const text = document.getElementById('code-output').textContent;
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById('btn-copy');
            const oldContent = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2-all"></i> Copié !';
            btn.classList.replace('btn-light', 'btn-success');
            btn.classList.add('text-white');
            setTimeout(() => {
                btn.innerHTML = oldContent;
                btn.classList.replace('btn-success', 'btn-light');
                btn.classList.remove('text-white');
            }, 2000);
        });
    });

});
</script>
</body>
</html>
