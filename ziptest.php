<?php
require __DIR__ . '/vendor/autoload.php';
echo 'ZipArchive class: ' . (class_exists('ZipArchive') ? 'EXISTS' : 'MISSING') . PHP_EOL;
echo 'zip extension: ' . (extension_loaded('zip') ? 'LOADED' : 'NOT LOADED') . PHP_EOL;
try {
    $z = new ZipArchive();
    echo 'new ZipArchive(): SUCCESS' . PHP_EOL;
    $z->open(__FILE__, ZipArchive::CHECKCONS);
} catch (Throwable $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
echo 'PhpSpreadsheet IOFactory: ' . (class_exists('PhpOffice\PhpSpreadsheet\IOFactory') ? 'EXISTS' : 'MISSING') . PHP_EOL;
