<?php
/**
 * Dispatch information.
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
 * Dispatch information.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
class DispatchInfo
{
    /**
     * The action to be handled.
     * @var Action
     */
    public $action;

    /**
     * The handler object.
     * @var object
     */
    public $handler;

    /**
     * Method name to invoke on target
     * @var string
     */
    public $method;

    /**
     * The interceptors, if any.
     * @var object[]
     */
    public $interceptors;

    public function __construct(
        Action $action, $handler, $method, array $interceptors = array()
    ) {
        $this->action = $action;
        $this->handler = $handler;
        $this->method = $method;
        $this->interceptors = $interceptors;
    }
}
