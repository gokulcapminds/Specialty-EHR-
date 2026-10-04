<?php
// public/index.php - Front Controller

require_once __DIR__ . '/../backend/bootstrap/app.php';

// Dispatch routing if this is an API request
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedPath = parse_url($requestUri, PHP_URL_PATH) ?? '/';

// Find /api/ case-insensitively to support case variations on Windows/Apache and subdirectories
$apiPos = stripos($parsedPath, '/api/');
if ($apiPos !== false) {
    // Extract route starting from /api/
    $routePath = substr($parsedPath, $apiPos);
    
    // REST API Endpoint
    $router = new \Routes\Router();
    require_once __DIR__ . '/../backend/routes/api.php';
    $router->dispatch($routePath, $_SERVER['REQUEST_METHOD'] ?? 'GET');
    exit();
}

// Otherwise, serve SPA interface shell
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= \App\Security\CSRFTokenManager::generateToken() ?>">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Specialty EHR - Clinical EHR & Workspace</title>
    <!-- Bootstrap 5 (vendored locally) -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/theme.css?v=<?= time() ?>">
    <!-- Module Specific CSS Stylesheets -->
    <link rel="stylesheet" href="css/modules/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="css/modules/login.css?v=<?= time() ?>">
    <link rel="stylesheet" href="css/modules/calendar.css?v=<?= time() ?>">
    <link rel="stylesheet" href="css/modules/administration.css?v=<?= time() ?>">
    <link rel="stylesheet" href="css/modules/messaging.css?v=<?= time() ?>">
    <link rel="stylesheet" href="css/modules/billing.css?v=<?= time() ?>">
    <link rel="stylesheet" href="css/modules/clinical.css?v=<?= time() ?>">
    <link rel="stylesheet" href="css/modules/patients.css?v=<?= time() ?>">
    <link rel="stylesheet" href="css/modules/imaging.css?v=<?= time() ?>">
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Bootstrap 5 bundled JS (includes Popper) -->
    <script src="js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <!-- Google Fonts for rich aesthetics -->
    <link href="css/google_fonts.css" rel="stylesheet">
</head>
<body class="theme-light">
    <!-- Screen Reader announcements (WCAG 2.1 AA) -->
    <div id="sr-announcer" class="sr-only" aria-live="assertive" aria-atomic="true"></div>

    <!-- Main Container -->
    <div id="app-root">
        <!-- Load loader initially -->
        <div id="initial-loader" class="loader-container">
            <div class="loader" role="status" aria-label="Loading application"></div>
        </div>
    </div>

    <!-- Global Application and UI component scripts -->
    <!-- <script type="module" src="js/security/inspector.js?v=<?= time() ?>"></script> -->
    <script src="js/specialty-registry.js?v=<?= time() ?>"></script>
    <script type="module" src="js/components/toast.js?v=<?= time() ?>"></script>
    <script type="module" src="js/components/bootstrap-alert.js?v=<?= time() ?>"></script>
    <script type="module" src="js/components/accordion.js?v=<?= time() ?>"></script>
    <script type="module" src="js/api.js?v=<?= time() ?>"></script>
    <script type="module" src="js/router.js?v=<?= time() ?>"></script>
    <script src="js/modules/clinical-tabs.js?v=<?= time() ?>"></script>
    <!-- Imaging/DICOM Viewer component (non-module, exposes ImagingViewer on window) -->
    <script src="js/components/imaging-viewer.js?v=<?= time() ?>"></script>
    <script type="module" src="js/app.js?v=<?= time() ?>"></script>
    <!-- Phase 2: Core Clinical Encounter lifecycle (Sign/Lock/Addendum/BMI/Status) -->
    <script type="module" src="js/encounter_lifecycle.js?v=<?= time() ?>"></script>
</body>
</html>
