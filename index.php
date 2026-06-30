<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
	<link rel="icon" href="favicon.ico">
    <title>SCUM Local Player Map</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">
    <style>
        * { box-sizing: border-box; }
        body { 
            background: #1a1a1a; 
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh; 
            margin: 0; 
            padding: 20px;
            gap: 12px;
            color: white; 
            font-family: sans-serif; 
        }

        /* ── Sidebar title ── */
        .sidebar-title {
            text-align: center;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px solid #00ffcc33;
            line-height: 1.2;
        }
        .sidebar-title .brand {
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #00ffcc;
        }
        .sidebar-title .map-name {
            display: block;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #ffffff;
            margin-top: 2px;
        }

        /* ── Sidebars ── */
        .sidebar {
            width: 180px;
            flex-shrink: 0;
            background: #111;
            border: 1px solid #00ffcc33;
            border-radius: 8px;
            padding: 12px;
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }
        .sidebar h3 {
            margin: 0 0 10px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #00ffcc;
            border-bottom: 1px solid #00ffcc33;
            padding-bottom: 8px;
        }
        .sidebar-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 2px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            color: #ccc;
            user-select: none;
        }
        .sidebar-row:hover { background: #1e1e1e; color: #fff; }
        .sidebar-row input[type="checkbox"] {
            accent-color: #00ffcc;
            width: 14px;
            height: 14px;
            cursor: pointer;
            flex-shrink: 0;
        }
        .sidebar-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .sidebar-label {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-count {
            font-size: 10px;
            color: #666;
            flex-shrink: 0;
        }
        .follow-btn {
            background: none;
            border: none;
            padding: 0 0 0 2px;
            cursor: pointer;
            font-size: 13px;
            line-height: 1;
            opacity: 0.3;
            flex-shrink: 0;
            transition: opacity 0.15s;
        }
        .follow-btn:hover  { opacity: 0.8; }
        .follow-btn.active { opacity: 1; filter: drop-shadow(0 0 3px #00ffcc); }
        .sidebar-empty {
            font-size: 12px;
            color: #555;
            font-style: italic;
            padding: 4px 2px;
        }

        /* ── Map ── */
        #mapShell {
            /* Sized once to the auto-fit zoom; never changes after init */
            position: relative;
            flex-shrink: 0;
            overflow: hidden;
        }
        #mapViewport {
            /* Dynamically sized to zoom level; shadow tracks the real map edge */
            position: absolute;
            top: 0;
            left: 0;
            width: 1080px;
            height: 1080px;
            pointer-events: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.8);
        }
        #mapContainer { 
            position: absolute;
            top: 0;
            left: 0;
            width: 1080px; 
            height: 1080px; 
            transform-origin: 0 0;
            will-change: transform;
        }
        canvas { 
            position: absolute; 
            top: 0; 
            left: 0; 
        }

        /* ── Zoom controls ── */
        .zoom-controls {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 4px;
        }
        .zoom-btn {
            background: #2a2a2a;
            border: 1px solid #444;
            border-radius: 4px;
            color: #ccc;
            font-size: 16px;
            line-height: 1;
            width: 28px;
            height: 28px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .zoom-btn:hover { background: #3a3a3a; color: #fff; border-color: #00ffcc; }
        #zoomLabel {
            flex: 1;
            text-align: center;
            font-size: 12px;
            color: #aaa;
            font-variant-numeric: tabular-nums;
        }

        /* ── Context menu ── */
        #contextMenu {
            position: fixed;
            background: rgba(10, 10, 10, 0.92);
            border: 1px solid #00ffcc66;
            border-radius: 6px;
            padding: 4px 0;
            display: none;
            z-index: 1000;
            min-width: 160px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.6);
        }
        #contextMenu button {
            display: block;
            width: 100%;
            background: none;
            border: none;
            color: #ffffff;
            font: 13px sans-serif;
            padding: 8px 16px;
            text-align: left;
            cursor: pointer;
        }
        #contextMenu button:hover { background: rgba(0,255,204,0.15); color: #00ffcc; }
        #contextMenu .ctx-divider { border: none; border-top: 1px solid #ffffff22; margin: 4px 0; }
        #contextMenu .ctx-danger:hover { background: rgba(255,60,60,0.2); color: #ff6060; }

        /* ── Tooltips ── */
        #mouseTooltip {
            position: fixed;
            background: rgba(0,0,0,0.75);
            color: #00ffcc;
            font: bold 12px monospace;
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #00ffcc44;
            pointer-events: none;
            display: none;
            white-space: nowrap;
            z-index: 999;
        }
        #markerTooltip {
            position: fixed;
            background: rgba(0,0,0,0.85);
            color: #ffffff;
            font: bold 13px sans-serif;
            padding: 5px 10px;
            border-radius: 4px;
            border: 1px solid #ffffff44;
            pointer-events: none;
            display: none;
            white-space: nowrap;
            z-index: 998;
        }

        /* ── Marker dialog ── */
        #markerDialog {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        #markerDialog.open { display: flex; }
        #markerDialogBox {
            background: #1e1e1e;
            border: 1px solid #00ffcc55;
            border-radius: 10px;
            padding: 24px;
            width: 320px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.8);
        }
        #markerDialogBox h3 {
            margin: 0 0 16px;
            color: #00ffcc;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        #markerDialogBox input[type="text"] {
            width: 100%;
            background: #111;
            border: 1px solid #444;
            border-radius: 5px;
            color: #fff;
            font-size: 14px;
            padding: 8px 10px;
            box-sizing: border-box;
            margin-bottom: 14px;
        }
        #markerDialogBox input[type="text"]:focus { outline: none; border-color: #00ffcc; }
        #markerTypeGrid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }
        .marker-type-btn {
            background: #2a2a2a;
            border: 2px solid #444;
            border-radius: 6px;
            color: #ccc;
            font-size: 11px;
            padding: 8px 4px;
            cursor: pointer;
            text-align: center;
            transition: border-color 0.15s, color 0.15s;
        }
        .marker-type-btn:hover { border-color: #888; color: #fff; }
        .marker-type-btn.selected { border-color: #00ffcc; color: #fff; background: #1a3a33; }
        .marker-type-dot { width: 16px; height: 16px; border-radius: 50%; margin: 0 auto 5px; }
        #markerDialogActions { display: flex; gap: 8px; justify-content: flex-end; }
        #markerDialogActions button {
            padding: 8px 18px;
            border-radius: 5px;
            border: none;
            font-size: 13px;
            cursor: pointer;
        }
        #markerCancelBtn { background: #333; color: #aaa; }
        #markerCancelBtn:hover { background: #444; }
        #markerSaveBtn { background: #00ffcc; color: #000; font-weight: bold; }
        #markerSaveBtn:hover { background: #00e6b8; }
    </style>
</head>
<body>

<!-- Left sidebar: Players, Markers, Options -->
<div class="sidebar" id="playerSidebar">
    <div class="sidebar-title">
        <span class="brand">NerdMaps</span>
        <span class="map-name">SCUM</span>
    </div>
    <h3>Players</h3>
    <div id="playerList"><div class="sidebar-empty">No players online</div></div>

    <h3 style="margin-top:16px">Markers</h3>
    <div id="markerList"><div class="sidebar-empty">No markers placed</div></div>

    <h3 style="margin-top:16px">Options</h3>
    <label class="sidebar-row">
        <input type="checkbox" id="optCoordTooltip" checked>
        <span class="sidebar-label">Coord tooltip</span>
    </label>
    <label class="sidebar-row">
        <input type="checkbox" id="optSectorGrid" checked>
        <span class="sidebar-label">Sector grid</span>
    </label>
    <div style="font-size:12px;color:#aaa;margin-top:10px;margin-bottom:4px;">Zoom</div>
    <div class="zoom-controls">
        <button class="zoom-btn" id="zoomOut">−</button>
        <span id="zoomLabel">100%</span>
        <button class="zoom-btn" id="zoomIn">+</button>
    </div>
</div>

<!-- Map -->
<div id="mapShell">
<div id="mapViewport"></div>
<div id="mapContainer">
    <img src="scum_map-1080x1080.png" width="1080" height="1080" alt="SCUM Map">
    <canvas id="mapCanvas" width="1080" height="1080"></canvas>
</div>
</div>
<div id="mouseTooltip"></div>
<div id="markerTooltip"></div>

<!-- Context menu -->
<div id="contextMenu">
    <button id="ctxPing">📡 Ping</button>
    <button id="ctxAddMarker">📍 Add Marker</button>
    <hr class="ctx-divider" id="ctxMarkerDivider" style="display:none">
    <button id="ctxRemoveMarker" class="ctx-danger" style="display:none">🗑 Remove Marker</button>
</div>

<!-- Marker placement dialog -->
<div id="markerDialog">
    <div id="markerDialogBox">
        <h3>Add Marker</h3>
        <input type="text" id="markerLabel" placeholder="Label (e.g. My Truck)" maxlength="64">
        <div id="markerTypeGrid"></div>
        <div id="markerDialogActions">
            <button id="markerCancelBtn">Cancel</button>
            <button id="markerSaveBtn">Place Marker</button>
        </div>
    </div>
</div>

<script>
    const canvas = document.getElementById('mapCanvas');
    const ctx = canvas.getContext('2d');

    const mapSize = 1080;

    // ── Zoom state ─────────────────────────────────────────────────
    const ZOOM_MIN   = 0.5;
    const ZOOM_MAX   = 2.0;
    const ZOOM_STEP  = 0.1;
    let   zoom       = 1.0;
    let   panX       = 0;   // px offset from origin (top-left of viewport)
    let   panY       = 0;

    function clampPan(z, px, py) {
        // Shell is fixed; content is mapSize*z. Clamp so content doesn't leave shell.
        const shell = document.getElementById('mapShell');
        const shellW = shell.offsetWidth  || Math.round(mapSize * zoom);
        const shellH = shell.offsetHeight || Math.round(mapSize * zoom);
        const contentW = mapSize * z;
        const contentH = mapSize * z;
        const maxPanX = contentW - shellW;
        const maxPanY = contentH - shellH;
        return {
            x: maxPanX <= 0 ? 0 : Math.max(-maxPanX, Math.min(0, px)),
            y: maxPanY <= 0 ? 0 : Math.max(-maxPanY, Math.min(0, py))
        };
    }

    function applyTransform() {
        const clamped = clampPan(zoom, panX, panY);
        panX = clamped.x;
        panY = clamped.y;
        document.getElementById('mapContainer').style.transform =
            `translate(${panX}px, ${panY}px) scale(${zoom})`;
        // Shadow overlay matches the shell (which is fixed after init)
        const shell = document.getElementById('mapShell');
        const vp    = document.getElementById('mapViewport');
        vp.style.width  = shell.offsetWidth  + 'px';
        vp.style.height = shell.offsetHeight + 'px';
        document.getElementById('zoomLabel').textContent = Math.round(zoom * 100) + '%';
        // Show grab cursor only when there's room to pan
        const shell2 = document.getElementById('mapShell');
        if (shell2 && !isPanning) {
            shell2.style.cursor = Math.round(mapSize * zoom) > shell2.offsetWidth + 1 ? 'grab' : '';
        }
    }

    // Convert viewport-relative pixel (after zoom/pan) → canvas pixel
    function viewportToCanvas(vx, vy) {
        return { cx: (vx - panX) / zoom, cy: (vy - panY) / zoom };
    }

    const worldMinX = -904800;
    const worldMaxX =  616818;
    const worldMinY = -904800;
    const worldMaxY =  618818;

    const worldWidth  = worldMaxX - worldMinX;
    const worldHeight = worldMaxY - worldMinY;

    function gameToPixelX(gameX) { return ((worldMaxX - gameX) / worldWidth) * mapSize; }
    function gameToPixelY(gameY) { return ((worldMaxY - gameY) / worldHeight) * mapSize; }
    function pixelToGameX(px)    { return worldMaxX - (px / mapSize) * worldWidth; }
    function pixelToGameY(py)    { return worldMaxY - (py / mapSize) * worldHeight; }

    // ── Marker type definitions ───────────────────────────────────
    const MARKER_TYPES = {
        vehicle:    { label: 'Vehicle',    color: '#ff44d3', tiClass: 'ti-car'            },
        tractor:    { label: 'Tractor',    color: '#b442ff', tiClass: 'ti-tractor'     },
        plane:      { label: 'Plane',      color: '#cc44ff', tiClass: 'ti-plane-tilt'     },
        bicycle:    { label: 'Bicycle',    color: '#ff42ff', tiClass: 'ti-bike'           },
        fuel:       { label: 'Fuel',       color: '#ff8800', tiClass: 'ti-gas-station'    },
        food:       { label: 'Food',       color: '#44cc44', tiClass: 'ti-meat'           },
        water:      { label: 'Water',      color: '#4488ff', tiClass: 'ti-droplet'        },
        power:      { label: 'Power',      color: '#ffee00', tiClass: 'ti-bolt'           },
        ammunition: { label: 'Ammunition', color: '#aaaaaa', tiClass: 'ti-box'            },
        weapon:     { label: 'Weapon',     color: '#ff4444', tiClass: 'ti-sword'          },
        danger:     { label: 'Danger',     color: '#ff0000', tiClass: 'ti-alert-triangle' },
        general:    { label: 'General',    color: '#ffffff', tiClass: 'ti-map-pin'        },
    };

    const BADGE_RADIUS = 9;

    // ── Preferences (localStorage) ───────────────────────────────
    const PREFS_KEY = 'nerdmaps_prefs';

    function loadPrefs() {
        try { return JSON.parse(localStorage.getItem(PREFS_KEY)) || {}; } catch { return {}; }
    }
    function savePrefs() {
        try {
            localStorage.setItem(PREFS_KEY, JSON.stringify({
                hiddenPlayers:     [...hiddenPlayers],
                hiddenMarkerTypes: [...hiddenMarkerTypes],
                showCoordTooltip,
                showSectorGrid,
                zoom
            }));
        } catch {}
    }

    const _prefs = loadPrefs();

    // ── Visibility state ──────────────────────────────────────────
    const hiddenPlayers     = new Set(_prefs.hiddenPlayers     || []);
    const hiddenMarkerTypes = new Set(_prefs.hiddenMarkerTypes || []);

    // ── Options state ─────────────────────────────────────────────
    let showCoordTooltip = _prefs.showCoordTooltip !== undefined ? _prefs.showCoordTooltip : true;
    let showSectorGrid   = _prefs.showSectorGrid   !== undefined ? _prefs.showSectorGrid   : true;

    // Restore zoom if saved (autoFitZoom will cap it down if needed)
    if (_prefs.zoom) zoom = Math.max(ZOOM_MIN, Math.min(ZOOM_MAX, _prefs.zoom));

    // Sync checkboxes to loaded prefs (script is deferred to bottom of body)
    document.getElementById('optCoordTooltip').checked = showCoordTooltip;
    document.getElementById('optSectorGrid').checked   = showSectorGrid;

    // ── Sector grid data ──────────────────────────────────────────
    // Boundaries in game coords (player.y = East/West, player.x = North/South)
    // Each sector is ~304,000 UE units wide/tall
    const SECTOR_ROW_LABELS = ['D','C','B','A','Z'];
    const SECTOR_COL_NUMS   = [4, 3, 2, 1, 0];

    // player.y values for vertical grid lines (West to East on screen = high to low player.y)
    const gridColBoundaries = [617505, 313373, 9241, -294891, -599023, -903155];
    // player.x values for horizontal grid lines (North to South on screen = high to low player.x)
    const gridRowBoundaries = [617953, 313597, 9241, -295115, -599471, -903827];

    function drawSectorGrid() {
        ctx.save();
        ctx.strokeStyle = 'rgba(0, 0, 0, 1)';
        ctx.lineWidth   = 2;
        ctx.setLineDash([6, 4]);

        // Vertical lines
        gridColBoundaries.forEach(gy => {
            const px = gameToPixelX(gy);
            ctx.beginPath();
            ctx.moveTo(px, 0);
            ctx.lineTo(px, mapSize);
            ctx.stroke();
        });

        // Horizontal lines
        gridRowBoundaries.forEach(gx => {
            const py = gameToPixelY(gx);
            ctx.beginPath();
            ctx.moveTo(0, py);
            ctx.lineTo(mapSize, py);
            ctx.stroke();
        });

        ctx.setLineDash([]);

        // Sector labels in upper-left of each cell
        ctx.font         = 'bold 11px monospace';
        ctx.textBaseline = 'top';
        ctx.textAlign    = 'left';

        for (let r = 0; r < 5; r++) {
            for (let c = 0; c < 5; c++) {
                const label = SECTOR_ROW_LABELS[r] + SECTOR_COL_NUMS[c];
                const px    = gameToPixelX(gridColBoundaries[c]) + 5;
                const py    = gameToPixelY(gridRowBoundaries[r]) + 4;

                // Shadow for readability
                ctx.fillStyle = 'rgba(0,0,0,1)';
                ctx.fillText(label, px + 2, py + 2);
                ctx.fillStyle = 'rgba(255,255,255,1)';
                ctx.fillText(label, px, py);
            }
        }

        ctx.textBaseline = 'alphabetic';
        ctx.textAlign    = 'left';
        ctx.restore();
    }

    // ── Webfont icon character resolver ──────────────────────────
    const iconCharCache = {};
    function getIconChar(tiClass) {
        if (iconCharCache[tiClass]) return iconCharCache[tiClass];
        const span = document.createElement('span');
        span.className = 'ti ' + tiClass;
        span.style.cssText = 'position:absolute;visibility:hidden;font-size:0';
        document.body.appendChild(span);
        const char = window.getComputedStyle(span, '::before').content.replace(/['"]/g, '');
        document.body.removeChild(span);
        iconCharCache[tiClass] = char || '?';
        return iconCharCache[tiClass];
    }

    // ── Draw a marker badge ───────────────────────────────────────
    function drawMarkerBadge(px, py, type) {
        const def  = MARKER_TYPES[type] || MARKER_TYPES.general;
        const r    = BADGE_RADIUS;
        const char = getIconChar(def.tiClass);

        ctx.shadowColor = 'rgba(0,0,0,0.7)';
        ctx.shadowBlur  = 6;

        ctx.beginPath();
        ctx.arc(px, py, r, 0, 2 * Math.PI);
        ctx.fillStyle = def.color;
        ctx.fill();

        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth   = 2;
        //ctx.stroke();
        ctx.shadowBlur  = 0;

        const textColor = (def.color === '#ffee00' || def.color === '#ffffff') ? '#000000' : '#ffffff';
        ctx.fillStyle    = textColor;
        ctx.font         = '14px tabler-icons';
        ctx.textAlign    = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(char, px, py + 1);
        ctx.textAlign    = 'left';
        ctx.textBaseline = 'alphabetic';
    }

    // ── Follow player ─────────────────────────────────────────────
    let followingPlayer = null; // player name string, or null

    function maybeFollowPlayer() {
        if (!followingPlayer) return;
        const player = currentPlayers.find(p => p.name === followingPlayer);
        if (!player) return;

        const shell   = document.getElementById('mapShell');
        const shellW  = shell.offsetWidth;
        const shellH  = shell.offsetHeight;

        // Canvas pixel position of the player
        const cpx = gameToPixelX(player.x);
        const cpy = gameToPixelY(player.y);

        // Pan offset that would place the player at the center of the shell
        const targetPanX = shellW / 2 - cpx * zoom;
        const targetPanY = shellH / 2 - cpy * zoom;

        // clampPan keeps us within map bounds automatically
        const clamped = clampPan(zoom, targetPanX, targetPanY);
        panX = clamped.x;
        panY = clamped.y;
        applyTransform();
    }

    // ── Player data ───────────────────────────────────────────────
    let currentPlayers = [];

    async function fetchPlayers() {
        try {
            const r = await fetch('coords.php');
            const players = await r.json();
            if (!Array.isArray(players)) { console.error("coords.php error:", players); return; }
            currentPlayers = players;
console.log(players);
            rebuildPlayerSidebar();
            maybeFollowPlayer();
        } catch (err) { console.error("Error fetching players:", err); }
    }

    function rebuildPlayerSidebar() {
        const list = document.getElementById('playerList');
        if (currentPlayers.length === 0) {
            list.innerHTML = '<div class="sidebar-empty">No players online</div>';
            return;
        }
        list.innerHTML = '';
        currentPlayers.forEach(player => {
            const row = document.createElement('label');
            row.className = 'sidebar-row';

            const cb = document.createElement('input');
            cb.type    = 'checkbox';
            cb.checked = !hiddenPlayers.has(player.name);
            cb.addEventListener('change', () => {
                if (cb.checked) hiddenPlayers.delete(player.name);
                else            hiddenPlayers.add(player.name);
                savePrefs();
            });

            const dot = document.createElement('div');
            dot.className = 'sidebar-dot';
            dot.style.background = '#00ffcc';

            const label = document.createElement('span');
            label.className = 'sidebar-label';
            label.textContent = player.name;

            const followBtn = document.createElement('button');
            followBtn.className = 'follow-btn' + (followingPlayer === player.name ? ' active' : '');
            followBtn.textContent = '🎯';
            followBtn.title = 'Follow player';
            followBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                followingPlayer = (followingPlayer === player.name) ? null : player.name;
                rebuildPlayerSidebar();
                maybeFollowPlayer();
            });

            row.appendChild(cb);
            row.appendChild(dot);
            row.appendChild(label);
            row.appendChild(followBtn);
            list.appendChild(row);
        });
    }

    // ── Marker data ───────────────────────────────────────────────
    let currentMarkers = [];

    async function fetchMarkers() {
        try {
            const r = await fetch('markers.php');
            currentMarkers = await r.json();
            rebuildMarkerSidebar();
        } catch (err) { console.error("Error fetching markers:", err); }
    }

    function rebuildMarkerSidebar() {
        const list = document.getElementById('markerList');

        // Count markers per type
        const counts = {};
        currentMarkers.forEach(m => { counts[m.type] = (counts[m.type] || 0) + 1; });

        if (Object.keys(counts).length === 0) {
            list.innerHTML = '<div class="sidebar-empty">No markers placed</div>';
            return;
        }

        list.innerHTML = '';
        Object.entries(MARKER_TYPES).forEach(([type, def]) => {
            const count = counts[type] || 0;
            if (count === 0) return; // only show types that have markers

            const row = document.createElement('label');
            row.className = 'sidebar-row';

            const cb = document.createElement('input');
            cb.type    = 'checkbox';
            cb.checked = !hiddenMarkerTypes.has(type);
            cb.addEventListener('change', () => {
                if (cb.checked) hiddenMarkerTypes.delete(type);
                else            hiddenMarkerTypes.add(type);
                savePrefs();
            });

            const dot = document.createElement('div');
            dot.className = 'sidebar-dot';
            dot.style.background = def.color;

            const label = document.createElement('span');
            label.className   = 'sidebar-label';
            label.textContent = def.label;

            const countEl = document.createElement('span');
            countEl.className   = 'sidebar-count';
            countEl.textContent = count;

            row.appendChild(cb);
            row.appendChild(dot);
            row.appendChild(label);
            row.appendChild(countEl);
            list.appendChild(row);
        });
    }

    async function saveMarker(type, label, gameX, gameY) {
        try {
            const r = await fetch('markers.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type, label, x: gameX, y: gameY })
            });
            const data = await r.json();
            if (data.success) {
                currentMarkers.push(data.marker);
                rebuildMarkerSidebar();
            }
        } catch (err) { console.error("Error saving marker:", err); }
    }

    async function deleteMarker(id) {
        try {
            await fetch('markers.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            currentMarkers = currentMarkers.filter(m => m.id !== id);
            rebuildMarkerSidebar();
        } catch (err) { console.error("Error deleting marker:", err); }
    }

    // ── Ping data ─────────────────────────────────────────────────
    let activePing = null;

    async function fetchPing() {
        try {
            const r = await fetch('ping.php');
            const data = await r.json();
            if (data && data.x !== undefined) {
                if (!activePing || activePing.expires !== data.expires) {
                    activePing = { ...data, animStart: performance.now() };
                }
            } else {
                activePing = null;
            }
        } catch (err) { console.error("Error fetching ping:", err); }
    }

    async function sendPing(gameX, gameY) {
        try {
            await fetch('ping.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ x: gameX, y: gameY })
            });
            activePing = { x: gameX, y: gameY, expires: Date.now() / 1000 + 15, animStart: performance.now() };
        } catch (err) { console.error("Error sending ping:", err); }
    }

    // ── Draw loop ─────────────────────────────────────────────────
    function drawFrame(timestamp) {
        ctx.clearRect(0, 0, mapSize, mapSize);

        // Draw sector grid (below markers and players)
        if (showSectorGrid) drawSectorGrid();

        // Draw visible markers
        currentMarkers.forEach(marker => {
            if (hiddenMarkerTypes.has(marker.type)) return;
            const px = gameToPixelX(marker.x);
            const py = gameToPixelY(marker.y);
            drawMarkerBadge(px, py, marker.type);
        });

        // Draw visible players
        currentPlayers.forEach(player => {
            if (hiddenPlayers.has(player.name)) return;
            const px = gameToPixelX(player.x);
            const py = gameToPixelY(player.y);

            ctx.beginPath();
            ctx.arc(px, py, 5, 0, 2 * Math.PI);
            ctx.fillStyle = '#00ffcc';
            ctx.fill();
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 2;
            ctx.stroke();

            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 14px sans-serif';
            ctx.shadowColor = 'black';
            ctx.shadowBlur = 4;
            // Flip label to left side if it would overflow the right edge
            const labelWidth = ctx.measureText(player.name).width;
            const labelX = (px + 12 + labelWidth > mapSize) ? px - 12 - labelWidth : px + 12;
            ctx.fillText(player.name, labelX, py + 4);
            ctx.shadowBlur = 0;
        });

        // Draw ping animation
        if (activePing) {
            const now = Date.now() / 1000;
            if (now >= activePing.expires) {
                activePing = null;
            } else {
                const px = gameToPixelX(activePing.x);
                const py = gameToPixelY(activePing.y);
                const elapsed = (timestamp - activePing.animStart) / 1000;
                const cycleDuration = 1.2;

                for (let offset of [0, cycleDuration / 2]) {
                    const t = ((elapsed + offset) % cycleDuration) / cycleDuration;
                    ctx.beginPath();
                    ctx.arc(px, py, (t * 40 >= 0 ? t * 40 : 0), 0, 2 * Math.PI);
                    ctx.strokeStyle = `rgba(173, 255, 252, ${1 - t})`;
                    ctx.lineWidth = 3;
                    ctx.stroke();
                }

                ctx.beginPath();
                ctx.arc(px, py, 5, 0, 2 * Math.PI);
                ctx.fillStyle = '#adfffc';
                ctx.fill();
            }
        }

        requestAnimationFrame(drawFrame);
    }

    // ── Polling ───────────────────────────────────────────────────
    async function poll() {
        await fetchPlayers();
        await fetchPing();
        await fetchMarkers();
    }

    setInterval(poll, 5000);
    poll();
    requestAnimationFrame(drawFrame);

    // ── Context menu ──────────────────────────────────────────────
    const contextMenu      = document.getElementById('contextMenu');
    const ctxRemoveMarker  = document.getElementById('ctxRemoveMarker');
    const ctxMarkerDivider = document.getElementById('ctxMarkerDivider');
    const mapContainer     = document.getElementById('mapContainer');
    let contextGameX = 0, contextGameY = 0;
    let hoveredMarkerId = null;

    function getMarkerAtPixel(vx, vy) {
        // vx/vy are viewport-relative; convert to canvas coords for hit testing
        const { cx, cy } = viewportToCanvas(vx, vy);
        for (const marker of currentMarkers) {
            if (hiddenMarkerTypes.has(marker.type)) continue;
            const mx = gameToPixelX(marker.x);
            const my = gameToPixelY(marker.y);
            if (Math.sqrt((cx - mx) ** 2 + (cy - my) ** 2) <= BADGE_RADIUS + 4) return marker;
        }
        return null;
    }

    mapContainer.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        const rect = document.getElementById('mapShell').getBoundingClientRect();
        const vx = e.clientX - rect.left;
        const vy = e.clientY - rect.top;
        const { cx, cy } = viewportToCanvas(vx, vy);
        contextGameX = pixelToGameX(cx);
        contextGameY = pixelToGameY(cy);

        const marker = getMarkerAtPixel(vx, vy);
        if (marker) {
            hoveredMarkerId = marker.id;
            ctxRemoveMarker.style.display  = 'block';
            ctxMarkerDivider.style.display = 'block';
        } else {
            hoveredMarkerId = null;
            ctxRemoveMarker.style.display  = 'none';
            ctxMarkerDivider.style.display = 'none';
        }

        contextMenu.style.left    = e.clientX + 'px';
        contextMenu.style.top     = e.clientY + 'px';
        contextMenu.style.display = 'block';
    });

    document.addEventListener('click', () => { contextMenu.style.display = 'none'; });

    document.getElementById('ctxPing').addEventListener('click', () => {
        sendPing(contextGameX, contextGameY);
        contextMenu.style.display = 'none';
    });

    document.getElementById('ctxAddMarker').addEventListener('click', () => {
        contextMenu.style.display = 'none';
        openMarkerDialog(contextGameX, contextGameY);
    });

    ctxRemoveMarker.addEventListener('click', () => {
        if (hoveredMarkerId) deleteMarker(hoveredMarkerId);
        contextMenu.style.display = 'none';
    });

    // ── Marker dialog ─────────────────────────────────────────────
    let dialogGameX = 0, dialogGameY = 0;
    let selectedMarkerType = 'general';

    const grid = document.getElementById('markerTypeGrid');
    Object.entries(MARKER_TYPES).forEach(([type, def]) => {
        const btn = document.createElement('button');
        btn.className    = 'marker-type-btn' + (type === 'general' ? ' selected' : '');
        btn.dataset.type = type;
        btn.innerHTML    = `<div class="marker-type-dot" style="background:${def.color}"></div>${def.label}`;
        btn.addEventListener('click', () => {
            document.querySelectorAll('.marker-type-btn').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            selectedMarkerType = type;
        });
        grid.appendChild(btn);
    });

    function openMarkerDialog(gameX, gameY) {
        dialogGameX = gameX;
        dialogGameY = gameY;
        document.getElementById('markerLabel').value = '';
        selectedMarkerType = 'general';
        document.querySelectorAll('.marker-type-btn').forEach(b => {
            b.classList.toggle('selected', b.dataset.type === 'general');
        });
        document.getElementById('markerDialog').classList.add('open');
        document.getElementById('markerLabel').focus();
    }

    document.getElementById('markerCancelBtn').addEventListener('click', () => {
        document.getElementById('markerDialog').classList.remove('open');
    });

    document.getElementById('markerSaveBtn').addEventListener('click', () => {
        const label = document.getElementById('markerLabel').value.trim() || MARKER_TYPES[selectedMarkerType].label;
        saveMarker(selectedMarkerType, label, dialogGameX, dialogGameY);
        document.getElementById('markerDialog').classList.remove('open');
    });

    document.getElementById('markerDialog').addEventListener('click', (e) => {
        if (e.target === document.getElementById('markerDialog'))
            document.getElementById('markerDialog').classList.remove('open');
    });

    // ── Marker hover tooltip ──────────────────────────────────────
    const markerTooltip = document.getElementById('markerTooltip');

    mapContainer.addEventListener('mousemove', (e) => {
        const rect = document.getElementById('mapShell').getBoundingClientRect();
        const vx = e.clientX - rect.left;
        const vy = e.clientY - rect.top;

        const marker = getMarkerAtPixel(vx, vy);
        if (marker) {
            const def = MARKER_TYPES[marker.type] || MARKER_TYPES.general;
            markerTooltip.textContent   = `${def.label}: ${marker.label}`;
            markerTooltip.style.display = 'block';
            markerTooltip.style.left    = (e.clientX + 14) + 'px';
            markerTooltip.style.top     = (e.clientY - 10) + 'px';
        } else {
            markerTooltip.style.display = 'none';
        }
    });

    mapContainer.addEventListener('mouseleave', () => {
        markerTooltip.style.display = 'none';
    });

    // ── Options checkboxes ───────────────────────────────────────
    document.getElementById('optCoordTooltip').addEventListener('change', (e) => {
        showCoordTooltip = e.target.checked;
        if (!showCoordTooltip) document.getElementById('mouseTooltip').style.display = 'none';
        savePrefs();
    });
    document.getElementById('optSectorGrid').addEventListener('change', (e) => {
        showSectorGrid = e.target.checked;
        savePrefs();
    });

    // ── Mouse coordinate tooltip ──────────────────────────────────
    const tooltip = document.getElementById('mouseTooltip');

    mapContainer.addEventListener('mousemove', (e) => {
        if (!showCoordTooltip) { tooltip.style.display = 'none'; return; }
        const rect = document.getElementById('mapShell').getBoundingClientRect();
        const vx = e.clientX - rect.left;
        const vy = e.clientY - rect.top;
        const { cx, cy } = viewportToCanvas(vx, vy);
        const gameX = Math.round(pixelToGameX(cx));
        const gameY = Math.round(pixelToGameY(cy));
        tooltip.textContent   = `X: ${gameX}, Y: ${gameY}`;
        tooltip.style.display = 'block';
        tooltip.style.left    = (e.clientX + 14) + 'px';
        tooltip.style.top     = (e.clientY + 24) + 'px';
    });

    mapContainer.addEventListener('mouseleave', () => {
        tooltip.style.display = 'none';
    });

    // ── Zoom: scroll wheel ────────────────────────────────────────
    document.getElementById('mapShell').addEventListener('wheel', (e) => {
        e.preventDefault();
        const rect = document.getElementById('mapShell').getBoundingClientRect();
        const vx = e.clientX - rect.left;
        const vy = e.clientY - rect.top;

        const delta = e.deltaY < 0 ? ZOOM_STEP : -ZOOM_STEP;
        const newZoom = Math.max(ZOOM_MIN, Math.min(ZOOM_MAX, zoom + delta));
        if (newZoom === zoom) return;

        // Zoom toward cursor: keep vx/vy pointing at same canvas coord
        const ratio = newZoom / zoom;
        panX = vx - ratio * (vx - panX);
        panY = vy - ratio * (vy - panY);
        zoom = newZoom;
        applyTransform();
        savePrefs();
    }, { passive: false });

    // ── Zoom: sidebar buttons ─────────────────────────────────────
    document.getElementById('zoomIn').addEventListener('click', () => {
        zoom = Math.min(ZOOM_MAX, parseFloat((zoom + ZOOM_STEP).toFixed(2)));
        panX = panX - (mapSize / 2) * ZOOM_STEP;
        panY = panY - (mapSize / 2) * ZOOM_STEP;
        applyTransform();
        savePrefs();
    });
    document.getElementById('zoomOut').addEventListener('click', () => {
        zoom = Math.max(ZOOM_MIN, parseFloat((zoom - ZOOM_STEP).toFixed(2)));
        panX = panX + (mapSize / 2) * ZOOM_STEP;
        panY = panY + (mapSize / 2) * ZOOM_STEP;
        applyTransform();
        savePrefs();
    });

    // ── Pan: left-mouse drag (only when map is larger than shell) ───
    let isPanning = false, didPan = false;
    let panStartX = 0, panStartY = 0, panStartPanX = 0, panStartPanY = 0;

    function mapIsLargerThanShell() {
        const shell = document.getElementById('mapShell');
        return Math.round(mapSize * zoom) > shell.offsetWidth + 1;
    }

    document.getElementById('mapShell').addEventListener('mousedown', (e) => {
        if (e.button !== 0) return;
        if (!mapIsLargerThanShell()) return;
        e.preventDefault();
        isPanning    = true;
        didPan       = false;
        panStartX    = e.clientX;
        panStartY    = e.clientY;
        panStartPanX = panX;
        panStartPanY = panY;
        document.getElementById('mapShell').style.cursor = 'grabbing';
    });
    document.addEventListener('mousemove', (e) => {
        if (!isPanning) return;
        const dx = e.clientX - panStartX;
        const dy = e.clientY - panStartY;
        if (!didPan && Math.abs(dx) < 3 && Math.abs(dy) < 3) return; // dead-zone
        didPan = true;
        panX = panStartPanX + dx;
        panY = panStartPanY + dy;
        applyTransform();
    });
    document.addEventListener('mouseup', (e) => {
        if (e.button !== 0 || !isPanning) return;
        isPanning = false;
        document.getElementById('mapShell').style.cursor = '';
    });

    // Update cursor whenever zoom changes so it reflects panability
    const _origApply = applyTransform;

    // ── Auto-fit zoom (on load + resize) ─────────────────────────
    function autoFitZoom() {
        const SIDEBAR_W  = 180;
        const BODY_PAD   = 40;
        const GAP        = 12;

        const availW = window.innerWidth  - SIDEBAR_W - GAP - BODY_PAD;
        const availH = window.innerHeight - BODY_PAD;
        const maxFit = Math.min(availW, availH) / mapSize;

        // Snap to nearest 10% that fits, clamped to allowed range
        const fitZoom = Math.max(ZOOM_MIN, Math.min(1.0, Math.floor(maxFit * 10) / 10));
        zoom = fitZoom;

        // Resize the shell to match; clamp pan in case it was panned at a larger size
        const shellSize = Math.round(mapSize * zoom);
        const shell = document.getElementById('mapShell');
        shell.style.width  = shellSize + 'px';
        shell.style.height = shellSize + 'px';

        // Reset pan — content now fits so no offset needed
        panX = 0;
        panY = 0;

        applyTransform();
    }

    autoFitZoom();

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(autoFitZoom, 100); // debounce 100ms
    });
</script>

</body>
</html>