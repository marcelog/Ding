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

use Ding\Annotation\Collection;
use Ding\Annotation\Parser;
use Ding\Cache\ICache;

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
     * @var \ReflectionClass[]
     */
    private $_reflectionClasses = array();
    /**
     * A map where the key is the class name.
     * @var string[]
     */
    private $_annotatedClasses = array();
    /**
     * A map where the key is the method name.
     * @var string[]
     */
    private $_annotatedMethods = array();
    /**
     * A map where the key is the property name.
     * @var string[]
     */
    private $_annotatedProperties = array();

    /**
     * A map where the key is the annotations, and the value is an array with
     * all the classes (not their methods) with this annotation.
     * @var string[]
     */
    private $_classesAnnotated = array();

    /**
     * Reflection methods, indexed by class.
     * @var \ReflectionMethod[]
     */
    private $_reflectionMethods = array();

    /**
     * Reflection properties, indexed by class.
     * @var \ReflectionProperty[]
     */
    private $_reflectionProperties = array();

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
     * The annotation parser used to get annotations from code blocks.
     * @var Parser
     */
    private $_annotationParser;

    /**
     * To inject the annotation parser.
     *
     * @param Parser $parser
     *
     * @return void
     */
    public function setAnnotationParser(Parser $parser)
    {
        $this->_annotationParser = $parser;
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
            $this->_classesAnnotated[$annotation] = $classes[$annotation];
            return $this->_classesAnnotated[$annotation];
        }
        return array();
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactory::getMethodAnnotations()
     */
    public function getMethodAnnotations($class, $method)
    {
        if (!$this->_withAnnotations) {
            return array();
        }
        $key = $class . $method;
        if (isset($this->_annotatedMethods[$key])) {
            return $this->_annotatedMethods[$key];
        }
        $cacheKey = $key . '.methodannotations';
        $result = false;
        $annotations = $this->_cache->fetch($cacheKey, $result);
        if ($result === true) {
            $this->_annotatedMethods[$key] = $annotations;
            return $annotations;
        }
        $rMethod = $this->getMethod($class, $method);
        $annotations = $this->_annotationParser->parse($rMethod->getDocComment());
        $this->_cache->store($cacheKey, $annotations);
        return $annotations;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactory::getPropertyAnnotations()
     */
    public function getPropertyAnnotations($class, $property)
    {
        if (!$this->_withAnnotations) {
            return array();
        }
        $key = $class . $property;
        if (isset($this->_annotatedProperties[$key])) {
            return $this->_annotatedProperties[$key];
        }
        $cacheKey = $key . '.propertyannotations';
        $result = false;
        $annotations = $this->_cache->fetch($cacheKey, $result);
        if ($result === true) {
            $this->_annotatedProperties[$key] = $annotations;
            return $annotations;
        }
        $rProperty = $this->getProperty($class, $property);
        $annotations = $this->_annotationParser->parse($rProperty->getDocComment());
        $this->_cache->store($cacheKey, $annotations);
        return $annotations;
    }

    /**
     * This one will populate the map indexed by annotations names, so we can
     * then get all classes with a particular annotation name.
     *
     * @param string $class A class name.
     * @param Collection $annotations The annotations to index.
     *
     * @return void
     */
    private function _populateClassesPerAnnotations($class, Collection $annotations)
    {
        foreach ($annotations->getAll() as $name => $annotation) {
            $cacheKey = $name . '.classbyannotations';
            if (!isset($this->_classesAnnotated[$name])) {
                $this->_classesAnnotated[$name] = array();
            }
            $this->_classesAnnotated[$name][$class] = $class;
            $this->_cache->store($cacheKey, $this->_classesAnnotated);
        }
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
        $cacheKey = $class . '.classannotations';
        $result = false;
        $annotations = $this->_cache->fetch($cacheKey, $result);
        if ($result === true) {
            $this->_annotatedClasses[$class] = $annotations;
            return $annotations;
        }
        $this->_annotatedClasses[$class] = array();
        $rClass = $this->getClass($class);
        $annotations = $this->_annotationParser->parse($rClass->getDocComment());
        $this->_populateClassesPerAnnotations($class, $annotations);
        $this->_annotatedClasses[$class] = $annotations;
        $this->_cache->store($cacheKey, $annotations);
        return $annotations;
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

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactory::getProperty()
     */
    public function getProperty($class, $property)
    {
        if (isset($this->_reflectionProperties[$class][$property])) {
            return $this->_reflectionProperties[$class][$property];
        }
        if (!isset($this->_reflectionProperties[$class])) {
            $this->_reflectionProperties[$class] = array();
        }
        $this->_reflectionProperties[$class][$property] = new \ReflectionProperty($class, $property);
        return $this->_reflectionProperties[$class][$property];
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactory::getClassAncestors()
     */
    public function getClassAncestors($class)
    {
        $ret = array();
        $rClass = $this->getClass($class);
        while($rClass = $rClass->getParentClass()) {
            $ret[] = $rClass->getName();
        }
        return $ret;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactory::getClassAncestorsAndInterfaces()
     */
    public function getClassAncestorsAndInterfaces($class)
    {
        $ret = array();
        $ret = $this->getClassAncestors($class);
        $ret = array_merge($ret, $this->getClass($class)->getInterfaceNames());
        return $ret;
    }
    /**
     * To inject the annotations cache.
     *
     * @param ICache $cache
     *
     * @return void
     */
    public function setCache(ICache $cache)
    {
        $this->_cache = $cache;
    }

    public function __construct($withAnnotations)
    {
        $this->_withAnnotations = $withAnnotations;
    }

}
