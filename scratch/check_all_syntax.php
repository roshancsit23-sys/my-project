<?php
$dir = dirname(__DIR__);
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$phpFiles = [];
$errors = [];

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getRealPath();
        // Skip .git or vendor if any
        if (strpos($path, '.git') !== false) continue;
        
        $cmd = escapeshellcmd('C:\xampp\php\php.exe') . ' -l ' . escapeshellarg($path);
        $output = [];
        $returnVar = 0;
        exec($cmd, $output, $returnVar);
        
        if ($returnVar !== 0) {
            $errors[] = [
                'file' => $path,
                'output' => implode("\n", $output)
            ];
        } else {
            $phpFiles[] = $path;
        }
    }
}

echo "Scanned " . (count($phpFiles) + count($errors)) . " PHP files.\n";
if (empty($errors)) {
    echo "ALL PHP FILES PASSED SYNTAX LINT CHECK!\n";
} else {
    echo "FOUND " . count($errors) . " SYNTAX ERRORS:\n";
    foreach ($errors as $e) {
        echo "File: " . $e['file'] . "\n" . $e['output'] . "\n\n";
    }
}
