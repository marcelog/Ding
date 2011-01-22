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

use Ding\Cache\Locator\CacheLocator;

use Ding\Bean\BeanAnnotationDefinition;

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
    private static $_annotatedClasses = array();
    private static $_classesAnnotated = array();

    /**
     * Parses all annotations in the given text.
     *
     * @param string $text
     *
     * @return BeanAnnotationDefinition[]
     */
    private static function _getAnnotations($text)
    {
        $ret = array();
        if (preg_match_all('/@.+/', $text, $matches) > 0) {
            foreach ($matches[0] as $annotation) {
                $argsStart = strpos($annotation, '(');
                $arguments = array();
                if ($argsStart !== false) {
                    $name = substr($annotation, 1, $argsStart - 1);
                    $args = substr($annotation, $argsStart + 1, -1);
                    // http://stackoverflow.com/questions/168171/regular-expression-for-parsing-name-value-pairs
                    $argsN = preg_match_all(
                    	'/([^=,]*)=("[^"]*"|[^,"]*)/', $args, $matches
                    );
                    if ($argsN > 0)
                    {
                        for ($i = 0; $i < $argsN; $i++) {
                            $key = trim($matches[1][$i]);
                            $value = trim($matches[2][$i]);
                            $arguments[$key] = $value;
                        }
                    }
                } else {
                    $name = substr($annotation, 1);
                }
                $ret[] = new BeanAnnotationDefinition($name, $arguments);
            }
        }
        return $ret;
    }

    public static function getClassesByAnnotation($annotation)
    {
        if (isset(self::$_classesAnnotated[$annotation])) {
            return self::$_classesAnnotated[$annotation];
        }
        $cache = CacheLocator::getAnnotationsCacheInstance();
        $cacheKey = str_replace('\\', '_', $annotation) . 'classbyannotations';
        $result = false;
        $classes = $cache->fetch($cacheKey, $result);
        if ($result === true) {
            self::$_classesAnnotated[$annotation] = $classes;
            return $classes;
        }
        return array();
    }

    public static function getClassAnnotations($class)
    {
        if (isset(self::$_annotatedClasses[$class])) {
            return self::$_annotatedClasses[$class];
        }
        $cache = CacheLocator::getAnnotationsCacheInstance();
        $cacheKey = str_replace('\\', '_', $class) . '.classannotations';
        $result = false;
        $annotations = $cache->fetch($cacheKey, $result);
        if ($result === true) {
            self::$_annotatedClasses[$class] = $annotations;
            return $annotations;
        }
        self::$_annotatedClasses[$class] = array();
        $rClass = ReflectionFactory::getClass($class);
        $ret = array();
        $ret['class'] = array();
        foreach (self::_getAnnotations($rClass->getDocComment()) as $annotation) {
            $name = $annotation->getName();
            $ret['class'][$name] = $annotation;
            if (!isset(self::$_classesAnnotated[$name])) {
                self::$_classesAnnotated[$name] = array();
            }
            self::$_classesAnnotated[$name][$class] = $class;
        }
        foreach ($rClass->getMethods() as $method) {
            $methodName = $method->getName();
            $ret[$methodName] = array();
            foreach (self::_getAnnotations($method->getDocComment()) as $annotation) {
                $name = $annotation->getName();
                $ret[$methodName][$name] = $annotation;
            }
        }
        self::$_annotatedClasses[$class] = $ret;
        $cache->store($cacheKey, $ret);
        return $ret;
    }

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