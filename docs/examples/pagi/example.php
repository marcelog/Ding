<?php
declare(ticks=1);
/**
 * Pagi example
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage Pagi
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
use Ding\Container\Impl\ContainerImpl;
use Ding\Helpers\PAMI\IPamiEventHandler;
use PAGI\Application\PAGIApplication;

class MyPagiApplication1 extends PAGIApplication
{
    public function init()
    {

    }

    public function shutdown()
    {

    }

    public function run()
    {
        $agi = $this->getAgi();
        $agi->sayDigits('1', '');
    }

    public function errorHandler($type, $message, $file, $line)
    {

    }

    public function signalHandler($signal)
    {

    }
}
class MyPagiApplication2 extends PAGIApplication
{
    public function errorHandler($type, $message, $file, $line)
    {

    }

    public function signalHandler($signal)
    {

    }

    public function init()
    {
        $agi = $this->getAgi();
        $agi->sayDigits('2', '');
    }

    public function shutdown()
    {

    }

    public function run()
    {
    }
}

$log4php = getenv('log4php_properties');
$properties = array(
    'ding' => array(
        'log4php.properties' => $log4php,
        'factory' => array(
            'bdef' => array('xml' => array('filename' => getenv('beans_xml'))),
            'properties' => array(
                'log4php.properties' => $log4php,
                'app1.num' => 1,
                'app2.num' => 2
            )
        ),
        'cache' => array(
            'proxy' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/proxy'),
            'bdef' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/bdef'),
            'beans' => array('impl' => 'dummy')
        )
    )
);
