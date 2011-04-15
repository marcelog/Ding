<?php
/**
 * Internal reflection manager.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Reflection
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://marcelog.github.com/
 *
 * Copyright 2011 Marcelo Gornstein <marcelog@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
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
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
class ReflectionFactory
{
    /**
     * Cache reflection classes instantiated so far.
     * @var ReflectionClass[]
     */
    private static $_reflectionClasses = array();
    /**
     * A map where the key is the class, and the value is an array with the
     * 'class annotations and its annotated methods.
     * @var string[]
     */
    private static $_annotatedClasses = array();

    /**
     * A map where the key is the annotations, and the value is an array with
     * all the classes (not their methods) with this annotation.
     * @var string[]
     */
    private static $_classesAnnotated = array();

    /**
     * Reflection methods, indexed by class.
     * @var string[]
     */
    private static $_reflectionMethods = array();

    /**
     * Wether to use annotations or not.
     * @var boolean
     */
    private static $_withAnnotations = false;

    /**
     * Taken from: http://stackoverflow.com/questions/928928/determining-what-classes-are-defined-in-a-php-class-file
     * Returns all php classes found in a code block. Multiple namespaces in one file are not supported.
     *
     * @param string $code PHP Code.
     *
     * @return string[]
     */
    public static function getClassesFromCode($code)
    {
        $classes = array();
        $tokens = token_get_all($code);
        $count = count($tokens);
        $namespace = '';
        for ($i = 0; $i < $count; $i++) {
            if (
                $tokens[$i][0] == T_CLASS
                && $tokens[$i + 1][0] == T_WHITESPACE
                && $tokens[$i + 2][0] == T_STRING
            ) {
                $class_name = $tokens[$i + 2][1];
                $classes[] = empty($namespace) ? $class_name : $namespace . "\\" . $class_name;
                $i += 2;
            } else if ($tokens[$i][0] === T_NAMESPACE) {
                for(; $tokens[$i][0] !== T_STRING; $i++);
                $namespace = $tokens[$i][1];
                for($i++; $tokens[$i][0] !== ';'; $i++) {
                        for(; $tokens[$i][0] !== T_STRING; $i++);
                        $namespace .= "\\" . $tokens[$i][1];
                }
            }
        }
        return $classes;
    }

    /**
     * Parses all annotations in the given text.
     *
     * @param string $text
     *
     * @return BeanAnnotationDefinition[]
     */
    public static function getAnnotations($text)
    {
        $ret = array();
        if (preg_match_all('/@[\-\/a-zA-Z0-9=_\?,\\\\\(\+\.\$\^\*\)\ ]+/', $text, $matches) > 0) {
            foreach ($matches[0] as $annotation) {
                $argsStart = strpos($annotation, '(');
                $arguments = array();
                if ($argsStart !== false) {
                    $name = trim(substr($annotation, 1, $argsStart - 1));
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
                    $stuff = explode(' ', $annotation);
                    $name = substr($stuff[0], 1);
                    $arguments[] = $stuff;
                }
                $ret[] = new BeanAnnotationDefinition($name, $arguments);
            }
        }
        return $ret;
    }

    /**
     * Returns all classes annotated with the given annotation.
     *
     * @param string $annotation Annotation name.
     *
     * @return string[]
     */
    public static function getClassesByAnnotation($annotation)
    {
        if (!self::$_withAnnotations) {
            return array();
        }
        if (isset(self::$_classesAnnotated[$annotation])) {
            return self::$_classesAnnotated[$annotation];
        }
        $cache = CacheLocator::getAnnotationsCacheInstance();
        $cacheKey = $annotation . '.classbyannotations';
        $result = false;
        $classes = $cache->fetch($cacheKey, $result);
        if ($result === true) {
            self::$_classesAnnotated[$annotation] = $classes;
            return $classes;
        }
        return array();
    }

    /**
     * Returns all annotations for the given class.
     *
     * @param string $class Class name.
     *
     * @return string[]
     */
    public static function getClassAnnotations($class)
    {
        if (!self::$_withAnnotations) {
            return array();
        }
        if (isset(self::$_annotatedClasses[$class])) {
            return self::$_annotatedClasses[$class];
        }
        $cache = CacheLocator::getAnnotationsCacheInstance();
        $cacheKeyPfx = str_replace('\\', '_', $class);
        $cacheKey = $cacheKeyPfx . '.classannotations';
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
        $ret['class']['properties'] = array();
        foreach (self::getAnnotations($rClass->getDocComment()) as $annotation) {
            $name = $annotation->getName();
            $ret['class'][$name] = $annotation;
            if (!isset(self::$_classesAnnotated[$name])) {
                self::$_classesAnnotated[$name] = array();
            }
            self::$_classesAnnotated[$name][$class] = $class;
            $cacheKeyA = $name . '.classbyannotations';
            $cache->store($cacheKeyA, self::$_classesAnnotated[$name]);
        }
        foreach ($rClass->getProperties() as $property) {
            $propertyName = $property->getName();
            $ret['class']['properties'][$propertyName] = array();
            foreach (self::getAnnotations($property->getDocComment()) as $annotation) {
                $name = $annotation->getName();
                $ret['class']['properties'][$propertyName][$name] = $annotation;
            }
        }
        foreach ($rClass->getMethods() as $method) {
            $methodName = $method->getName();
            $ret[$methodName] = array();
            foreach (self::getAnnotations($method->getDocComment()) as $annotation) {
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
        if (isset(self::$_reflectionClasses[$class])) {
            return self::$_reflectionClasses[$class];
        }
        self::$_reflectionClasses[$class] = new \ReflectionClass($class);
        return self::$_reflectionClasses[$class];
    }

    public static function configure($withAnnotations)
    {
        self::$_withAnnotations = $withAnnotations;
    }

    /**
     * Returns a (cached) reflection class method.
     *
     * @param string $class  Class name.
     * @param string $method Method name.
     *
     * @throws ReflectionException
     * @return ReflectionClass
     */
    public static function getMethod($class, $method)
    {
        if (isset(self::$_reflectionMethods[$class][$method])) {
            return self::$_reflectionMethods[$class][$method];
        }
        if (!isset(self::$_reflectionMethods[$class])) {
            self::$_reflectionMethods[$class] = array();
        }
        self::$_reflectionMethods[$class][$method] = new \ReflectionMethod($class, $method);
        return self::$_reflectionMethods[$class][$method];
    }
}