<?php
$_SESSION = ['user_id' => 2, 'user_role' => 'Doctor'];
require __DIR__ . '/backend/vendor/autoload.php';
$config = require __DIR__ . '/backend/config/database.php';
// fake the Database class connection
class FakeDB {
    public static $pdo;
    public static function init($pdo) { self::$pdo = $pdo; }
}
// wait, I can just call the endpoint using curl.
