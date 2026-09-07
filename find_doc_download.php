<?php
$js = file('public/js/app.js');
foreach ($js as $lineNum => $line) {
    if (stripos($line, 'documents/download') !== false || stripos($line, 'api/documents') !== false) {
        echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
    }
}
