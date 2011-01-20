<?php
/**
 * Syslog example
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage Syslog
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
ini_set(
    'include_path',
    implode(
        PATH_SEPARATOR,
        array(
            ini_get('include_path'),
            implode(DIRECTORY_SEPARATOR, array('..', '..', '..', 'src', 'mg'))
        )
    )
);

require_once 'Ding/Autoloader/Ding_Autoloader.php';
Ding_Autoloader::register();

use Ding\Container\Impl\ContainerImpl;

$properties = array(
    'ding' => array(
        'log4php.properties' => './log4php.properties',
        'factory' => array(
            'bdef' => array('xml' => array('filename' => 'beans.xml')),
            'properties' => array(
                'ident' => 'ident',
                'options' => LOG_PID | LOG_ODELAY,
                'facility' => LOG_USER
            )
        ),
        'cache' => array(
            'proxy' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/proxy'),
            'bdef' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/bdef'),
            'beans' => array('impl' => 'dummy')
        )
    )
);
$a = ContainerImpl::getInstance($properties);
$syslog = $a->getBean('Syslog');
$syslog->emerg('some log');
$syslog->alert('some log');
$syslog->critical('some log');
$syslog->error('some log');
$syslog->warning('some log');
$syslog->notice('some log');
$syslog->info('some log');
$syslog->debug('some log');
