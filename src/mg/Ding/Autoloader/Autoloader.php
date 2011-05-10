<?php
/**
 * Ding autoloader, you will surely need this.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Autoloader
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
namespace Ding\Autoloader;

// Check for log4php.
use Ding\Cache\Impl\DummyCacheImpl;
use Ding\Cache\ICache;

if (!class_exists('Logger')) {
    foreach (explode(PATH_SEPARATOR, ini_get('include_path')) as $path) {
        $truePath = implode(
            DIRECTORY_SEPARATOR,
            array($path, 'log4php', 'Logger.php')
        );
        if (file_exists($truePath)) {
            require_once $truePath;
        }
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
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
class Autoloader
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
     * Include path.
     * @var string[]
     */
    private static $_includePath;

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

    /**
     * Resolves a class name to a filesystem entry. False if none found.
     *
     * @param string $class Class name.
     *
     * @return string
     */
    private static function _resolve($class)
    {
        //if (strpos($class, 'Ding\\') !== 0) {
        //    return false;
        //}
        foreach (self::$_includePath as $path) {
            $file
                = $path
                . DIRECTORY_SEPARATOR
                . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php'
            ;
            if (file_exists($file)) {
                return $file;
            }
        }
        return false;
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
        if (self::$_cache !== false) {
            $result = false;
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
            array(__DIR__, '..', '..')
        );
        self::$_includePath = explode(PATH_SEPARATOR, ini_get('include_path'));
        return spl_autoload_register('\Ding\Autoloader\Autoloader::load');
    }
}
