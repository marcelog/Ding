<?php
/**
 * Ding autoloader, you will surely need this.
 *
 * PHP Version 5
 *
 * @category ding
 * @package  autoloader
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
 * @category ding
 * @package  autoloader
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
class Autoloader
{
    /**
     * Holds classes. Associative array, where key is class name (full, 
     * including namespace), and the value is the path where to locate the
     * file containing the class.
     * @var array
     */
    private static $_myClasses;
    
    /**
     * Called by php to load a given class. Returns true if the class was
     * successfully loaded.
     * 
     * @return boolean
     */
    public static function load($class)
    {
        $realClass = substr($class, 5);
        if (isset(self::$_myClasses[$class])) {
            $finalName = implode(
                DIRECTORY_SEPARATOR,
                array(
                    self::$_myClasses[$class], $realClass . '.php'
                )
            ); 
            include_once $finalName;
            return true;
        }
        return false;
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
        self::$_myClasses = array(
        	'Ding\BeanDefinition' => implode(DIRECTORY_SEPARATOR, array('component')),
        	'Ding\BeanList' => implode(DIRECTORY_SEPARATOR, array('component')),
        	'Ding\BeanListException' => implode(DIRECTORY_SEPARATOR, array('component', 'exception')),
        	'Ding\BeanPropertyDefinition' => implode(DIRECTORY_SEPARATOR, array('component')),
        	'Ding\ContainerException'  => implode(DIRECTORY_SEPARATOR, array('container', 'exception')),
        	'Ding\ContainerImpl' => implode(DIRECTORY_SEPARATOR, array('container', 'impl')),
        	'Ding\IContainer' => implode(DIRECTORY_SEPARATOR, array('container')),
        	'Ding\InterceptorDefinition' => implode(DIRECTORY_SEPARATOR, array('aspect')),
        	'Ding\AspectDefinition' => implode(DIRECTORY_SEPARATOR, array('aspect')),
            'Ding\Proxy' => implode(DIRECTORY_SEPARATOR, array('aspect')),
            'Ding\MethodInvocation' => implode(DIRECTORY_SEPARATOR, array('aspect')),
            'Ding\DingException' => implode(DIRECTORY_SEPARATOR, array('exception')),
            'Ding\IBeanFactory' => implode(DIRECTORY_SEPARATOR, array('component', 'factory')),
            'Ding\BeanFactoryException' => implode(DIRECTORY_SEPARATOR, array('component', 'factory', 'exception')),
        	'Ding\BeanFactoryXmlImpl' => implode(DIRECTORY_SEPARATOR, array('component', 'factory', 'impl'))
        );
        return spl_autoload_register('Autoloader::load');
    }
}
