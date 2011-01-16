<?php
/**
 * PAMI Event handler interface.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage Pami
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Helpers\PAMI;

use PAMI\Message\Event\EventMessage;

/**
 * PAMI Event handler interface.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage Pami
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
interface IPamiEventHandler
{
    /**
     * Will be called for every PAMI event.
     *
     * @param EventMessage $event PAMI Event message.
     *
     * @return void
     */
    public function handlePamiEvent(EventMessage $event);
}