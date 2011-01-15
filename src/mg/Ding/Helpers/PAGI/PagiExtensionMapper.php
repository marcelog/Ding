<?php
/**
 * This will map an extension to a specific application (bean).
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage Pagi
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Helpers\PAGI;

/**
 * This will map an extension to a specific application (bean).
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage Pagi
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class PagiExtensionMapper
{
    /**
     * Extensions/Applications map.
     * @var array
     */
    private $_map;

    /**
     * Sets the map to use.
     *
     * @param array $map Map to set.
     *
     * @return void
     */
    public function setMap(array $map)
    {
        $this->_map = $map;
    }

    /**
     * Resolve extension to application (bean).
     *
     * @param string $extension Extension to map.
     *
     * @return PAGIApplication
     */
    public function resolve($extension)
    {
        foreach ($this->_map as $map) {
            if ($map['extension'] == $extension) {
                return $map['application'];
            }
        }
        return $this->_map['default']['application'];
    }

    /**
     * Consctructor.
     *
     * @return void
     */
    public function __construct()
    {
    }
}