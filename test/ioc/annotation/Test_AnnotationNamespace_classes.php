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

    /**
     * @Bean(class=Some\Namespaces\Clazz\SomeOtherNamespacedClass2)
     * @Scope(value=singleton)
     */
    public function someOtherBean()
    {
        return new SomeOtherNamespacedClass2;
    }
}

class SomeOtherNamespacedClass
{
    /**
     * @Resource
     */
    public $someOtherBean;
}

class SomeOtherNamespacedClass2
{

}