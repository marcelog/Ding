<?php
/**
 * Ding autoloader, you will surely need this.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Autoloader
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */

// Check for log4php.
use Ding\Cache\Impl\DummyCacheImpl;
use Ding\Cache\ICache;

foreach (explode(PATH_SEPARATOR, ini_get('include_path')) as $path) {
    $truePath = implode(
        DIRECTORY_SEPARATOR,
        array($path, 'log4php', 'Logger.php')
    );
    if (file_exists($truePath)) {
        require_once $truePath;
    }
}
// If not found, include our own dummy logger.
if (!class_exists('Logger')) {
    $truePath = implode(
        DIRECTORY_SEPARATOR,
        array('Ding', 'Logger', 'Logger.php')
    );
    require_once $truePath;
}

/**
 * Ding autoloader, you will surely need this.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Autoloader
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
class Ding_Autoloader
{
    /**
     * log4php logger or own dummy instance.
     * @var Logger
     */
    private static $_logger;

    /**
     * Holds current realpath.
     * @var string
     */
    private static $_myPath;

    /**
     * Autoloader cache.
     * @var ICache
     */
    private static $_cache = false;

    /**
     * Sets the autoloader cache to be used.
     *
     * @param ICache $cache Autoloader cache to use.
     *
     * @return void
     */
    public static function setCache(ICache $cache)
    {
        self::$_cache = $cache;
    }

    private static function _resolve($class)
    {
        $file = realpath(implode(
            DIRECTORY_SEPARATOR,
            array(
                self::$_myPath,
                str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php'
            )
        ));
        if (!file_exists($file)) {
            return false;
        }
        return $file;
    }

    /**
     * Called by php to load a given class. Returns true if the class was
     * successfully loaded.
     *
     * @return boolean
     */
    public static function load($class)
    {
        $cacheKey = $class . '.autoloader';
        $result = false;
        if (self::$_cache !== false) {
            $file = self::$_cache->fetch($cacheKey, $result);
            if ($result === false) {
                $file = self::_resolve($class);
                if ($file === false) {
                    return false;
                }
                self::$_cache->store($cacheKey, $file);
            }
        } else {
            $file = self::_resolve($class);
            if ($file === false) {
               return false;
            }
        }
        include_once $file;
        return true;
    }

    /**
     * You need to use this function to autoregister this loader.
     *
     * @see spl_autoload_register()
     *
     * @return boolean
     */
    public static function register()
    {
        self::$_cache = false;
        self::$_myPath = implode(
            DIRECTORY_SEPARATOR,
            array(realpath(dirname(__FILE__)), '..', '..')
        );
        return spl_autoload_register('Ding_Autoloader::load');
    }
}
