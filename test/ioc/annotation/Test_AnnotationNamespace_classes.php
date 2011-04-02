<?php
namespace Some\Namespaces\Clazz;

/**
 * @Configuration
 */
class SomeNamespacedClass
{
    /**
     * @Bean(class=Some\Namespaces\Clazz\SomeOtherNamespacedClass)
     * @Scope(value=singleton)
     */
    public function aBeanFromANamespacedClass()
    {
        return new SomeOtherNamespacedClass();
    }
}

class SomeOtherNamespacedClass
{
    /**
     * @Resource
     */
    public $aBeanFromANamespacedClass;
}
