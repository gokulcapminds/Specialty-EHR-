<!-- public/modules/imaging.php -->
<link rel="stylesheet" href="css/modules/dashboard.css?v=<?= time() ?>">
<link rel="stylesheet" href="css/modules/imaging.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'imaging'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <!-- Header -->
        <header class="workspace-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-x-ray" style="color: #0284c7;"></i> Medical Imaging &amp; DICOM Hub
                </h1>
                <p style="color: #64748b; font-size: 0.9rem; margin: 4px 0 0 0;">Interactive PACS Workstation, X-Ray / DICOM Viewer, Multi-modality Inspection &amp; AI Diagnostic Assistant.</p>
            </div>
            <div style="display: flex; align-items: center; gap: 14px;">
                <button type="button" class="btn btn-primary" id="btn-imaging-upload-modal" style="background: linear-gradient(135deg,#0284c7,#0369a1); border: none; font-weight: 700; font-size: 0.9rem; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px; color: #ffffff; cursor: pointer; box-shadow: 0 2px 6px rgba(2,132,199,0.25);">
                    <i class="fas fa-cloud-upload-alt"></i> Upload Study / DICOM
                </button>
                <?php include __DIR__ . '/topbar.php'; ?>
            </div>
        </header>

        <!-- Summary KPI Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="width: 48px; height: 48px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                    <i class="fas fa-images"></i>
                </div>
                <div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Studies</div>
                    <div style="font-size: 1.4rem; font-weight: 800; color: #0f172a;" id="imaging-stat-total">0</div>
                </div>
            </div>

            <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="width: 48px; height: 48px; border-radius: 10px; background: #f3e8ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                    <i class="fas fa-brain"></i>
                </div>
                <div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">AI Analyzed</div>
                    <div style="font-size: 1.4rem; font-weight: 800; color: #7c3aed;" id="imaging-stat-ai">0</div>
                </div>
            </div>

            <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="width: 48px; height: 48px; border-radius: 10px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Cardio &amp; Chest Scans</div>
                    <div style="font-size: 1.4rem; font-weight: 800; color: #dc2626;" id="imaging-stat-cardio">0</div>
                </div>
            </div>

            <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="width: 48px; height: 48px; border-radius: 10px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">PACS Workstation</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #16a34a;">Active &amp; Online</div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px 20px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="display: grid; grid-template-columns: 2fr 1.2fr 1.2fr 1fr; gap: 16px; align-items: center;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.8rem; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Search Scans / Patients</label>
                    <div style="position: relative;">
                        <input type="text" id="imaging-filter-search" class="form-control" placeholder="Search filename, patient name, study description..." style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px; padding-left: 36px;">
                        <i class="fas fa-search" style="position: absolute; left: 12px; top: 13px; color: #94a3b8;"></i>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.8rem; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Modality</label>
                    <select id="imaging-filter-modality" class="form-control" style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                        <option value="">All Modalities</option>
                        <option value="X-RAY">X-Ray / Radiography</option>
                        <option value="CT">CT Scan</option>
                        <option value="MRI">MRI</option>
                        <option value="ULTRASOUND">Ultrasound / Echo</option>
                        <option value="ECG">ECG / Rhythm</option>
                        <option value="OTHER">Other Imaging</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.8rem; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">AI Reading Status</label>
                    <select id="imaging-filter-ai" class="form-control" style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                        <option value="">All Records</option>
                        <option value="completed">AI Analysis Completed</option>
                        <option value="pending">Pending AI Review</option>
                    </select>
                </div>

                <div style="display: flex; gap: 8px; margin-top: 24px;">
                    <button type="button" id="btn-imaging-reset-filters" class="btn btn-secondary" style="height: 40px; border-radius: 6px; width: 100%; font-size: 0.85rem; font-weight: 600;">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- DICOM & Imaging Worklist Directory Table -->
        <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-list-check" style="color: #0284c7;"></i> PACS Imaging Studies Directory
                </h3>
                <span id="imaging-studies-count" style="font-size: 0.85rem; color: #64748b; font-weight: 600;">0 studies found</span>
            </div>

            <div class="table-responsive">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
                            <th style="padding: 12px 16px; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase;">Study / File</th>
                            <th style="padding: 12px 16px; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase;">Patient</th>
                            <th style="padding: 12px 16px; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase;">Modality</th>
                            <th style="padding: 12px 16px; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase;">Study Date</th>
                            <th style="padding: 12px 16px; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase;">AI Reading</th>
                            <th style="padding: 12px 16px; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="imaging-studies-tbody">
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 36px; color: #94a3b8;">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p style="margin-top: 8px; font-weight: 600;">Loading DICOM and imaging studies...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Upload Study Modal -->
<div class="modal fade" id="modal-upload-imaging" tabindex="-1" style="display: none; background: rgba(15,23,42,0.6);">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 540px; margin: 1.75rem auto;">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);">
            <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; border-radius: 12px 12px 0 0;">
                <h5 class="modal-title" style="font-weight: 800; color: #0f172a; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-file-medical" style="color: #0284c7;"></i> Upload Medical Scan / DICOM
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <form id="form-upload-imaging">
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #334155;">Select Patient <span style="color: #ef4444;">*</span></label>
                        <select id="upload-imaging-patient" class="form-control" required style="border-radius: 6px; height: 40px;">
                            <option value="">-- Choose Patient --</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #334155;">Modality / Image Type <span style="color: #ef4444;">*</span></label>
                        <select id="upload-imaging-modality" class="form-control" required style="border-radius: 6px; height: 40px;">
                            <option value="X-RAY">Chest / Bone X-Ray (CR/DX)</option>
                            <option value="CT">Computed Tomography (CT)</option>
                            <option value="MRI">Magnetic Resonance (MRI)</option>
                            <option value="ULTRASOUND">Echocardiogram / Ultrasound (US)</option>
                            <option value="ECG">ECG / Cardiac Rhythm Trace</option>
                            <option value="OTHER">Other Clinical Image</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #334155;">Study Description / Notes</label>
                        <input type="text" id="upload-imaging-desc" class="form-control" placeholder="e.g. Chest 2-View PA/Lateral, Cardiac Echo apical 4-chamber" style="border-radius: 6px; height: 40px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #334155;">Scan File (.dcm, .dicom, .jpg, .png) <span style="color: #ef4444;">*</span></label>
                        <div id="imaging-dropzone" style="border: 2px dashed #cbd5e1; border-radius: 8px; padding: 24px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.2s ease;">
                            <i class="fas fa-cloud-arrow-up" style="font-size: 2rem; color: #0284c7; margin-bottom: 8px;"></i>
                            <div style="font-weight: 600; font-size: 0.9rem; color: #1e293b;">Click to browse or drag &amp; drop file here</div>
                            <div style="font-size: 0.78rem; color: #64748b; margin-top: 4px;">Supports DICOM (.dcm), JPEG, PNG (Up to 50MB)</div>
                            <input type="file" id="upload-imaging-file" accept=".dcm,.dicom,.jpg,.jpeg,.png,.gif,application/dicom" style="display: none;" required>
                            <div id="upload-file-chosen-name" style="margin-top: 10px; font-weight: 700; color: #0284c7; font-size: 0.85rem; display: none;"></div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 6px; font-weight: 600;">Cancel</button>
                        <button type="submit" id="btn-submit-upload-imaging" class="btn btn-primary" style="background: #0284c7; border: none; border-radius: 6px; font-weight: 700; padding: 8px 20px;">
                            <i class="fas fa-upload"></i> Upload &amp; Open
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
