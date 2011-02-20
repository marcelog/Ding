 <?php
$stub = <<<TEXT
<?php
ini_set('include_path', implode(
    PATH_SEPARATOR,
    array(
        ini_get('include_path'),
	'phar://' . __FILE__
    ))
);
require_once 'Ding/Autoloader/Ding_Autoloader.php';
Ding_Autoloader::register();
__HALT_COMPILER();
?>
TEXT;
$phar = new Phar($argv[1]);
$phar->buildFromDirectory($argv[2]);
$phar->setStub($stub);
