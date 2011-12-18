<?php
namespace BookStore\Aspect;

use Ding\Aspect\MethodInvocation;
use Ding\Logger\ILoggerAware;

/**
 * @Aspect
 */
class Profiler implements ILoggerAware
{
    protected $logger;

    public function setLogger(\Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @MethodInterceptor(class-expression=.*AbstractService.*,expression=.*)
     */
    public function profileDomainServices(MethodInvocation $invocation)
    {
        $time = microtime(true);
        $originalInvocation = $invocation->getOriginalInvocation();
        $ret = $invocation->proceed();
        $total = microtime(true) - $time;
        $this->logger->debug(
        	"Execution of "
            . $originalInvocation->getClass() . "::" . $originalInvocation->getMethod()
            . " took: " . sprintf('%.5f', $total)
        );
        return $ret;
    }
}