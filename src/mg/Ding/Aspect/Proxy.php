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

use Ding\Aspect\InterceptorDefinition;
use Ding\Aspect\MethodInvocation;

final class NEW_NAME extends CLASS_NAME {
    private static \$_interceptors = array();

    /**
     * This is used from the container to set the interceptors (aspects).
     *
     * @param InterceptorDefinition \$interceptor This holds the information
     * needed to call the advices. You can call this as many times as you want.
     *
     * @return void
     */
    public static function setInterceptor(
        InterceptorDefinition \$interceptor
    ) {
        self::\$_interceptors[\$interceptor->getTargetMethod()->getName()][]
            = \$interceptor
        ;
    }
    METHODS
}
TEXT;

    /**
     * Method template (i.e: effetively, the proxy methods).
     * @var string
     */
    private static $_methodTemplate = <<<TEXT
    VISIBILITY ADDITIONAL function METHOD_NAME()
    {
        if (isset(self::\$_interceptors['METHOD_NAME'])) {
            foreach (self::\$_interceptors['METHOD_NAME'] as \$interceptor) {
                \$invocation = new MethodInvocation(
                    __CLASS__, __METHOD__, func_get_args(), null
                );
                \$advice = \$interceptor->getInterceptorMethod();
                \$advice->invokeArgs(
                    \$interceptor->getObjectInterceptor(), array(\$invocation)
                );
            }
        }
        \$method = new \ReflectionMethod('CLASS_NAME', 'METHOD_NAME');
        return \$method->invokeArgs(\$this, func_get_args());
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
            $name = ' __construct(';
        } else if ($method->isDestructor()) {
            $name = ' __destruct';
        }
        $src = self::$_methodTemplate;
        $src = str_replace('VISIBILITY', $visibility, $src);
        $src = str_replace('ADDITIONAL', $additional, $src);
        $src = str_replace('METHOD_NAME', $name, $src);
        $src = str_replace(
        	'CLASS_NAME', $method->getDeclaringClass()->getName(), $src
        );
        return $src;
    }

    /**
     * This will give you an instance of a proxy class for any given class.
     * 
     * @param string $class Class to be proxied.
     * 
     * @todo Currently, final classes can't be proxied because the proxy class
     * extends it (this may change in the near future).
     * 
     * @return object 
     */
    public static function create($class)
    {
        $subject = new \ReflectionClass($class);
        $proxyClassName = 'Proxy' . $subject->getName()  . self::$_proxyCount;
        $src = self::_createClass($proxyClassName, $subject);
        eval($src);
        self::$_proxyCount++;
        return new $proxyClassName();
    }
}