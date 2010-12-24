<?php
/**
 * Internal reflection manager.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Reflection
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */
namespace Ding\Reflection;

/**
 * Internal reflection manager.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Reflection
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
class ReflectionFactory
{
    /**
     * Cache reflection classes instantiated so far.
     * @var ReflectionClass[]
     */
    private static $_reflectionClasses = array();
    
    /**
     * Returns a (cached) reflection class.
     *
     * @param string $class Class name
     * 
     * @throws ReflectionException
     * @return ReflectionClass
     */
    public static function getClass($class)
    {
        $ret = false;
        if (isset(self::$_reflectionClasses[$class])) {
            $ret = self::$_reflectionClasses[$class];
        } else {
            $ret = new \ReflectionClass($class);
            self::$_reflectionClasses[$class] = $ret;
        }
        return $ret;
    }
}