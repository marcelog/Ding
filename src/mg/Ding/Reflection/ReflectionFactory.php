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

use Ding\Cache\ICache;
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
class ReflectionFactory implements IReflectionFactory
{
    /**
     * Cache reflection classes instantiated so far.
     * @var ReflectionClass[]
     */
    private $_reflectionClasses = array();
    /**
     * A map where the key is the class, and the value is an array with the
     * 'class annotations and its annotated methods.
     * @var string[]
     */
    private $_annotatedClasses = array();

    /**
     * A map where the key is the annotations, and the value is an array with
     * all the classes (not their methods) with this annotation.
     * @var string[]
     */
    private $_classesAnnotated = array();

    /**
     * Reflection methods, indexed by class.
     * @var string[]
     */
    private $_reflectionMethods = array();

    /**
     * Wether to use annotations or not.
     * @var boolean
     */
    private $_withAnnotations = false;

    /**
     * Annotations cache.
     * @var ICache
     */
    private $_cache = false;

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactory::getClassesFromCode()
     */
    public function getClassesFromCode($code)
    {
        // Taken from: http://stackoverflow.com/questions/928928/determining-what-classes-are-defined-in-a-php-class-file
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
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactory::getAnnotations()
     */
    public function getAnnotations($text)
    {
        $ret = array();
        if (preg_match_all('/@([^@\n\r\t]*)/', $text, $matches) > 0) {
            foreach ($matches[1] as $annotation) {
                $argsStart = strpos($annotation, '(');
                $arguments = array();
                if ($argsStart !== false) {
                    $argsEnd = strrpos($annotation, ')');
                    $argsLength = $argsEnd - $argsStart - 1;
                    $name = trim(substr($annotation, 0, $argsStart));
                    $args = trim(substr($annotation, $argsStart + 1, $argsLength));
                    $argsN = preg_match_all(
                    	'/([^=,]*)=[\s]*([\s]*"[^"]+"|\{[^\{\}]+\}|[^,"]*[\s]*)/', $args, $matches
                    );
                    if ($argsN > 0)
                    {
                        for ($i = 0; $i < $argsN; $i++) {
                            $key = trim($matches[1][$i]);
                            $value = str_replace('"', '', trim($matches[2][$i]));
                            if (strpos($value, '{') === 0) {
                                $value = substr($value, 1, -1);
                                $value = explode(',', $value);
                                foreach ($value as $k => $v) {
                                    $value[$k] = trim($v);
                                }
                            }
                            $arguments[$key] = $value;
                        }
                    }
                } else {
                    preg_match('/([a-zA-Z0-9]+)/', $annotation, $matches);
                    $name = $matches[1];
                }
                $ret[] = new BeanAnnotationDefinition($name, $arguments);
            }
        }
        return $ret;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactory::getClassesByAnnotation()
     */
    public function getClassesByAnnotation($annotation)
    {
        if (!$this->_withAnnotations) {
            return array();
        }
        if (isset($this->_classesAnnotated[$annotation])) {
            return $this->_classesAnnotated[$annotation];
        }
        $cacheKey = $annotation . '.classbyannotations';
        $result = false;
        $classes = $this->_cache->fetch($cacheKey, $result);
        if ($result === true) {
            $this->_classesAnnotated[$annotation] = $classes;
            return $classes;
        }
        return array();
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactory::getClassAnnotations()
     */
    public function getClassAnnotations($class)
    {
        if (!$this->_withAnnotations) {
            return array();
        }
        if (isset($this->_annotatedClasses[$class])) {
            return $this->_annotatedClasses[$class];
        }
        $cacheKeyPfx = str_replace('\\', '_', $class);
        $cacheKey = $cacheKeyPfx . '.classannotations';
        $result = false;
        $annotations = $this->_cache->fetch($cacheKey, $result);
        if ($result === true) {
            $this->_annotatedClasses[$class] = $annotations;
            return $annotations;
        }
        $this->_annotatedClasses[$class] = array();
        $rClass = $this->getClass($class);
        $ret = array();
        $ret['class'] = array();
        $ret['class']['properties'] = array();
        foreach ($this->getAnnotations($rClass->getDocComment()) as $annotation) {
            $name = $annotation->getName();
            $ret['class'][$name] = $annotation;
            if (!isset($this->_classesAnnotated[$name])) {
                $this->_classesAnnotated[$name] = array();
            }
            $this->_classesAnnotated[$name][$class] = $class;
            $cacheKeyA = $name . '.classbyannotations';
            $this->_cache->store($cacheKeyA, $this->_classesAnnotated[$name]);
        }
        foreach ($rClass->getProperties() as $property) {
            $propertyName = $property->getName();
            $ret['class']['properties'][$propertyName] = array();
            foreach ($this->getAnnotations($property->getDocComment()) as $annotation) {
                $name = $annotation->getName();
                $ret['class']['properties'][$propertyName][$name] = $annotation;
            }
        }
        foreach ($rClass->getMethods() as $method) {
            $methodName = $method->getName();
            $ret[$methodName] = array();
            foreach ($this->getAnnotations($method->getDocComment()) as $annotation) {
                $name = $annotation->getName();
                $ret[$methodName][$name] = $annotation;
            }
        }
        $this->_annotatedClasses[$class] = $ret;
        $this->_cache->store($cacheKey, $ret);
        return $ret;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactory::getClass()
     */
    public function getClass($class)
    {
        if (isset($this->_reflectionClasses[$class])) {
            return $this->_reflectionClasses[$class];
        }
        $this->_reflectionClasses[$class] = new \ReflectionClass($class);
        return $this->_reflectionClasses[$class];
    }

    public function __construct($withAnnotations)
    {
        $this->_withAnnotations = $withAnnotations;
    }

    public function setCache(ICache $cache)
    {
        $this->_cache = $cache;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactory::getMethod()
     */
    public function getMethod($class, $method)
    {
        if (isset($this->_reflectionMethods[$class][$method])) {
            return $this->_reflectionMethods[$class][$method];
        }
        if (!isset($this->_reflectionMethods[$class])) {
            $this->_reflectionMethods[$class] = array();
        }
        $this->_reflectionMethods[$class][$method] = new \ReflectionMethod($class, $method);
        return $this->_reflectionMethods[$class][$method];
    }
}
