<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Telehealth Consultation - Specialty EHR</title>
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
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header.telehealth-header {
            background-color: #1e293b;
            border-bottom: 1px solid #334155;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
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

        .brand-title {
            font-weight: 800;
            font-size: 1.15rem;
            color: #ffffff;
            line-height: 1.1;
        }

        .brand-subtitle {
            font-size: 0.78rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .badge-encrypted {
            background: rgba(13, 148, 136, 0.2);
            color: #2dd4bf;
            border: 1px solid rgba(45, 212, 191, 0.3);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        main.telehealth-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
            background-color: #020617;
        }

        #patient-jitsi-container {
            width: 100%;
            height: calc(100vh - 65px);
            min-height: 500px;
            background: #000000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .status-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 32px;
            max-width: 480px;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
            margin: auto;
        }

        .status-card i.spinner {
            font-size: 2.5rem;
            color: #0284c7;
            margin-bottom: 16px;
        }

        .status-card h2 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .status-card p {
            font-size: 0.9rem;
            color: #94a3b8;
            line-height: 1.6;
        }

        .error-card {
            background: #450a0a;
            border: 1px solid #991b1b;
            color: #fca5a5;
        }

        .error-card i {
            color: #ef4444 !important;
        }
    </style>
</head>
<body>

    <header class="telehealth-header">
        <div class="brand-logo">
            <div class="logo-icon"><i class="fas fa-video"></i></div>
            <div>
                <div class="brand-title">Specialty EHR</div>
                <div class="brand-subtitle">Telehealth Consultation</div>
            </div>
        </div>
        <div class="badge-encrypted">
            <i class="fas fa-lock"></i> HIPAA Encrypted Session
        </div>
    </header>

    <main class="telehealth-main">
        <div id="patient-jitsi-container">
            <div class="status-card" id="status-display">
                <i class="fas fa-spinner fa-spin spinner"></i>
                <h2>Connecting to Video Consultation</h2>
                <p>Please wait while we establish your encrypted peer-to-peer connection with your healthcare provider...</p>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const urlParams = new URLSearchParams(window.location.search);
            const roomName = urlParams.get('room');
            const container = document.getElementById('patient-jitsi-container');
            const statusDisplay = document.getElementById('status-display');

            if (!roomName) {
                container.innerHTML = `
                    <div class="status-card error-card">
                        <i class="fas fa-exclamation-triangle spinner"></i>
                        <h2 style="color:#ffffff;">Invalid Consultation Link</h2>
                        <p style="color:#fca5a5;">The telehealth consultation link is missing a valid room identifier. Please click the join link from your invitation email again.</p>
                    </div>
                `;
                return;
            }

            const card = (icon, color, title, body, extra = '') => `
                <div class="status-card" style="border-color:${color};">
                    <i class="fas ${icon}" style="font-size:2.6rem; color:${color}; margin-bottom:16px;"></i>
                    <h2>${title}</h2>
                    <p>${body}</p>${extra}
                </div>`;

            // Time gate: the server decides (patients may join only 5 min before the scheduled start)
            const checkWindow = async () => {
                const res = await fetch('api/telehealth/join-check?room=' + encodeURIComponent(roomName), { cache: 'no-store' });
                return res.json();
            };
            const fmt = (d) => new Date(d.replace(' ', 'T')).toLocaleString([], { weekday: 'short', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });

            let gate;
            try {
                gate = await checkWindow();
                while (gate.state === 'too_early') {
                    const target = Date.now() + gate.seconds_until_open * 1000;
                    container.innerHTML = card('fa-clock', '#f59e0b', 'Too early to join',
                        `Your consultation is scheduled for <strong>${fmt(gate.starts_at)}</strong>.<br>You can join starting <strong>${gate.early_minutes} minutes</strong> before your appointment time (from ${fmt(gate.opens_at)}). Please keep this page open &mdash; it will connect automatically.`,
                        `<p style="margin-top:16px; font-size:1.4rem; font-weight:800; color:#fbbf24;" id="join-countdown"></p>`);
                    const cd = document.getElementById('join-countdown');
                    await new Promise((resolve) => {
                        const tick = () => {
                            const left = Math.max(0, Math.ceil((target - Date.now()) / 1000));
                            const h = Math.floor(left / 3600), m = Math.floor((left % 3600) / 60), sec = left % 60;
                            cd.textContent = (h ? h + ':' + String(m).padStart(2, '0') : m) + ':' + String(sec).padStart(2, '0') + ' until you can join';
                            if (left <= 0) { clearInterval(iv); resolve(); }
                        };
                        const iv = setInterval(tick, 1000);
                        tick();
                    });
                    gate = await checkWindow();
                }
            } catch (e) {
                container.innerHTML = card('fa-exclamation-circle', '#ef4444', 'Unable to verify your appointment', 'Please check your internet connection and refresh this page.');
                return;
            }

            if (gate.state === 'invalid') {
                container.innerHTML = card('fa-exclamation-triangle', '#ef4444', 'Invalid Consultation Link', 'This consultation link is not recognised. Please use the link from your most recent invitation email.');
                return;
            }
            if (gate.state === 'ended') {
                container.innerHTML = card('fa-calendar-check', '#94a3b8', 'This consultation has ended', 'The scheduled time for this video consultation has passed. Please contact the clinic if you need to reschedule.');
                return;
            }

            // Function to dynamically load Jitsi API
            const loadJitsiScript = () => {
                return new Promise((resolve, reject) => {
                    if (window.JitsiMeetExternalAPI) {
                        resolve();
                        return;
                    }
                    const script = document.createElement('script');
                    script.src = 'https://meet.jit.si/external_api.js';
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            };

            try {
                await loadJitsiScript();
                container.innerHTML = ''; // Clear status card

                const domain = 'meet.jit.si';
                const options = {
                    roomName: roomName,
                    width: '100%',
                    height: '100%',
                    parentNode: container,
                    configOverwrite: {
                        startWithAudioMuted: false,
                        startWithVideoMuted: false,
                        prejoinPageEnabled: false,
                        disableDeepLinking: true
                    },
                    interfaceConfigOverwrite: {
                        TOOLBAR_BUTTONS: [
                            'microphone', 'camera', 'desktop', 'fullscreen',
                            'fodeviceselection', 'hangup', 'chat', 'settings',
                            'videoquality', 'tileview'
                        ]
                    }
                };

                const api = new JitsiMeetExternalAPI(domain, options);

                api.addEventListener('videoConferenceLeft', () => {
                    container.innerHTML = `
                        <div class="status-card">
                            <i class="fas fa-check-circle" style="font-size:3rem; color:#0d9488; margin-bottom:16px;"></i>
                            <h2>Consultation Ended</h2>
                            <p>Thank you for participating in your telehealth consultation with Specialty EHR. You may now close this browser tab.</p>
                        </div>
                    `;
                    api.dispose();
                });

            } catch (err) {
                console.error("Failed to load Jitsi API", err);
                container.innerHTML = `
                    <div class="status-card error-card">
                        <i class="fas fa-exclamation-circle spinner"></i>
                        <h2 style="color:#ffffff;">Connection Failed</h2>
                        <p style="color:#fca5a5;">Unable to initiate the video consultation framework. Please ensure your device is connected to the internet and refresh the page.</p>
                    </div>
                `;
            }
        });
    </script>
</body>
</html>
