<?php
/**
 * So... php does not have such a thing.. and here's what it needs to be done
 * to have a proxy class and any kind of "dynamic class".
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Aspect
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
namespace Ding\Aspect;

use Ding\Reflection\IReflectionFactoryAware;
use Ding\Cache\ICache;
use Ding\Aspect\Interceptor\IDispatcher;
use Ding\Reflection\IReflectionFactory;

/**
 * So... php does not have such a thing.. and here's what it needs to be done
 * to have a proxy class and any kind of "dynamic class".
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Aspect
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 *
 * @todo Performance: Remove new MethodInvocation in proxied invocation.
 */
class Proxy implements IReflectionFactoryAware
{
    /**
     * A ReflectionFactory implementation
     * @var IReflectionFactory
     */
    protected $reflectionFactory;

    /**
     * Proxy cache implementation.
     * @var ICache
     */
    protected $cache = false;

    /**
     * Proxy class template (i.e: the dynamic class)
     * @var string
     */
    protected $proxyTemplate = <<<TEXT

use Ding\Aspect\Interceptor\IDispatcher;
use Ding\Aspect\MethodInvocation;
use Ding\Reflection\IReflectionFactory;

final class NEW_NAME extends CLASS_NAME {
    /**
     * Holds advice dispatcher.
     * @var Dispatcher
     */
    private static \$_dispatcher = false;
	private static \$_reflectionFactory = false;

    /**
	 * Used to recognice proxies along the container.
     * @var boolean
     */
    public static \$iAmADingProxy = true;

    /**
     * Clone this object.
     *
     * @return void
     */
    public function __clone()
    {
        self::\$_dispatcher = clone self::\$_dispatcher;
    }

    /**
     * This is used from the container to set the dispatcher for the aspects.
     *
     * @param IDispatcher \$dispatcher Advice dispatcher.
     *
     * @return void
     */
    public static function setDispatcher(IDispatcher \$dispatcher)
    {
        self::\$_dispatcher = \$dispatcher;
    }

    public static function setReflectionFactory(IReflectionFactory \$reflectionFactory)
    {
    	self::\$_reflectionFactory = \$reflectionFactory;
    }
    METHODS
}
TEXT;

    /**
     * Method template (i.e: effetively, the proxy methods).
     * @var string
     */
    protected $methodTemplate = <<<TEXT
    VISIBILITY ADDITIONAL function METHOD_NAME(METHOD_ARGS)
    {
        \$invocation = new MethodInvocation(
            'CLASS_NAME', 'METHOD_NAME', func_get_args(), \$this,
            self::\$_reflectionFactory
        );
        try
        {
        	return self::\$_dispatcher->invoke(\$invocation);
        } catch (Exception \$exception) {
            \$invocation->setException(\$exception);
        	return self::\$_dispatcher->invokeException(\$invocation);
        }
	}
TEXT;

    /**
     * This will return a proxy class source.
     *
     * @param string          $newName     Name for the proxy class.
     * @param ReflectionClass $targetClass Class to be proxied.
     *
     * @see Proxy::$proxyTemplate
     *
     * @return string
     */
    protected function createClass($newName, array $proxyMethods, \ReflectionClass $class)
    {
        $src = $this->proxyTemplate;
        $src = str_replace('NEW_NAME', $newName, $src);
        $src = str_replace('CLASS_NAME', $class->getName(), $src);
        $methods = array();
        foreach ($proxyMethods as $method) {
            if ($class->hasMethod($method)) {
                $methods[] = $this->createMethod($class->getMethod($method));
            }
        }
        $src = str_replace('METHODS', implode("\n", $methods), $src);
        return $src;
    }

    /**
     * This will return a full proxy-method-parameter source.
     *
     * @param \ReflectionParameter $parameter The method parameter.
     *
     * @see Proxy::$methodTemplate
     *
     * @return string
     */
    protected function createParameter(\ReflectionParameter $parameter)
    {
        $parameterSrc = '';
        $paramClass = $parameter->getClass();
        if ($parameter->isArray()) {
            $parameterSrc .= 'array ';
        } else if ($paramClass) {
            $parameterSrc .= $paramClass->getName() . ' ';
        }
        if ($parameter->isPassedByReference()) {
            $parameterSrc .= ' &';
        }
        $parameterSrc .= '$' . $parameter->getName();
        if ($parameter->isOptional()) {
            $parameterSrc .= '=';
            if ($parameter->getDefaultValue() === null) {
                $parameterSrc .= 'null';
            } else if ($parameter->getDefaultValue() === false) {
                $parameterSrc .= 'false';
            } else if ($parameter->getDefaultValue() === true) {
                $parameterSrc .= 'true';
            } else if (is_array($parameter->getDefaultValue())) {
                $parameterSrc .= 'array()';
            } else {
                $parameterSrc .= "'" . $parameter->getDefaultValue() . "'";
            }
        }
        return $parameterSrc;
    }

    /**
     * This will return a full proxy-method source.
     *
     * @param \ReflectionMethod $method The method to be proxied.
     *
     * @see Proxy::$methodTemplate
     *
     * @return string
     */
    protected function createMethod(\ReflectionMethod $method)
    {
        $visibility = '';
        $additional = '';
        $name = $method->getName();
        if ($method->isPublic()) {
            $visibility = ' public';
        } else if ($method->isProtected()) {
            $visibility = ' protected';
        } else if ($method->isPrivate()) {
            // useless really. $visibility = ' private';
            return '';
        }
        if ($method->isStatic()) {
            // useless really. $additional .= ' static ';
            return '';
        }
        //if ($method->isAbstract()) {
            // useless really. $$additional .= ' abstract ';
            //return '';
        //}
        if ($method->isConstructor()) {
            $name = '__construct';
        } else if ($method->isDestructor()) {
            $name = '__destruct';
        }
        $args = array();
        foreach ($method->getParameters() as $parameter) {
            $args[] = $this->createParameter($parameter);
        }

        $src = $this->methodTemplate;
        $src = str_replace('VISIBILITY', $visibility, $src);
        $src = str_replace('ADDITIONAL', $additional, $src);
        $src = str_replace('METHOD_NAME', $name, $src);
        $src = str_replace('METHOD_ARGS', implode(',', $args), $src);
        $src = str_replace(
        	'CLASS_NAME', $method->getDeclaringClass()->getName(), $src
        );
        return $src;
    }

    /**
     * Sets Proxy cache to use.
     *
     * @param ICache $cache
     *
     * @return void
     */
    public function setCache(ICache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactoryAware::setReflectionFactory()
     */
    public function setReflectionFactory(IReflectionFactory $reflectionFactory)
    {
        $this->reflectionFactory = $reflectionFactory;
    }
    /**
     * This will give you the name of a proxy class as a string. The class will
     * already exist in the vm.
     *
     * @return string
     */
    public function create($class, IDispatcher $dispatcher)
    {
        $subject = $this->reflectionFactory->getClass($class);
        $proxyClassName = 'Proxy' . str_replace('\\', '', $subject->getName());
        $cacheKey = $proxyClassName . '.proxy';
        $result = false;
        $src = $this->cache->fetch($cacheKey, $result);
        if (!$result) {
            $src = $this->createClass(
                $proxyClassName, $dispatcher->getMethodsIntercepted(), $subject
            );
            $this->cache->store($cacheKey, $src);
        }
        eval($src);
        $proxyClassName::setDispatcher($dispatcher);
        $proxyClassName::setReflectionFactory($this->reflectionFactory);
        return $proxyClassName;
    }
}
