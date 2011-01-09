<?php
/**
 * Http session "facade".
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Mvc
 * @subpackage Http
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\HttpSession;

/**
 * Http session "facade".
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Mvc
 * @subpackage Http
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class HttpSession
{
    /**
     * Current instance.
     * @var HttpSession
     */
    private static $_instance = false;

    /**
     * Destroys the current session.
     *
     * @return void
     */
    public function destroy()
    {
        session_destroy();
    }

    /**
     * Returns a previously saved session attribute with setAttribute().
     *
     * @param string $name Session attribute name.
     *
     * @return mixed
     */
    public function getAttribute($name)
    {
        global $_SESSION;
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
        return false;
    }

    /**
     * Saves an attribute to the session.
     *
     * @param string $name  Session attribute name.
     * @param mixed  $value Value.
     *
     * @return void
     */
    public function setAttribute($name, $value)
    {
        global $_SESSION;
        $_SESSION[$name] = $value;
    }

    /**
     * Returns an instance of a session facade.
     *
     * @return HttpSession
     */
    public static function getSession()
    {
        if (self::$_instance === false) {
            $ret = new HttpSession();
        } else {
            $ret = self::$_instance;
        }
        return $ret;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    protected function __construct()
    {
    }
}