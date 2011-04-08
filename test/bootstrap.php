<?php
ini_set(
    'include_path',
    implode(
        PATH_SEPARATOR,
        array(
            ini_get('include_path'),
            realpath(implode(
                DIRECTORY_SEPARATOR, array(__DIR__, '..', 'src', 'mg')
            ))
        )
    )
);
require_once 'Ding/Logger/Logger.php';

if (!defined('RESOURCES_DIR')) {
    define('RESOURCES_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'resources');
}

require_once 'Ding/Autoloader/Ding_Autoloader.php'; // Include ding autoloader.
Ding_Autoloader::register(); // Call autoloader register for ding autoloader.

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
