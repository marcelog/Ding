<?php
/**
 * An http action. It adds a method (get, post, etc).
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
namespace Ding\Mvc\Http;

use Ding\Mvc\Action;

/**
 * An http action. It adds a method (get, post, etc).
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
class HttpAction extends Action
{
    /**
     * Method used to trigger this action.
     * @var string
     */
    private $_method;

    /**
     * Sets the method to trigger this action.
     *
     * @param string $method Method used to trigger this action.
     *
     * @return void
     */
    public function setMethod($method)
    {
        $this->_method = $method;
    }

    /**
     * Returns the method to trigger this action.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * Constructor.
     *
     * @param string $id        Url.
     * @param array  $arguments Arguments posted (or getted :P).
     *
     * @return void
     */
    public function __construct($id, array $arguments = array())
    {
        parent::__construct($id, $arguments);
        $this->_method = 'GET';
    }
}