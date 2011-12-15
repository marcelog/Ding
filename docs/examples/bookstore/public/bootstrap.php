<?php
define('APPLICATION_PATH', realpath(__DIR__ . '/..'));
define('APPLICATION_PRIVATE', APPLICATION_PATH . '/private');
define('APPLICATION_PUBLIC', APPLICATION_PATH . '/public');
define('APPLICATION_CFG', APPLICATION_PRIVATE . '/config');
define('APPLICATION_BEANS', APPLICATION_PRIVATE . '/context');
define('APPLICATION_SRC', APPLICATION_PRIVATE . '/src');
define('APPLICATION_I18N', APPLICATION_PRIVATE . '/i18n');
define('APPLICATION_DATA', APPLICATION_PRIVATE . '/data');
ini_set('include_path', implode(PATH_SEPARATOR, array(
    __DIR__ . '/../../../../src/mg',
    APPLICATION_SRC, APPLICATION_I18N,
    ini_get('include_path')
)));
require_once 'Ding/Autoloader/Autoloader.php';
\Ding\Autoloader\Autoloader::register();

$properties = array(
    'ding' => array(
    	'log4php.properties' => APPLICATION_CFG . '/log4php.properties',
        'factory' => array(
            'bdef' => array(
             	'xml' => array('filename' => 'beans.xml', 'directories' => array(APPLICATION_BEANS)),
                'annotation' => array('scanDir' => array(APPLICATION_SRC))
            ),
            'properties' => array('application.config' => APPLICATION_CFG)
        ),
        'cache' => array()
    )
);
