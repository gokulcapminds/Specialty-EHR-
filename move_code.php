<?php
$content = file_get_contents('C:\wamp64\www\pf_ehr\public\js\app.js');
$startMarker = '    // Common Global Notifications Bell Center across all pages';
$endMarker = '    window.addEventListener(\'moduleLoaded\', () => {
        window.initGlobalNotificationBell();
        window.loadAuditLogs();
    });';

$startPos = strpos($content, $startMarker);
$endPos = strpos($content, $endMarker, $startPos) + strlen($endMarker);

if ($startPos !== false && $endPos !== false) {
    $chunk = substr($content, $startPos, $endPos - $startPos);
    
    // Remove chunk from original position
    $newContent = substr_replace($content, '', $startPos, $endPos - $startPos);
    
    // Append chunk to the end of the file
    $newContent .= "\n" . $chunk . "\n";
    
    file_put_contents('C:\wamp64\www\pf_ehr\public\js\app.js', $newContent);
    echo "Moved global code to bottom of app.js\n";
} else {
    echo "Could not find markers\n";
}
