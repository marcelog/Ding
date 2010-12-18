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
class Autoloader
{
    /**
     * Holds current realpath.
     * @var string
     */
    private static $_myPath;
    
    /**
     * Called by php to load a given class. Returns true if the class was
     * successfully loaded.
     * 
     * @return boolean
     */
    public static function load($class)
    {
        $file = implode(
            DIRECTORY_SEPARATOR,
            array(
                self::$_myPath,
                str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php'
            )
        );
        if (!file_exists($file)) {
            return false;
        }
        include_once realpath($file);
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
        self::$_myPath = implode(
            DIRECTORY_SEPARATOR, 
            array(realpath(dirname(__FILE__)), '..', '..')
        );
        return spl_autoload_register('Autoloader::load');
    }
}
