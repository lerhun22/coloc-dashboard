<?php

$zipFile = __DIR__ . '/../writable/zip_123.zip';
$dest = __DIR__ . '/test_extract';

echo "ZIP: " . $zipFile . "<br>";
echo "DEST: " . $dest . "<br>";

if (!is_dir($dest)) {
    mkdir($dest);
}

$zip = new ZipArchive();

$r = $zip->open($zipFile);

echo "open = ";
var_dump($r);

if ($r === TRUE) {

    $zip->extractTo($dest);
    $zip->close();

    echo "EXTRACT OK";
} else {

    echo "EXTRACT FAIL";
}
