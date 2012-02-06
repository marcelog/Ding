<?php
/**
 * This driver will search for annotations.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Driver
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
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
namespace Ding\Bean\Factory\Driver;

use Ding\Reflection\IReflectionFactory;
use Ding\Reflection\IReflectionFactoryAware;
use Ding\Cache\ICache;


/**
 * This driver will search for annotations.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Driver
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class AnnotationDiscovererDriver implements IReflectionFactoryAware
{
    /**
     * A ReflectionFactory implementation.
     * @var IReflectionFactory
     */
    private $_reflectionFactory;

    /**
     * Annotations cache.
     * @var ICache
     */
    private $_cache;

    /**
     * Sets annotations cache.
     *
     * @param Ding\Cache\ICache $cache
     *
     * @return void
     */
    public function setCache(ICache $cache)
    {
        $this->_cache = $cache;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactoryAware::setReflectionFactory()
     */
    public function setReflectionFactory(IReflectionFactory $reflectionFactory)
    {
        $this->_reflectionFactory = $reflectionFactory;
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

    private function _getClassesFromFile($file)
    {
        $cacheKey = "$file.classesinfile";
        $result = false;
        $classes = $this->_cache->fetch($cacheKey, $result);
        if ($result === true) {
            return $classes;
        }
        $classes = array_merge(get_declared_classes(), get_declared_interfaces());
        require_once $file;
        $newClasses = array_diff(array_merge(get_declared_classes(), get_declared_interfaces()), $classes);
        if (empty($newClasses)) {
            foreach ($classes as $class) {
                $rClass = $this->_reflectionFactory->getClass($class);
                if ($rClass->getFileName() == $file) {
                    $newClasses[] = $rClass->getName();
                }
            }
        }
        $this->_cache->store($cacheKey, $newClasses);
        return $newClasses;
    }

    private function _getClassesFromDirectory($dir)
    {
        $cacheKey = "$dir.classesindir";
        $result = false;
        $classes = $this->_cache->fetch($cacheKey, $result);
        if ($result === true) {
            return $classes;
        }
        $classes = array();
        foreach ($this->_getCandidateFilesForClassScanning($dir) as $file) {
            $classes[$file] = $this->_getClassesFromFile($file);
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

    public function parse()
    {
        foreach ($this->_directories as $dir) {
            $classesPerFile = $this->_getClassesFromDirectory($dir);
            foreach ($classesPerFile as $file => $classes) {
                foreach ($classes as $class) {
                    $this->_reflectionFactory->getClassAnnotations($class);
                }
            }
        }
    }

    public function __construct(array $directories)
    {
        $this->_directories = $directories;
    }
}
