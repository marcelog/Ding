<?php
ini_set(
    'include_path',
    implode(
        PATH_SEPARATOR,
        array(
            realpath(implode(
                DIRECTORY_SEPARATOR, array(__DIR__, '..', 'src', 'mg')
            )),
            ini_get('include_path'),
        )
    )
);
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'Ding/Logger/Logger.php';

if (!defined('RESOURCES_DIR')) {
    define('RESOURCES_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'resources');
}

require_once 'Ding/Autoloader/Autoloader.php'; // Include ding autoloader.
\Ding\Autoloader\Autoloader::register(); // Call autoloader register for ding autoloader.

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
