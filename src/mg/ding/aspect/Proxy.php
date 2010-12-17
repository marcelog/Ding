<?php
namespace Ding;

class Proxy
{
    private static $_proxyCount = 1;
    private static $_proxyTemplate = <<<TEXT

final class NEW_NAME extends CLASS_NAME {
	private static \$_interceptors = array();

	public static function setInterceptor(Ding\InterceptorDefinition \$interceptor)
	{
		self::\$_interceptors[\$interceptor->getTargetMethod()->getName()][]
			= \$interceptor
		;
	}
	METHODS
}
TEXT;

    private static $_methodTemplate = <<<TEXT
	VISIBILITY ADDITIONAL function METHOD_NAME()
    {
    	if (isset(self::\$_interceptors['METHOD_NAME'])) {
    		foreach (self::\$_interceptors['METHOD_NAME'] as \$interceptor) {
    			\$invocation = new Ding\MethodInvocation(
    				__CLASS__, __METHOD__, func_get_args__, null
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
        $src = str_replace('CLASS_NAME', $method->getDeclaringClass()->getName(), $src);
        return $src;
    }
    
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