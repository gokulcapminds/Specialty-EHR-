<?php
require_once 'backend/bootstrap/app.php';
use App\Models\Database;

try {
    $cols = Database::fetchAll("DESCRIBE clinical_notes");
    foreach ($cols as $c) {
        echo "Column: {$c['Field']} ({$c['Type']})\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
