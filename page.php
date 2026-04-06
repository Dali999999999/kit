<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générateur de Vues PHP (Pages CRUD)</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/atom-one-dark.min.css">
    
    <style>
        body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif; background-color: #f4f7f6; color: #333; }
        .app-header { background: linear-gradient(135deg, #6f42c1, #d63384); color: white; padding: 2rem 0; margin-bottom: 2rem; border-radius: 0 0 20px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .setup-card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; background: #fff; border-top: 4px solid #6f42c1; }
        .code-box { position: relative; border-radius: 0 0 10px 10px; overflow: hidden; background: #282c34; border: 1px solid #dee2e6; border-top: none;}
        .code-box pre { margin: 0; padding: 20px; font-size: 1rem; }
        .nav-tabs .nav-link { font-weight: 600; color: #6c757d; }
        .nav-tabs .nav-link.active { color: #6f42c1; border-top: 3px solid #6f42c1; }
    </style>
</head>
<body>

<div class="app-header text-center">
    <h1><i class="bi bi-window-stack"></i> Views Template Generator</h1>
    <p class="mb-0">Générez des pages <code>list.php</code>, <code>create.php</code>, et <code>edit.php</code> complètes en un clic</p>
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
                <div class="col-md-3"><label class="form-label text-muted small fw-bold text-purp" style="color:#6f42c1">Nom de la BDD</label><input type="text" id="db-name" class="form-control" placeholder="ex: ma_base"></div>
            </div>
            <button id="btn-connect" class="btn text-white mt-3 w-100 fw-bold" style="background-color:#6f42c1"><i class="bi bi-database-check"></i> Connecter et Lister les Tables</button>
            <div id="conn-msg" class="mt-2 text-center fw-bold"></div>
        </div>
    </div>

    <!-- 2. SELECTION TABLE -->
    <div id="step-table" class="card setup-card d-none">
        <div class="card-body">
            <h5 class="card-title mb-3"><i class="bi bi-list-ul"></i> 2. Sélection de la Table</h5>
            <div class="row align-items-end g-3">
                <div class="col-md-8">
                    <label class="form-label text-muted small">Choisissez une table pour générer ses Vues (Pages)</label>
                    <select id="table-select" class="form-select form-select-lg"></select>
                </div>
                <div class="col-md-4">
                    <button id="btn-config-fields" class="btn btn-dark btn-lg w-100 fw-bold">Suivant <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. CONFIGURATION CHAMPS -->
    <div id="step-config" class="card setup-card d-none">
        <div class="card-body">
            <h5 class="card-title mb-3"><i class="bi bi-sliders"></i> 3. Configuration des Affichages</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                            <th style="min-width: 140px;">Champ SQL</th>
                            <th style="min-width: 180px;">Label Formulaire</th>
                            <th>Affichage (Si FK) & Select Dynamique</th>
                            <th class="text-center" style="width: 130px;">Tri & Ordre<br><small class="text-muted">(ex: 1 ASC)</small></th>
                            <th class="text-center" style="width: 70px;" title="Recherche libre (LIKE %q%)">
                                <i class="bi bi-search d-block mb-1"></i>
                                <span class="small fw-bold">LIKE</span>
                            </th>
                            <th class="text-center" style="width: 70px;" title="Filtre via liste déroulante">
                                <i class="bi bi-funnel d-block mb-1"></i>
                                <span class="small fw-bold">Filtre</span>
                            </th>
                            <th class="text-center" style="width: 70px;" title="Visible dans la Liste (Tableau/Cards)">
                                <i class="bi bi-table d-block mb-1"></i>
                                <span class="small fw-bold">Liste</span>
                            </th>
                            <th class="text-center" style="width: 70px;" title="Visible dans Create (AJOUT)">
                                <i class="bi bi-plus-circle d-block mb-1"></i>
                                <span class="small fw-bold">Ajout</span>
                            </th>
                            <th class="text-center" style="width: 70px;" title="Visible dans Edit (MODIF)">
                                <i class="bi bi-pencil-square d-block mb-1"></i>
                                <span class="small fw-bold">Modif</span>
                            </th>
                    </thead>
                    <tbody id="config-tbody"></tbody>
                </table>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card border-primary h-100 bg-light">
                        <div class="card-body">
                            <h5 class="text-primary mb-3"><i class="bi bi-sliders"></i> Options Avancées (Structure & Sécurité)</h5>
                            
                            <div class="row g-4">
                                <div class="col-md-4 border-end">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-layout-split"></i> Disposition du Formulaire</h6>
                                    <select id="sel-layout" class="form-select mb-3">
                                        <option value="1">1 Colonne (Classique)</option>
                                        <option value="2">2 Colonnes (Compact)</option>
                                    </select>
                                    
                                    <h6 class="fw-bold mb-3"><i class="bi bi-grid-3x3-gap"></i> Style de Liste (list.php)</h6>
                                    <select id="sel-list-layout" class="form-select mb-3">
                                        <option value="table">Tableau Classique</option>
                                        <option value="cards">Grille de Cards Bootstrap</option>
                                    </select>

                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="chk-use-datatable" checked>
                                        <label class="form-check-label fw-bold" for="chk-use-datatable">Intégrer DataTables (Recherche & Pagination auto)</label>
                                    </div>

                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="chk-generate-view" checked>
                                        <label class="form-check-label fw-bold" for="chk-generate-view">Générer page de Détails (view.php)</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="chk-generate-search" checked>
                                        <label class="form-check-label fw-bold" for="chk-generate-search">Générer page de Recherche (search.php)</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="chk-auto-join" checked>
                                        <label class="form-check-label fw-bold" for="chk-auto-join">Auto-Joins SQL (Libellés FK)</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-8">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-shield-lock"></i> Sécurité & Espace Membre</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="chk-protect">
                                                <label class="form-check-label fw-bold" for="chk-protect">Protéger ces pages (Auth)</label>
                                            </div>
                                            <div id="admin-mode-container" class="form-check form-switch d-none mb-3">
                                                <input class="form-check-input" type="checkbox" id="chk-admin-mode">
                                                <label class="form-check-label fw-bold" for="chk-admin-mode">Mode Admin Global (Accès total)</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div id="filter-user-container" class="form-check form-switch d-none">
                                                <input class="form-check-input" type="checkbox" id="chk-filter">
                                                <label class="form-check-label fw-bold" for="chk-filter">Filtrer par Utilisateur</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="filter-options" class="d-none mt-2 p-3 bg-white border rounded">
                                        <label class="form-label text-muted small">Clé Étrangère de l'utilisateur session (ex: id_user)</label>
                                        <select id="filter-fk" class="form-select form-select-sm"></select>
                                    </div>

                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="fw-bold mb-2"><i class="bi bi-lightning"></i> Règles d'affichage conditionnel (Selects)</h6>
                                        <div id="rules-container" class="mb-2 small"></div>
                                        <button type="button" id="btn-add-rule" class="btn btn-outline-primary btn-sm"><i class="bi bi-plus"></i> Ajouter une règle Show/Hide</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-3 border-top">
                                <h5 class="text-secondary mb-3"><i class="bi bi-gear"></i> Colonne "Actions" (list.php)</h5>
                                <div class="row g-3">
                                    <div class="col-md-4 border-end">
                                        <h6 class="fw-bold small">Boutons Affichés</h6>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="chk-action-view" checked>
                                            <label class="form-check-label fw-bold" for="chk-action-view">Bouton "Voir"</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="chk-action-edit" checked>
                                            <label class="form-check-label fw-bold" for="chk-action-edit">Bouton "Modifier"</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="chk-action-delete" checked>
                                            <label class="form-check-label fw-bold" for="chk-action-delete">Bouton "Supprimer"</label>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="fw-bold small">Format & Personnalisation Textes</h6>
                                        <div class="mb-3 d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="btnType" id="btn-icon" value="icon" onchange="document.getElementById('btn-text-inputs').classList.add('d-none')" checked>
                                                <label class="form-check-label" for="btn-icon">Afficher des Icônes d'action</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="btnType" id="btn-text" value="text" onchange="document.getElementById('btn-text-inputs').classList.remove('d-none')">
                                                <label class="form-check-label" for="btn-text">Afficher du Texte</label>
                                            </div>
                                        </div>
                                        <div class="row g-2 d-none" id="btn-text-inputs">
                                            <div class="col-4">
                                                <label class="form-label small text-muted">A la place de l'icône Voir</label>
                                                <input type="text" id="action-text-view" class="form-control form-control-sm" value="Détails">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label small text-muted">A la place de l'icône Éditer</label>
                                                <input type="text" id="action-text-edit" class="form-control form-control-sm" value="Modifier">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label small text-muted">A la place de l'icône Delete</label>
                                                <input type="text" id="action-text-delete" class="form-control form-control-sm" value="Supprimer">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-3 border-top">
                                <h5 class="text-secondary mb-3"><i class="bi bi-file-earmark-code"></i> Personnalisation des noms de fichiers</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Liste</label>
                                        <input type="text" id="filename-list" class="form-control form-control-sm" placeholder="Ex: list_etudiants.php">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Création</label>
                                        <input type="text" id="filename-create" class="form-control form-control-sm" placeholder="Ex: create_etudiant.php">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Modification</label>
                                        <input type="text" id="filename-edit" class="form-control form-control-sm" placeholder="Ex: edit_etudiant.php">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Suppression</label>
                                        <input type="text" id="filename-delete" class="form-control form-control-sm" placeholder="Ex: delete_etudiant.php">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Détails (View)</label>
                                        <input type="text" id="filename-view" class="form-control form-control-sm" placeholder="Ex: view_etudiant.php">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Recherche (Search)</label>
                                        <input type="text" id="filename-search" class="form-control form-control-sm" placeholder="Ex: research_med.php">
                                    </div>
                                </div>
                                <div class="mt-2 text-muted small"><i class="bi bi-info-circle"></i> Ces noms seront utilisés pour les liens et redirections dans le code généré.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button id="btn-generate" class="btn btn-success btn-lg fw-bold"><i class="bi bi-magic"></i> Générer les Fichiers Définitifs</button>
            </div>
        </div>
    </div>

    <!-- 4. RESULTAT -->
    <div id="step-result" class="d-none mt-4">
        <h3 class="mb-3"><i class="bi bi-file-earmark-code"></i> 4. Fichiers Générés</h3>
        
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-pane" type="button" role="tab"><i class="bi bi-table"></i> list.php</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="create-tab" data-bs-toggle="tab" data-bs-target="#create-pane" type="button" role="tab"><i class="bi bi-plus-square"></i> create.php</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit-pane" type="button" role="tab"><i class="bi bi-pencil-square"></i> edit.php</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="delete-tab" data-bs-toggle="tab" data-bs-target="#delete-pane" type="button" role="tab"><i class="bi bi-trash"></i> delete.php</button>
            </li>
            <li class="nav-item d-none" id="view-tab-li">
                <button class="nav-link" id="view-tab" data-bs-toggle="tab" data-bs-target="#view-pane" type="button" role="tab"><i class="bi bi-eye"></i> view.php</button>
            </li>
            <li class="nav-item d-none" id="search-tab-li">
                <button class="nav-link" id="search-tab" data-bs-toggle="tab" data-bs-target="#search-pane" type="button" role="tab"><i class="bi bi-search"></i> search.php</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="style-tab" data-bs-toggle="tab" data-bs-target="#style-pane" type="button" role="tab"><i class="bi bi-palette"></i> style.css</button>
            </li>
        </ul>
        
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="list-pane" role="tabpanel">
                <div class="code-box">
                    <button id="btn-copy-list" class="btn btn-light btn-sm position-absolute top-0 end-0 m-3 fw-bold btn-copy" style="z-index: 10;"><i class="bi bi-copy"></i> Copier list.php</button>
                    <pre><code id="code-list" class="language-php"></code></pre>
                </div>
            </div>
            <div class="tab-pane fade" id="create-pane" role="tabpanel">
                 <div class="code-box">
                    <button id="btn-copy-create" class="btn btn-light btn-sm position-absolute top-0 end-0 m-3 fw-bold btn-copy" style="z-index: 10;"><i class="bi bi-copy"></i> Copier create.php</button>
                    <pre><code id="code-create" class="language-php"></code></pre>
                </div>
            </div>
            <div class="tab-pane fade" id="edit-pane" role="tabpanel">
                 <div class="code-box">
                    <button id="btn-copy-edit" class="btn btn-light btn-sm position-absolute top-0 end-0 m-3 fw-bold btn-copy" style="z-index: 10;"><i class="bi bi-copy"></i> Copier edit.php</button>
                    <pre><code id="code-edit" class="language-php"></code></pre>
                </div>
            </div>
            <div class="tab-pane fade" id="delete-pane" role="tabpanel">
                 <div class="code-box">
                    <button id="btn-copy-delete" class="btn btn-light btn-sm position-absolute top-0 end-0 m-3 fw-bold btn-copy" style="z-index: 10;"><i class="bi bi-copy"></i> Copier delete.php</button>
                    <pre><code id="code-delete" class="language-php"></code></pre>
                </div>
            </div>
            <div class="tab-pane fade" id="view-pane" role="tabpanel">
                 <div class="code-box">
                    <button id="btn-copy-view" class="btn btn-light btn-sm position-absolute top-0 end-0 m-3 fw-bold btn-copy" style="z-index: 10;"><i class="bi bi-copy"></i> Copier</button>
                    <pre><code id="code-view" class="language-php"></code></pre>
                </div>
            </div>
            <div class="tab-pane fade" id="search-pane" role="tabpanel">
                 <div class="code-box">
                    <button id="btn-copy-search" class="btn btn-light btn-sm position-absolute top-0 end-0 m-3 fw-bold btn-copy" style="z-index: 10;"><i class="bi bi-copy"></i> Copier</button>
                    <pre><code id="code-search" class="language-php"></code></pre>
                </div>
            </div>
            <div class="tab-pane fade" id="style-pane" role="tabpanel">
                 <div class="code-box">
                    <button id="btn-copy-style" class="btn btn-light btn-sm position-absolute top-0 end-0 m-3 fw-bold btn-copy" style="z-index: 10;"><i class="bi bi-copy"></i> Copier style.css</button>
                    <pre><code id="code-style" class="language-css"></code></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/highlight.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

    const hostInput = document.getElementById('db-host');
    const userInput = document.getElementById('db-user');
    const passInput = document.getElementById('db-pass');
    const dbnameInput = document.getElementById('db-name');
    const connMsg = document.getElementById('conn-msg');
    const tableSelect = document.getElementById('table-select');
    const API_URL = 'api/page_api.php';

    if (localStorage.getItem('dbGeneratorName')) {
        dbnameInput.value = localStorage.getItem('dbGeneratorName');
    }

    let currentFields = [];

    // --- BTN CONNECT ---
    document.getElementById('btn-connect').addEventListener('click', async () => {
        const host = hostInput.value;
        const user = userInput.value;
        const pass = passInput.value;
        const dbname = dbnameInput.value;

        if(!dbname) { connMsg.innerHTML = '<span class="text-danger">Veuillez renseigner un nom de BDD.</span>'; return; }
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
                connMsg.innerHTML = `<span class="text-success" style="color:#6f42c1!important"><i class="bi bi-check"></i> Tables récupérées !</span>`;
                document.getElementById('step-table').classList.remove('d-none');
                tableSelect.innerHTML = '';
                if(data.tables.length === 0) {
                    tableSelect.innerHTML = '<option value="">Aucune table trouvée</option>';
                    document.getElementById('btn-generate').disabled = true;
                } else {
                    document.getElementById('btn-generate').disabled = false;
                    data.tables.forEach(t => tableSelect.add(new Option(t, t)));
                }
            } else {
                connMsg.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> ${data.message}</span>`;
            }
        } catch (e) { connMsg.innerHTML = `<span class="text-danger">Erreur serveur.</span>`; }
    });

    // --- BTN CONFIG FIELDS ---
    document.getElementById('btn-config-fields').addEventListener('click', async () => {
        const host = hostInput.value, user = userInput.value, pass = passInput.value, dbname = dbnameInput.value;
        const table = tableSelect.value;
        
        const btn = document.getElementById('btn-config-fields');
        const oldText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Chargement...';

        try {
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: 'fetch_fields_config', host, user, pass, dbname, table })
            });
            const data = await res.json();

            if (data.success) {
                currentFields = data.fields;
                document.getElementById('step-config').classList.remove('d-none');
                document.getElementById('step-result').classList.add('d-none');
                
                const tbody = document.getElementById('config-tbody');
                tbody.innerHTML = '';
                const filterSel = document.getElementById('filter-fk');
                filterSel.innerHTML = '<option value="">-- Choisir une FK --</option>';

                data.fields.forEach(f => {
                    let fkSelectHtml = '<span class="text-muted">-</span>';
                    let dependHtml = '';
                    
                    if (f.is_fk) {
                        fkSelectHtml = `<select class="form-select form-select-sm text-primary fw-bold fk-display-sel mb-1" data-field="${f.name}">`;
                        f.fk_columns.forEach(col => fkSelectHtml += `<option value="${col}">${col}</option>`);
                        fkSelectHtml += `</select>`;
                        filterSel.add(new Option(f.name, f.name));

                        dependHtml = `
                            <div class="mt-2 p-2 bg-light border rounded small">
                                <b>Dépend de (Parent) :</b>
                                <select class="form-select form-select-sm depend-on-sel mb-1">
                                    <option value="">Aucun parent</option>
                                    ${data.fields.filter(df => df.is_fk && df.name !== f.name).map(df => `<option value="${df.name}">${df.name}</option>`).join('')}
                                </select>
                                <b>Colonne dans ${f.fk_target} correspondante :</b>
                                <select class="form-select form-select-sm depend-col-sel">
                                    <option value="">(Aucune)</option>
                                    ${f.fk_columns.map(col => `<option value="${col}">${col}</option>`).join('')}
                                </select>
                            </div>
                        `;
                    }
                    
                    let defaultLabel = f.name.charAt(0).toUpperCase() + f.name.slice(1).replace(/_/g, ' ');

                    tbody.innerHTML += `
                        <tr class="config-row" data-field="${f.name}">
                            <td><code class="text-dark fw-bold">${f.name}</code></td>
                            <td><input type="text" class="form-control form-control-sm label-input" value="${defaultLabel}"></td>
                            <td>
                                ${fkSelectHtml}
                                ${dependHtml}
                            </td>
                            <td class="text-center align-middle" style="width: 150px;">
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control sort-prio text-center px-1" placeholder="N°" min="1" max="99" style="width: 45px; flex: none;">
                                    <select class="form-select sort-dir px-1 text-center" style="width: 75px; flex: none; font-size: 0.85rem;">
                                        <option value="">-</option>
                                        <option value="ASC">ASC</option>
                                        <option value="DESC">DESC</option>
                                    </select>
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input chk-search" type="checkbox">
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input chk-filter" type="checkbox">
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input chk-vis-list" type="checkbox" checked>
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input chk-vis-create" type="checkbox" checked>
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input chk-vis-edit" type="checkbox" checked>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                window.scrollTo({ top: document.getElementById('step-config').offsetTop, behavior: 'smooth' });
            } else { alert(data.message); }
        } catch (e) { alert("Erreur lors de la récupération des options du champ."); } 
        finally { btn.innerHTML = oldText; }
    });

    // --- RULES MANAGEMENT ---
    const rulesContainer = document.getElementById('rules-container');
    document.getElementById('btn-add-rule').addEventListener('click', () => {
        const triggerOptions = currentFields.filter(f => f.is_fk || f.type.includes('enum')).map(f => `<option value="${f.name}">${f.name}</option>`).join('');
        const targetOptions = currentFields.map(f => `<option value="${f.name}">${f.name}</option>`).join('');
        
        const ruleDiv = document.createElement('div');
        ruleDiv.className = 'rule-row d-flex align-items-center gap-2 mb-2 p-2 bg-white border rounded border-primary border-opacity-25';
        ruleDiv.innerHTML = `
            <span>SI</span>
            <select class="form-select form-select-sm rule-trigger" style="width:130px">${triggerOptions}</select>
            <span>==</span>
            <input type="text" class="form-control form-control-sm rule-value" placeholder="Valeur" style="width:100px">
            <span>ALORS</span>
            <select class="form-select form-select-sm rule-action" style="width:90px"><option value="show">Afficher</option><option value="hide">Cacher</option></select>
            <select class="form-select form-select-sm rule-target" style="width:130px">${targetOptions}</select>
            <button class="btn btn-link btn-sm text-danger p-0" onclick="this.parentElement.remove()"><i class="bi bi-trash"></i></button>
        `;
        rulesContainer.appendChild(ruleDiv);
    });

    document.getElementById('chk-protect').addEventListener('change', function() {
        document.getElementById('admin-mode-container').classList.toggle('d-none', !this.checked);
        document.getElementById('filter-user-container').classList.toggle('d-none', !this.checked);
        if(!this.checked) {
            document.getElementById('chk-admin-mode').checked = false;
            document.getElementById('chk-filter').checked = false;
            document.getElementById('filter-options').classList.add('d-none');
        }
    });

    document.getElementById('chk-filter').addEventListener('change', function() {
        document.getElementById('filter-options').classList.toggle('d-none', !this.checked);
        if(this.checked) document.getElementById('chk-admin-mode').checked = false;
    });

    document.getElementById('chk-admin-mode').addEventListener('change', function() {
        if(this.checked) {
            document.getElementById('chk-filter').checked = false;
            document.getElementById('filter-options').classList.add('d-none');
        }
    });

    // --- SUGGEST FILENAMES ---
    tableSelect.addEventListener('change', () => {
        const table = tableSelect.value;
        if (!table) return;
        document.getElementById('filename-list').value = `list_${table}.php`;
        document.getElementById('filename-create').value = `create_${table}.php`;
        document.getElementById('filename-edit').value = `edit_${table}.php`;
        document.getElementById('filename-delete').value = `delete_${table}.php`;
        document.getElementById('filename-view').value = `view_${table}.php`;
        document.getElementById('filename-search').value = `search_${table}.php`;
    });

    // --- BTN GENERATE ---
    document.getElementById('btn-generate').addEventListener('click', async () => {
        const host = hostInput.value, user = userInput.value, pass = passInput.value, dbname = dbnameInput.value, table = tableSelect.value;
        if (!table) return alert('Sélectionnez une table.');

        const filenames = {
            list: document.getElementById('filename-list').value || `list_${table}.php`,
            create: document.getElementById('filename-create').value || `create_${table}.php`,
            edit: document.getElementById('filename-edit').value || `edit_${table}.php`,
            delete: document.getElementById('filename-delete').value || `delete_${table}.php`,
            view: document.getElementById('filename-view').value || `view_${table}.php`,
            search: document.getElementById('filename-search').value || `search_${table}.php`
        };

        const fields_config = {};
        document.querySelectorAll('.config-row').forEach(row => {
            const field = row.getAttribute('data-field');
            const label = row.querySelector('.label-input').value;
            const fkSel = row.querySelector('.fk-display-sel');
            const depOnSel = row.querySelector('.depend-on-sel');
            const depColSel = row.querySelector('.depend-col-sel');
            const sortPrio = row.querySelector('.sort-prio');
            const sortDir = row.querySelector('.sort-dir');
            
            fields_config[field] = { 
                label, 
                fk_display: fkSel ? fkSel.value : null, 
                depends_on: depOnSel ? depOnSel.value : null,
                depends_col: depColSel ? depColSel.value : null,
                sort_prio: sortPrio && sortPrio.value ? parseInt(sortPrio.value) : 999,
                sort_dir: sortDir ? sortDir.value : '',
                is_search: row.querySelector('.chk-search').checked, 
                is_filter: row.querySelector('.chk-filter').checked,
                vis_list: row.querySelector('.chk-vis-list').checked,
                vis_create: row.querySelector('.chk-vis-create').checked,
                vis_edit: row.querySelector('.chk-vis-edit').checked
            };
        });

        const conditional_rules = [];
        document.querySelectorAll('.rule-row').forEach(row => {
            conditional_rules.push({
                trigger: row.querySelector('.rule-trigger').value,
                value: row.querySelector('.rule-value').value,
                action: row.querySelector('.rule-action').value,
                target: row.querySelector('.rule-target').value
            });
        });

        const is_protected = document.getElementById('chk-protect').checked;
        const filter_fk = document.getElementById('chk-filter').checked ? document.getElementById('filter-fk').value : '';
        const admin_mode = document.getElementById('chk-admin-mode').checked;
        const generate_view = document.getElementById('chk-generate-view').checked;
        const generate_search = document.getElementById('chk-generate-search').checked;
        const auto_join = document.getElementById('chk-auto-join').checked;
        const form_layout = document.getElementById('sel-layout').value;
        const list_layout = document.getElementById('sel-list-layout').value;
        const use_datatable = document.getElementById('chk-use-datatable').checked;
        const style_config = {}; 

        const action_config = {
            show_view: document.getElementById('chk-action-view').checked,
            show_edit: document.getElementById('chk-action-edit').checked,
            show_delete: document.getElementById('chk-action-delete').checked,
            btn_type: document.querySelector('input[name="btnType"]:checked').value,
            text_view: document.getElementById('action-text-view').value,
            text_edit: document.getElementById('action-text-edit').value,
            text_delete: document.getElementById('action-text-delete').value
        };

        const btn = document.getElementById('btn-generate');
        const oldText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Génération...';
        btn.disabled = true;

        try {
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ 
                    action: 'generate', host, user, pass, dbname, table, fields_config, 
                    is_protected, filter_fk, admin_mode, generate_view, generate_search, auto_join, 
                    conditional_rules, form_layout, list_layout, use_datatable, style_config, filenames, action_config
                })
            });
            const data = await res.json();
            
            if (data.success) {
                // Update tab labels
                document.getElementById('list-tab').innerHTML = `<i class="bi bi-table"></i> ${filenames.list}`;
                document.getElementById('create-tab').innerHTML = `<i class="bi bi-plus-square"></i> ${filenames.create}`;
                document.getElementById('edit-tab').innerHTML = `<i class="bi bi-pencil-square"></i> ${filenames.edit}`;
                document.getElementById('delete-tab').innerHTML = `<i class="bi bi-trash"></i> ${filenames.delete}`;

                const codeList = document.getElementById('code-list');
                const codeCreate = document.getElementById('code-create');
                const codeEdit = document.getElementById('code-edit');
                const codeDelete = document.getElementById('code-delete');
                const codeStyle = document.getElementById('code-style');
                const codeView = document.getElementById('code-view');
                const codeSearch = document.getElementById('code-search');

                if (codeList) codeList.textContent = data.list_code;
                if (codeCreate) codeCreate.textContent = data.create_code;
                if (codeEdit) codeEdit.textContent = data.edit_code;
                if (codeDelete) codeDelete.textContent = data.delete_code;
                if (codeStyle) codeStyle.textContent = data.style_code;
                if (codeView) codeView.textContent = data.view_code;
                if (codeSearch) codeSearch.textContent = data.search_code;

                // View tab
                const viewLi = document.getElementById('view-tab-li');
                if (generate_view) {
                    document.getElementById('view-tab').innerHTML = `<i class="bi bi-eye"></i> ${filenames.view}`;
                    viewLi.classList.remove('d-none');
                } else {
                    viewLi.classList.add('d-none');
                }

                // Search tab
                const searchLi = document.getElementById('search-tab-li');
                if (generate_search) {
                    document.getElementById('search-tab').innerHTML = `<i class="bi bi-search"></i> ${filenames.search}`;
                    searchLi.classList.remove('d-none');
                } else {
                    searchLi.classList.add('d-none');
                }
                
                document.getElementById('step-result').classList.remove('d-none');
                document.querySelectorAll('pre code').forEach(el => {
                    if (el.textContent) {
                         delete el.dataset.highlighted;
                         hljs.highlightElement(el);
                    }
                });
                window.scrollTo({ top: document.getElementById('step-result').offsetTop, behavior: 'smooth' });
            } else {
                alert(data.message || "Erreur lors de la génération.");
            }
        } finally {
            btn.innerHTML = oldText;
            btn.disabled = false;
        }
    });

    // --- BTN COPY ---
    function setupCopy(btnId, outputId) {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        btn.addEventListener('click', () => {
            const outEl = document.getElementById(outputId);
            if (!outEl) return;
            const text = outEl.textContent;
            navigator.clipboard.writeText(text).then(() => {
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
    }

    setupCopy('btn-copy-list', 'code-list');
    setupCopy('btn-copy-create', 'code-create');
    setupCopy('btn-copy-edit', 'code-edit');
    setupCopy('btn-copy-delete', 'code-delete');
    setupCopy('btn-copy-style', 'code-style');
    setupCopy('btn-copy-view', 'code-view');
    setupCopy('btn-copy-search', 'code-search');

});
</script>
</body>
</html>
