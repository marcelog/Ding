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
     * @var ReflectionClass[]
     */
    private $_reflectionClasses = array();
    /**
     * A map where the key is the class, and the value is an array with the
     * 'class annotations and its annotated methods.
     * @var string[]
     */
    private $_annotatedClasses = array();
    private $_annotatedMethods = array();
    private $_annotatedProperties = array();

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

    private $_annotationParser;

    public function setAnnotationParser(Parser $parser)
    {
        $this->_annotationParser = $parser;
    }

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
     * Returns all files elegible for scanning for classes.
     *
     * @param string $path Absolute path to a directory or filename.
     *
     * @return string[]
     */
    private function _getCandidateFilesForClassScanning($path)
    {
        $cacheKey = "$path.candidatefiles";
        $result = false;
        $files = $this->_cache->fetch($cacheKey, $result);
        if ($result === true) {
            return $files;
        }
        $files = array();
        if (is_dir($path)) {
            foreach (scandir($path) as $entry) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $entry = "$path/$entry";
                foreach ($this->_getCandidateFilesForClassScanning($entry) as $file) {
                    $files[] = $file;
                }
            }
        } else if ($this->_isScannable($path)) {
            $files[] = realpath($path);
        }
        $this->_cache->store($cacheKey, $files);
        return $files;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactory::getClassesFromFile()
     */
    public function getClassesFromFile($file)
    {
        $cacheKey = "$file.classesinfile";
        $result = false;
        $classes = $this->_cache->fetch($cacheKey, $result);
        if ($result === true) {
            return $classes;
        }
        $classes = $this->getClassesFromCode(@file_get_contents($file));
        $this->_cache->store($cacheKey, $classes);
        return $classes;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactory::getClassesFromDirectory()
     */
    public function getClassesFromDirectory($dir)
    {
        $cacheKey = "$dir.classesindir";
        $result = false;
        $classes = $this->_cache->fetch($cacheKey, $result);
        if ($result === true) {
            return $classes;
        }
        $classes = array();
        foreach ($this->_getCandidateFilesForClassScanning($dir) as $file) {
            $classes[$file] = $this->getClassesFromFile($file);
        }
        $this->_cache->store($cacheKey, $classes);
        return $classes;
    }
    /**
     * Returns true if the given filesystem entry is interesting to scan.
     *
     * @param string $path Filesystem entry.
     */
    private function _isScannable($path)
    {
        $extensionPos = strrpos($path, '.');
        if ($extensionPos === false) {
            return false;
        }
        if (substr($path, $extensionPos, 4) != '.php') {
            return false;
        }
        return true;
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

    public function setCache(ICache $cache)
    {
        $this->_cache = $cache;
    }

    public function __construct($withAnnotations)
    {
        $this->_withAnnotations = $withAnnotations;
    }

}
