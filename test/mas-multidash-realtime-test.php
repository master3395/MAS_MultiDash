<?php
/**
 * MAS_MultiDash sanity checks (run from CLI: php test/mas-multidash-realtime-test.php)
 */
define('CMS_VERSION', '2.2.10');
$root = dirname(__DIR__);
$errors = 0;

$required = array(
    'MAS_MultiDash.module.php',
    'method.install.php',
    'action.ajax_api.php',
    'action.stream.php',
    'lib/class.MasMdSecurity.php',
    'lib/class.MasMdPathGuard.php',
    'js/mas-md-admin.js',
    'lang/en_US.php',
    'images/icon.gif',
    'CHANGELOG.md',
);

foreach ($required as $f) {
    $path = $root . DIRECTORY_SEPARATOR . $f;
    if (!is_file($path)) {
        fwrite(STDERR, "MISSING: $f\n");
        $errors++;
    } else {
        echo "OK: $f\n";
    }
}

$lineLimit = 500;
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/lib'));
foreach ($iter as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $lines = count(file($file->getPathname()));
    if ($lines > $lineLimit) {
        fwrite(STDERR, "LINE LIMIT: {$file->getPathname()} ($lines)\n");
        $errors++;
    }
}

exit($errors > 0 ? 1 : 0);
