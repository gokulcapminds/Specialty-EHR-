<?php
$js = file('public/js/app.js');
foreach ($js as $lineNum => $line) {
    if (strpos($line, 'function renderWaitingListWorkspace') !== false) {
        echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
    }
}
