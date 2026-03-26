<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIO Visual Builder PRO</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap-icons.css">
    <style>
        :root { --sidebar-width: 320px; --bg-dark: #1e1e1e; --bg-panel: #2d2d2d; --accent: #0d6efd; --text: #e0e0e0; }
        body { margin: 0; padding: 0; height: 100vh; display: flex; flex-direction: column; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: var(--bg-dark); color: var(--text); overflow: hidden; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-dark); }
        ::-webkit-scrollbar-thumb { background: #555; border-radius: 3px; }

        /* Top Bar */
        .topbar { height: 50px; background-color: var(--bg-panel); border-bottom: 1px solid #444; display: flex; align-items: center; justify-content: space-between; padding: 0 20px; }
        .topbar h1 { font-size: 1.2rem; margin: 0; font-weight: 600; color: #fff; }
        .topbar .btn-group button { border-radius: 4px; padding: 4px 12px; font-size: 0.85rem; }

        /* Main Workspace */
        .workspace { display: flex; flex: 1; height: calc(100vh - 50px); }

        /* Left Sidebar (Files) */
        .sidebar { width: var(--sidebar-width); background-color: var(--bg-panel); border-right: 1px solid #444; display: flex; flex-direction: column; }
        .sidebar-header { padding: 15px; font-weight: bold; border-bottom: 1px solid #444; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; color: #aaa; }
        .file-list { flex: 1; overflow-y: auto; padding: 10px; list-style: none; margin: 0; }
        .file-list li { padding: 8px 12px; margin-bottom: 4px; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: 0.2s; font-size: 0.9rem; }
        .file-list li:hover { background-color: rgba(255,255,255,0.05); }
        .file-list li.active { background-color: var(--accent); color: white; font-weight: 500; }
        
        /* Canvas */
        .canvas-area { flex: 1; background-color: #121212; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; overflow: hidden; }
        .canvas-wrapper { width: 100%; height: 100%; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.5); border-radius: 8px; overflow: hidden; transition: 0.3s; position: relative; }
        iframe { width: 100%; height: 100%; border: none; background: #fff; }

        /* Right Sidebar (Inspector) */
        .inspector { width: 350px; background-color: var(--bg-panel); border-left: 1px solid #444; display: flex; flex-direction: column; overflow-y: auto; }
        .inspector-panel { padding: 15px; border-bottom: 1px solid #444; }
        .inspector-title { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: #aaa; margin-bottom: 15px; font-weight: bold; display: flex; align-items: center; gap: 8px; }
        
        .prop-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .prop-label { font-size: 0.85rem; color: #ccc; flex: 1; }
        .prop-input { flex: 1; background: #1e1e1e; border: 1px solid #444; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; width: 100%; }
        .prop-input:focus { outline: none; border-color: var(--accent); }
        .prop-input[type="color"] { height: 30px; padding: 2px; cursor: pointer; }

        .selected-badge { background: #333; padding: 5px 10px; border-radius: 4px; font-family: monospace; font-size: 0.8rem; word-break: break-all; margin-top: 10px; color: #0dcaf0; border: 1px solid #444; }

        /* State Toggle */
        .state-toggle { display: flex; background: #1e1e1e; border-radius: 6px; padding: 4px; margin-bottom: 15px; }
        .state-btn { flex: 1; text-align: center; padding: 6px; cursor: pointer; border-radius: 4px; font-size: 0.8rem; font-weight: bold; color: #aaa; transition: 0.2s; }
        .state-btn.active { background: #444; color: #fff; }
    </style>
</head>
<body>

<div class="topbar">
    <h1><i class="bi bi-palette-fill text-primary"></i> AIO Visual Builder Pro</h1>
    <div class="d-flex align-items-center gap-3">
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-box-arrow-left"></i> Quitter</a>
        <button id="btn-save" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-cloud-arrow-up-fill"></i> Sauvegarder style.css</button>
    </div>
</div>

<div class="workspace">
    <!-- LEFT SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-header"><i class="bi bi-folder2-open"></i> Explorateur de Pages</div>
        <ul class="file-list" id="file-list">
            <li class="text-center text-muted mt-3"><span class="spinner-border spinner-border-sm"></span> Chargement...</li>
        </ul>
    </div>

    <!-- CANVAS -->
    <div class="canvas-area">
        <div class="canvas-wrapper">
            <iframe id="preview-frame"></iframe>
            <div id="loading-overlay" style="position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(255,255,255,0.8); display:flex; justify-content:center; align-items:center; z-index:100; display:none;">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>

    <!-- RIGHT SIDEBAR (INSPECTOR) -->
    <div class="inspector" id="inspector" style="opacity: 0.5; pointer-events: none;">
        
        <div class="inspector-panel" style="background: rgba(13, 110, 253, 0.1); border-bottom: 2px solid var(--accent);">
            <div class="d-flex gap-2 mb-3">
                <button id="btn-sel-body" class="btn btn-sm btn-dark flex-fill border-secondary"><i class="bi bi-browser-window"></i> Cibler le Body</button>
                <button id="btn-sel-parent" class="btn btn-sm btn-dark flex-fill border-secondary" disabled><i class="bi bi-arrow-up-circle"></i> Parent</button>
            </div>
            
            <div class="inspector-title text-primary"><i class="bi bi-cursor-fill"></i> Élément Sélectionné</div>
            <div id="sel-tag" class="fw-bold fs-5 text-white">Aucun</div>
            <div id="sel-path" class="selected-badge">Cliquez sur un élément dans la page...</div>
            
            <div class="state-toggle mt-3">
                <div class="state-btn active" data-state="normal">Normal</div>
                <div class="state-btn text-warning" data-state="hover">:hover (Survol)</div>
            </div>
            
            <div class="mt-3 pt-3 border-top border-secondary">
                <div class="prop-label mb-2"><i class="bi bi-pencil-square"></i> Contenu Texte (Statique uniquement)</div>
                <div class="d-flex gap-2">
                    <input type="text" id="edit-text-input" class="prop-input" placeholder="Texte non modifiable..." disabled>
                    <button id="btn-apply-text" class="btn btn-sm btn-outline-primary" disabled><i class="bi bi-check2"></i></button>
                </div>
            </div>
        </div>

        <div class="inspector-panel">
            <div class="inspector-title"><i class="bi bi-paint-bucket"></i> Arrière-plan & Couleurs</div>
            <div class="prop-row">
                <div class="prop-label">Couleur de fond</div>
                <input type="color" class="prop-input css-prop" data-prop="background-color" value="#ffffff">
            </div>
            <div class="prop-row">
                <div class="prop-label">Couleur du texte</div>
                <input type="color" class="prop-input css-prop" data-prop="color" value="#000000">
            </div>
        </div>

        <div class="inspector-panel">
            <div class="inspector-title"><i class="bi bi-type"></i> Typographie</div>
            <div class="prop-row">
                <div class="prop-label">Famille (Font)</div>
                <select class="prop-input css-prop" data-prop="font-family">
                    <option value="">Défaut</option>
                    <option value="Arial, sans-serif">Arial</option>
                    <option value="Georgia, serif">Georgia</option>
                    <option value="Tahoma, sans-serif">Tahoma</option>
                    <option value="Verdana, sans-serif">Verdana</option>
                    <option value="'Courier New', monospace">Courier New</option>
                    <option value="'Times New Roman', serif">Times New Roman</option>
                </select>
            </div>
            <div class="prop-row">
                <div class="prop-label">Taille (px/rem)</div>
                <input type="text" class="prop-input css-prop" data-prop="font-size" placeholder="ex: 16">
            </div>
            <div class="prop-row">
                <div class="prop-label">Graisse (Weight)</div>
                <select class="prop-input css-prop" data-prop="font-weight">
                    <option value="">Par défaut</option>
                    <option value="normal">Normal (400)</option>
                    <option value="bold">Gras (700)</option>
                    <option value="900">Très Gras (900)</option>
                </select>
            </div>
            <div class="prop-row">
                <div class="prop-label">Position texte</div>
                <select class="prop-input css-prop" data-prop="text-align">
                    <option value="">Défaut</option>
                    <option value="left">Gauche</option>
                    <option value="center">Centre</option>
                    <option value="right">Droite</option>
                </select>
            </div>
        </div>

        <div class="inspector-panel">
            <div class="inspector-title"><i class="bi bi-distribute-vertical"></i> Espacements (Margin/Padding)</div>
            <div class="prop-row">
                <div class="prop-label">Padding (Intérieur)</div>
                <input type="text" class="prop-input css-prop" data-prop="padding" placeholder="ex: 10px 20px">
            </div>
            <div class="prop-row">
                <div class="prop-label">Margin (Extérieur)</div>
                <input type="text" class="prop-input css-prop" data-prop="margin" placeholder="ex: 10px auto">
            </div>
        </div>

        <div class="inspector-panel">
            <div class="inspector-title"><i class="bi bi-square"></i> Bordures</div>
            <div class="prop-row">
                <div class="prop-label">Radius (Arrondi)</div>
                <input type="text" class="prop-input css-prop" data-prop="border-radius" placeholder="ex: 8">
            </div>
            <div class="prop-row">
                <div class="prop-label">Bordure (Complète)</div>
                <input type="text" class="prop-input css-prop" data-prop="border" placeholder="ex: 2px solid red">
            </div>
        </div>
        
        <div class="inspector-panel">
            <div class="inspector-title"><i class="bi bi-arrows-move"></i> Affichage & Ombre</div>
            <div class="prop-row">
                <div class="prop-label">Box-Shadow</div>
                <input type="text" class="prop-input css-prop" data-prop="box-shadow" placeholder="ex: 0 4px 10px rgba(0,0,0,0.1)">
            </div>
            <div class="prop-row">
                <div class="prop-label">Display</div>
                <select class="prop-input css-prop" data-prop="display">
                    <option value="">Défaut</option>
                    <option value="block">Block</option>
                    <option value="inline-block">Inline-Block</option>
                    <option value="flex">Flex</option>
                    <option value="none">Masqué (None)</option>
                </select>
            </div>
            <div class="prop-row">
                 <div class="prop-label text-warning small"><i class="bi bi-info-circle"></i> Modifier le Display en "flex" permet d'ordonner ses enfants.</div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    
    // --- 1. ETAT GLOBAL ---
    let currentHtmlFile = '';
    let currentSelector = '';
    let currentState = 'normal'; // 'normal' | 'hover'
    let customStyles = {}; // { ".btn:hover": { "background-color": "red" } }

    const iframe = document.getElementById('preview-frame');
    const inspector = document.getElementById('inspector');
    const selPath = document.getElementById('sel-path');
    const selTag = document.getElementById('sel-tag');
    let injectedStyleTag = null;

    // --- 2. INITIALISATION DES FICHIERS ---
    const targetDir = localStorage.getItem('aio_target_dir') || '';
    const targetUrl = localStorage.getItem('aio_target_url') || '';

    try {
        const res = await fetch(`api/builder_api.php?action=list_files&target_dir=${encodeURIComponent(targetDir)}`);
        const data = await res.json();
        if (data.success) {
            const list = document.getElementById('file-list');
            list.innerHTML = '';
            if (data.files.length === 0) {
                list.innerHTML = '<li class="text-center text-muted mt-3">Aucun fichier trouvé. Vérifiez le dossier cible.</li>';
            }
            data.files.forEach(f => {
                const li = document.createElement('li');
                li.innerHTML = `<i class="bi bi-filetype-${f.endsWith('.php')?'php text-primary':'html text-warning'}"></i> ${f}`;
                li.onclick = () => loadFileInFrame(f, li);
                list.appendChild(li);
            });
        } else {
            document.getElementById('file-list').innerHTML = `<li class="text-danger p-2 text-center small">${data.message || 'Erreur explorateur'}</li>`;
        }
    } catch(e) { alert("Erreur chargement explorateur"); }

    // --- 3. CHARGEMENT FICHIER DANS IFRAME ---
    function loadFileInFrame(filename, liElement) {
        document.querySelectorAll('.file-list li').forEach(el => el.classList.remove('active'));
        if(liElement) liElement.classList.add('active');
        
        currentHtmlFile = filename;
        document.getElementById('loading-overlay').style.display = 'flex';
        // Utiliser l'URL statique locale ou l'URL ciblée absolue
        iframe.src = targetUrl ? (targetUrl + filename) : filename;
    }

    iframe.onload = () => {
        document.getElementById('loading-overlay').style.display = 'none';
        injectBuilderLogic();
    };

    // --- 4. INJECTION DE LA LOGIQUE BUILDER DANS L'IFRAME ---
    function injectBuilderLogic() {
        if(!iframe.contentWindow) return;
        const subDoc = iframe.contentWindow.document;
        
        // Empêcher les liens et forms de naviguer
        subDoc.querySelectorAll('a').forEach(a => a.addEventListener('click', e => e.preventDefault()));
        subDoc.querySelectorAll('form').forEach(f => f.addEventListener('submit', e => e.preventDefault()));

        // Gérer le survol et clic
        let lastOutline = null;
        let lastTarget = null;

        subDoc.addEventListener('mouseover', e => {
            if(lastOutline) lastOutline.style.outline = '';
            lastTarget = e.target;
            lastOutline = e.target;
            e.target.style.outline = '2px dashed #0d6efd';
            e.target.style.outlineOffset = '-2px';
            e.target.style.cursor = 'crosshair';
        });

        subDoc.addEventListener('mouseout', e => {
            if(lastOutline) lastOutline.style.outline = '';
        });

        subDoc.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
            if(lastOutline) lastOutline.style.outline = ''; // Remove hover outline
            
            // Remove previous selected outline
            subDoc.querySelectorAll('[data-builder-selected]').forEach(el => {
                el.removeAttribute('data-builder-selected');
                el.style.boxShadow = '';
            });

            e.target.setAttribute('data-builder-selected', 'true');
            e.target.style.boxShadow = 'inset 0 0 0 3px #0d6efd';

            const selector = generateComputedSelector(e.target);
            lastSelectedElement = e.target;
            selectElement(selector, e.target.tagName);
        }, true);

        // --- BOUTONS DOM NAVIGATION (BODY & PARENT) ---
        document.getElementById('btn-sel-body').addEventListener('click', () => {
            const bodySel = generateComputedSelector(subDoc.body);
            lastSelectedElement = subDoc.body;
            selectElement(bodySel, 'BODY');
            document.getElementById('btn-sel-parent').disabled = true;
        });

        document.getElementById('btn-sel-parent').addEventListener('click', () => {
            if (lastSelectedElement && lastSelectedElement.parentElement && lastSelectedElement.tagName !== 'BODY') {
                const parent = lastSelectedElement.parentElement;
                
                // Clear old visual selection
                subDoc.querySelectorAll('[data-builder-selected]').forEach(el => {
                    el.removeAttribute('data-builder-selected');
                    el.style.boxShadow = '';
                });

                parent.setAttribute('data-builder-selected', 'true');
                parent.style.boxShadow = 'inset 0 0 0 3px #0d6efd';
                
                lastSelectedElement = parent;
                selectElement(generateComputedSelector(parent), parent.tagName);
                
                if (parent.tagName === 'BODY' || parent.tagName === 'HTML') {
                    document.getElementById('btn-sel-parent').disabled = true;
                }
            }
        });

        // --- INJECTION HTML5 DRAG AND DROP ---
        let draggedEl = null;

        // On cible uniquement les blocs de formulaires pour éviter de casser les tableaux PHP
        subDoc.querySelectorAll('.mb-3, .col-md-6, .col-md-12').forEach(el => {
            el.setAttribute('draggable', true);
            el.style.cursor = 'grab';

            el.addEventListener('dragstart', function(e) {
                draggedEl = this;
                e.dataTransfer.effectAllowed = 'move';
                this.style.opacity = '0.4';
            });

            el.addEventListener('dragend', function(e) {
                this.style.opacity = '1';
                subDoc.querySelectorAll('.drag-over-top').forEach(d => { d.classList.remove('drag-over-top'); d.style.borderTop = ""; });
            });

            el.addEventListener('dragover', function(e) {
                e.preventDefault();
                if(this !== draggedEl && this.parentNode === draggedEl.parentNode) {
                    this.classList.add('drag-over-top');
                    this.style.borderTop = "4px solid #0d6efd"; // Indicateur visuel
                }
            });

            el.addEventListener('dragleave', function(e) {
                this.classList.remove('drag-over-top');
                this.style.borderTop = "";
            });

            el.addEventListener('drop', function(e) {
                e.preventDefault();
                this.style.borderTop = "";
                if(draggedEl && this !== draggedEl && this.parentNode === draggedEl.parentNode) {
                    let parent = this.parentNode;
                    // Déplacement DOM pour le preview visuel
                    parent.insertBefore(draggedEl, this);
                    
                    // Conversion du DOM en configuration CSS Flex Order
                    let parentSel = generateComputedSelector(parent);
                    if(!customStyles[parentSel]) customStyles[parentSel] = {};
                    customStyles[parentSel]['display'] = 'flex';
                    if (parent.classList.contains('row')) {
                        customStyles[parentSel]['flex-wrap'] = 'wrap';
                    } else {
                        customStyles[parentSel]['flex-direction'] = 'column';
                    }
                    
                    // Appliquer l'ordre css à tous les enfants frères
                    Array.from(parent.children).forEach((child, idx) => {
                        let sel = generateComputedSelector(child);
                        if(!customStyles[sel]) customStyles[sel] = {};
                        customStyles[sel]['order'] = idx + 1;
                    });
                    
                    applyLiveStyles();
                }
            });
        });

        // Injecter ou retrouver la balise <style> dynamique du builder
        injectedStyleTag = subDoc.getElementById('aio-builder-live-style');
        if(!injectedStyleTag) {
            injectedStyleTag = subDoc.createElement('style');
            injectedStyleTag.id = 'aio-builder-live-style';
            subDoc.head.appendChild(injectedStyleTag);
        }
        
        // Charger le CSS existant au premier affichage
        loadCSSFromServer();
    }

    // --- 5. GENERATION DE SELECTEUR ROBUSTE ---
    function generateComputedSelector(el) {
        if(el.tagName.toLowerCase() === 'html') return 'html';
        if(el.tagName.toLowerCase() === 'body') {
            let sel = 'body';
            // Boost specificity against Bootstrap (ex: body.bg-light)
            let classes = Array.from(el.classList).filter(c => !c.startsWith('hover-') && !c.startsWith('active-'));
            if(classes.length > 0) sel += '.' + classes.join('.');
            return sel;
        }
        
        let path = [];
        let current = el;
        
        while(current && current.tagName.toLowerCase() !== 'body') {
            let selector = current.tagName.toLowerCase();
            
            // Si on a un ID, on le priorise car unique (sauf s'il est auto-généré aléatoirement)
            if (current.id) {
                selector += '#' + current.id;
                path.unshift(selector);
                break; // Arrêt, chemin absolu trouvé
            } else {
                // Utiliser les classes utiles (éviter les classes dynamiques)
                let classes = Array.from(current.classList).filter(c => !c.startsWith('hover-') && !c.startsWith('active-'));
                if(classes.length > 0) {
                    selector += '.' + classes.join('.');
                }
                
                // Pour éviter trop d'ambiguité s'il y a des frères avec les mêmes classes
                let siblingIndex = 1;
                let sibling = current.previousElementSibling;
                while(sibling) {
                    if(sibling.tagName === current.tagName) siblingIndex++;
                    sibling = sibling.previousElementSibling;
                }
                if(siblingIndex > 1 || current.nextElementSibling) {
                    selector += `:nth-of-type(${siblingIndex})`;
                }
            }
            path.unshift(selector);
            current = current.parentElement;
        }
        return path.join(' > ');
    }

    // --- 6. LOGIQUE DE L'INSPECTEUR ---
    let lastSelectedElement = null;
    let originalText = '';

    function selectElement(selector, tagName) {
        currentSelector = selector;
        selPath.textContent = selector;
        selTag.textContent = `<${tagName.toLowerCase()}>`;
        inspector.style.opacity = '1';
        inspector.style.pointerEvents = 'auto';
        
        // Activation du bouton Parent
        document.getElementById('btn-sel-parent').disabled = (tagName.toUpperCase() === 'BODY' || tagName.toUpperCase() === 'HTML');

        // Logique de texte statique (Sécurisation PHP)
        const textInput = document.getElementById('edit-text-input');
        const applyBtn = document.getElementById('btn-apply-text');
        const safeTags = ['LABEL', 'TH', 'TD', 'DIV', 'BUTTON', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'A', 'SPAN', 'P', 'LI', 'STRONG', 'B', 'I', 'EM'];
        
        // On s'assure qu'on ne cible pas les cellules de données qui contiennent du PHP
        if(safeTags.includes(tagName.toUpperCase()) && lastSelectedElement) {
            textInput.disabled = false;
            applyBtn.disabled = false;
            originalText = lastSelectedElement.innerText.trim();
            textInput.value = originalText;
        } else {
            textInput.disabled = true;
            applyBtn.disabled = true;
            originalText = '';
            textInput.value = '';
            textInput.placeholder = 'Donnée PHP / Dynamique (Protégée)';
        }
        
        updateInspectorFields();
    }

    // Bouton de remplacement de texte
    document.getElementById('btn-apply-text').addEventListener('click', async () => {
        const newText = document.getElementById('edit-text-input').value.trim();
        if(!newText || newText === originalText || !currentHtmlFile) return;

        const btn = document.getElementById('btn-apply-text');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        try {
            const formData = new URLSearchParams();
            formData.append('action', 'replace_text');
            formData.append('target_dir', targetDir);
            formData.append('file', currentHtmlFile);
            formData.append('old_text', originalText);
            formData.append('new_text', newText);

            const res = await fetch('api/builder_api.php', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (data.success) {
                // Modification réussie dans le PHP !
                lastSelectedElement.innerText = newText;
                originalText = newText; // Mettre à jour la base
                btn.innerHTML = '<i class="bi bi-check2 text-success"></i>';
            } else {
                alert("Modification impossible: " + data.message);
                btn.innerHTML = '<i class="bi bi-x-circle text-danger"></i>';
            }
        } catch(e) { 
            alert("Erreur réseau");
            btn.innerHTML = '<i class="bi bi-check2"></i>';
        }

        setTimeout(() => { btn.innerHTML = '<i class="bi bi-check2"></i>'; }, 2000);
    });

    // Change State (Normal / Hover)
    document.querySelectorAll('.state-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.state-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentState = this.dataset.state;
            updateInspectorFields(); // Rafraichir les champs avec les valeurs du nouvel état
        });
    });

    function getActiveSelector() {
        return currentState === 'hover' ? currentSelector + ':hover' : currentSelector;
    }

    // Met à jour les inputs avec les valeurs connues dans le JSON local
    function updateInspectorFields() {
        if(!currentSelector) return;
        const targetSel = getActiveSelector();
        
        document.querySelectorAll('.css-prop').forEach(input => {
            const prop = input.dataset.prop;
            if(customStyles[targetSel] && customStyles[targetSel][prop]) {
                input.value = customStyles[targetSel][prop];
            } else {
                input.value = input.type === 'color' ? '#000000' : '';
            }
        });
    }

    // Ecoute des modifications de inputs CSS
    document.querySelectorAll('.css-prop').forEach(input => {
        input.addEventListener('input', function() {
            if(!currentSelector) return;
            const targetSel = getActiveSelector();
            const prop = this.dataset.prop;
            let val = this.value;

            // Auto-append 'px' if user just typed a number for dimensions
            if (val && /^[0-9]+$/.test(val.trim())) {
                if (['padding', 'margin', 'border-radius', 'font-size'].includes(prop)) {
                    val += 'px';
                }
            }

            if(!customStyles[targetSel]) customStyles[targetSel] = {};
            
            if(val === '' && input.type !== 'color') {
                delete customStyles[targetSel][prop];
                if(Object.keys(customStyles[targetSel]).length === 0) delete customStyles[targetSel];
            } else {
                customStyles[targetSel][prop] = val;
            }
            applyLiveStyles();
        });
    });

    // Applique le JSON dans la balise <style> de l'iframe
    function applyLiveStyles() {
        if(!injectedStyleTag) return;
        let cssText = '/* AIO Visuel Builder Auto-Generated */\n\n';
        
        for(let sel in customStyles) {
            cssText += sel + ' {\n';
            for(let prop in customStyles[sel]) {
                // Rendre les règles prioritaires (!) pour out-passer Bootstrap
                cssText += `  ${prop}: ${customStyles[sel][prop]} !important;\n`;
            }
            cssText += '}\n\n';
        }
        injectedStyleTag.textContent = cssText;
    }

    // --- 7. CHARGEMENT ET SAUVEGARDE VIA API ---
    async function loadCSSFromServer() {
        if (!targetDir) return;
        try {
            const res = await fetch(`api/builder_api.php?action=load_css&target_dir=${encodeURIComponent(targetDir)}`);
            const data = await res.json();
            if(data.success && data.css) {
                parseCSSToObject(data.css);
                applyLiveStyles();
            }
        } catch(e) { console.error("Erreur chargement CSS existant", e); }
    }

    function parseCSSToObject(cssText) {
        customStyles = {};
        const cssRegex = /(.*?)\s*{\s*([^}]+)\s*}/g;
        let match;
        while((match = cssRegex.exec(cssText)) !== null) {
            let selector = match[1].trim();
            if(selector.startsWith('/*')) continue;
            
            let rulesBlock = match[2];
            let rules = {};
            
            let ruleRegex = /([^:]+):\s*([^;]+);/g;
            let rm;
            while((rm = ruleRegex.exec(rulesBlock)) !== null) {
                let prop = rm[1].trim();
                let val = rm[2].replace('!important', '').trim();
                rules[prop] = val;
            }
            if(Object.keys(rules).length > 0) {
                customStyles[selector] = rules;
            }
        }
    }

    document.getElementById('btn-save').addEventListener('click', async () => {
        let cssText = '';
        for(let sel in customStyles) {
            cssText += sel + ' {\n';
            for(let prop in customStyles[sel]) {
                cssText += `  ${prop}: ${customStyles[sel][prop]} !important;\n`;
            }
            cssText += '}\n\n';
        }

        if(cssText.trim() === '') {
            alert("Aucun style n'a été ajouté visuellement. Cliquez sur un élément et modifiez une propriété d'abord.");
            return;
        }

        const btn = document.getElementById('btn-save');
        const oldBtn = btn.innerHTML;
        btn.innerHTML = 'Enregistrement...';

        try {
            const formData = new URLSearchParams();
            formData.append('action', 'save_css');
            formData.append('css', cssText);
            formData.append('target_dir', targetDir);

            const res = await fetch('api/builder_api.php', { method: 'POST', body: formData });
            const data = await res.json();
            if(data.success) {
                btn.classList.replace('btn-primary', 'btn-success');
                btn.innerHTML = '<i class="bi bi-check"></i> style.css Sauvegardé !';
            } else { alert("Erreur: " + data.message); }
        } catch(e) { alert("Erreur réseau"); }

        setTimeout(() => { btn.classList.replace('btn-success', 'btn-primary'); btn.innerHTML = oldBtn; }, 2000);
    });

});
</script>
</body>
</html>
