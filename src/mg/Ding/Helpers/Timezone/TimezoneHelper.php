<?php
/**
 * This is a bean that will setup php default timezone.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage Timezone
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Helpers\Timezone;

/**
 * This is a bean that will setup php default timezone.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage Timezone
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class TimezoneHelper
{
    /**
     * log4php logger or our own.
     * @var Logger
     */
    private $_logger;

    /**
     * Configured timezone.
     * @var string
     */
    private $_timezone;

    /**
     * Sets php default timezone.
     *
     * @param string $timezone Timezone to set, like 'America/Buenos_Aires'.
     *
     * @return void
     */
    public function setTimezone($timezone)
    {
        $this->_timezone = $timezone;
    }

    /**
     * Returns configured timezone.
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->_timezone;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_logger = \Logger::getLogger('Ding.TimezoneHelper');
    }
}