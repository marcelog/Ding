<?php
/**
 * Advice definition.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Aspect
 * @subpackage Interceptor
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
namespace Ding\Aspect\Interceptor;

/**
 * Advice definition.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Aspect
 * @subpackage Interceptor
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
class AdviceDefinition
{

    /**
     * Holds the name for the method adviced.
     * @var string
     */
    private $_method;

    /**
     * The interceptor object.
     * @var object
     */
    private $_interceptor;

    /**
     * Holds the name for the advice method.
     * @var string
     */
    private $_interceptorMethod;

    /**
     * Returns the interceptor method.
     *
     * @return string
     */
    public function getInterceptorMethod()
    {
        return $this->_interceptorMethod;
    }

    /**
     * Returns the interceptor.
     *
     * @return object
     */
    public function getInterceptor()
    {
        return $this->_interceptor;
    }

    /**
     * Constructor.
     *
     * @param string $method            Method name.
     * @param object $interceptor       Interceptor object.
     * @param string $interceptorMethod Interceptor method name.
     *
     * @return void
     */
    public function __construct($method, $interceptor, $interceptorMethod)
    {
        $this->_method = $method;
        $this->_interceptor = $interceptor;
        $this->_interceptorMethod = $interceptorMethod;
    }
}