<?php
namespace Ding\Aspect\Interceptor;

use Ding\Aspect\MethodInvocation;

interface IMethodInterceptor
{
    public function invoke(MethodInvocation $invocation);
}