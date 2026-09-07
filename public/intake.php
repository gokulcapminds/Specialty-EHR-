<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Intake Forms - CareHealth Family Medicine</title>
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: #f8fafc;
            margin: 0;
            padding: 24px 16px;
            color: #1e293b;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .intake-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            max-width: 780px;
            width: 100%;
            padding: 32px;
            position: relative;
        }

        .intake-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-logo .logo-icon {
            width: 36px;
            height: 36px;
            background: #0284c7;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .brand-logo .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-logo .brand-title {
            font-weight: 800;
            font-size: 1.15rem;
            color: #0f172a;
            line-height: 1.1;
        }

        .brand-logo .brand-subtitle {
            font-size: 0.78rem;
            color: #64748b;
            font-weight: 500;
        }

        .step-progress-wrapper {
            text-align: right;
            width: 180px;
        }

        .step-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: 6px;
        }

        .progress-bar-bg {
            height: 6px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            width: 100%;
        }

        .progress-bar-fill {
            height: 100%;
            background: #0d9488;
            width: 50%;
            transition: width 0.3s ease;
        }

        .divider-line {
            height: 1px;
            background: #e2e8f0;
            margin: 20px 0 28px 0;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.01em;
            margin-bottom: 16px;
            text-transform: uppercase;
        }

        .section-title i {
            color: #0284c7;
        }

        .alert-banner {
            border-radius: 10px;
            padding: 14px 18px;
            font-size: 0.88rem;
            line-height: 1.5;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .alert-banner.alert-green {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert-banner.alert-blue {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            color: #075985;
        }

        .terms-block {
            font-size: 0.88rem;
            color: #334155;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .terms-block h4 {
            font-size: 0.92rem;
            font-weight: 700;
            color: #0f172a;
            margin: 16px 0 6px 0;
        }

        .key-points-card {
            background: #fafafa;
            border: 1px solid #f1f5f9;
            border-radius: 10px;
            padding: 18px;
            margin-top: 14px;
        }

        .key-points-card h5 {
            margin: 0 0 10px 0;
            font-size: 0.88rem;
            font-weight: 700;
            color: #0f172a;
        }

        .key-points-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .key-points-list li {
            font-size: 0.85rem;
            color: #475569;
            margin-bottom: 8px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .key-points-list li i {
            color: #0284c7;
            margin-top: 2px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 24px 0;
            user-select: none;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #0d9488;
            cursor: pointer;
        }

        .checkbox-group label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
            cursor: pointer;
        }

        .signature-box {
            background: #fafafa;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 28px;
        }

        .signature-box label {
            font-size: 0.82rem;
            font-weight: 700;
            color: #475569;
            display: block;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .form-control-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .form-control-input:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
        }

        .canvas-container {
            position: relative;
            margin-top: 12px;
            margin-bottom: 12px;
        }

        canvas.sig-canvas {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            width: 100%;
            height: 140px;
            cursor: crosshair;
            touch-action: none;
        }

        .clear-btn {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 0.78rem;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .clear-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .btn-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }

        .btn-outline-secondary {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #475569;
            font-weight: 600;
            padding: 10px 22px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-outline-secondary:hover {
            background: #f8fafc;
        }

        .btn-primary-blue {
            background: #0284c7;
            border: none;
            color: #ffffff;
            font-weight: 600;
            padding: 10px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary-blue:hover {
            background: #0369a1;
        }

        .btn-success-green {
            background: #0d9488;
            border: none;
            color: #ffffff;
            font-weight: 600;
            padding: 10px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-success-green:hover {
            background: #0f766e;
        }

        .footer-security {
            text-align: center;
            margin-top: 24px;
            font-size: 0.8rem;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .error-message-box {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 12px;
            border-radius: 6px;
            font-size: 0.88rem;
            margin-bottom: 18px;
            display: none;
        }
    </style>
</head>
<body>

<div class="intake-card">

    <!-- Header -->
    <div class="intake-header">
        <div class="brand-logo">
            <div class="logo-icon"><i class="fas fa-plus"></i></div>
            <div class="brand-text">
                <span class="brand-title">CareHealth</span>
                <span class="brand-subtitle">Family Medicine</span>
            </div>
        </div>
        <div class="step-progress-wrapper">
            <div class="step-label" id="step-label-text">Step 1 of 2</div>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" id="progress-bar-fill"></div>
            </div>
        </div>
    </div>

    <div class="divider-line"></div>

    <!-- Error Banner -->
    <div class="error-message-box" id="error-message-box">
        <i class="fas fa-exclamation-circle"></i> <span id="error-message-text">Please complete required fields.</span>
    </div>

    <!-- Loading State -->
    <div id="loading-state" style="text-align: center; padding: 40px 0;">
        <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #0284c7; margin-bottom: 12px;"></i>
        <div style="font-weight: 600; color: #475569;">Loading Intake Forms...</div>
    </div>

    <!-- Submitted / Completion State -->
    <div id="completion-state" style="display: none; text-align: center; padding: 40px 20px;">
        <div style="width: 64px; height: 64px; background: #dcfce7; color: #16a34a; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 16px;">
            <i class="fas fa-check"></i>
        </div>
        <h2 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Intake Forms Submitted!</h2>
        <p style="font-size: 0.95rem; color: #475569; max-width: 480px; margin: 0 auto 20px auto; line-height: 1.6;">
            Thank you for completing your Consent to Treat and Privacy Practices acknowledgment forms for CareHealth Family Medicine. Your signed records have been safely saved to your medical chart.
        </p>
        <div style="font-size: 0.85rem; color: #64748b; background: #f1f5f9; padding: 10px 16px; border-radius: 8px; display: inline-block;">
            <i class="fas fa-lock"></i> Encryption Secured & Signed Electronically
        </div>
    </div>

    <!-- Main Wizard Form Container -->
    <div id="wizard-form-container" style="display: none;">

        <!-- STEP 1: CONSENT TO TREAT -->
        <div id="step-1-content">
            <div class="section-title">
                <i class="fas fa-user-shield"></i> Consent to Treat
            </div>

            <div class="alert-banner alert-green">
                <i class="fas fa-shield-alt" style="font-size: 1.2rem;"></i>
                <div>By signing below, you acknowledge that you give consent for treatment and authorize CareHealth Family Medicine and its providers to provide care.</div>
            </div>

            <div class="terms-block">
                <h4>1. Consent for Evaluation and Treatment</h4>
                <p style="margin:0 0 10px 0;">I voluntarily consent to evaluation and treatment by the providers and staff at CareHealth Family Medicine. I understand that no guarantee has been made to me concerning the results of the examination or treatment.</p>

                <h4>2. Disclosure of Information</h4>
                <p style="margin:0 0 10px 0;">I authorize the disclosure of any information, including medical records, necessary to process insurance claims and for the ongoing treatment and payment of services.</p>

                <h4>3. Responsibility for Payment</h4>
                <p style="margin:0 0 10px 0;">I understand that I am responsible for payment of all charges for services received, including any services not covered by my insurance.</p>

                <h4>4. Acknowledgment</h4>
                <p style="margin:0;">I have read, understand, and agree to the terms above. I had the opportunity to ask questions and all my questions were answered.</p>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="consent-agree-check">
                <label for="consent-agree-check">I have read and agree to the Consent to Treat. <span style="color:#ef4444;">*</span></label>
            </div>

            <div class="signature-box">
                <label>Signature</label>
                <div style="margin-bottom: 12px;">
                    <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 4px;">Type Full Name</div>
                    <input type="text" id="consent-full-name" class="form-control-input" placeholder="Enter your full name">
                </div>

                <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 4px;">Draw Signature</div>
                <div class="canvas-container">
                    <canvas id="consent-canvas" class="sig-canvas" width="700" height="140"></canvas>
                    <button type="button" class="clear-btn" id="clear-consent-canvas">
                        <i class="fas fa-sync-alt"></i> Clear
                    </button>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #475569;">
                    <span>Date Signed:</span>
                    <strong id="consent-date-display"></strong>
                </div>
            </div>

            <div class="btn-footer">
                <button type="button" class="btn-outline-secondary" id="cancel-step-1">Cancel</button>
                <button type="button" class="btn-primary-blue" id="next-step-1">
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- STEP 2: HIPAA / NOTICE OF PRIVACY PRACTICES -->
        <div id="step-2-content" style="display: none;">
            <div class="section-title">
                <i class="fas fa-shield-virus"></i> HIPAA / Notice of Privacy Practices Acknowledgment
            </div>

            <div class="alert-banner alert-blue">
                <i class="fas fa-info-circle" style="font-size: 1.2rem;"></i>
                <div>Please read the following acknowledgment carefully.</div>
            </div>

            <div class="terms-block">
                <p style="margin:0 0 14px 0;">
                    I acknowledge that I have received, read, and understand the Notice of Privacy Practices (NPP) of CareHealth Family Medicine. The NPP describes how my protected health information (PHI) may be used and disclosed and how I can access this information.
                </p>

                <div class="key-points-card">
                    <h5>Key Points</h5>
                    <ul class="key-points-list">
                        <li><i class="fas fa-check-circle"></i> I understand that my health information may be used for treatment, payment, and health care operations.</li>
                        <li><i class="fas fa-check-circle"></i> I understand that I have the right to review the Notice of Privacy Practices prior to signing this acknowledgment.</li>
                        <li><i class="fas fa-check-circle"></i> I understand that I may request a copy of the Notice of Privacy Practices at any time.</li>
                        <li><i class="fas fa-check-circle"></i> I understand that CareHealth Family Medicine reserves the right to change the terms of the Notice of Privacy Practices and that I may obtain a revised notice by contacting the office.</li>
                    </ul>
                </div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="hipaa-agree-check">
                <label for="hipaa-agree-check">I have read and understand the Notice of Privacy Practices. <span style="color:#ef4444;">*</span></label>
            </div>

            <div class="signature-box">
                <label>Signature</label>
                <div style="margin-bottom: 12px;">
                    <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 4px;">Type Full Name</div>
                    <input type="text" id="hipaa-full-name" class="form-control-input" placeholder="Enter your full name">
                </div>

                <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 4px;">Draw Signature</div>
                <div class="canvas-container">
                    <canvas id="hipaa-canvas" class="sig-canvas" width="700" height="140"></canvas>
                    <button type="button" class="clear-btn" id="clear-hipaa-canvas">
                        <i class="fas fa-sync-alt"></i> Clear
                    </button>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #475569;">
                    <span>Date Signed:</span>
                    <strong id="hipaa-date-display"></strong>
                </div>
            </div>

            <div class="btn-footer">
                <button type="button" class="btn-outline-secondary" id="back-step-2">
                    <i class="fas fa-chevron-left"></i> Back
                </button>
                <button type="button" class="btn-success-green" id="submit-intake-btn">
                    Submit Forms <i class="fas fa-check-circle"></i>
                </button>
            </div>
        </div>

    </div>

    <!-- Security Footer -->
    <div class="footer-security">
        <i class="fas fa-lock"></i> Your information is secure and encrypted.
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');

        const loadingState = document.getElementById('loading-state');
        const wizardFormContainer = document.getElementById('wizard-form-container');
        const completionState = document.getElementById('completion-state');
        const errorBox = document.getElementById('error-message-box');
        const errorText = document.getElementById('error-message-text');

        const todayStr = new Date().toLocaleDateString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit' });
        document.getElementById('consent-date-display').textContent = todayStr;
        document.getElementById('hipaa-date-display').textContent = todayStr;

        const showError = (msg) => {
            errorText.textContent = msg;
            errorBox.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        const hideError = () => {
            errorBox.style.display = 'none';
        };

        if (!token) {
            loadingState.style.display = 'none';
            showError('Invalid request. Intake token is missing from the link.');
            return;
        }

        // Calculate dynamic API path relative to public/intake.php location
        const currentPath = window.location.pathname;
        const publicIndex = currentPath.indexOf('/public/intake.php');
        const basePrefix = publicIndex !== -1 ? currentPath.substring(0, publicIndex) : '';
        const apiBaseUrl = `${basePrefix}/public/api`;

        // Fetch form state by token
        fetch(`${apiBaseUrl}/intake/view?token=${encodeURIComponent(token)}`)
            .then(res => res.json())
            .then(data => {
                loadingState.style.display = 'none';
                if (data.status === 'success') {
                    const info = data.data;
                    if (info.form_status === 'Submitted' || info.form_status === 'Approved') {
                        completionState.style.display = 'block';
                        const stepWrapper = document.querySelector('.step-progress-wrapper');
                        if (stepWrapper) {
                            stepWrapper.innerHTML = '<span class="step-label" style="color:#0d9488; font-weight:700;"><i class="fas fa-check-circle"></i> Completed</span><div class="progress-bar-bg" style="margin-top:6px;"><div class="progress-bar-fill" style="width: 100%;"></div></div>';
                        }
                    } else {
                        wizardFormContainer.style.display = 'block';
                        if (info.patient_name) {
                            document.getElementById('consent-full-name').value = info.patient_name;
                            document.getElementById('hipaa-full-name').value = info.patient_name;
                        }
                    }
                } else {
                    showError(data.message || 'Unable to load intake forms.');
                }
            })
            .catch(err => {
                loadingState.style.display = 'none';
                showError('Network error connecting to intake service.');
            });

        // HTML5 Canvas Drawing Logic
        const initCanvas = (canvasId, clearBtnId) => {
            const canvas = document.getElementById(canvasId);
            const ctx = canvas.getContext('2d');
            let isDrawing = false;
            let hasDrawn = false;

            const rect = canvas.getBoundingClientRect();
            // Fix resolution crispness
            canvas.width = canvas.offsetWidth || 700;
            canvas.height = 140;

            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#0f172a';

            const getPos = (e) => {
                const cRect = canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                return {
                    x: clientX - cRect.left,
                    y: clientY - cRect.top
                };
            };

            const startDrawing = (e) => {
                isDrawing = true;
                const pos = getPos(e);
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
            };

            const draw = (e) => {
                if (!isDrawing) return;
                e.preventDefault();
                hasDrawn = true;
                const pos = getPos(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
            };

            const stopDrawing = () => {
                isDrawing = false;
            };

            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseleave', stopDrawing);

            canvas.addEventListener('touchstart', startDrawing);
            canvas.addEventListener('touchmove', draw);
            canvas.addEventListener('touchend', stopDrawing);

            document.getElementById(clearBtnId).addEventListener('click', () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                hasDrawn = false;
            });

            return {
                isEmpty: () => !hasDrawn,
                toDataURL: () => canvas.toDataURL('image/png')
            };
        };

        const consentSig = initCanvas('consent-canvas', 'clear-consent-canvas');
        const hipaaSig = initCanvas('hipaa-canvas', 'clear-hipaa-canvas');

        // Stepper Navigation
        const step1Content = document.getElementById('step-1-content');
        const step2Content = document.getElementById('step-2-content');
        const stepLabel = document.getElementById('step-label-text');
        const progressFill = document.getElementById('progress-bar-fill');

        document.getElementById('next-step-1').addEventListener('click', () => {
            hideError();
            const agreed = document.getElementById('consent-agree-check').checked;
            const name = document.getElementById('consent-full-name').value.trim();

            if (!agreed) {
                showError('Please check the box to agree to the Consent to Treat.');
                return;
            }
            if (!name) {
                showError('Please type your full name in the signature section.');
                return;
            }
            if (consentSig.isEmpty()) {
                showError('Please draw your signature in the Signature box.');
                return;
            }

            step1Content.style.display = 'none';
            step2Content.style.display = 'block';
            stepLabel.textContent = 'Step 2 of 2';
            progressFill.style.width = '100%';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        document.getElementById('back-step-2').addEventListener('click', () => {
            hideError();
            step2Content.style.display = 'none';
            step1Content.style.display = 'block';
            stepLabel.textContent = 'Step 1 of 2';
            progressFill.style.width = '50%';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        document.getElementById('cancel-step-1').addEventListener('click', () => {
            if (confirm('Are you sure you want to cancel? Any unsaved progress will be lost.')) {
                window.location.reload();
            }
        });

        // Form Submission
        document.getElementById('submit-intake-btn').addEventListener('click', () => {
            hideError();
            const hipaaAgreed = document.getElementById('hipaa-agree-check').checked;
            const hipaaName = document.getElementById('hipaa-full-name').value.trim();

            if (!hipaaAgreed) {
                showError('Please check the box to acknowledge the Notice of Privacy Practices.');
                return;
            }
            if (!hipaaName) {
                showError('Please type your full name in the signature section.');
                return;
            }
            if (hipaaSig.isEmpty()) {
                showError('Please draw your signature in the Signature box.');
                return;
            }

            const payload = {
                token: token,
                consent_agreed: 1,
                consent_name: document.getElementById('consent-full-name').value.trim(),
                consent_signature: consentSig.toDataURL(),
                consent_signed_date: new Date().toISOString().split('T')[0],
                hipaa_agreed: 1,
                hipaa_name: hipaaName,
                hipaa_signature: hipaaSig.toDataURL(),
                hipaa_signed_date: new Date().toISOString().split('T')[0]
            };

            const submitBtn = document.getElementById('submit-intake-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

            fetch(`${apiBaseUrl}/intake/submit`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Submit Forms <i class="fas fa-check-circle"></i>';
                if (data.status === 'success') {
                    wizardFormContainer.style.display = 'none';
                    completionState.style.display = 'block';
                    const stepWrapper = document.querySelector('.step-progress-wrapper');
                    if (stepWrapper) {
                        stepWrapper.innerHTML = '<span class="step-label" style="color:#0d9488; font-weight:700;"><i class="fas fa-check-circle"></i> Completed</span><div class="progress-bar-bg" style="margin-top:6px;"><div class="progress-bar-fill" style="width: 100%;"></div></div>';
                    }
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    showError(data.message || 'Error submitting intake forms.');
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Submit Forms <i class="fas fa-check-circle"></i>';
                showError('Network error submitting intake forms. Please try again.');
            });
        });
    });
</script>

</body>
</html>
