<?php
$js = file('public/js/app.js');
foreach ($js as $lineNum => $line) {
    if (stripos($line, 'Record Vitals') !== false || stripos($line, 'vitals') !== false) {
        echo "Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
    }
}
