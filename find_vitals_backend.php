<?php
function searchDir($dir) {
    foreach (scandir($dir) as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            searchDir($path);
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($path);
            if (stripos($content, 'vital_') !== false || stripos($content, 'vitals') !== false) {
                echo "File: {$path}\n";
            }
        }
    }
}
searchDir('backend');
