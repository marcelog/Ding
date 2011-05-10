 <?php
$stub =
'<?php
Phar::mapPhar();
include "phar://ding.phar/Ding/Autoloader/Autoloader.php";
\Ding\Autoloader\Autoloader::register();
__HALT_COMPILER();
?>';
$phar = new Phar($argv[1]);
$phar->setAlias('ding.phar');
$phar->buildFromDirectory($argv[2]);
$phar->setStub($stub);
