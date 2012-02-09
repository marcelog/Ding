 <?php
$stub =
'<?php
Phar::mapPhar();
spl_autoload_register(function ($class) {
    $classFile = "phar://ding.phar/" . str_replace("\\\", "/", $class) . ".php";
    if (file_exists($classFile)) {
        require_once $classFile;
        return true;
    }
});
include "phar://ding.phar/Ding/Autoloader/Autoloader.php";
\Ding\Autoloader\Autoloader::register();
__HALT_COMPILER();
?>';
$phar = new Phar($argv[1]);
$phar->setAlias('ding.phar');
$phar->buildFromDirectory($argv[2]);
$phar->setStub($stub);
