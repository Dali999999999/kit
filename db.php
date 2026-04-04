

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DB Builder - Pro Edition</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap-icons.css">
    
    <style>
        body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"; background-color: #f4f7f6; color: #333; }
        .app-header { background: linear-gradient(135deg, #0d6efd, #0dcaf0); color: white; padding: 2rem 0; margin-bottom: 2rem; border-radius: 0 0 20px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        /* Séparation stricte des classes pour éviter les bugs JavaScript */
        .setup-card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; background: #fff; }
        .table-card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; overflow: hidden; background: #fff; }
        
        .table-card-header { background-color: #fff; padding: 15px 20px; border-bottom: 2px solid #e9ecef; display: flex; align-items: center; justify-content: space-between; border-top: 4px solid #0d6efd; }
        .table-name-input { font-size: 1.2rem; font-weight: 600; border: none; background: #f8f9fa; border-radius: 8px; padding: 8px 15px; width: 60%; transition: 0.2s; }
        .table-name-input:focus { background: #fff; box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25); outline: none; }
        .field-row { background: #fff; padding: 12px 20px; border-bottom: 1px solid #f1f3f5; transition: 0.2s; }
        .field-row:hover { background-color: #f8f9fa; }
        .field-grid { display: grid; grid-template-columns: 2fr 1.5fr 1.5fr auto 40px; gap: 15px; align-items: center; }
        .options-group { display: flex; gap: 8px; flex-wrap: wrap; }
        .opt-checkbox { display: none; }
        .opt-label { cursor: pointer; padding: 5px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; border: 1px solid #dee2e6; color: #6c757d; background: #fff; transition: all 0.2s; user-select: none; }
        .opt-checkbox.nn-check:checked + .opt-label { background: #6c757d; color: #fff; border-color: #6c757d; }
        .opt-checkbox.uq-check:checked + .opt-label { background: #6f42c1; color: #fff; border-color: #6f42c1; }
        .opt-checkbox.pk-check:checked + .opt-label { background: #dc3545; color: #fff; border-color: #dc3545; }
        .opt-checkbox.ai-check:checked + .opt-label { background: #0dcaf0; color: #fff; border-color: #0dcaf0; }
        .opt-checkbox.fi-check:checked + .opt-label { background: #20c997; color: #fff; border-color: #20c997; }
        .opt-checkbox.fk-check:checked + .opt-label { background: #fd7e14; color: #fff; border-color: #fd7e14; }
        .fk-zone { background: #fff3cd; border: 1px solid #ffe69c; padding: 10px 15px; border-radius: 8px; margin-top: 10px; display: flex; gap: 15px; align-items: center; }
        .schema-box { background: #1e1e1e; color: #4af626; padding: 20px; border-radius: 10px; font-family: 'Courier New', Courier, monospace; font-size: 1.05rem; white-space: pre-wrap; line-height: 1.5; }
        .auto-save-badge { position: absolute; top: 10px; right: 20px; font-size: 0.8rem; background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 20px; }
    </style>
</head>
<body>

<div class="app-header text-center position-relative">
    <div class="auto-save-badge"><i class="bi bi-cloud-check"></i> Sauvegarde auto activée</div>
    <h1><i class="bi bi-database-fill-gear"></i> DB Builder Pro</h1>
    <p class="mb-0">Conception intuitive de bases de données InnoDB</p>
</div>

<div class="container pb-5">
    
    <!-- 1. CONNEXION -->
    <div id="step-connection" class="card setup-card">
        <div class="card-body">
            <h5 class="card-title mb-3"><i class="bi bi-plug"></i> 1. Connexion WampServer</h5>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label text-muted small">Host</label><input type="text" id="db-host" class="form-control" value="localhost"></div>
                <div class="col-md-3"><label class="form-label text-muted small">Utilisateur</label><input type="text" id="db-user" class="form-control" value="root"></div>
                <div class="col-md-3"><label class="form-label text-muted small">Mot de passe</label><input type="password" id="db-pass" class="form-control" placeholder="(vide par défaut)"></div>
                <div class="col-md-3"><label class="form-label text-muted small fw-bold text-primary">Nom de la BDD</label><input type="text" id="db-name" class="form-control" placeholder="ex: ma_base"></div>
            </div>
            <button id="btn-test-conn" class="btn btn-primary mt-3 w-100 fw-bold"><i class="bi bi-check-circle"></i> Connecter & Commencer</button>
            <div id="conn-msg" class="mt-2 text-center fw-bold"></div>
        </div>
    </div>

    <!-- 2. BUILDER -->
    <div id="step-builder" class="d-none">
        <div class="d-flex justify-content-between align-items-end mb-4 mt-5">
            <h3 class="mb-0"><i class="bi bi-table"></i> 2. Structure des Tables</h3>
            <div>
                <button id="btn-reset" class="btn btn-outline-danger me-2"><i class="bi bi-trash"></i> Réinitialiser</button>
                <button id="btn-add-table" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Ajouter une Table</button>
            </div>
        </div>
        
        <div id="tables-container"></div>

        <button id="btn-build-db" class="btn btn-success btn-lg w-100 mt-4 shadow-sm py-3" style="font-size: 1.3rem;">
            <i class="bi bi-rocket-takeoff"></i> GÉNÉRER LA BASE DE DONNÉES
        </button>
    </div>

    <!-- 3. RESULTAT -->
    <div id="step-result" class="d-none mt-5">
        <h3 class="mb-3"><i class="bi bi-diagram-3"></i> 3. Schéma Conceptuel</h3>
        <div class="position-relative mb-4">
            <div id="schema-output" class="schema-box"></div>
            <button id="btn-copy-schema" class="btn btn-light btn-sm position-absolute top-0 end-0 m-3 fw-bold"><i class="bi bi-copy"></i> Copier</button>
        </div>

        <h3 class="mb-3"><i class="bi bi-code-square"></i> 4. Code SQL Complet</h3>
        <div class="position-relative">
            <div id="sql-output" class="schema-box" style="color: #61afef;"></div>
            <button id="btn-copy-sql" class="btn btn-light btn-sm position-absolute top-0 end-0 m-3 fw-bold"><i class="bi bi-copy"></i> Copier SQL</button>
        </div>
    </div>
</div>

<!-- TEMPLATES HTML -->
<template id="tpl-table">
    <div class="card table-card">
        <div class="table-card-header">
            <input type="text" class="table-name-input" placeholder="Nom de la table (ex: utilisateurs)">
            <button class="btn btn-outline-danger btn-sm btn-remove-table"><i class="bi bi-trash3-fill"></i> Supprimer</button>
        </div>
        <div class="card-body p-0">
            <div class="fields-container"></div>
            <div class="p-3 bg-light border-top">
                <button class="btn btn-sm btn-secondary btn-add-field"><i class="bi bi-plus-circle"></i> Ajouter un champ</button>
            </div>
        </div>
    </div>
</template>

<template id="tpl-field">
    <div class="field-row">
        <div class="field-grid">
            <input type="text" class="form-control field-name-input" placeholder="Nom du champ">
            
            <select class="form-select field-type-select">
                <option value="INT">INT</option>
                <option value="VARCHAR">VARCHAR</option>
                <option value="TEXT">TEXT</option>
                <option value="DECIMAL">DECIMAL</option>
                <option value="FLOAT">FLOAT</option>
                <option value="DOUBLE">DOUBLE</option>
                <option value="BOOLEAN">BOOLEAN</option>
                <option value="DATE">DATE</option>
                <option value="DATETIME">DATETIME</option>
                <option value="ENUM">ENUM</option>
            </select>

            <input type="text" class="form-control field-length-input" placeholder="Taille / Valeurs">
            
            <div class="options-group">
                <label title="Not Null (Obligatoire)">
                    <input class="opt-checkbox nn-check" type="checkbox" checked>
                    <div class="opt-label">NN</div>
                </label>
                <label title="Unique (Pas de doublons)">
                    <input class="opt-checkbox uq-check" type="checkbox">
                    <div class="opt-label">UQ</div>
                </label>
                <label title="Clé Primaire">
                    <input class="opt-checkbox pk-check" type="checkbox">
                    <div class="opt-label">PK</div>
                </label>
                <label title="Auto Increment">
                    <input class="opt-checkbox ai-check" type="checkbox">
                    <div class="opt-label">A.I.</div>
                </label>
                <label title="Fichier / Image">
                    <input class="opt-checkbox fi-check" type="checkbox">
                    <div class="opt-label">FI</div>
                </label>
                <label title="Clé Étrangère">
                    <input class="opt-checkbox fk-check" type="checkbox">
                    <div class="opt-label">FK</div>
                </label>
            </div>

            <button class="btn btn-light text-danger btn-remove-field" title="Supprimer le champ"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="fk-zone d-none">
            <i class="bi bi-link-45deg fs-4 text-warning"></i>
            <span class="fw-bold text-muted small">Fait référence à :</span>
            <select class="form-select form-select-sm w-auto fk-table-select">
                <option value="">-- Choisir la Table --</option>
            </select>
            <i class="bi bi-arrow-right text-muted"></i>
            <select class="form-select form-select-sm w-auto fk-field-select">
                <option value="">-- Choisir le Champ --</option>
            </select>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    
    // --- SYSTEME DE SAUVEGARDE AUTO ---
    function saveState() {
        if (!document.getElementById('db-host')) return;

        const state = {
            conn: {
                host: document.getElementById('db-host').value,
                user: document.getElementById('db-user').value,
                pass: document.getElementById('db-pass').value,
                dbname: document.getElementById('db-name').value
            },
            isBuilderActive: !document.getElementById('step-builder').classList.contains('d-none'),
            tables: []
        };

        // On ne cible STRICTEMENT QUE les cartes dans tables-container (Correction du Bug Ligne 196)
        document.querySelectorAll('#tables-container .table-card').forEach(tableCard => {
            const fields = [];
            tableCard.querySelectorAll('.field-row').forEach(fieldRow => {
                fields.push({
                    name: fieldRow.querySelector('.field-name-input').value,
                    type: fieldRow.querySelector('.field-type-select').value,
                    length: fieldRow.querySelector('.field-length-input').value,
                    nn: fieldRow.querySelector('.nn-check').checked,
                    uq: fieldRow.querySelector('.uq-check').checked,
                    pk: fieldRow.querySelector('.pk-check').checked,
                    ai: fieldRow.querySelector('.ai-check').checked,
                    fi: fieldRow.querySelector('.fi-check').checked,
                    fk: fieldRow.querySelector('.fk-check').checked,
                    fkTable: fieldRow.querySelector('.fk-table-select').value,
                    fkField: fieldRow.querySelector('.fk-field-select').value
                });
            });
            
            const tableNameInput = tableCard.querySelector('.table-name-input');
            if(tableNameInput) {
                state.tables.push({
                    name: tableNameInput.value,
                    fields: fields
                });
            }
        });

        localStorage.setItem('dbBuilderDraft', JSON.stringify(state));
    }

    document.addEventListener('input', saveState);
    document.addEventListener('change', saveState);

    document.getElementById('btn-reset').addEventListener('click', () => {
        if(confirm("Êtes-vous sûr de vouloir effacer tout le schéma et recommencer à zéro ?")) {
            localStorage.removeItem('dbBuilderDraft');
            location.reload();
        }
    });

    // --- RESTAURATION SECURISEE ---
    function restoreState() {
        const saved = localStorage.getItem('dbBuilderDraft');
        if (saved) {
            try {
                const state = JSON.parse(saved);
                
                if(state.conn) {
                    document.getElementById('db-host').value = state.conn.host || 'localhost';
                    document.getElementById('db-user').value = state.conn.user || 'root';
                    document.getElementById('db-pass').value = state.conn.pass || '';
                    document.getElementById('db-name').value = state.conn.dbname || '';
                }

                if (state.isBuilderActive) {
                    document.getElementById('step-builder').classList.remove('d-none');
                    if (state.tables && state.tables.length > 0) {
                        state.tables.forEach(tableData => addTable(tableData));
                    } else {
                        addTable();
                    }
                }
            } catch (e) {
                console.error("Fichier de sauvegarde corrompu, nettoyage en cours.");
                localStorage.removeItem('dbBuilderDraft');
            }
        }
    }

    // --- CONNEXION ---
    document.getElementById('btn-test-conn').addEventListener('click', async () => {
        const host = document.getElementById('db-host').value;
        const user = document.getElementById('db-user').value;
        const pass = document.getElementById('db-pass').value;
        const dbname = document.getElementById('db-name').value;
        const msgDiv = document.getElementById('conn-msg');

        if(!dbname) { msgDiv.innerHTML = '<span class="text-danger">Veuillez renseigner un nom de BDD.</span>'; return; }
        msgDiv.innerHTML = '<span class="text-primary"><span class="spinner-border spinner-border-sm"></span> Connexion...</span>';

        try {
            const res = await fetch('api/db_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: 'test_connection', host, user, pass, dbname })
            });
            const data = await res.json();

            if (data.success) {
                msgDiv.innerHTML = `<span class="text-success"><i class="bi bi-check"></i> ${data.message}</span>`;
                document.getElementById('step-builder').classList.remove('d-none');
                if(document.getElementById('tables-container').children.length === 0) addTable();
                saveState();
            } else {
                msgDiv.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> ${data.message}</span>`;
            }
        } catch (e) {
            msgDiv.innerHTML = `<span class="text-danger">Erreur serveur.</span>`;
        }
    });

    // --- BUILDER INTERFACE ---
    const tplTable = document.getElementById('tpl-table');
    const tplField = document.getElementById('tpl-field');
    const tablesContainer = document.getElementById('tables-container');

    function addTable(savedData = null) {
        const clone = tplTable.content.cloneNode(true);
        const tableElement = clone.querySelector('.table-card');
        
        if (savedData) tableElement.querySelector('.table-name-input').value = savedData.name;

        clone.querySelector('.btn-add-field').addEventListener('click', () => { addField(tableElement); saveState(); });
        clone.querySelector('.btn-remove-table').addEventListener('click', () => { tableElement.remove(); saveState(); });
        
        tablesContainer.appendChild(tableElement);

        if (savedData && savedData.fields.length > 0) {
            savedData.fields.forEach(fData => addField(tableElement, fData));
        } else {
            addField(tableElement);
        }
    }

    function addField(tableElement, savedData = null) {
        const fieldsContainer = tableElement.querySelector('.fields-container');
        const clone = tplField.content.cloneNode(true);
        const fieldElement = clone.querySelector('.field-row');

        const typeSelect = clone.querySelector('.field-type-select');
        const lengthInput = clone.querySelector('.field-length-input');
        const fkCheck = clone.querySelector('.fk-check');
        const fkZone = clone.querySelector('.fk-zone');
        const fkTableSelect = clone.querySelector('.fk-table-select');
        const fkFieldSelect = clone.querySelector('.fk-field-select');

        // Appliquer les données sauvegardées
        if (savedData) {
            fieldElement.querySelector('.field-name-input').value = savedData.name || '';
            typeSelect.value = savedData.type || 'INT';
            lengthInput.value = savedData.length || '';
            fieldElement.querySelector('.nn-check').checked = savedData.nn || false;
            fieldElement.querySelector('.uq-check').checked = savedData.uq || false;
            fieldElement.querySelector('.pk-check').checked = savedData.pk || false;
            fieldElement.querySelector('.ai-check').checked = savedData.ai || false;
            fieldElement.querySelector('.fi-check').checked = savedData.fi || false;
            fkCheck.checked = savedData.fk || false;
            
            if (savedData.fk) {
                fkZone.classList.remove('d-none');
                fkTableSelect.innerHTML = `<option value="${savedData.fkTable}">${savedData.fkTable}</option>`;
                fkFieldSelect.innerHTML = `<option value="${savedData.fkField}">${savedData.fkField}</option>`;
            }
        }

        typeSelect.addEventListener('change', (e) => {
            const val = e.target.value;
            if (val === 'VARCHAR' && lengthInput.value === '') {
                lengthInput.value = '255'; lengthInput.placeholder = "Taille (ex: 255)";
            } else if (val === 'ENUM') {
                lengthInput.placeholder = "ex: 'Admin', 'User'";
                if(lengthInput.value === '255') lengthInput.value = '';
            } else if (val === 'DECIMAL') {
                lengthInput.placeholder = "ex: 10,2";
                if(lengthInput.value === '255') lengthInput.value = '';
            } else {
                lengthInput.placeholder = "Taille / Valeurs";
                if(lengthInput.value === '255') lengthInput.value = '';
            }
        });

        fieldElement.querySelector('.ai-check').addEventListener('change', (e) => {
            if(e.target.checked && typeSelect.value !== 'INT') typeSelect.value = 'INT';
        });

        // --- GESTION FK ---
        const refreshTablesList = () => {
            const currentVal = fkTableSelect.value;
            let expectedTables = [];
            document.querySelectorAll('#tables-container .table-name-input').forEach(input => {
                const tName = input.value.trim();
                if(tName) expectedTables.push(tName);
            });
            let currentOptions = Array.from(fkTableSelect.options).map(o => o.value).filter(v => v !== "");
            if (JSON.stringify(expectedTables) === JSON.stringify(currentOptions)) return;

            fkTableSelect.innerHTML = '<option value="">-- Choisir la Table --</option>';
            expectedTables.forEach(tName => fkTableSelect.add(new Option(tName, tName)));
            if (expectedTables.includes(currentVal)) fkTableSelect.value = currentVal;
        };

        const refreshFieldsList = () => {
            const selectedTable = fkTableSelect.value;
            const currentVal = fkFieldSelect.value;
            let expectedFields = [];

            if (selectedTable) {
                document.querySelectorAll('#tables-container .table-card').forEach(card => {
                    const tNameInput = card.querySelector('.table-name-input');
                    if(tNameInput && tNameInput.value.trim() === selectedTable) {
                        card.querySelectorAll('.field-name-input').forEach(input => {
                            const fName = input.value.trim();
                            if(fName) expectedFields.push(fName);
                        });
                    }
                });
            }

            let currentOptions = Array.from(fkFieldSelect.options).map(o => o.value).filter(v => v !== "");
            if (JSON.stringify(expectedFields) === JSON.stringify(currentOptions)) return;

            fkFieldSelect.innerHTML = '<option value="">-- Choisir le Champ --</option>';
            expectedFields.forEach(fName => fkFieldSelect.add(new Option(fName, fName)));
            if (expectedFields.includes(currentVal)) fkFieldSelect.value = currentVal;
        };

        fkCheck.addEventListener('change', (e) => {
            if(e.target.checked) {
                fkZone.classList.remove('d-none');
                refreshTablesList();
                refreshFieldsList();
            } else {
                fkZone.classList.add('d-none');
            }
        });

        fkTableSelect.addEventListener('mouseenter', refreshTablesList);
        fkTableSelect.addEventListener('change', refreshFieldsList);
        fkFieldSelect.addEventListener('mouseenter', refreshFieldsList);

        clone.querySelector('.btn-remove-field').addEventListener('click', () => { fieldElement.remove(); saveState(); });
        fieldsContainer.appendChild(fieldElement);
    }

    // Chargement de la sauvegarde (avant d'ajouter les events liés au click)
    restoreState();

    // --- AJOUTER UNE TABLE ---
    document.getElementById('btn-add-table').addEventListener('click', () => {
        addTable();
        saveState();
    });

    // --- GENERER LA BASE ---
    document.getElementById('btn-build-db').addEventListener('click', async () => {
        const tablesData = [];
        let hasError = false;
        
        document.querySelectorAll('#tables-container .table-card').forEach(tableCard => {
            const tableName = tableCard.querySelector('.table-name-input').value.trim();
            if (!tableName) return;

            const fields = [];
            tableCard.querySelectorAll('.field-row').forEach(fieldRow => {
                const name = fieldRow.querySelector('.field-name-input').value.trim();
                if (!name) return;

                const type = fieldRow.querySelector('.field-type-select').value;
                const length = fieldRow.querySelector('.field-length-input').value.trim();
                const isFk = fieldRow.querySelector('.fk-check').checked;
                const fkTable = fieldRow.querySelector('.fk-table-select').value;
                const fkField = fieldRow.querySelector('.fk-field-select').value;

                if (type === 'VARCHAR' && length === '') {
                    alert(`Erreur : Le champ '${name}' (table '${tableName}') nécessite une taille.`);
                    hasError = true;
                }
                if (type === 'ENUM' && length === '') {
                    alert(`Erreur : Le champ '${name}' (table '${tableName}') nécessite des valeurs.`);
                    hasError = true;
                }
                if (isFk && (!fkTable || !fkField)) {
                    alert(`Erreur : Vous avez coché FK pour '${name}' mais la cible est incomplète.`);
                    hasError = true;
                }

                fields.push({
                    name: name, type: type, length: length,
                    isNn: fieldRow.querySelector('.nn-check').checked,
                    isUq: fieldRow.querySelector('.uq-check').checked,
                    isPk: fieldRow.querySelector('.pk-check').checked,
                    isAi: fieldRow.querySelector('.ai-check').checked,
                    isFi: fieldRow.querySelector('.fi-check').checked,
                    isFk: isFk, fkTable: fkTable, fkField: fkField
                });
            });

            if (fields.length > 0) tablesData.push({ name: tableName, fields: fields });
        });

        if (hasError) return;
        if (tablesData.length === 0) { alert("Veuillez créer au moins une table avec un champ."); return; }

        const host = document.getElementById('db-host').value;
        const user = document.getElementById('db-user').value;
        const pass = document.getElementById('db-pass').value;
        const dbname = document.getElementById('db-name').value;

        const btn = document.getElementById('btn-build-db');
        const oldText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Création en cours...';

        try {
            const res = await fetch('api/db_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: 'build_database', host, user, pass, dbname, tables: tablesData })
            });
            const data = await res.json();

            if (data.success) {
                document.getElementById('step-result').classList.remove('d-none');
                document.getElementById('schema-output').textContent = data.schema;
                document.getElementById('sql-output').textContent = data.sql;
                window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
            } else {
                alert(data.message);
            }
        } catch (e) {
            alert("Erreur lors de la communication avec le serveur.");
        } finally {
            btn.innerHTML = oldText;
        }
    });

    // --- COPIER ---
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

    setupCopy('btn-copy-schema', 'schema-output');
    setupCopy('btn-copy-sql', 'sql-output');
});
</script>
</body>
</html>