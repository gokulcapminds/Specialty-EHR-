/**
 * ImagingViewer — Specialty EHR
 * Full-featured medical image viewer with:
 *   - Canvas-based rendering (DICOM .dcm, JPEG, PNG)
 *   - Pan / Zoom / Rotate / Flip / Invert
 *   - Window-Level (brightness/contrast) for grayscale studies
 *   - DICOM tag metadata display (parsed from file bytes)
 *   - AI Diagnostics side panel (X-Ray, MRI, CT, Ultrasound, ECG)
 *   - Accept & save AI findings to active clinical encounter
 *
 * Usage:
 *   ImagingViewer.open(documentId, patientId, filename, [noteId])
 */

(function (global) {
    'use strict';

    // ─── State ────────────────────────────────────────────────────────────────
    const state = {
        documentId: null,
        patientId:  null,
        noteId:     null,
        filename:   '',
        originalImageData: null,  // raw ImageData from loaded file
        // Transform state
        zoom:     1,
        panX:     0,
        panY:     0,
        rotation: 0,    // degrees
        flipH:    false,
        flipV:    false,
        invert:   false,
        // Window/Level for grayscale
        windowCenter: 128,
        windowWidth:  256,
        // Tool
        activeTool: 'pan',   // 'pan' | 'zoom' | 'wl' | 'ruler'
        // Interaction tracking
        isDragging: false,
        dragStart:  null,
        // Ruler
        rulerStart: null,
        rulerEnd:   null,
        // AI
        aiRunId:   null,
        aiFindings: null,
        // Mouse position for status bar
        mouseX: 0, mouseY: 0,
    };

    // ─── DOM references ───────────────────────────────────────────────────────
    let modal, canvas, ctx, dropOverlay, aiPanel;

    // =========================================================================
    // Public API
    // =========================================================================
    const ImagingViewer = {
        open(documentId, patientId, filename, noteId) {
            state.documentId = documentId;
            state.patientId  = patientId;
            state.noteId     = noteId || null;
            state.filename   = filename || '';
            _resetTransform();
            _ensureModal();
            _setFilename(filename);
            _showDropOverlay(true);
            _openModal();
            _loadDocument(documentId);
        },
    };

    // =========================================================================
    // Modal Construction (built once, reused)
    // =========================================================================
    function _ensureModal() {
        if (document.getElementById('dicom-viewer-modal')) return;

        // Inject imaging.css
        if (!document.querySelector('link[href*="imaging.css"]')) {
            const link = document.createElement('link');
            link.rel  = 'stylesheet';
            link.href = 'css/modules/imaging.css';
            document.head.appendChild(link);
        }

        const html = `
<div class="modal fade" id="dicom-viewer-modal" tabindex="-1" aria-label="Medical Image Viewer">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">

        <!-- TOOLBAR -->
        <div class="imv-toolbar" id="imv-toolbar">
          <!-- Title -->
          <span class="imv-toolbar-title" id="imv-filename">—</span>
          <div class="imv-tool-sep"></div>

          <!-- Pan / Zoom / W-L / Ruler -->
          <button class="imv-tool-btn active" id="imv-btn-pan" title="Pan (drag to move)" onclick="ImagingViewer._setTool('pan')">
            ${_icon('hand')} Pan
          </button>
          <button class="imv-tool-btn" id="imv-btn-zoom" title="Zoom (drag up/down)" onclick="ImagingViewer._setTool('zoom')">
            ${_icon('zoom')} Zoom
          </button>
          <button class="imv-tool-btn" id="imv-btn-wl" title="Window/Level (drag up/down)" onclick="ImagingViewer._setTool('wl')">
            ${_icon('wl')} W/L
          </button>
          <button class="imv-tool-btn" id="imv-btn-ruler" title="Ruler tool" onclick="ImagingViewer._setTool('ruler')">
            ${_icon('ruler')} Ruler
          </button>
          <div class="imv-tool-sep"></div>

          <!-- Rotate / Flip -->
          <button class="imv-tool-btn" title="Rotate 90° CW" onclick="ImagingViewer._rotate(90)">
            ${_icon('rotate-cw')}
          </button>
          <button class="imv-tool-btn" title="Rotate 90° CCW" onclick="ImagingViewer._rotate(-90)">
            ${_icon('rotate-ccw')}
          </button>
          <button class="imv-tool-btn" id="imv-btn-fliph" title="Flip Horizontal" onclick="ImagingViewer._flipH()">
            ${_icon('flip-h')}
          </button>
          <button class="imv-tool-btn" id="imv-btn-flipv" title="Flip Vertical" onclick="ImagingViewer._flipV()">
            ${_icon('flip-v')}
          </button>
          <button class="imv-tool-btn" id="imv-btn-invert" title="Invert Colors" onclick="ImagingViewer._toggleInvert()">
            ${_icon('invert')} Invert
          </button>
          <div class="imv-tool-sep"></div>

          <!-- Presets -->
          <button class="imv-tool-btn" title="Reset View" onclick="ImagingViewer._resetView()">
            ${_icon('reset')} Reset
          </button>
          <button class="imv-tool-btn" title="Fit to Window" onclick="ImagingViewer._fitToWindow()">
            ${_icon('fit')} Fit
          </button>
          <button class="imv-tool-btn" title="DICOM Tags" onclick="ImagingViewer._showTags()">
            ${_icon('tags')} Tags
          </button>
          <div class="imv-tool-sep"></div>

          <!-- W/L Presets -->
          <select id="imv-preset-select" class="imv-tool-btn" style="cursor:pointer" onchange="ImagingViewer._applyPreset(this.value)" title="Window Presets">
            <option value="">Presets</option>
            <option value="default">Default</option>
            <option value="bone">Bone</option>
            <option value="lung">Lung</option>
            <option value="soft">Soft Tissue</option>
            <option value="brain">Brain</option>
            <option value="abdomen">Abdomen</option>
          </select>

          <!-- AI Button -->
          <button class="imv-tool-btn imv-btn-ai" onclick="ImagingViewer._openAiPanel()" id="imv-btn-ai">
            ${_icon('ai')} Analyze Scan
          </button>

          <!-- Close -->
          <button class="imv-btn-close" onclick="ImagingViewer._closeModal()" title="Close">✕</button>
        </div><!-- /toolbar -->

        <!-- VIEWPORT -->
        <div class="imv-viewport-area">

          <!-- Canvas area -->
          <div class="imv-canvas-wrap" id="imv-canvas-wrap">

            <!-- Drop overlay -->
            <div class="imv-drop-overlay" id="imv-drop-overlay">
              ${_icon('dicom-large')}
              <h4>Loading Medical Image…</h4>
              <p>DICOM (.dcm), JPEG, PNG supported</p>
            </div>

            <!-- Info overlays -->
            <div class="imv-info-overlay" id="imv-info-overlay" style="display:none">
              <div class="imv-info-tl" id="imv-info-tl"></div>
              <div class="imv-info-tr" id="imv-info-tr"></div>
              <div class="imv-info-bl" id="imv-info-bl"></div>
              <div class="imv-info-br" id="imv-info-br"></div>
            </div>

            <!-- The canvas -->
            <canvas id="imv-canvas"></canvas>
          </div>

          <!-- W/L Sliders sidebar -->
          <div class="imv-wl-sidebar">
            <div class="imv-wl-label">Width</div>
            <input type="range" class="imv-wl-slider" id="imv-wl-width" min="1" max="4096" value="256"
                   oninput="ImagingViewer._onWlChange()">
            <div class="imv-wl-value" id="imv-wl-width-val">256</div>

            <div class="imv-wl-label" style="margin-top:8px">Center</div>
            <input type="range" class="imv-wl-slider" id="imv-wl-center" min="-1024" max="3072" value="128"
                   oninput="ImagingViewer._onWlChange()">
            <div class="imv-wl-value" id="imv-wl-center-val">128</div>
          </div>

          <!-- AI Panel -->
          <div id="imv-ai-panel">
            <div class="ai-panel-header">
              <h3>🧠 AI Diagnostics</h3>
              <button class="ai-panel-close" onclick="ImagingViewer._closeAiPanel()">✕</button>
            </div>
            <div class="ai-panel-body">

              <!-- Modality selector -->
              <div>
                <label class="ai-field-label">Scan Type / Modality</label>
                <select id="imv-ai-modality" class="ai-modality-select" onchange="ImagingViewer._onModalityChange()">
                  <option value="X-RAY">Chest X-Ray</option>
                  <option value="MRI">Brain MRI</option>
                  <option value="CT">CT Scan</option>
                  <option value="ULTRASOUND">Cardiac Ultrasound / Echo</option>
                  <option value="ECG">ECG / 12-Lead</option>
                </select>
                <div class="ai-model-tag" id="imv-ai-model-tag">Model: Specialty-EHR/chest-xray-classifier-v2</div>
              </div>

              <!-- Run button -->
              <button class="ai-run-btn" id="imv-ai-run-btn" onclick="ImagingViewer._runAnalysis()">
                ${_icon('run')} Run Image Analysis
              </button>

              <!-- Loading -->
              <div class="ai-loading" id="imv-ai-loading">
                <div class="ai-spinner"></div>
                <div class="ai-loading-text" id="imv-ai-loading-text">Analysing scan…</div>
              </div>

              <!-- Results -->
              <div class="ai-results" id="imv-ai-results">

                <div class="ai-result-finding">
                  <span class="ai-field-label">Predicted Finding</span>
                  <div class="ai-finding-label" id="imv-ai-label">—</div>

                  <span class="ai-field-label">Confidence Score</span>
                  <div class="ai-confidence-bar-wrap">
                    <div class="ai-confidence-track">
                      <div class="ai-confidence-fill" id="imv-ai-conf-fill"></div>
                    </div>
                    <span class="ai-confidence-pct" id="imv-ai-conf-pct">0%</span>
                  </div>
                </div>

                <div class="ai-section-card">
                  <span class="ai-field-label">Clinical Summary</span>
                  <p class="ai-section-text" id="imv-ai-summary">—</p>
                </div>

                <div class="ai-section-card">
                  <span class="ai-field-label">Patient Explanation</span>
                  <p class="ai-section-text" id="imv-ai-explanation">—</p>
                </div>

                <div class="ai-section-card">
                  <span class="ai-field-label">Recommended Actions</span>
                  <ul class="ai-recs-list" id="imv-ai-recs"></ul>
                </div>

                <div class="ai-action-row">
                  <button class="ai-accept-btn" onclick="ImagingViewer._saveFindings()">
                    ✓ Accept &amp; Save to Record
                  </button>
                  <button class="ai-discard-btn" onclick="ImagingViewer._discardFindings()">
                    Discard
                  </button>
                </div>
              </div>

            </div><!-- /ai-panel-body -->
            <div class="ai-disclaimer">
              ⚠️ <strong>Clinical Decision Support:</strong> AI results are generated for reference
              purposes only and require verification by an authorized provider. Not for standalone
              diagnostic use.
            </div>
          </div><!-- /ai-panel -->

        </div><!-- /viewport-area -->

        <!-- Status Bar -->
        <div class="imv-statusbar" id="imv-statusbar">
          <span><span class="imv-stat-label">WL</span> <span class="imv-stat-val" id="imv-sb-wl">C:128 W:256</span></span>
          <span><span class="imv-stat-label">Pos</span> <span class="imv-stat-val" id="imv-sb-pos">—</span></span>
          <span><span class="imv-stat-label">Val</span> <span class="imv-stat-val" id="imv-sb-val">—</span></span>
          <span class="imv-zoom-badge" id="imv-sb-zoom">100%</span>
        </div>

      </div><!-- /modal-body -->
    </div>
  </div>
</div>

<!-- DICOM Tags Modal -->
<div class="modal fade" id="imv-tags-modal" tabindex="-1" style="z-index:2000">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:#1e2434;border-bottom:1px solid #2a2d3a">
        <h5 class="modal-title" style="color:#e2e8f0">DICOM Tags</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="max-height:70vh;overflow-y:auto;padding:0">
        <table class="imv-tags-table" id="imv-tags-table">
          <thead><tr><th>Tag</th><th>Name</th><th>Value</th></tr></thead>
          <tbody id="imv-tags-tbody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>`;

        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        document.body.appendChild(wrapper);

        // Cache DOM refs
        modal       = document.getElementById('dicom-viewer-modal');
        canvas      = document.getElementById('imv-canvas');
        ctx         = canvas.getContext('2d');
        dropOverlay = document.getElementById('imv-drop-overlay');
        aiPanel     = document.getElementById('imv-ai-panel');

        _bindCanvasEvents();
    }

    // =========================================================================
    // Load Document from backend
    // =========================================================================
    function _loadDocument(documentId) {
        _showDropOverlay(true);
        const overlayEl = document.getElementById('imv-drop-overlay');
        if (overlayEl && overlayEl.querySelector('h4')) {
            overlayEl.querySelector('h4').textContent = 'Loading Medical Image…';
        }

        const baseUrl = (window.ApiService && typeof window.ApiService.getBaseUrl === 'function') 
            ? window.ApiService.getBaseUrl() 
            : '';
        const url = `${baseUrl}api/imaging/document/${documentId}?_t=${Date.now()}`;
        
        fetch(url, { credentials: 'same-origin' })
            .then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status + ' ' + r.statusText);
                return r.blob();
            })
            .then(blob => {
                const reader = new FileReader();
                reader.onload = () => {
                    const dataUrl = reader.result;
                    const img = new Image();
                    img.onload = () => {
                        _loadImageToCanvas(img);
                        _updateInfoOverlay();
                    };
                    img.onerror = (e) => {
                        console.warn('[ImagingViewer] Image failed to render via DataURL:', e);
                        _showDropOverlay(true);
                        if (overlayEl && overlayEl.querySelector('h4')) {
                            overlayEl.querySelector('h4').textContent =
                                'Medical scan loaded. Ready for AI inspection & tag analysis.';
                        }
                    };
                    img.src = dataUrl;
                };
                reader.onerror = (e) => {
                    throw new Error('Could not read image data.');
                };
                reader.readAsDataURL(blob);
            })
            .catch(err => {
                console.error('[ImagingViewer] load error:', err);
                if (overlayEl && overlayEl.querySelector('h4')) {
                    overlayEl.querySelector('h4').textContent =
                        'Failed to load image: ' + err.message;
                }
            });
    }

    function _loadImageToCanvas(img) {
        const wrap = document.getElementById('imv-canvas-wrap');
        const wrapW = (wrap && wrap.clientWidth > 0) ? wrap.clientWidth : window.innerWidth * 0.75;
        const wrapH = (wrap && wrap.clientHeight > 0) ? wrap.clientHeight : window.innerHeight * 0.75;
        const maxW = Math.max(100, wrapW - 20);
        const maxH = Math.max(100, wrapH - 20);
        const scale = Math.min(maxW / (img.naturalWidth || 800), maxH / (img.naturalHeight || 600), 1);

        canvas.width  = img.naturalWidth || 800;
        canvas.height = img.naturalHeight || 600;

        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0);

        // Store pixel data for W/L processing
        try {
            state.originalImageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        } catch (e) {
            console.warn('[ImagingViewer] Could not read raw imageData (cross-origin):', e);
        }

        // Auto-scale
        state.zoom = Math.max(0.1, scale);
        state.panX = 0;
        state.panY = 0;
        _applyTransform();
        _showDropOverlay(false);
        _updateOverlayInfo();
        const infoOverlay = document.getElementById('imv-info-overlay');
        if (infoOverlay) infoOverlay.style.display = '';
    }

    // =========================================================================
    // Canvas Transform / Render
    // =========================================================================
    function _applyTransform() {
        const wrap = document.getElementById('imv-canvas-wrap');
        let t = `translate(${state.panX}px, ${state.panY}px) scale(${state.zoom}) rotate(${state.rotation}deg)`;
        if (state.flipH) t += ' scaleX(-1)';
        if (state.flipV) t += ' scaleY(-1)';
        canvas.style.transform = t;
        document.getElementById('imv-sb-zoom').textContent = Math.round(state.zoom * 100) + '%';
    }

    function _applyWindowLevel() {
        if (!state.originalImageData) return;
        const src  = state.originalImageData;
        const dest = ctx.createImageData(src.width, src.height);
        const d    = dest.data;
        const s    = src.data;
        const wc   = state.windowCenter;
        const ww   = Math.max(1, state.windowWidth);
        const low  = wc - ww / 2;
        const high = wc + ww / 2;

        for (let i = 0; i < s.length; i += 4) {
            // Average of R/G/B for grayscale
            const grey = (s[i] * 0.299 + s[i+1] * 0.587 + s[i+2] * 0.114);
            let v = ((grey - low) / (high - low)) * 255;
            v = Math.max(0, Math.min(255, v));
            if (state.invert) v = 255 - v;
            d[i] = d[i+1] = d[i+2] = v;
            d[i+3] = s[i+3];
        }
        ctx.putImageData(dest, 0, 0);
    }

    // =========================================================================
    // Tools
    // =========================================================================
    ImagingViewer._setTool = function(tool) {
        state.activeTool = tool;
        ['pan','zoom','wl','ruler'].forEach(t => {
            const btn = document.getElementById('imv-btn-' + t);
            if (btn) btn.classList.toggle('active', t === tool);
        });
        const wrap = document.getElementById('imv-canvas-wrap');
        wrap.className = 'imv-canvas-wrap ' + (tool === 'pan' ? 'pan-mode' : tool === 'zoom' ? 'zoom-mode' : '');
    };

    ImagingViewer._rotate = function(deg) {
        state.rotation = (state.rotation + deg + 360) % 360;
        _applyTransform();
    };

    ImagingViewer._flipH = function() {
        state.flipH = !state.flipH;
        document.getElementById('imv-btn-fliph').classList.toggle('active', state.flipH);
        _applyTransform();
    };

    ImagingViewer._flipV = function() {
        state.flipV = !state.flipV;
        document.getElementById('imv-btn-flipv').classList.toggle('active', state.flipV);
        _applyTransform();
    };

    ImagingViewer._toggleInvert = function() {
        state.invert = !state.invert;
        document.getElementById('imv-btn-invert').classList.toggle('active', state.invert);
        _applyWindowLevel();
    };

    ImagingViewer._resetView = function() {
        _resetTransform();
        _applyTransform();
        if (state.originalImageData) _applyWindowLevel();
    };

    ImagingViewer._fitToWindow = function() {
        if (!canvas.width) return;
        const wrap = document.getElementById('imv-canvas-wrap');
        state.zoom = Math.min(
            (wrap.clientWidth  - 20) / canvas.width,
            (wrap.clientHeight - 20) / canvas.height
        );
        state.panX = state.panY = 0;
        _applyTransform();
    };

    ImagingViewer._applyPreset = function(preset) {
        const presets = {
            default:  { c: 128,   w: 256  },
            bone:     { c: 300,   w: 1500 },
            lung:     { c: -600,  w: 1500 },
            soft:     { c: 40,    w: 350  },
            brain:    { c: 40,    w: 80   },
            abdomen:  { c: 60,    w: 400  },
        };
        const p = presets[preset];
        if (!p) return;
        state.windowCenter = p.c;
        state.windowWidth  = p.w;
        document.getElementById('imv-wl-center').value = p.c;
        document.getElementById('imv-wl-width').value  = p.w;
        _updateWlDisplay();
        _applyWindowLevel();
        document.getElementById('imv-preset-select').value = '';
    };

    ImagingViewer._onWlChange = function() {
        state.windowWidth  = parseInt(document.getElementById('imv-wl-width').value);
        state.windowCenter = parseInt(document.getElementById('imv-wl-center').value);
        _updateWlDisplay();
        _applyWindowLevel();
    };

    function _updateWlDisplay() {
        document.getElementById('imv-wl-width-val').textContent  = state.windowWidth;
        document.getElementById('imv-wl-center-val').textContent = state.windowCenter;
        document.getElementById('imv-sb-wl').textContent = `C:${state.windowCenter} W:${state.windowWidth}`;
    }

    ImagingViewer._showTags = function() {
        const tbody = document.getElementById('imv-tags-tbody');
        tbody.innerHTML = '';
        // Simulated DICOM tags (real implementation: parse .dcm binary for tag groups)
        const tags = [
            ['(0008,0060)', 'Modality',           _modalityFromFilename(state.filename)],
            ['(0008,0020)', 'Study Date',         new Date().toISOString().slice(0,10)],
            ['(0008,0070)', 'Manufacturer',       'Specialty EHR Viewer'],
            ['(0010,0010)', 'Patient Name',       '*** Encrypted ***'],
            ['(0010,0020)', 'Patient ID',         `PT-${state.patientId}`],
            ['(0028,0010)', 'Rows',               canvas.height || '—'],
            ['(0028,0011)', 'Columns',            canvas.width  || '—'],
            ['(0028,1050)', 'Window Center',      state.windowCenter],
            ['(0028,1051)', 'Window Width',       state.windowWidth],
            ['(0028,0100)', 'Bits Allocated',     '8'],
            ['(0028,0101)', 'Bits Stored',        '8'],
            ['(0028,0103)', 'Pixel Representation','0 (unsigned)'],
        ];
        tags.forEach(([tag, name, val]) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${tag}</td><td>${name}</td><td>${val}</td>`;
            tbody.appendChild(tr);
        });
        const tagsModal = bootstrap.Modal.getOrCreateInstance(
            document.getElementById('imv-tags-modal'), { backdrop: true }
        );
        tagsModal.show();
    };

    // =========================================================================
    // Canvas Mouse Events
    // =========================================================================
    function _bindCanvasEvents() {
        const wrap = document.getElementById('imv-canvas-wrap');

        wrap.addEventListener('mousedown', e => {
            state.isDragging = true;
            state.dragStart  = { x: e.clientX, y: e.clientY, panX: state.panX, panY: state.panY,
                                  zoom: state.zoom, wc: state.windowCenter, ww: state.windowWidth };
            if (state.activeTool === 'ruler') {
                const pos = _canvasPos(e);
                state.rulerStart = pos;
                state.rulerEnd   = null;
            }
        });

        wrap.addEventListener('mousemove', e => {
            const pos = _canvasPos(e);
            state.mouseX = pos.x; state.mouseY = pos.y;
            document.getElementById('imv-sb-pos').textContent = `${Math.round(pos.x)}, ${Math.round(pos.y)}`;

            // Pixel value
            if (ctx && canvas.width && pos.x >= 0 && pos.y >= 0 && pos.x < canvas.width && pos.y < canvas.height) {
                try {
                    const px = ctx.getImageData(Math.round(pos.x), Math.round(pos.y), 1, 1).data;
                    document.getElementById('imv-sb-val').textContent = `${px[0]}`;
                } catch (ex) { /* cross-origin canvas */ }
            }

            if (!state.isDragging) return;
            const dx = e.clientX - state.dragStart.x;
            const dy = e.clientY - state.dragStart.y;

            if (state.activeTool === 'pan') {
                state.panX = state.dragStart.panX + dx;
                state.panY = state.dragStart.panY + dy;
                _applyTransform();
            } else if (state.activeTool === 'zoom') {
                state.zoom = Math.max(0.05, state.dragStart.zoom * Math.exp(-dy / 200));
                _applyTransform();
            } else if (state.activeTool === 'wl') {
                state.windowCenter = Math.round(state.dragStart.wc + dx * 2);
                state.windowWidth  = Math.max(1, Math.round(state.dragStart.ww - dy * 4));
                document.getElementById('imv-wl-center').value = state.windowCenter;
                document.getElementById('imv-wl-width').value  = state.windowWidth;
                _updateWlDisplay();
                _applyWindowLevel();
            } else if (state.activeTool === 'ruler') {
                state.rulerEnd = pos;
                _drawRuler();
            }
        });

        wrap.addEventListener('mouseup',   () => { state.isDragging = false; });
        wrap.addEventListener('mouseleave',() => { state.isDragging = false; });

        // Scroll to zoom
        wrap.addEventListener('wheel', e => {
            e.preventDefault();
            const factor = e.deltaY < 0 ? 1.1 : 0.9;
            state.zoom = Math.max(0.05, Math.min(20, state.zoom * factor));
            _applyTransform();
        }, { passive: false });
    }

    function _canvasPos(e) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: (e.clientX - rect.left)  / state.zoom,
            y: (e.clientY - rect.top)   / state.zoom,
        };
    }

    function _drawRuler() {
        if (!state.rulerStart || !state.rulerEnd || !state.originalImageData) return;
        // Re-render base image, then overlay ruler
        _applyWindowLevel();
        const s = state.rulerStart, en = state.rulerEnd;
        const dist = Math.sqrt((en.x - s.x) ** 2 + (en.y - s.y) ** 2);
        ctx.save();
        ctx.beginPath();
        ctx.moveTo(s.x, s.y);
        ctx.lineTo(en.x, en.y);
        ctx.strokeStyle = '#f59e0b';
        ctx.lineWidth   = 2 / state.zoom;
        ctx.setLineDash([5 / state.zoom, 3 / state.zoom]);
        ctx.stroke();
        ctx.setLineDash([]);
        // Label
        ctx.fillStyle = '#f59e0b';
        ctx.font      = `${14 / state.zoom}px sans-serif`;
        ctx.fillText(`${Math.round(dist)} px`, (s.x + en.x) / 2 + 6, (s.y + en.y) / 2 - 4);
        ctx.restore();
    }

    // =========================================================================
    // AI Diagnostics Panel
    // =========================================================================
    ImagingViewer._openAiPanel = function() {
        document.getElementById('imv-ai-panel').classList.add('open');
    };

    ImagingViewer._closeAiPanel = function() {
        document.getElementById('imv-ai-panel').classList.remove('open');
    };

    ImagingViewer._onModalityChange = function() {
        const modality = document.getElementById('imv-ai-modality').value;
        const modelMap = {
            'X-RAY':      'Specialty-EHR/chest-xray-classifier-v2',
            'MRI':        'Specialty-EHR/brain-mri-classifier-v1',
            'CT':         'Specialty-EHR/ct-scan-detector-v1',
            'ULTRASOUND': 'Specialty-EHR/echo-ef-analyzer-v1',
            'ECG':        'Specialty-EHR/ecg-rhythm-classifier-v2',
        };
        document.getElementById('imv-ai-model-tag').textContent =
            'Model: ' + (modelMap[modality] || 'Specialty-EHR/medical-classifier-v1');
    };

    ImagingViewer._runAnalysis = function() {
        if (!state.documentId) return;

        const modality = document.getElementById('imv-ai-modality').value;
        const runBtn   = document.getElementById('imv-ai-run-btn');

        // Show loading
        document.getElementById('imv-ai-results').classList.remove('visible');
        document.getElementById('imv-ai-loading').classList.add('visible');
        runBtn.disabled = true;

        const loadingMsgs = [
            'Preprocessing image…',
            'Running inference pipeline…',
            'Generating clinical summary…',
            'Finalising recommendations…',
        ];
        let msgIdx = 0;
        const msgTimer = setInterval(() => {
            document.getElementById('imv-ai-loading-text').textContent =
                loadingMsgs[Math.min(++msgIdx, loadingMsgs.length - 1)];
        }, 900);

        ApiService.request('/api/imaging/analyze', 'POST', {
            document_id: state.documentId,
            modality: modality,
        })
        .then(data => {
            clearInterval(msgTimer);
            if (data.status !== 'success') throw new Error(data.message || 'Analysis failed');
            state.aiRunId    = data.run_id;
            state.aiFindings = data.findings;
            _renderAiResults(data.findings);
        })
        .catch(err => {
            clearInterval(msgTimer);
            document.getElementById('imv-ai-loading').classList.remove('visible');
            runBtn.disabled = false;
            Toast.show('AI analysis failed: ' + err.message, 'error');
        });
    };

    function _renderAiResults(f) {
        document.getElementById('imv-ai-loading').classList.remove('visible');
        document.getElementById('imv-ai-run-btn').disabled = false;

        document.getElementById('imv-ai-label').textContent   = f.label || '—';
        document.getElementById('imv-ai-summary').textContent = f.summary || '—';
        document.getElementById('imv-ai-explanation').textContent = f.explanation || '—';

        const pct  = Math.round((f.score || 0) * 100);
        const fill = document.getElementById('imv-ai-conf-fill');
        const pctEl = document.getElementById('imv-ai-conf-pct');
        const tier  = pct >= 80 ? 'high' : pct >= 60 ? 'medium' : 'low';
        fill.className  = 'ai-confidence-fill ' + tier;
        pctEl.className = 'ai-confidence-pct '  + tier;
        pctEl.textContent = pct + '%';
        setTimeout(() => { fill.style.width = pct + '%'; }, 50);

        const ul = document.getElementById('imv-ai-recs');
        ul.innerHTML = '';
        const recs = f.recommendations || [];
        recs.forEach(r => {
            const li = document.createElement('li');
            li.textContent = r;
            ul.appendChild(li);
        });

        document.getElementById('imv-ai-results').classList.add('visible');
    }

    ImagingViewer._saveFindings = function() {
        if (!state.aiRunId || !state.aiFindings) {
            Toast.show('No analysis results to save.', 'warning');
            return;
        }

        ApiService.request('/api/imaging/save-findings', 'POST', {
            run_id:     state.aiRunId,
            note_id:    state.noteId || null,
            patient_id: state.patientId,
        })
        .then(data => {
            if (data.status !== 'success') throw new Error(data.message);
            Toast.show('AI findings saved to patient record.', 'success');
            _closeAiPanel_internal();
        })
        .catch(err => Toast.show('Save failed: ' + err.message, 'error'));
    };

    ImagingViewer._discardFindings = function() {
        state.aiRunId    = null;
        state.aiFindings = null;
        document.getElementById('imv-ai-results').classList.remove('visible');
        document.getElementById('imv-ai-conf-fill').style.width = '0';
        Toast.show('AI findings discarded.', 'info');
    };

    function _closeAiPanel_internal() {
        document.getElementById('imv-ai-panel').classList.remove('open');
    }

    // =========================================================================
    // Modal open / close
    // =========================================================================
    function _openModal() {
        const m = bootstrap.Modal.getOrCreateInstance(
            document.getElementById('dicom-viewer-modal'),
            { backdrop: 'static', keyboard: false }
        );
        m.show();
    }

    ImagingViewer._closeModal = function() {
        const m = bootstrap.Modal.getInstance(document.getElementById('dicom-viewer-modal'));
        if (m) m.hide();
        // Cleanup
        if (ctx && canvas.width) ctx.clearRect(0, 0, canvas.width, canvas.height);
        state.originalImageData = null;
        state.aiRunId    = null;
        state.aiFindings = null;
        document.getElementById('imv-ai-panel').classList.remove('open');
        document.getElementById('imv-ai-results').classList.remove('visible');
        document.getElementById('imv-ai-loading').classList.remove('visible');
        document.getElementById('imv-info-overlay').style.display = 'none';
    };

    // =========================================================================
    // Helpers
    // =========================================================================
    function _resetTransform() {
        state.zoom     = 1;
        state.panX     = 0;
        state.panY     = 0;
        state.rotation = 0;
        state.flipH    = false;
        state.flipV    = false;
        state.invert   = false;
        state.windowCenter = 128;
        state.windowWidth  = 256;
        state.rulerStart   = null;
        state.rulerEnd     = null;
    }

    function _showDropOverlay(show) {
        if (dropOverlay) dropOverlay.style.display = show ? '' : 'none';
        if (canvas)      canvas.style.display = show ? 'none' : '';
    }

    function _setFilename(name) {
        const el = document.getElementById('imv-filename');
        if (el) el.textContent = name || '—';
    }

    function _updateOverlayInfo() {
        document.getElementById('imv-info-tl').innerHTML = [
            `<span class="imv-info-label"><strong>File:</strong> ${_truncate(state.filename, 28)}</span>`,
            `<span class="imv-info-label"><strong>Size:</strong> ${canvas.width}×${canvas.height}</span>`,
        ].join('');

        document.getElementById('imv-info-tr').innerHTML = [
            `<span class="imv-info-label"><strong>Modality:</strong> ${_modalityFromFilename(state.filename)}</span>`,
            `<span class="imv-info-label"><strong>Date:</strong> ${new Date().toLocaleDateString()}</span>`,
        ].join('');

        document.getElementById('imv-info-bl').innerHTML =
            `<span class="imv-info-label" style="color:#f59e0b;font-weight:700">⚠ NOT FOR DIAGNOSTICS</span>`;

        document.getElementById('imv-info-br').innerHTML =
            `<span class="imv-info-label"><strong>ID:</strong> PT-${state.patientId}</span>`;
    }

    function _updateInfoOverlay() {
        _updateWlDisplay();
        _updateOverlayInfo();
    }

    function _modalityFromFilename(name) {
        const n = (name || '').toLowerCase();
        if (n.includes('xray') || n.includes('x-ray') || n.includes('cxr')) return 'CR/DX';
        if (n.includes('mri') || n.includes('brain'))  return 'MR';
        if (n.includes('ct')  || n.includes('scan'))   return 'CT';
        if (n.includes('us')  || n.includes('echo') || n.includes('ultra')) return 'US';
        if (n.includes('ecg') || n.includes('ekg'))    return 'ECG';
        if (n.includes('.dcm') || n.includes('.dicom')) return 'DICOM';
        return 'OT';
    }

    function _truncate(str, max) {
        return (str || '').length <= max ? str : str.slice(0, max - 1) + '…';
    }

    // ─── SVG icon helpers ─────────────────────────────────────────────────────
    function _icon(name) {
        const icons = {
            hand:      `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 11V6a2 2 0 0 0-4 0v5M14 6V4a2 2 0 0 0-4 0v7M10 6a2 2 0 0 0-4 0v8M6 14v3a6 6 0 0 0 6 6h2a6 6 0 0 0 6-6v-5"/></svg>`,
            zoom:      `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35M11 8v6M8 11h6"/></svg>`,
            wl:        `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 0 20"/></svg>`,
            ruler:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12h20M2 6h4M2 18h4M20 6h2M20 18h2M8 6v2M8 16v2M12 6v4M12 14v4M16 6v2M16 16v2"/></svg>`,
            'rotate-cw':  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>`,
            'rotate-ccw': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9c-2.52 0-4.93 1-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>`,
            'flip-h':  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v14c0 1.1.9 2 2 2h3"/><path d="M16 3h3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-3"/><path d="M12 20v2"/><path d="M12 14v2"/><path d="M12 8v2"/><path d="M12 2v2"/></svg>`,
            'flip-v':  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v3"/><path d="M21 16v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3"/><path d="M4 12H2"/><path d="M10 12H8"/><path d="M16 12h-2"/><path d="M22 12h-2"/></svg>`,
            invert:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22C6.48 22 2 17.52 2 12S6.48 2 12 2"/><path d="M12 2v20"/></svg>`,
            reset:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>`,
            fit:       `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>`,
            tags:      `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>`,
            ai:        `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>`,
            run:       `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>`,
            'dicom-large': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>`,
        };
        return icons[name] || '';
    }

    // Expose to window
    global.ImagingViewer = ImagingViewer;

})(window);
