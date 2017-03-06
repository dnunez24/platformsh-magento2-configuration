<?php

use Platformsh\Environment;

require_once 'src/Platformsh/Environment.php';
$env = new Environment();

$env->log("Copying static.php to front-static.php");
copy($env->getMagentoPath('/pub/static.php'), $env->getMagentoPath('/pub/front-static.php'));

$dirName = __DIR__ . '/patches';

$files = glob($dirName . '/*');
sort($files);
foreach ($files as $file) {
    $cmd = 'git apply '  . $file;
    $env->execute($cmd);
}

copy($env->getMagentoPath('/app/etc/di.xml'), $env->getMagentoPath('/app/di.xml'));
mkdir($env->getMagentoPath('/app/enterprise'), 0777, true);
copy($env->getMagentoPath('/app/etc/enterprise/di.xml'), $env->getMagentoPath('/app/enterprise/di.xml'));

$sampleDataDir = $env->getMagentoPath('/vendor/magento/sample-data-media');
if (file_exists($sampleDataDir)) {
    $env->log("Sample data media found. Marshalling to pub/media.");
    $destination = $env->getMagentoPath('/pub/media');
    foreach (
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sampleDataDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST) as $item
    ) {
        if ($item->isDir()) {
            if (!file_exists($destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName())) {
                mkdir($destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        } else {
            copy($item, $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        }
    }
}
