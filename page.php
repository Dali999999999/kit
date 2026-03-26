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
                        <tr>
                            <th>Champ SQL</th>
                            <th>Label Formulaire</th>
                            <th>Affichage (Si FK) & Select Dynamique</th>
                            <th class="text-center">Ordre<br><small class="text-muted">(list.php)</small></th>
                            <th class="text-center" title="Recherche libre via LIKE">Recherche <i class="bi bi-search"></i></th>
                            <th class="text-center" title="Filtre via select">Filtre <i class="bi bi-funnel"></i></th>
                        </tr>
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
                                    <select id="sel-layout" class="form-select">
                                        <option value="1">1 Colonne (Classique)</option>
                                        <option value="2">2 Colonnes (Compact)</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-8">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-shield-lock"></i> Sécurité & Espace Membre</h6>
                                    <div class="d-flex gap-4 mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="chk-protect">
                                            <label class="form-check-label fw-bold" for="chk-protect">Protéger ces pages (require 'protect.php')</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="chk-filter">
                                            <label class="form-check-label fw-bold" for="chk-filter">Restreindre à l'utilisateur ciblé</label>
                                        </div>
                                    </div>
                                    <div id="filter-options" class="d-none mt-2 p-3 bg-white border rounded">
                                        <label class="form-label text-muted small">Clé Étrangère de l'utilisateur session (ex: id_user)</label>
                                        <select id="filter-fk" class="form-select form-select-sm"></select>
                                    </div>
                                </div>
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
                            <td class="text-center align-middle" style="width: 140px;">
                                <select class="form-select form-select-sm sort-sel text-center">
                                    <option value="">Par défaut</option>
                                    <option value="ASC">A -> Z (Asc)</option>
                                    <option value="DESC">Z -> A (Desc)</option>
                                </select>
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
                        </tr>
                    `;
                });
                window.scrollTo({ top: document.getElementById('step-config').offsetTop, behavior: 'smooth' });
            } else { alert(data.message); }
        } catch (e) { alert("Erreur lors de la récupération des options du champ."); } 
        finally { btn.innerHTML = oldText; }
    });

    document.getElementById('chk-filter').addEventListener('change', function() {
        document.getElementById('filter-options').classList.toggle('d-none', !this.checked);
    });

    // --- BTN GENERATE ---
    document.getElementById('btn-generate').addEventListener('click', async () => {
        const host = hostInput.value, user = userInput.value, pass = passInput.value, dbname = dbnameInput.value, table = tableSelect.value;
        
        const fields_config = {};
        document.querySelectorAll('.config-row').forEach(row => {
            const field = row.getAttribute('data-field');
            const label = row.querySelector('.label-input').value;
            const fkSel = row.querySelector('.fk-display-sel');
            const depOnSel = row.querySelector('.depend-on-sel');
            const depColSel = row.querySelector('.depend-col-sel');
            const sortSel = row.querySelector('.sort-sel');
            
            fields_config[field] = { 
                label, 
                fk_display: fkSel ? fkSel.value : null, 
                depends_on: depOnSel ? depOnSel.value : null,
                depends_col: depColSel ? depColSel.value : null,
                sort_order: sortSel.value,
                is_search: row.querySelector('.chk-search').checked, 
                is_filter: row.querySelector('.chk-filter').checked 
            };
        });

        const is_protected = document.getElementById('chk-protect').checked;
        const filter_fk = document.getElementById('chk-filter').checked ? document.getElementById('filter-fk').value : '';
        const form_layout = document.getElementById('sel-layout').value;
        const style_config = {}; // Obsolète grâce au Visual Builder

        const btn = document.getElementById('btn-generate');
        const oldText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Création...';

        try {
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: 'generate', host, user, pass, dbname, table, fields_config, is_protected, filter_fk, form_layout, style_config })
            });
            const data = await res.json();

            if (data.success) {
                document.getElementById('step-result').classList.remove('d-none');
                
                ['list', 'create', 'edit', 'delete'].forEach(k => {
                    const el = document.getElementById('code-' + k);
                    if(el) {
                        el.textContent = data[k + '_code'];
                        delete el.dataset.highlighted; 
                        hljs.highlightElement(el);
                    }
                });
                
                window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
            } else { alert(data.message); }
        } catch (e) { alert("Erreur lors de la génération."); } 
        finally { btn.innerHTML = oldText; }
    });

    // --- BTN COPY ---
    function setupCopy(btnId, outputId) {
        document.getElementById(btnId).addEventListener('click', () => {
            const text = document.getElementById(outputId).textContent;
            navigator.clipboard.writeText(text).then(() => {
                const btn = document.getElementById(btnId);
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

});
</script>
</body>
</html>
