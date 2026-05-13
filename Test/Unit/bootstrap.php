<?php
$autoloadCandidates = [
    __DIR__ . '/../../../../autoload.php',
    __DIR__ . '/../../../../../../vendor/autoload.php',
];
foreach ($autoloadCandidates as $autoload) {
    if (file_exists($autoload)) {
        require_once $autoload;
        break;
    }
}

$magentoBootstrapCandidates = [
    __DIR__ . '/../../../../../dev/tests/unit/framework/bootstrap.php',
    __DIR__ . '/../../../../../../dev/tests/unit/framework/bootstrap.php',
];
foreach ($magentoBootstrapCandidates as $magentoBootstrap) {
    if (file_exists($magentoBootstrap)) {
        require_once $magentoBootstrap;
        break;
    }
}
