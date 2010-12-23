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
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */
namespace Ding\Aspect;

use Ding\Aspect\Interceptor\IDispatcher;

/**
 * So... php does not have such a thing.. and here's what it needs to be done
 * to have a proxy class and any kind of "dynamic class".
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Aspect
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 * 
 * @todo Performance: Remove new MethodInvocation in proxied invocation.
 */
class Proxy
{
    /**
     * Number of proxy classes.
     * @var integer
     */
    private static $_proxyCount = 1;
    
    /**
     * Proxy class template (i.e: the dynamic class)
     * @var string
     */
    private static $_proxyTemplate = <<<TEXT

use Ding\Aspect\Interceptor\IDispatcher;
use Ding\Aspect\MethodInvocation;

final class NEW_NAME extends CLASS_NAME {
    /**
     * Holds advice dispatcher.
     * @var Dispatcher
     */
    private static \$_dispatcher = false;

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
    
    METHODS
}
TEXT;

    /**
     * Method template (i.e: effetively, the proxy methods).
     * @var string
     */
    private static $_methodTemplate = <<<TEXT
    VISIBILITY ADDITIONAL function METHOD_NAME(METHOD_ARGS)
    {
        \$invocation = new MethodInvocation(
            'CLASS_NAME', 'METHOD_NAME', func_get_args(), \$this
        );
        try
        {
        	return self::\$_dispatcher->invoke(\$invocation);
        } catch (Exception \$exception) {
            \$invocation->setException(\$exception);
        	self::\$_dispatcher->invokeException(\$invocation);
        }
	}
TEXT;

    /**
     * This will return a proxy class source.
     * 
     * @param string          $newName     Name for the proxy class.
     * @param ReflectionClass $targetClass Class to be proxied.
     * 
     * @see Proxy::$_proxyTemplate
     * 
     * @return string
     */
    private static function _createClass($newName, \ReflectionClass $class)
    {
        $src = self::$_proxyTemplate;
        $src = str_replace('NEW_NAME', $newName, $src);
        $src = str_replace('CLASS_NAME', $class->getName(), $src);
        $methods = array();
        foreach ($class->getMethods() as $method) {
            $methods[] = self::_createMethod($method);
        }
        $src = str_replace('METHODS', implode("\n", $methods), $src);
        return $src;
    }
    
    /**
     * This will return a full proxy-method-parameter source.
     * 
     * @param \ReflectionParameter $parameter The method parameter.
     * 
     * @see Proxy::$_methodTemplate
     * 
     * @return string
     */
    private static function _createParameter(\ReflectionParameter $parameter)
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
            if ($parameter->getDefaultValue() == null) {
                $parameterSrc .= 'null';
            } else {
                $parameterSrc .= $parameter->getDefaultValue();
            }
        }
        return $parameterSrc;
    }
    
    /**
     * This will return a full proxy-method source.
     * 
     * @param \ReflectionMethod $method The method to be proxied.
     * 
     * @see Proxy::$_methodTemplate
     * 
     * @return string
     */
    private static function _createMethod(\ReflectionMethod $method)
    {
        $visibility = '';
        $additional = '';
        $name = $method->getName();
        if ($method->isPublic()) {
            $visibility = ' public';
        } else if ($method->isProtected()) {
            $visibility = ' protected';
        } else if ($method->isPrivate()) {
            $visibility = ' private';
        }
        if ($method->isStatic()) {
            $additional .= ' static ';
        }
        if ($method->isAbstract()) {
            $additional .= ' abstract ';
        }
        if ($method->isConstructor()) {
            $name = '__construct';
        } else if ($method->isDestructor()) {
            $name = '__destruct';
        }
        $args = array();
        foreach ($method->getParameters() as $parameter) {
            $args[] = self::_createParameter($parameter);            
        }
        
        $src = self::$_methodTemplate;
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
     * This will give you a string for a new proxy class.
     * 
     * @param string      $class                Class to be proxied.
     * @param string      $cacheDir             Cache directory for classes.
     * @param IDispatcher $dispatcher           Dispatcher to invoke aspects.
     * @param array       $constructorArguments Constructor arguments.
     * 
     * @todo Currently, final classes can't be proxied because the proxy class
     * extends it (this may change in the near future).
     * 
     * @return string 
     */
    public static function create(
        $class, $cacheDir, IDispatcher $dispatcher = null
    ) {
        $subject = new \ReflectionClass($class);
        $proxyClassName = 'Proxy' . str_replace('\\', '', $subject->getName());
        $proxyFile = implode(
            DIRECTORY_SEPARATOR, array($cacheDir, $proxyClassName . '.php')
        );
        if (!file_exists($proxyFile)) {
            $src = self::_createClass($proxyClassName, $subject);
            file_put_contents($proxyFile, '<?php '  . $src);
        }
        include_once $proxyFile;
        if ($dispatcher != null) {
            $proxyClassName::setDispatcher($dispatcher);
        }
        self::$_proxyCount++;
        return $proxyClassName;
    }
}
