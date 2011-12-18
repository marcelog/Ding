<?php
/**
 * Http session "facade".
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Mvc
 * @subpackage Http
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
namespace Ding\HttpSession;

/**
 * Http session "facade".
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Mvc
 * @subpackage Http
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class HttpSession
{
    /**
     * Current instance.
     * @var HttpSession
     */
    private static $_instance = false;

    /**
     * Destroys the current session.
     *
     * @return void
     */
    public function destroy()
    {
        session_destroy();
    }

    /**
     * Returns true if this session contains this attribute.
     *
     * @param string $name
     *
     * @return boolean
     */
    public function hasAttribute($name)
    {
        return isset($_SESSION[$name]);
    }
    /**
     * Returns a previously saved session attribute with setAttribute().
     *
     * @param string $name Session attribute name.
     *
     * @return mixed
     */
    public function getAttribute($name)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
        return false;
    }

    /**
     * Saves an attribute to the session.
     *
     * @param string $name  Session attribute name.
     * @param mixed  $value Value.
     *
     * @return void
     */
    public function setAttribute($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Returns an instance of a session facade.
     *
     * @return HttpSession
     */
    public static function getSession()
    {
        if (self::$_instance === false) {
            self::$_instance = new HttpSession();
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    protected function __construct()
    {
        @session_start();
    }
}