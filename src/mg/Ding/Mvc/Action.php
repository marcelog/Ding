<?php
/**
 * Abstract action.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
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
namespace Ding\Mvc;

/**
 * Abstract action.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
abstract class Action
{
    /**
     * Action name, or id, url, path, etc.
     * @var string
     */
    private $_id;

    /**
     * Arguments to this action invocation.
     * @var array
     */
    private $_arguments;

    /**
     * Returns action id/name.
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Returns action arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->_arguments;
    }

    /**
     * Constructor.
     *
     * @param string $id        Action name/id/path/url/etc.
     * @param array  $arguments Action arguments.
     *
     * @return void
     */
    protected function __construct($id, array $arguments = array())
    {
        $this->_id = $id;
        $this->_arguments = $arguments;
    }
}