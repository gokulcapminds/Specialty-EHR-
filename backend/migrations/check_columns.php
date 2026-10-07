<?php
require_once __DIR__ . '/../bootstrap/app.php';
use App\Models\Database;

$cols = Database::fetchAll('SHOW COLUMNS FROM patient_documents', []);
foreach ($cols as $c) {
    echo $c['Field'] . "\n";
}
